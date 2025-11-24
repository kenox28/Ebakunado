<?php
/**
 * Daily Cron Job for Sending Vaccination Schedule Notifications
 * 
 * This script automatically runs daily at 2:40 AM Philippines time to check 
 * for upcoming vaccination schedules and send SMS + Email notifications to parents.
 * 
 * NOTIFICATION TYPES:
 * 1. REMINDER: 1 day before vaccination (tomorrow's schedules)
 * 2. SAME-DAY: On the day of vaccination (today's schedules)
 * 
 * Setup cron job:
 * 40 2 * * * /usr/bin/php /path/to/ebakunado/cron/daily_notifications.php
 * 
 * This will run every day at 2:40 AM Philippines time
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../database/SupabaseConfig.php';
require_once __DIR__ . '/../database/DatabaseHelper.php';
require_once __DIR__ . '/../database/SystemSettingsHelper.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Log file for cron job
$logFile = __DIR__ . '/../logs/daily_notifications_' . date('Y-m-d') . '.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo $logEntry; // Also output to console
}

// Helper function for dose text
function getDoseText($doseNumber) {
    $doseMap = [
        1 => '1st dose',
        2 => '2nd dose', 
        3 => '3rd dose',
        4 => '4th dose'
    ];
    return $doseMap[$doseNumber] ?? "Dose $doseNumber";
}

/**
 * Fetch immunization records scheduled (guideline or batch) for a specific date.
 * Checks batch_schedule_date first, then schedule_date, then catch_up_date.
 */
function fetchSchedulesForDate(string $target_date) {
    $columns = 'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date,catch_up_date';
    $result = [];
    $seen = [];

    // Fetch by batch_schedule_date (priority 1)
    $byBatch = supabaseSelect(
        'immunization_records',
        $columns,
        [
            'batch_schedule_date' => $target_date,
            'status' => 'scheduled'
        ],
        'batch_schedule_date.asc'
    );

    // Fetch by schedule_date (priority 2)
    $bySchedule = supabaseSelect(
        'immunization_records',
        $columns,
        [
            'schedule_date' => $target_date,
            'status' => 'scheduled'
        ],
        'schedule_date.asc'
    );

    // Fetch by catch_up_date (priority 3)
    $byCatchUp = supabaseSelect(
        'immunization_records',
        $columns,
        [
            'catch_up_date' => $target_date,
            'status' => 'scheduled'
        ],
        'catch_up_date.asc'
    );

    // Merge results, prioritizing batch > schedule > catch_up
    foreach ([$byBatch, $bySchedule, $byCatchUp] as $group) {
        if (!$group || !is_array($group)) { continue; }
        foreach ($group as $row) {
            $id = $row['id'] ?? null;
            if ($id === null || isset($seen[$id])) { continue; }
            $seen[$id] = true;
            $result[] = $row;
        }
    }

    return $result;
}

/**
 * Get the actual date to use for notification (prioritize batch > schedule > catch_up)
 * Returns array with 'date' and 'date_source' (batch/guideline/catch_up)
 */
function getNotificationDate($schedule) {
    if (!empty($schedule['batch_schedule_date'])) {
        return [
            'date' => $schedule['batch_schedule_date'],
            'source' => 'batch',
            'label' => 'Batch Date'
        ];
    } elseif (!empty($schedule['schedule_date'])) {
        return [
            'date' => $schedule['schedule_date'],
            'source' => 'guideline',
            'label' => 'Guideline Date'
        ];
    } elseif (!empty($schedule['catch_up_date'])) {
        return [
            'date' => $schedule['catch_up_date'],
            'source' => 'catch_up',
            'label' => 'Catch Up Date'
        ];
    }
    return null;
}

