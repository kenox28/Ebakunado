<?php
/**
 * Daily Notifications API Endpoint
 *
 * This endpoint provides daily immunization notifications as an alternative to direct Supabase queries.
 * It handles the same logic as the cron job but through the Flutter app.
 *
 * Returns:
 * - Today immunizations
 * - Tomorrow immunizations
 * - Missed immunizations
 * - Checks notification_logs to prevent duplicates
 *
 * GET /php/supabase/users/get_daily_notifications.php
 *
 * Response format:
 * {
 *   "status": "success",
 *   "data": {
 *     "today": [...],
 *     "tomorrow": [...],
 *     "missed": [...]
 *   }
 * }
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Start session for authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit;
}

$currentUserId = $_SESSION['user_id'];

try {
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $notifications = [
        'today' => [],
        'tomorrow' => [],
        'missed' => []
    ];

    // Helper function to check if notification already sent
    function isNotificationAlreadySent($babyId, $type, $date) {
        global $currentUserId;
        $exists = supabaseSelect(
            'notification_logs',
            'id',
            [
                'baby_id' => $babyId,
                'user_id' => $currentUserId,
                'type' => $type,
                'notification_date' => $date
            ],
            null,
            1
        );
        return count($exists) > 0;
    }

    // Helper function to get child info
    function getChildInfo($babyId) {
        return supabaseSelect(
            'child_health_records',
            'baby_id,child_fname,child_lname',
            ['baby_id' => $babyId],
            null,
            1
        );
    }

    /**
     * Fetch schedules for a specific date considering both guideline and batch dates.
     */
    function fetchSchedulesForDate($targetDate) {
        $columns = 'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date';
        $merged = [];
        $seen = [];

        $bySchedule = supabaseSelect(
            'immunization_records',
            $columns,
            [
                'schedule_date' => $targetDate,
                'status' => 'scheduled'
            ],
            'schedule_date.asc'
        ) ?: [];

        $byBatch = supabaseSelect(
            'immunization_records',
            $columns,
            [
                'batch_schedule_date' => $targetDate,
                'status' => 'scheduled'
            ],
            'batch_schedule_date.asc'
        ) ?: [];

        foreach (array_merge($bySchedule, $byBatch) as $row) {
            if (isset($seen[$row['id']])) {
                continue;
            }
            $seen[$row['id']] = true;
            $merged[] = $row;
        }

        return $merged;
    }

    /**
     * Build the notification payload with batch-aware context.
     */
    function buildNotificationPayload($schedule, $childName, $type, $targetDate, $whenLabel) {
        $guidelineDate = $schedule['schedule_date'] ?? null;
        $batchDate = $schedule['batch_schedule_date'] ?? null;
        $isBatch = !empty($batchDate);
        $dateLabel = $isBatch ? 'batch' : 'guideline';

        switch ($type) {
            case 'today':
                $message = $isBatch
                    ? "$childName has a batch schedule for {$schedule['vaccine_name']} $whenLabel"
                    : "$childName has {$schedule['vaccine_name']} scheduled $whenLabel";
                break;
            case 'tomorrow':
                $message = $isBatch
                    ? "$childName has a batch schedule for {$schedule['vaccine_name']} $whenLabel"
                    : "$childName has {$schedule['vaccine_name']} scheduled $whenLabel";
                break;
            case 'missed':
                $prettyDate = $targetDate ?? ($batchDate ?? $guidelineDate ?? 'the previous date');
                $message = $isBatch
                    ? "$childName missed the batch schedule for {$schedule['vaccine_name']} on $prettyDate"
                    : "$childName missed {$schedule['vaccine_name']} scheduled on $prettyDate";
                break;
            default:
                $message = "$childName has {$schedule['vaccine_name']} updates";
        }

        return [
            'baby_id' => $schedule['baby_id'],
            'child_name' => $childName,
            'vaccine_name' => $schedule['vaccine_name'],
            'dose_number' => $schedule['dose_number'],
            'guideline_date' => $guidelineDate,
            'batch_schedule_date' => $batchDate,
            'target_date' => $targetDate,
            'date_source' => $dateLabel,
            'message' => $message,
            'type' => $type
        ];
    }

    // Check for TODAY's immunizations
    $todaySchedules = fetchSchedulesForDate($today);

    foreach ($todaySchedules as $schedule) {
        $childInfo = getChildInfo($schedule['baby_id']);
        if (count($childInfo) > 0) {
            $child = $childInfo[0];
            $childName = $child['child_fname'] . ' ' . $child['child_lname'];

            $targetDate = $schedule['batch_schedule_date'] ?? $schedule['schedule_date'];
            if (!isNotificationAlreadySent($schedule['baby_id'], 'schedule_same_day', $targetDate ?? $today)) {
                $notifications['today'][] = buildNotificationPayload(
                    $schedule,
                    $childName,
                    'today',
                    $targetDate ?? $today,
                    'today'
                );
            }
        }
    }

    // Check for TOMORROW's immunizations
    $tomorrowSchedules = fetchSchedulesForDate($tomorrow);

    foreach ($tomorrowSchedules as $schedule) {
        $childInfo = getChildInfo($schedule['baby_id']);
        if (count($childInfo) > 0) {
            $child = $childInfo[0];
            $childName = $child['child_fname'] . ' ' . $child['child_lname'];

            $targetDate = $schedule['batch_schedule_date'] ?? $schedule['schedule_date'];
            if (!isNotificationAlreadySent($schedule['baby_id'], 'schedule_reminder', $targetDate ?? $tomorrow)) {
                $notifications['tomorrow'][] = buildNotificationPayload(
                    $schedule,
                    $childName,
                    'tomorrow',
                    $targetDate ?? $tomorrow,
                    'tomorrow'
                );
            }
        }
    }

    // Check for MISSED immunizations
    $missedSchedules = supabaseSelect(
        'immunization_records',
        'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date',
        [
            'status' => 'scheduled'
        ],
        'schedule_date.asc'
    ) ?: [];

    foreach ($missedSchedules as $schedule) {
        $targetDate = $schedule['batch_schedule_date'] ?? $schedule['schedule_date'];

        // Only include if schedule date is before today
        if ($targetDate && $targetDate < $today) {
            $childInfo = getChildInfo($schedule['baby_id']);
            if (count($childInfo) > 0) {
                $child = $childInfo[0];
                $childName = $child['child_fname'] . ' ' . $child['child_lname'];

                if (!isNotificationAlreadySent($schedule['baby_id'], 'missed_schedule', $targetDate)) {
                    $notifications['missed'][] = buildNotificationPayload(
                        $schedule,
                        $childName,
                        'missed',
                        $targetDate,
                        $targetDate
                    );
                }
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Daily notifications retrieved successfully',
        'data' => $notifications,
        'user_id' => $currentUserId,
        'today_date' => $today,
        'tomorrow_date' => $tomorrow
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'user_id' => $currentUserId ?? null
    ]);
}
?>
