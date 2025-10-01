<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$notification_type = $_POST['type'] ?? ''; // 'upcoming' or 'missed'
$custom_message = $_POST['custom_message'] ?? '';

if (empty($notification_type)) {
    echo json_encode(['status' => 'error', 'message' => 'Notification type is required']);
    exit();
}

try {
    // Get notification data directly from database
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $target_data = [];

    if ($notification_type === 'upcoming' || $notification_type === 'both') {
        // Get users with schedules tomorrow
        $upcoming_data = supabaseSelect(
            'immunization_records', 
            'baby_id,vaccine_name,dose_number,schedule_date', 
            ['schedule_date' => $tomorrow, 'status' => 'pending'], 
            'schedule_date.asc'
        );
        
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
                        $target_data[] = [
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
        // Get users with missed schedules
        $missed_data = supabaseSelect(
            'immunization_records', 
            'baby_id,vaccine_name,dose_number,schedule_date,catch_up_date', 
            ['status' => 'missed'], 
            'schedule_date.desc'
        );
        
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
                        $target_data[] = [
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

    // Remove duplicates
    $unique_target_data = [];
    $seen = [];
    foreach ($target_data as $item) {
        $key = $item['user_id'] . '_' . $item['vaccine_name'] . '_' . $item['dose_number'];
        if (!in_array($key, $seen)) {
            $unique_target_data[] = $item;
            $seen[] = $key;
        }
    }
    $target_data = $unique_target_data;
    
    if (empty($target_data)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'No ' . $notification_type . ' schedules found to notify',
            'sent_count' => 0
        ]);
        exit();
    }

    // TextBee.dev API configuration
    $apiKey = '859e05f9-b29e-4071-b29f-0bd14a273bc2';
    $deviceId = '687e5760c87689a0c22492b3';
    $url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";

    $sent_count = 0;
    $failed_count = 0;
    $results = [];

    foreach ($target_data as $notification) {
        $phone_number = $notification['phone_number'];
        
        // Clean phone number
        $phone_number = preg_replace('/[^0-9+]/', '', $phone_number);
        
        // Ensure phone number starts with +63 for Philippines
        if (!str_starts_with($phone_number, '+63')) {
            if (str_starts_with($phone_number, '09')) {
                $phone_number = '+63' . substr($phone_number, 1);
            } elseif (str_starts_with($phone_number, '9')) {
                $phone_number = '+63' . $phone_number;
            } else {
                $results[] = [
                    'user' => $notification['user_name'],
                    'phone' => $notification['phone_number'],
                    'status' => 'failed',
                    'message' => 'Invalid phone number format'
                ];
                $failed_count++;
                continue;
            }
        }

        // Generate appropriate message
        if (!empty($custom_message)) {
            $message = $custom_message;
        } else {
            if ($notification_type === 'upcoming') {
                $message = "Hello " . $notification['user_name'] . "!\n\n";
                $message .= "Reminder: Your child " . $notification['child_name'] . " has a scheduled vaccination tomorrow (" . date('F j, Y', strtotime($notification['schedule_date'])) . ").\n\n";
                $message .= "Vaccine: " . $notification['vaccine_name'] . " (Dose " . $notification['dose_number'] . ")\n\n";
                $message .= "Please visit your local health center. If you cannot make it, please contact us to reschedule.\n\n";
                $message .= "Thank you,\nEbakunado Health System";
            } else { // missed
                $message = "Hello " . $notification['user_name'] . "!\n\n";
                $message .= "Important: Your child " . $notification['child_name'] . " missed a scheduled vaccination.\n\n";
                $message .= "Missed Vaccine: " . $notification['vaccine_name'] . " (Dose " . $notification['dose_number'] . ")\n";
                $message .= "Original Date: " . date('F j, Y', strtotime($notification['schedule_date'])) . "\n";
                if (!empty($notification['catch_up_date'])) {
                    $message .= "Catch-up Date: " . date('F j, Y', strtotime($notification['catch_up_date'])) . "\n";
                }
                $message .= "\nPlease visit your local health center as soon as possible to catch up on your child's vaccination schedule.\n\n";
                $message .= "Thank you,\nEbakunado Health System";
            }
        }

        // Prepare SMS data
        $smsData = [
            'recipients' => [$phone_number],
            'message' => $message,
            'sender' => 'ebakunado'
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Check result
        if ($curlError) {
            $results[] = [
                'user' => $notification['user_name'],
                'phone' => $notification['phone_number'],
                'status' => 'failed',
                'message' => 'Network error'
            ];
            $failed_count++;
        } elseif ($httpCode === 200 || $httpCode === 201) {
            $results[] = [
                'user' => $notification['user_name'],
                'phone' => $notification['phone_number'],
                'status' => 'success',
                'message' => 'SMS sent successfully'
            ];
            $sent_count++;
        } else {
            $results[] = [
                'user' => $notification['user_name'],
                'phone' => $notification['phone_number'],
                'status' => 'failed',
                'message' => 'SMS API error'
            ];
            $failed_count++;
        }

        // Add small delay to avoid rate limiting
        usleep(500000); // 0.5 seconds
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'SMS notification process completed',
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'total_count' => count($target_data),
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log('Error sending SMS notifications: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to send SMS notifications']);
}
?>
