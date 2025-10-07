<?php
// Suppress all PHP warnings and errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

$user_id = $_SESSION['user_id'];
$notifications = [];
$readIds = isset($_SESSION['read_notifications']) && is_array($_SESSION['read_notifications']) ? $_SESSION['read_notifications'] : [];

try {
    // 1. Pending Child Health Record Approvals
    $pending_chr = supabaseSelect('child_health_records', 'id,baby_id,child_fname,child_lname,date_created', ['user_id' => $user_id, 'status' => 'pending'], 'date_created.desc', 10);

    if ($pending_chr && count($pending_chr) > 0) {
        foreach ($pending_chr as $chr) {
            $notifications[] = [
                'id' => 'user_pending_' . $chr['id'],
                'type' => 'pending_approval',
                'priority' => 'high',
                'title' => 'Pending Approval',
                'message' => 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' is pending BHW approval',
                'action' => 'View pending requests',
                'action_url' => './Request.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($chr['date_created'])),
                'unread' => true,
                'icon' => '⏳'
            ];
        }
    }

    // 2. Today's Vaccination Schedules
    $today = date('Y-m-d');
    $children = supabaseSelect('child_health_records', 'baby_id,child_fname,child_lname', ['user_id' => $user_id, 'status' => 'accepted']);
    
    if ($children) {
        $baby_ids = array_column($children, 'baby_id');
        $children_lookup = [];
        foreach ($children as $child) {
            $children_lookup[$child['baby_id']] = $child['child_fname'] . ' ' . $child['child_lname'];
        }

        foreach ($baby_ids as $baby_id) {
            $today_schedules = supabaseSelect('immunization_records', 
                'id,vaccine_name,dose_number,schedule_date,status', 
                ['baby_id' => $baby_id, 'schedule_date' => $today], 
                null, 10
            );
            
            // Filter out completed ones
            if ($today_schedules) {
                $today_schedules = array_filter($today_schedules, function($schedule) {
                    return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
                });
            }

            if ($today_schedules) {
                foreach ($today_schedules as $schedule) {
                    $child_name = $children_lookup[$baby_id] ?? 'Unknown Child';
                    $notifications[] = [
                        'id' => 'today_schedule_' . $schedule['id'],
                        'type' => 'today_schedule',
                        'priority' => 'high',
                        'title' => 'Today\'s Vaccination',
                        'message' => $schedule['vaccine_name'] . ' (Dose ' . $schedule['dose_number'] . ') for ' . $child_name . ' is scheduled today',
                        'action' => 'View today\'s schedule',
                        'action_url' => './upcoming_schedule.php',
                        'timestamp' => date('Y-m-d H:i:s', strtotime($schedule['schedule_date'])),
                        'unread' => true,
                        'icon' => '💉'
                    ];
                }
            }
        }
    }

    // 3. Tomorrow's Vaccination Schedules
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    if ($children) {
        foreach ($baby_ids as $baby_id) {
            $tomorrow_schedules = supabaseSelect('immunization_records', 
                'id,vaccine_name,dose_number,schedule_date,status', 
                ['baby_id' => $baby_id, 'schedule_date' => $tomorrow], 
                null, 5
            );
            
            // Filter out completed ones
            if ($tomorrow_schedules) {
                $tomorrow_schedules = array_filter($tomorrow_schedules, function($schedule) {
                    return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
                });
            }

            if ($tomorrow_schedules) {
                foreach ($tomorrow_schedules as $schedule) {
                    $child_name = $children_lookup[$baby_id] ?? 'Unknown Child';
                    $notifications[] = [
                        'id' => 'tomorrow_schedule_' . $schedule['id'],
                        'type' => 'tomorrow_schedule',
                        'priority' => 'medium',
                        'title' => 'Tomorrow\'s Vaccination',
                        'message' => $schedule['vaccine_name'] . ' (Dose ' . $schedule['dose_number'] . ') for ' . $child_name . ' is scheduled tomorrow',
                        'action' => 'View upcoming schedule',
                        'action_url' => './upcoming_schedule.php',
                        'timestamp' => date('Y-m-d H:i:s', strtotime($schedule['schedule_date'])),
                        'unread' => true,
                        'icon' => '📅'
                    ];
                }
            }
        }
    }

    // 4. Missed Vaccination Schedules
    if ($children) {
        foreach ($baby_ids as $baby_id) {
            $missed_schedules = supabaseSelect('immunization_records', 
                'id,vaccine_name,dose_number,schedule_date,status', 
                ['baby_id' => $baby_id, 'schedule_date.lt' => $today], 
                null, 10
            );
            
            // Filter out completed ones
            if ($missed_schedules) {
                $missed_schedules = array_filter($missed_schedules, function($schedule) {
                    return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
                });
            }

            if ($missed_schedules) {
                foreach ($missed_schedules as $missed) {
                    $child_name = $children_lookup[$baby_id] ?? 'Unknown Child';
                    $days_missed = (strtotime($today) - strtotime($missed['schedule_date'])) / (60 * 60 * 24);
                    $notifications[] = [
                        'id' => 'missed_schedule_' . $missed['id'],
                        'type' => 'missed_schedule',
                        'priority' => 'urgent',
                        'title' => 'Missed Vaccination',
                        'message' => $missed['vaccine_name'] . ' (Dose ' . $missed['dose_number'] . ') for ' . $child_name . ' - ' . round($days_missed) . ' days overdue',
                        'action' => 'View missed vaccinations',
                        'action_url' => './missed_immunization.php',
                        'timestamp' => date('Y-m-d H:i:s', strtotime($missed['schedule_date'])),
                        'unread' => true,
                        'icon' => '⚠️'
                    ];
                }
            }
        }
    }

    // 5. Recent Activity (approved/rejected requests)
    $recent_activities = supabaseSelect('child_health_records', 
        'id,child_fname,child_lname,status,date_updated,date_created', 
        ['user_id' => $user_id], 
        'date_updated.desc', 5
    );

    if ($recent_activities) {
        foreach ($recent_activities as $activity) {
            $activity_type = 'info';
            $title = 'Child Registration Update';
            $message = 'Child registration for ' . $activity['child_fname'] . ' ' . $activity['child_lname'];
            $icon = '📋';
            
            if ($activity['status'] === 'accepted') {
                $activity_type = 'approval';
                $title = 'Registration Approved';
                $message = 'Child registration for ' . $activity['child_fname'] . ' ' . $activity['child_lname'] . ' has been approved by BHW';
                $icon = '✅';
            } elseif ($activity['status'] === 'rejected') {
                $activity_type = 'rejection';
                $title = 'Registration Rejected';
                $message = 'Child registration for ' . $activity['child_fname'] . ' ' . $activity['child_lname'] . ' has been rejected';
                $icon = '❌';
            }

            $notifications[] = [
                'id' => 'activity_' . $activity['id'],
                'type' => $activity_type,
                'priority' => 'low',
                'title' => $title,
                'message' => $message,
                'action' => 'View dashboard',
                'action_url' => './home.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($activity['date_updated'] ?: $activity['date_created'])),
                'unread' => true,
                'icon' => $icon
            ];
        }
    }

    // 6. Approved CHR document requests (Transfer / School)
    $approved_chr_docs = supabaseSelect(
        'chrdocrequest',
        'id,baby_id,request_type,approved_at,status',
        ['user_id' => $user_id, 'status' => 'approved'],
        'approved_at.desc',
        10
    );

    if ($approved_chr_docs) {
        // Build a lookup for child names to enrich messages
        $child_lookup = [];
        if (!isset($children) || !$children) {
            $children = supabaseSelect('child_health_records', 'baby_id,child_fname,child_lname', ['user_id' => $user_id]);
        }
        if ($children) {
            foreach ($children as $c) {
                $child_lookup[$c['baby_id']] = trim(($c['child_fname'] ?? '') . ' ' . ($c['child_lname'] ?? ''));
            }
        }

        foreach ($approved_chr_docs as $doc) {
            $rtype = strtolower((string)($doc['request_type'] ?? ''));
            $label = $rtype === 'transfer' ? 'Transfer Copy' : ($rtype === 'school' ? 'School Copy' : 'CHR Document');
            $child_name = $child_lookup[$doc['baby_id']] ?? $doc['baby_id'];
            $notifications[] = [
                'id' => 'chrdoc_' . $doc['id'],
                'type' => 'chr_approved',
                'priority' => 'high',
                'title' => 'CHR request approved (' . $label . ')',
                'message' => 'For ' . $child_name,
                'action' => 'Download ' . $label,
                'action_url' => './approved_requests.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($doc['approved_at'] ?? date('Y-m-d H:i:s'))),
                'unread' => true,
                'icon' => '📄'
            ];
        }
    }

    // Sort notifications by priority and timestamp
    usort($notifications, function($a, $b) {
        $priority_order = ['urgent' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
        $a_priority = $priority_order[$a['priority']] ?? 5;
        $b_priority = $priority_order[$b['priority']] ?? 5;

        if ($a_priority === $b_priority) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        }
        return $a_priority - $b_priority;
    });

    // Apply read state from session
    foreach ($notifications as &$n) {
        if (in_array('ALL', $readIds, true) || in_array($n['id'], $readIds, true)) { $n['unread'] = false; }
    }
    unset($n);

    // Count unread notifications
    $unread_count = count(array_filter($notifications, function($n) { return !empty($n['unread']); }));

    // Set proper JSON header
    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'data' => [
            'notifications' => $notifications,
            'unread_count' => $unread_count,
            'total_count' => count($notifications)
        ]
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch notifications: ' . $e->getMessage()
    ]);
}
?>
