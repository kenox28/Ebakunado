<?php
/**
 * Get Notifications for Midwives
 * Retrieves comprehensive system notifications (same as BHW)
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Check if user is midwife
if (!isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Midwife ID not found in session']);
    exit();
}

$midwife_id = $_SESSION['midwife_id'];
$notifications = [];
$readIds = isset($_SESSION['midwife_read_notifications']) && is_array($_SESSION['midwife_read_notifications']) ? $_SESSION['midwife_read_notifications'] : [];

try {
    // 1. Pending Child Health Record Approvals
    $pending_chr = supabaseSelect('child_health_records', 'id,baby_id,child_fname,child_lname,date_created', ['status' => 'pending'], 'date_created.desc', 10);
    
    if ($pending_chr && count($pending_chr) > 0) {
        $pending_count = count($pending_chr);
        if ($pending_count === 1) {
            $chr = $pending_chr[0];
            $notifications[] = [
                'id' => 'chr_pending_' . $chr['id'],
                'type' => 'pending_approval',
                'priority' => 'high',
                'title' => 'Pending Child Record Approval',
                'message' => 'Child record for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' needs approval',
                'action' => 'Review and approve',
                'action_url' => './pending-approval.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($chr['date_created'])),
                'unread' => true,
                'icon' => '⏳'
            ];
        } else {
            $notifications[] = [
                'id' => 'multiple_pending_chr',
                'type' => 'pending_approval',
                'priority' => 'high',
                'title' => 'Multiple Pending Child Records',
                'message' => $pending_count . ' child health records are pending approval',
                'action' => 'Review all pending records',
                'action_url' => './pending-approval.php',
                'timestamp' => date('Y-m-d H:i:s'),
                'unread' => true,
                'icon' => '📋'
            ];
        }
    }
    
    // 2. Pending CHR DOC requests from users (Transfer/School) - Midwives handle these
    $pending_chr_docs = supabaseSelect('chrdocrequest', 'id,baby_id,request_type,created_at,status', ['status' => 'pendingCHR'], 'created_at.desc', 20);
    if ($pending_chr_docs) {
        $child_lookup = [];
        foreach ($pending_chr_docs as $doc) {
            $bid = $doc['baby_id'];
            if (!isset($child_lookup[$bid])){
                $c = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $bid], null, 1);
                $child_lookup[$bid] = ($c && count($c)>0) ? trim(($c[0]['child_fname']??'').' '.($c[0]['child_lname']??'')) : $bid;
            }
            $rtype = strtolower((string)($doc['request_type'] ?? ''));
            $label = $rtype === 'transfer' ? 'Transfer Copy' : ($rtype === 'school' ? 'School Copy' : 'CHR Document');
            $notifications[] = [
                'id' => 'chrreq_' . $doc['id'],
                'type' => 'chr_pending',
                'priority' => 'high',
                'title' => 'CHR request pending (' . $label . ')',
                'message' => 'From ' . ($child_lookup[$bid] ?? $bid),
                'action' => 'Open CHR Doc Requests',
                'action_url' => './chr-doc-requests.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($doc['created_at'] ?? date('Y-m-d H:i:s'))),
                'unread' => true,
                'icon' => '📄'
            ];
        }
    }
    
    // 3. Today's Immunization Schedules
    $today = date('Y-m-d');
    $today_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,status', 
        ['schedule_date' => $today], 
        null, 10
    );
    
    if ($today_schedules) {
        $today_schedules = array_filter($today_schedules, function($schedule) {
            return $schedule['status'] !== 'completed';
        });
    }

    if ($today_schedules && count($today_schedules) > 0) {
        foreach ($today_schedules as $schedule) {
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $schedule['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            if ($child_info && count($child_info) > 0) {
                $child_name = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            }
            
            $notifications[] = [
                'id' => 'today_schedule_' . $schedule['id'],
                'type' => 'today_schedule',
                'priority' => 'medium',
                'title' => 'Today\'s Vaccination Schedule',
                'message' => $schedule['vaccine_name'] . ' (Dose ' . $schedule['dose_number'] . ') for ' . $child_name,
                'action' => 'View immunization schedule',
                'action_url' => './immunization.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($schedule['schedule_date'])),
                'unread' => true,
                'icon' => '💉'
            ];
        }
    }

    // 4. Tomorrow's Immunization Schedules
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $tomorrow_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,status', 
        ['schedule_date' => $tomorrow], 
        null, 5
    );
    
    if ($tomorrow_schedules) {
        $tomorrow_schedules = array_filter($tomorrow_schedules, function($schedule) {
            return $schedule['status'] !== 'completed';
        });
    }

    if ($tomorrow_schedules && count($tomorrow_schedules) > 0) {
        $notification_count = count($tomorrow_schedules);
        $notifications[] = [
            'id' => 'tomorrow_schedule_summary',
            'type' => 'tomorrow_schedule',
            'priority' => 'low',
            'title' => 'Tomorrow\'s Vaccination Schedule',
            'message' => $notification_count . ' vaccination(s) scheduled for tomorrow',
            'action' => 'View tomorrow\'s schedule',
            'action_url' => './immunization.php',
            'timestamp' => $tomorrow,
            'unread' => true,
            'icon' => '📅'
        ];
    }

    // 5. Missed Immunization Schedules
    $missed_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,status', 
        ['schedule_date.lt' => $today], 
        null, 10
    );
    
    if ($missed_schedules) {
        $missed_schedules = array_filter($missed_schedules, function($schedule) {
            return $schedule['status'] !== 'completed';
        });
    }

    if ($missed_schedules && count($missed_schedules) > 0) {
        foreach ($missed_schedules as $missed) {
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $missed['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            if ($child_info && count($child_info) > 0) {
                $child_name = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            }
            
            $days_missed = (strtotime($today) - strtotime($missed['schedule_date'])) / (60 * 60 * 24);
            $notifications[] = [
                'id' => 'missed_schedule_' . $missed['id'],
                'type' => 'missed_schedule',
                'priority' => 'urgent',
                'title' => 'Missed Vaccination Schedule',
                'message' => $missed['vaccine_name'] . ' (Dose ' . $missed['dose_number'] . ') for ' . $child_name . ' - ' . round($days_missed) . ' days overdue',
                'action' => 'Schedule catch-up vaccination',
                'action_url' => './immunization.php',
                'timestamp' => date('Y-m-d H:i:s', strtotime($missed['schedule_date'])),
                'unread' => true,
                'icon' => '⚠️'
            ];
        }
    }

    // 6. Upcoming Schedules (next 7 days)
    $next_week = date('Y-m-d', strtotime('+7 days'));
    $upcoming_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,status', 
        ['schedule_date.gte' => $tomorrow, 'schedule_date.lte' => $next_week], 
        null, 10
    );
    
    if ($upcoming_schedules) {
        $upcoming_schedules = array_filter($upcoming_schedules, function($schedule) {
            return $schedule['status'] !== 'completed';
        });
    }

    if ($upcoming_schedules && count($upcoming_schedules) > 0) {
        $upcoming_count = count($upcoming_schedules);
        $notifications[] = [
            'id' => 'upcoming_schedules_week',
            'type' => 'upcoming_schedules',
            'priority' => 'medium',
            'title' => 'Upcoming Vaccinations This Week',
            'message' => $upcoming_count . ' vaccination(s) scheduled for the next 7 days',
            'action' => 'View upcoming schedules',
            'action_url' => './immunization.php',
            'timestamp' => $tomorrow,
            'unread' => true,
            'icon' => '📅'
        ];
    }

    // 7. Overdue Vaccinations (missed for more than 30 days)
    $overdue_date = date('Y-m-d', strtotime('-30 days'));
    $overdue_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,status', 
        ['schedule_date.lt' => $overdue_date], 
        null, 5
    );
    
    if ($overdue_schedules) {
        $overdue_schedules = array_filter($overdue_schedules, function($schedule) {
            return $schedule['status'] !== 'completed';
        });
    }

    if ($overdue_schedules && count($overdue_schedules) > 0) {
        $overdue_count = count($overdue_schedules);
        $notifications[] = [
            'id' => 'overdue_vaccinations',
            'type' => 'overdue_vaccinations',
            'priority' => 'urgent',
            'title' => 'Overdue Vaccinations (30+ days)',
            'message' => $overdue_count . ' vaccination(s) overdue for more than 30 days',
            'action' => 'Review overdue vaccinations',
            'action_url' => './immunization.php',
            'timestamp' => $overdue_date,
            'unread' => true,
            'icon' => '🚨'
        ];
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
        if (in_array('ALL', $readIds, true) || in_array($n['id'], $readIds, true)) { 
            $n['unread'] = false; 
        }
    }
    unset($n);

    // Count unread notifications
    $unread_count = count(array_filter($notifications, function($n) { return !empty($n['unread']); }));
    
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
