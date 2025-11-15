<?php
/**
 * Cron Job: Update Missed Immunizations
 *
 * Marks overdue immunization schedules as "missed" and assigns a catch-up date
 * seven (7) days after the original schedule. Mirrors the logic used when
 * fetching immunization records so that catch-up dates are available even
 * without a manual refresh from the app.
 *
 * Suggested cron entry (runs daily at 1:00 AM Philippines time):
 * 0 1 * * * /usr/bin/php /path/to/ebakunado/cron/update_missed_immunizations.php
 */

date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../database/SupabaseConfig.php';
require_once __DIR__ . '/../database/DatabaseHelper.php';

$logFile = __DIR__ . '/../logs/update_missed_immunizations_' . date('Y-m-d') . '.log';

/**
 * Write a message to the cron log file (and echo to stdout).
 */
function logMessage(string $message): void
{
    global $logFile;

    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;

    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    echo $entry;
}

logMessage('=== Update Missed Immunizations Cron Started ===');

try {
    $today = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $todayStr = $today->format('Y-m-d');

    $batchSize = 1000;
    $offset = 0;
    $statusesToCheck = ['scheduled', 'pending', 'missed', 'rescheduled'];

    $processed = 0;
    $updated = 0;
    $skipped = 0;
    $errors = 0;

    do {
        $records = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date',
            ['status' => $statusesToCheck],
            'schedule_date.asc',
            $batchSize,
            $offset
        );

        if ($records === false) {
            logMessage('ERROR: Unable to fetch immunization records from Supabase.');
            throw new RuntimeException('Supabase select returned false');
        }

        if (!is_array($records) || count($records) === 0) {
            break;
        }

        logMessage('Processing batch of ' . count($records) . ' records (offset ' . $offset . ').');

        foreach ($records as $record) {
            $processed++;

            $status = strtolower(trim($record['status'] ?? ''));
            $dateGiven = $record['date_given'] ?? null;
            $scheduleDate = $record['schedule_date'] ?? null;
            $catchUpDate = $record['catch_up_date'] ?? null;
            $baseDate = $scheduleDate ?: $catchUpDate;

            if (empty($baseDate)) {
                $skipped++;
                continue;
            }

            if (!empty($dateGiven)) {
                $skipped++;
                continue;
            }

            if ($baseDate >= $todayStr) {
                $skipped++;
                continue;
            }

            if ($status === 'completed' || $status === 'taken') {
                $skipped++;
                continue;
            }

            if ($status === 'missed' && !empty($catchUpDate)) {
                $skipped++;
                continue;
            }

            try {
                $base = new DateTime($baseDate, new DateTimeZone('Asia/Manila'));
                $catchUpFromSched = $base->modify('+7 day')->format('Y-m-d');

                $updateData = [
                    'status' => 'missed',
                    'catch_up_date' => $catchUpFromSched
                ];

                $updateResult = supabaseUpdate('immunization_records', $updateData, ['id' => $record['id']]);

                if ($updateResult === false) {
                    $errors++;
                    logMessage('ERROR: Failed to update record ID ' . $record['id']);
                    continue;
                }

                $updated++;
                logMessage(sprintf(
                    'Updated record ID %s (baby: %s, vaccine: %s dose %s) -> catch-up %s.',
                    $record['id'],
                    $record['baby_id'] ?? 'N/A',
                    $record['vaccine_name'] ?? 'N/A',
                    $record['dose_number'] ?? 'N/A',
                    $catchUpFromSched
                ));

            } catch (Exception $ex) {
                $errors++;
                logMessage('ERROR: ' . $ex->getMessage() . ' (record ID ' . $record['id'] . ')');
            }
        }

        $offset += $batchSize;

    } while (count($records) === $batchSize);

    logMessage("Summary: processed=$processed updated=$updated skipped=$skipped errors=$errors");
    logMessage('=== Update Missed Immunizations Cron Completed ===');

} catch (Exception $e) {
    logMessage('FATAL: ' . $e->getMessage());
    logMessage('=== Update Missed Immunizations Cron Aborted ===');
    exit(1);
}

exit(0);
?>

