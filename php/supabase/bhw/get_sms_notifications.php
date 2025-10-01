<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Log the request for debugging
error_log('SMS Notifications Request - BHW ID: ' . $_SESSION['bhw_id'] . ', Type: ' . ($_GET['type'] ?? 'both'));

$notification_type = $_GET['type'] ?? 'both'; // 'upcoming', 'missed', or 'both'
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

try {
    error_log('SMS Notifications - Starting query for type: ' . $notification_type . ', Tomorrow: ' . $tomorrow);
    
    $results = [
        'upcoming' => [],
        'missed' => []
    ];

    if ($notification_type === 'upcoming' || $notification_type === 'both') {
        error_log('SMS Notifications - Querying upcoming schedules for date: ' . $tomorrow);
        
        // Get users with schedules tomorrow
        $upcoming_data = supabaseSelect(
            'immunization_records', 
            'baby_id,vaccine_name,dose_number,schedule_date', 
            ['schedule_date' => $tomorrow, 'status' => 'pending'], 
            'schedule_date.asc'
        );
        
        error_log('SMS Notifications - Upcoming data result: ' . json_encode($upcoming_data));
        
        if ($upcoming_data) {
            foreach ($upcoming_data as $record) {
                // Get child info
                $child_info = supabaseSelect(
                    'child_health_records',
                    'user_id,child_fname,child_lname',
                    ['baby_id' => $record['baby_id']],
                    null,
                    1
                );
                
                if ($child_info) {
                    // Get user info
                    $user_info = supabaseSelect(
                        'users',
                        'user_id,fname,lname,phone_number',
                        ['user_id' => $child_info[0]['user_id']],
                        null,
                        1
                    );
                    
                    if ($user_info && !empty($user_info[0]['phone_number'])) {
                        $results['upcoming'][] = [
                            'user_id' => $user_info[0]['user_id'],
                            'user_name' => $user_info[0]['fname'] . ' ' . $user_info[0]['lname'],
                            'phone_number' => $user_info[0]['phone_number'],
                            'child_name' => $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'],
                            'vaccine_name' => $record['vaccine_name'],
                            'dose_number' => $record['dose_number'],
                            'schedule_date' => $record['schedule_date'],
                            'baby_id' => $record['baby_id']
                        ];
                    }
                }
            }
        }
    }

    if ($notification_type === 'missed' || $notification_type === 'both') {
        error_log('SMS Notifications - Querying missed schedules');
        
        // Get users with missed schedules
        $missed_data = supabaseSelect(
            'immunization_records', 
            'baby_id,vaccine_name,dose_number,schedule_date,catch_up_date', 
            ['status' => 'missed'], 
            'schedule_date.desc'
        );
        
        error_log('SMS Notifications - Missed data result: ' . json_encode($missed_data));
        
        if ($missed_data) {
            foreach ($missed_data as $record) {
                // Get child info
                $child_info = supabaseSelect(
                    'child_health_records',
                    'user_id,child_fname,child_lname',
                    ['baby_id' => $record['baby_id']],
                    null,
                    1
                );
                
                if ($child_info) {
                    // Get user info
                    $user_info = supabaseSelect(
                        'users',
                        'user_id,fname,lname,phone_number',
                        ['user_id' => $child_info[0]['user_id']],
                        null,
                        1
                    );
                    
                    if ($user_info && !empty($user_info[0]['phone_number'])) {
                        $results['missed'][] = [
                            'user_id' => $user_info[0]['user_id'],
                            'user_name' => $user_info[0]['fname'] . ' ' . $user_info[0]['lname'],
                            'phone_number' => $user_info[0]['phone_number'],
                            'child_name' => $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'],
                            'vaccine_name' => $record['vaccine_name'],
                            'dose_number' => $record['dose_number'],
                            'schedule_date' => $record['schedule_date'],
                            'catch_up_date' => $record['catch_up_date'],
                            'baby_id' => $record['baby_id']
                        ];
                    }
                }
            }
        }
    }

    // Remove duplicates based on user_id and vaccine combination
    $unique_upcoming = [];
    $seen_upcoming = [];
    foreach ($results['upcoming'] as $item) {
        $key = $item['user_id'] . '_' . $item['vaccine_name'] . '_' . $item['dose_number'];
        if (!in_array($key, $seen_upcoming)) {
            $unique_upcoming[] = $item;
            $seen_upcoming[] = $key;
        }
    }

    $unique_missed = [];
    $seen_missed = [];
    foreach ($results['missed'] as $item) {
        $key = $item['user_id'] . '_' . $item['vaccine_name'] . '_' . $item['dose_number'];
        if (!in_array($key, $seen_missed)) {
            $unique_missed[] = $item;
            $seen_missed[] = $key;
        }
    }

    error_log('SMS Notifications - Final results: Upcoming=' . count($unique_upcoming) . ', Missed=' . count($unique_missed));

    echo json_encode([
        'status' => 'success',
        'data' => [
            'upcoming' => $unique_upcoming,
            'missed' => $unique_missed,
            'summary' => [
                'upcoming_count' => count($unique_upcoming),
                'missed_count' => count($unique_missed)
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log('Error getting SMS notifications: ' . $e->getMessage());
    error_log('Error stack trace: ' . $e->getTraceAsString());
    
    // Return detailed error information
    $error_response = [
        'status' => 'error', 
        'message' => 'Failed to get notification data: ' . $e->getMessage(),
        'error_details' => [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ],
        'debug_info' => [
            'notification_type' => $notification_type,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'session_bhw_id' => $_SESSION['bhw_id'] ?? 'not_set'
        ]
    ];
    
    echo json_encode($error_response, JSON_PRETTY_PRINT);
}
?>