// SMS notification function
function sendSMSNotification($parent, $child, $vaccines, $schedule_date, $message_prefix, $date_label, $date_source_label = '') {
    $phone_number = $parent['phone_number'] ?? '';
    
    if (empty($phone_number)) {
        return false;
    }
    
    // Clean phone number
    $phone_number = preg_replace('/[^0-9+]/', '', $phone_number);
    
    // Ensure phone number starts with +63 for Philippines
    if (!str_starts_with($phone_number, '+63')) {
        if (str_starts_with($phone_number, '09')) {
            $phone_number = '+63' . substr($phone_number, 1);
        } elseif (str_starts_with($phone_number, '9')) {
            $phone_number = '+63' . $phone_number;
        } else {
            return false;
        }
    }
    
    // SMS message - include date source if provided
    $dateInfo = date('M d, Y', strtotime($schedule_date));
    if (!empty($date_source_label) && $date_source_label !== 'Guideline Date') {
        $dateInfo .= " ({$date_source_label})";
    }
    $message = "Hi " . $parent['fname'] . ", $message_prefix: " . $child['child_fname'] . " " . $child['child_lname'] . " has " . $vaccines . " scheduled $date_label (" . $dateInfo . "). Please bring your child to the health center. - City Health Department, Ormoc City";
    
    // Get TextBee credentials from database (Midwife's settings for notifications)
    $credentials = getNotificationCredentials();
    $apiKey = $credentials['api_key'];
    $deviceId = $credentials['device_id'];
    $url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";
    
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
    
    // Check for successful response
    if ($curlError || ($httpCode !== 200 && $httpCode !== 201)) {
        return false;
    }
    
    $responseData = json_decode($response, true);
    
    // Check if the response indicates success
    if ($responseData && (
        (isset($responseData['data']['success']) && $responseData['data']['success'] === true) ||
        (isset($responseData['success']) && $responseData['success'] === true)
    )) {
        return true;
    }
    
    return $httpCode === 200 || $httpCode === 201;
}

