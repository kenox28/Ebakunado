<?php
// BHW notifications with actual pending requests
header('Content-Type: application/json');

try {
    // Check session
    session_start();
    
    if (!isset($_SESSION['bhw_id'])) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Unauthorized - BHW ID not found in session',
            'session_data' => array_keys($_SESSION)
        ]);
        exit();
    }
    
    require_once __DIR__ . '/../../../database/SupabaseConfig.php';
    require_once __DIR__ . '/../../../database/DatabaseHelper.php';
    
    $notifications = [];
    
    // 1. Check for recent child health records (last 24 hours)
    $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $recent_chr = supabaseSelect('child_health_records', 
        'id,baby_id,child_fname,child_lname,date_created', 
        ['date_created.gte' => $yesterday], 
        'date_created.desc', 
        10
    );
    
    if ($recent_chr && count($recent_chr) > 0) {
        foreach ($recent_chr as $chr) {
            $time_ago = time() - strtotime($chr['date_created']);
            $time_text = '';
            if ($time_ago < 3600) {
                $time_text = floor($time_ago / 60) . ' minutes ago';
            } else if ($time_ago < 86400) {
                $time_text = floor($time_ago / 3600) . ' hours ago';
            } else {
                $time_text = floor($time_ago / 86400) . ' days ago';
            }
            
            $notifications[] = [
                'id' => 'new_chr_' . $chr['id'],
                'type' => 'new_request',
                'priority' => 'high',
                'title' => 'New Child Health Record',
                'message' => 'Child record for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' submitted ' . $time_text,
                'action' => 'Review child record',
                'action_url' => './child_health_record.php?baby_id=' . $chr['baby_id'],
                'timestamp' => $chr['date_created'],
                'unread' => true,
                'icon' => '👶'
            ];
        }
    }
    
    // 2. Check for today's immunization schedules
    $today = date('Y-m-d');
    $today_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $today, 'status' => 'scheduled'], 
        'schedule_date.asc', 
        10
    );
    
    if ($today_schedules && count($today_schedules) > 0) {
        foreach ($today_schedules as $schedule) {
            // Get child info
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $schedule['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            if ($child_info && count($child_info) > 0) {
                $child_name = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            }
            
            $notifications[] = [
                'id' => 'today_schedule_' . $schedule['id'],
                'type' => 'today_schedule',
                'priority' => 'medium',
                'title' => 'Today\'s Vaccination',
                'message' => $schedule['vaccine_name'] . ' (Dose ' . $schedule['dose_number'] . ') for ' . $child_name,
                'action' => 'View immunization schedule',
                'action_url' => './immunization.php',
                'timestamp' => $schedule['schedule_date'],
                'unread' => true,
                'icon' => '💉'
            ];
        }
    }
    
    // 3. Check for missed schedules
    $missed_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date.lt' => $today, 'status' => 'scheduled'], 
        'schedule_date.desc', 
        5
    );
    
    if ($missed_schedules && count($missed_schedules) > 0) {
        foreach ($missed_schedules as $missed) {
            // Get child info
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
                'title' => 'Missed Vaccination',
                'message' => $missed['vaccine_name'] . ' (Dose ' . $missed['dose_number'] . ') for ' . $child_name . ' - ' . round($days_missed) . ' days overdue',
                'action' => 'Schedule catch-up',
                'action_url' => './immunization.php',
                'timestamp' => $missed['schedule_date'],
                'unread' => true,
                'icon' => '⚠️'
            ];
        }
    }
    
    // Sort by priority and timestamp
    usort($notifications, function($a, $b) {
        $priority_order = ['urgent' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
        $a_priority = $priority_order[$a['priority']] ?? 5;
        $b_priority = $priority_order[$b['priority']] ?? 5;
        
        if ($a_priority === $b_priority) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        }
        return $a_priority - $b_priority;
    });
    
    // Count unread
    $unread_count = count(array_filter($notifications, function($n) { return $n['unread']; }));
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'notifications' => $notifications,
            'unread_count' => $unread_count,
            'total_count' => count($notifications),
            'debug' => [
                'recent_chr_count' => $recent_chr ? count($recent_chr) : 0,
                'today_schedules_count' => $today_schedules ? count($today_schedules) : 0,
                'missed_schedules_count' => $missed_schedules ? count($missed_schedules) : 0
            ]
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
