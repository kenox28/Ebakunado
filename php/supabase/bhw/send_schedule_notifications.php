<?php
// Automatic notification service for upcoming vaccination schedules
// This script checks for tomorrow's schedules and sends SMS + Email notifications

header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    // Get tomorrow's date
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    // Get all immunization records scheduled for tomorrow
    $upcoming_schedules = supabaseSelect(
        'immunization_records',
        'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date',
        [
            'schedule_date' => $tomorrow,
            'status' => 'scheduled'
        ],
        'schedule_date.asc'
    );
    
    if (!$upcoming_schedules || count($upcoming_schedules) === 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'No upcoming schedules for tomorrow',
            'date' => $tomorrow,
            'notifications_sent' => 0
        ]);
        exit();
    }
    
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
        $notification_exists = supabaseSelect(
            'notification_logs',
            'id',
            [
                'baby_id' => $baby_id,
                'notification_date' => date('Y-m-d'),
                'type' => 'schedule_reminder'
            ],
            null,
            1
        );
        
        if ($notification_exists && count($notification_exists) > 0) {
            continue; // Already sent today
        }
        
        // Prepare vaccine list for notification
        $vaccine_list = [];
        foreach ($schedules as $schedule) {
            $dose_text = getDoseText($schedule['dose_number']);
            $vaccine_list[] = $schedule['vaccine_name'] . ' (' . $dose_text . ')';
        }
        $vaccines_text = implode(', ', $vaccine_list);
        
        // Send SMS notification
        $sms_sent = sendSMSNotification($parent, $child, $vaccines_text, $tomorrow);
        
        // Send Email notification
        $email_sent = sendEmailNotification($parent, $child, $vaccines_text, $tomorrow);
        
        // Log the notification
        if ($sms_sent || $email_sent) {
            supabaseInsert('notification_logs', [
                'baby_id' => $baby_id,
                'user_id' => $parent['id'],
                'type' => 'schedule_reminder',
                'message' => "Vaccination reminder for " . $child['child_fname'] . ' ' . $child['child_lname'],
                'notification_date' => date('Y-m-d'),
                'sms_sent' => $sms_sent,
                'email_sent' => $email_sent,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $notifications_sent++;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Notifications processed',
        'date' => $tomorrow,
        'notifications_sent' => $notifications_sent,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Notification service error: ' . $e->getMessage()
    ]);
}

function getDoseText($doseNumber) {
    $doseMap = [
        1 => '1st dose',
        2 => '2nd dose', 
        3 => '3rd dose',
        4 => '4th dose'
    ];
    return $doseMap[$doseNumber] ?? "Dose $doseNumber";
}

function sendSMSNotification($parent, $child, $vaccines, $schedule_date) {
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
    
    // SMS message
    $message = "Hi " . $parent['fname'] . ", reminder: " . $child['child_fname'] . " " . $child['child_lname'] . " has " . $vaccines . " scheduled tomorrow (" . date('M d, Y', strtotime($schedule_date)) . "). Please bring your child to the health center. - City Health Department, Ormoc City";
    
    // TextBee.dev API configuration
    $apiKey = '859e05f9-b29e-4071-b29f-0bd14a273bc2';
    $deviceId = '687e5760c87689a0c22492b3';
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

function sendEmailNotification($parent, $child, $vaccines, $schedule_date) {
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
        $mail->Subject = 'Vaccination Schedule Reminder - ' . $child['child_fname'] . ' ' . $child['child_lname'];
        
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
                <h3>Ormoc City - Vaccination Reminder</h3>
            </div>
            
            <div class="content">
                <p>Dear ' . $parent['fname'] . ' ' . $parent['lname'] . ',</p>
                
                <p>This is a friendly reminder that your child has an upcoming vaccination appointment:</p>
                
                <div class="highlight">
                    <h3>📅 Vaccination Details</h3>
                    <p><strong>Child:</strong> ' . $child['child_fname'] . ' ' . $child['child_lname'] . '</p>
                    <p><strong>Scheduled Date:</strong> ' . date('M d, Y', strtotime($schedule_date)) . ' (Tomorrow)</p>
                    <p><strong>Vaccines:</strong> ' . $vaccines . '</p>
                </div>
                
                <p><strong>Please remember to:</strong></p>
                <ul>
                    <li>Bring your child to the health center on time</li>
                    <li>Bring the child health record (CHR)</li>
                    <li>Ensure your child is in good health</li>
                    <li>Follow any pre-vaccination instructions</li>
                </ul>
                
                <p>If you have any questions or need to reschedule, please contact your barangay health worker or visit the health center.</p>
                
                <p>Thank you for keeping your child\'s vaccinations up to date!</p>
                
                <p>Best regards,<br>
                <strong>City Health Department<br>
                Ormoc City</strong></p>
            </div>
            
            <div class="footer">
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>eBakunado - Child Health Record Management System</p>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = "Dear " . $parent['fname'] . " " . $parent['lname'] . ",\n\nThis is a reminder that " . $child['child_fname'] . " " . $child['child_lname'] . " has " . $vaccines . " scheduled tomorrow (" . date('M d, Y', strtotime($schedule_date)) . "). Please bring your child to the health center on time.\n\nBest regards,\nCity Health Department, Ormoc City";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log('Email notification failed: ' . $e->getMessage());
        return false;
    }
}
?>