// Email notification function
function sendEmailNotification($parent, $child, $vaccines, $schedule_date, $notification_type, $date_label, $date_source_label = '') {
    $email = $parent['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'iquenxzx@gmail.com';
        $mail->Password = 'lews hdga hdvb glym';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('iquenxzx@gmail.com', 'City Health Department, Ormoc City');
        $mail->addAddress($email, $parent['fname'] . ' ' . $parent['lname']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Vaccination Schedule ' . ($notification_type === 'same_day' ? 'Today' : 'Reminder') . ' - ' . $child['child_fname'] . ' ' . $child['child_lname'];
        
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background-color: #1976d2; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .highlight { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #1976d2; margin: 15px 0; }
                .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>🏥 City Health Department</h2>
                <h3>Ormoc City - Vaccination ' . ($notification_type === 'same_day' ? 'Today' : 'Reminder') . '</h3>
            </div>
            
            <div class="content">
                <p>Dear ' . $parent['fname'] . ' ' . $parent['lname'] . ',</p>
                
                <p>This is a ' . ($notification_type === 'same_day' ? 'confirmation' : 'friendly reminder') . ' that your child has a vaccination appointment ' . ($notification_type === 'same_day' ? 'today' : 'coming up') . ':</p>
                
                <div class="highlight">
                    <h3>📅 Vaccination Details</h3>
                    <p><strong>Child:</strong> ' . $child['child_fname'] . ' ' . $child['child_lname'] . '</p>
                    <p><strong>Scheduled Date:</strong> ' . date('M d, Y', strtotime($schedule_date)) . ' (' . ucfirst($date_label) . ')' . (!empty($date_source_label) && $date_source_label !== 'Guideline Date' ? ' - ' . $date_source_label : '') . '</p>
                    <p><strong>Vaccines:</strong> ' . $vaccines . '</p>
                </div>
                
                <p><strong>Please remember to:</strong></p>
                <ul>
                    <li>Bring your child to the health center on time</li>
                    <li>Bring the child health record (CHR)</li>
                    <li>Ensure your child is in good health</li>
                    <li>Follow any pre-vaccination instructions</li>
                </ul>
                <p>Thank you for helping us keep your child healthy!</p>
                <p>Sincerely,<br>City Health Department, Ormoc City</p>
            </div>
            
            <div class="footer">
                <p>&copy; ' . date('Y') . ' City Health Department, Ormoc City. All rights reserved.</p>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email notification failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Start logging
logMessage("=== Daily Notification Cron Job Started ===");

try {
    // Send REMINDER notifications (1 day before)
    logMessage("Starting REMINDER notifications (1 day before)...");
    
    // Process reminder notifications (tomorrow's schedules)
    try {
        $notification_type = 'reminder';
        $target_date = date('Y-m-d', strtotime('+1 day')); // Tomorrow
        $date_label = 'tomorrow';
        $message_prefix = '🔔 REMINDER';
        
        // Get all immunization records scheduled for target date
        $upcoming_schedules = fetchSchedulesForDate($target_date);
        
        if (!$upcoming_schedules || count($upcoming_schedules) === 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "No upcoming schedules for $date_label",
                'date' => $target_date,
                'notifications_sent' => 0,
                'notification_type' => $notification_type
            ]);
        } else {
            // Process reminder notifications (same logic as same-day)
            $baby_ids = array_unique(array_column($upcoming_schedules, 'baby_id'));
            
            $children_info = supabaseSelect(
                'child_health_records',
                'baby_id,child_fname,child_lname,user_id,mother_name,father_name',
                ['baby_id' => $baby_ids],
                'child_fname.asc'
            );
            
            $user_ids = array_unique(array_column($children_info, 'user_id'));
            $users_info = supabaseSelect(
                'users',
                'user_id,fname,lname,email,phone_number',
                ['user_id' => $user_ids],
                'fname.asc'
            );
            
            $children_lookup = [];
            foreach ($children_info as $child) {
                $children_lookup[$child['baby_id']] = $child;
            }
            
            $users_lookup = [];
            foreach ($users_info as $user) {
                $users_lookup[$user['user_id']] = $user;
            }
            
            $schedules_by_baby = [];
            foreach ($upcoming_schedules as $schedule) {
                $schedules_by_baby[$schedule['baby_id']][] = $schedule;
            }
            
            $notifications_sent = 0;
            $errors = [];
            
            foreach ($schedules_by_baby as $baby_id => $schedules) {
                $child = $children_lookup[$baby_id] ?? null;
                $parent = $users_lookup[$child['user_id']] ?? null;
                
                if (!$child || !$parent) {
                    $errors[] = "Missing child or parent info for baby_id: $baby_id";
                    continue;
                }
                
                $notification_type_key = 'schedule_reminder';
                $notification_exists = supabaseSelect(
                    'notification_logs',
                    'id',
                    [
                        'baby_id' => $baby_id,
                        'notification_date' => $target_date,
                        'type' => $notification_type_key
                    ],
                    null,
                    1
                );
                
                if ($notification_exists && count($notification_exists) > 0) {
                    continue;
                }
                
                // Determine the actual date to use (prioritize batch > schedule > catch_up)
                $firstSchedule = $schedules[0];
                $dateInfo = getNotificationDate($firstSchedule);
                if (!$dateInfo) {
                    $errors[] = "No valid date found for baby_id: $baby_id";
                    continue;
                }
                $actualDate = $dateInfo['date'];
                $dateSource = $dateInfo['source'];
                $dateLabel = $dateInfo['label'];
                
                $vaccine_list = [];
                foreach ($schedules as $schedule) {
                    $dose_text = getDoseText($schedule['dose_number']);
                    $vaccine_list[] = $schedule['vaccine_name'] . ' (' . $dose_text . ')';
                }
                $vaccines_text = implode(', ', $vaccine_list);
                
                // Add date source indicator to date_label if batch
                $enhancedDateLabel = $date_label;
                if ($dateSource === 'batch') {
                    $enhancedDateLabel = $date_label . ' (Batch)';
                }
                
                $sms_sent = sendSMSNotification($parent, $child, $vaccines_text, $actualDate, $message_prefix, $enhancedDateLabel, $dateLabel);
                $email_sent = sendEmailNotification($parent, $child, $vaccines_text, $actualDate, $notification_type, $enhancedDateLabel, $dateLabel);
                
                if ($sms_sent || $email_sent) {
                    supabaseInsert('notification_logs', [
                        'baby_id' => $baby_id,
                        'user_id' => $parent['user_id'],
                        'type' => $notification_type_key,
                        'message' => "Vaccination reminder for " . $child['child_fname'] . ' ' . $child['child_lname'],
                        'notification_date' => $actualDate,
                        'sms_sent' => $sms_sent,
                        'email_sent' => $email_sent,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $notifications_sent++;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => ucfirst($notification_type) . ' notifications processed',
                'date' => $target_date,
                'notifications_sent' => $notifications_sent,
                'errors' => $errors,
                'notification_type' => $notification_type
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'notification_type' => 'reminder'
        ]);
    }
    
    logMessage("REMINDER notifications completed");
    
    // Send SAME-DAY notifications (today)
    logMessage("Starting SAME-DAY notifications (today)...");
    $GLOBALS['notification_type'] = 'same_day'; // Set global flag for same-day notifications
    
    // Execute the notification logic directly since require_once won't work twice
    try {
        // Determine notification type (reminder or same-day)
        $notification_type = isset($GLOBALS['notification_type']) ? $GLOBALS['notification_type'] : 'reminder';
        
        // Set target date based on notification type
        if ($notification_type === 'same_day') {
            $target_date = date('Y-m-d'); // Today
            $date_label = 'today';
            $message_prefix = '📅 TODAY';
        } else {
            $target_date = date('Y-m-d', strtotime('+1 day')); // Tomorrow
            $date_label = 'tomorrow';
            $message_prefix = '🔔 REMINDER';
        }
        
        // Get all immunization records scheduled for target date
        $upcoming_schedules = fetchSchedulesForDate($target_date);
        
        if (!$upcoming_schedules || count($upcoming_schedules) === 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "No upcoming schedules for $date_label",
                'date' => $target_date,
                'notifications_sent' => 0,
                'notification_type' => $notification_type
            ]);
        } else {
            // Get unique baby_ids
            $baby_ids = array_unique(array_column($upcoming_schedules, 'baby_id'));
            
            // Get child and parent information
            $children_info = supabaseSelect(
                'child_health_records',
                'baby_id,child_fname,child_lname,user_id,mother_name,father_name',
                ['baby_id' => $baby_ids],
                'child_fname.asc'
            );
            
            // Get parent contact information
            $user_ids = array_unique(array_column($children_info, 'user_id'));
            $users_info = supabaseSelect(
                'users',
                'user_id,fname,lname,email,phone_number',
                ['user_id' => $user_ids],
                'fname.asc'
            );
            
            // Create lookup arrays
            $children_lookup = [];
            foreach ($children_info as $child) {
                $children_lookup[$child['baby_id']] = $child;
            }
            
            $users_lookup = [];
            foreach ($users_info as $user) {
                $users_lookup[$user['user_id']] = $user;
            }
            
            // Group schedules by baby_id
            $schedules_by_baby = [];
            foreach ($upcoming_schedules as $schedule) {
                $schedules_by_baby[$schedule['baby_id']][] = $schedule;
            }
            
            $notifications_sent = 0;
            $errors = [];
            
            // Process each child's upcoming schedules
            foreach ($schedules_by_baby as $baby_id => $schedules) {
                $child = $children_lookup[$baby_id] ?? null;
                $parent = $users_lookup[$child['user_id']] ?? null;
                
                if (!$child || !$parent) {
                    $errors[] = "Missing child or parent info for baby_id: $baby_id";
                    continue;
                }
                
                // Check if notification was already sent today
                $notification_type_key = $notification_type === 'same_day' ? 'schedule_same_day' : 'schedule_reminder';
                $notification_exists = supabaseSelect(
                    'notification_logs',
                    'id',
                    [
                        'baby_id' => $baby_id,
                        'notification_date' => $target_date,
                        'type' => $notification_type_key
                    ],
                    null,
                    1
                );
                
                if ($notification_exists && count($notification_exists) > 0) {
                    continue; // Already sent today
                }
                
                // Determine the actual date to use (prioritize batch > schedule > catch_up)
                $firstSchedule = $schedules[0];
                $dateInfo = getNotificationDate($firstSchedule);
                if (!$dateInfo) {
                    $errors[] = "No valid date found for baby_id: $baby_id";
                    continue;
                }
                $actualDate = $dateInfo['date'];
                $dateSource = $dateInfo['source'];
                $dateLabel = $dateInfo['label'];
                
                // Prepare vaccine list for notification
                $vaccine_list = [];
                foreach ($schedules as $schedule) {
                    $dose_text = getDoseText($schedule['dose_number']);
                    $vaccine_list[] = $schedule['vaccine_name'] . ' (' . $dose_text . ')';
                }
                $vaccines_text = implode(', ', $vaccine_list);
                
                // Add date source indicator to date_label if batch
                $enhancedDateLabel = $date_label;
                if ($dateSource === 'batch') {
                    $enhancedDateLabel = $date_label . ' (Batch)';
                }
                
                // Send SMS notification
                $sms_sent = sendSMSNotification($parent, $child, $vaccines_text, $actualDate, $message_prefix, $enhancedDateLabel, $dateLabel);
                
                // Send Email notification
                $email_sent = sendEmailNotification($parent, $child, $vaccines_text, $actualDate, $notification_type, $enhancedDateLabel, $dateLabel);
                
                // Log the notification
                if ($sms_sent || $email_sent) {
                    supabaseInsert('notification_logs', [
                        'baby_id' => $baby_id,
                        'user_id' => $parent['user_id'],
                        'type' => $notification_type_key,
                        'message' => "Vaccination " . ($notification_type === 'same_day' ? 'same-day notification' : 'reminder') . " for " . $child['child_fname'] . ' ' . $child['child_lname'],
                        'notification_date' => $actualDate,
                        'sms_sent' => $sms_sent,
                        'email_sent' => $email_sent,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $notifications_sent++;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => ucfirst($notification_type) . ' notifications processed',
                'date' => $target_date,
                'notifications_sent' => $notifications_sent,
                'errors' => $errors,
                'notification_type' => $notification_type
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'notification_type' => 'same_day'
        ]);
    }
    
    logMessage("SAME-DAY notifications completed");
    
    logMessage("All notification services completed successfully");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

logMessage("=== Daily Notification Cron Job Completed ===");
?>
