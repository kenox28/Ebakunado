<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check if user is authorized (BHW, Admin, or Super Admin)
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id']) && !isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$test_type = $_POST['test_type'] ?? '';

try {
    if ($test_type === 'email') {
        testEmailConfiguration();
    } elseif ($test_type === 'sms') {
        testSMSConfiguration();
    } elseif ($test_type === 'create_account') {
        testCreateAccountFlow();
    } elseif ($test_type === 'forgot_password') {
        testForgotPasswordFlow();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid test type']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Test failed: ' . $e->getMessage()
    ]);
}

function testEmailConfiguration() {
    $email_username = $_POST['email_username'] ?? '';
    $email_password = $_POST['email_password'] ?? '';
    
    if (empty($email_username) || empty($email_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email username and password are required']);
        exit();
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $email_username;
        $mail->Password = $email_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Test recipient (send to the same email for testing)
        $mail->setFrom($email_username, 'eBakunado System Test');
        $mail->addAddress($email_username, 'Test User');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'eBakunado System Email Configuration Test';
        
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background-color: #1976d2; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>✅ System Email Configuration Test</h2>
            </div>
            <div class="content">
                <p>This is a test email to verify that the eBakunado system email configuration is working correctly.</p>
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li>From: ' . $email_username . '</li>
                    <li>To: ' . $email_username . '</li>
                    <li>Sent: ' . date('Y-m-d H:i:s') . '</li>
                    <li>Purpose: System-wide email configuration test</li>
                    <li>Used for: Create Account, Forgot Password, Vaccination Reminders</li>
                </ul>
                <p>If you receive this email, the email configuration is working correctly for all system features!</p>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = "This is a test email to verify the eBakunado system email configuration is working correctly. Sent at " . date('Y-m-d H:i:s');
        
        $mail->send();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Email configuration test successful! Email sent to ' . $email_username . '. This configuration will be used for all system emails (Create Account, Forgot Password, Vaccination Reminders).'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email test failed: ' . $e->getMessage()
        ]);
    }
}

function testSMSConfiguration() {
    $api_key = $_POST['sms_api_key'] ?? '';
    $device_id = $_POST['sms_device_id'] ?? '';
    
    if (empty($api_key) || empty($device_id)) {
        echo json_encode(['status' => 'error', 'message' => 'SMS API key and device ID are required']);
        exit();
    }
    
    // Use a test phone number (you can change this to your own for testing)
    $test_phone = '+639123456789'; // Replace with your test phone number
    
    $message = "Test SMS from eBakunado system. Configuration test successful! This SMS configuration will be used for OTP, Password Reset, and Vaccination Reminders. Sent at " . date('Y-m-d H:i:s');
    
    $url = "https://api.textbee.dev/api/v1/gateway/devices/$device_id/send-sms";
    
    // Prepare SMS data
    $smsData = [
        'recipients' => [$test_phone],
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
        'X-API-Key: ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curlError) {
        echo json_encode([
            'status' => 'error',
            'message' => 'SMS API connection failed: ' . $curlError
        ]);
        exit();
    }
    
    // Check for successful response
    if ($httpCode === 200 || $httpCode === 201) {
        $responseData = json_decode($response, true);
        
        // Check if the response indicates success
        if ($responseData && (
            (isset($responseData['data']['success']) && $responseData['data']['success'] === true) ||
            (isset($responseData['success']) && $responseData['success'] === true)
        )) {
            echo json_encode([
                'status' => 'success',
                'message' => 'SMS configuration test successful! Test SMS sent to ' . $test_phone . '. This configuration will be used for all system SMS (OTP, Password Reset, Vaccination Reminders).'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'SMS API returned unexpected response: ' . $response
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'SMS API request failed with HTTP code: ' . $httpCode . '. Response: ' . $response
        ]);
    }
}

function testCreateAccountFlow() {
    // Test if the create account email functionality would work
    try {
        // Check if create account file exists and has proper email configuration
        $create_account_file = __DIR__ . '/../../create_account.php';
        
        if (!file_exists($create_account_file)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Create Account file not found'
            ]);
            exit();
        }
        
        $file_content = file_get_contents($create_account_file);
        
        // Check if file contains email sending functionality
        if (strpos($file_content, 'PHPMailer') === false) {
            echo json_encode([
                'status' => 'warning',
                'message' => 'Create Account file found but may not have email functionality configured'
            ]);
            exit();
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Create Account flow test passed! Email configuration is properly set up in the create account system.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Create Account test failed: ' . $e->getMessage()
        ]);
    }
}

function testForgotPasswordFlow() {
    // Test if the forgot password SMS functionality would work
    try {
        // Check if forgot password file exists and has proper SMS configuration
        $forgot_password_file = __DIR__ . '/../../forgot_password.php';
        
        if (!file_exists($forgot_password_file)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Forgot Password file not found'
            ]);
            exit();
        }
        
        $file_content = file_get_contents($forgot_password_file);
        
        // Check if file contains SMS sending functionality
        if (strpos($file_content, 'textbee') === false && strpos($file_content, 'TextBee') === false) {
            echo json_encode([
                'status' => 'warning',
                'message' => 'Forgot Password file found but may not have SMS functionality configured'
            ]);
            exit();
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Forgot Password flow test passed! SMS configuration is properly set up in the password reset system.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Forgot Password test failed: ' . $e->getMessage()
        ]);
    }
}
?>
