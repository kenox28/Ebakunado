<?php
// Suppress all PHP warnings and errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../shared/access_control.php';

// Check if user is logged in (BHW or Midwife)
$user_type = getCurrentUserType();
$user_data = getCurrentUserData();

if (!$user_type || !$user_data) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - No valid session found']);
    exit();
}

// For midwives, check if they're approved
if ($user_type === 'midwife' && !isApprovedMidwife()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Account pending approval']);
    exit();
}

$user_id = $user_data['id'];

try {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    // 1. Pending Child Health Record Approvals (BHW only) or CHR Requests (Midwives)
    if ($user_type === 'bhw') {
        $pending_approvals = supabaseSelect('child_health_records', 'id,baby_id,child_fname,child_lname,date_created', ['status' => 'pending'], 'date_created.desc', 10);
        $pending_count = $pending_approvals ? count($pending_approvals) : 0;
    } else {
        // For midwives, get CHR requests
        $pending_approvals = supabaseSelect('chr_document_requests', 'id,request_id,child_name,parent_name,request_date', ['status' => 'pending'], 'request_date.desc', 10);
        $pending_count = $pending_approvals ? count($pending_approvals) : 0;
    }

    // 2. Today's Vaccination Schedules
    $today_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $today], 
        null, 50
    );
    
    // Filter out completed ones
    if ($today_schedules) {
        $today_schedules = array_filter($today_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $today_count = $today_schedules ? count($today_schedules) : 0;

    // 3. Missed Vaccination Schedules (before today, not completed)
    $missed_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date.lt' => $today], 
        null, 50
    );
    
    // Filter out completed ones
    if ($missed_schedules) {
        $missed_schedules = array_filter($missed_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $missed_count = $missed_schedules ? count($missed_schedules) : 0;

    // 4. Total Children Under Care (accepted child health records)
    $total_children = supabaseSelect('child_health_records', 'baby_id', ['status' => 'accepted'], null, 1000);
    $total_children_count = $total_children ? count($total_children) : 0;

    // 5. Tomorrow's Vaccination Schedules
    $tomorrow_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $tomorrow], 
        null, 20
    );
    
    // Filter out completed ones
    if ($tomorrow_schedules) {
        $tomorrow_schedules = array_filter($tomorrow_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $tomorrow_count = $tomorrow_schedules ? count($tomorrow_schedules) : 0;

    // 6. Recent Activity (last 7 days)
    $last_week = date('Y-m-d H:i:s', strtotime('-7 days'));
    $recent_activities = [];
    
    // Recent child health record submissions
    $recent_chr = supabaseSelect('child_health_records', 
        'id,child_fname,child_lname,status,date_created,date_updated', 
        ['date_created.gte' => $last_week], 
        'date_created.desc', 10
    );
    
    if ($recent_chr) {
        foreach ($recent_chr as $chr) {
            $activity_type = 'submission';
            $title = 'New Child Record Submitted';
            $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'];
            $icon = '📋';
            
            if ($chr['status'] === 'accepted') {
                $activity_type = 'approval';
                $title = 'Child Record Approved';
                $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' approved';
                $icon = '✅';
            } elseif ($chr['status'] === 'rejected') {
                $activity_type = 'rejection';
                $title = 'Child Record Rejected';
                $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' rejected';
                $icon = '❌';
            }

            $recent_activities[] = [
                'type' => $activity_type,
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'timestamp' => $chr['date_created'],
                'status' => $chr['status']
            ];
        }
    }

    // Recent vaccination completions
    $recent_vaccinations = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,status,date_given', 
        ['date_given.gte' => $last_week, 'status' => 'completed'], 
        'date_given.desc', 5
    );
    
    if ($recent_vaccinations) {
        foreach ($recent_vaccinations as $vaccination) {
            // Get child info
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $vaccination['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            if ($child_info && count($child_info) > 0) {
                $child_name = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            }

            $recent_activities[] = [
                'type' => 'vaccination',
                'title' => 'Vaccination Completed',
                'description' => $vaccination['vaccine_name'] . ' completed for ' . $child_name,
                'icon' => '💉',
                'timestamp' => $vaccination['date_given'],
                'status' => 'completed'
            ];
        }
    }

    // Sort activities by timestamp (most recent first)
    usort($recent_activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Limit to 8 most recent activities
    $recent_activities = array_slice($recent_activities, 0, 8);

    // 7. Quick Tasks Summary
    $overdue_count = 0;
    if ($missed_schedules) {
        $overdue_count = count($missed_schedules);
    }

    // Set proper JSON header
    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'data' => [
            'stats' => [
                'pending_approvals' => $pending_count,
                'today_vaccinations' => $today_count,
                'missed_vaccinations' => $missed_count,
                'total_children' => $total_children_count,
                'tomorrow_vaccinations' => $tomorrow_count,
                'overdue_tasks' => $overdue_count
            ],
            'recent_activities' => $recent_activities,
            'pending_approvals_list' => $pending_approvals ? array_slice($pending_approvals, 0, 5) : [],
            'today_schedules_list' => $today_schedules ? array_slice($today_schedules, 0, 5) : [],
            'tomorrow_schedules_list' => $tomorrow_schedules ? array_slice($tomorrow_schedules, 0, 5) : [],
            'user_type' => $user_type,
            'user_permissions' => $user_data['permissions'] ?? 'full'
        ]
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
    ]);
}
?>
