<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

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

try {
    // Get current user info
    $user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
    $user_type = $_SESSION['user_type'] ?? null;
    $user_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
    
    if (!$user_id || !$user_type) {
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
        exit();
    }
    
    // Get form data
    $email_username = $_POST['email_username'] ?? '';
    $email_password = $_POST['email_password'] ?? '';
    $sms_api_key = $_POST['sms_api_key'] ?? '';
    $sms_device_id = $_POST['sms_device_id'] ?? '';
    $notification_time = $_POST['notification_time'] ?? '02:40';
    $system_name = $_POST['system_name'] ?? 'eBakunado';
    $health_center_name = $_POST['health_center_name'] ?? 'City Health Department, Ormoc City';
    
    // Validate required fields
    if (empty($email_username) || empty($email_password) || empty($sms_api_key) || empty($sms_device_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and SMS configuration fields are required']);
        exit();
    }
    
    // Validate email format
    if (!filter_var($email_username, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }
    
    // Validate time format
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $notification_time)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid time format']);
        exit();
    }
    
    // Prepare settings data
    $settings_data = [
        'user_id' => $user_id,
        'user_type' => $user_type,
        'email_username' => $email_username,
        'email_password' => $email_password,
        'sms_api_key' => $sms_api_key,
        'sms_device_id' => $sms_device_id,
        'notification_time' => $notification_time,
        'system_name' => $system_name,
        'health_center_name' => $health_center_name,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Check if settings already exist
    $existing_settings = supabaseSelect('system_settings', 'user_id', [
        'user_id' => $user_id,
        'user_type' => $user_type
    ], null, 1);
    
    if ($existing_settings && count($existing_settings) > 0) {
        // Update existing settings
        $result = supabaseUpdate('system_settings', $settings_data, [
            'user_id' => $user_id,
            'user_type' => $user_type
        ]);
        
        if ($result) {
            // Log activity for midwife settings update
            if ($user_type === 'midwifes' || $user_type === 'midwife' || $user_type === 'bhw') {
                supabaseInsert('activity_logs', [
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'action_type' => 'UPDATE',
                    'description' => $user_name . ' updated SMS notification settings (API Key and Device ID)',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'System settings updated successfully. Daily vaccination notifications will use the new SMS configuration.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update system settings'
            ]);
        }
    } else {
        // Insert new settings
        $settings_data['created_at'] = date('Y-m-d H:i:s');
        $result = supabaseInsert('system_settings', $settings_data);
        
        if ($result) {
            // Log activity for midwife settings creation
            if ($user_type === 'midwifes' || $user_type === 'midwife' || $user_type === 'bhw') {
                supabaseInsert('activity_logs', [
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'action_type' => 'CREATE',
                    'description' => $user_name . ' created SMS notification settings (API Key and Device ID)',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'System settings saved successfully. Daily vaccination notifications will use the new SMS configuration.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save system settings'
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error saving system settings: ' . $e->getMessage()
    ]);
}

// Function to update all system files with new settings
function updateSystemFiles($settings) {
    try {
        // Update notification service file
        updateNotificationServiceFile($settings);
        
        // Update forgot password file
        updateForgotPasswordFile($settings);
        
        // Update create account file
        updateCreateAccountFile($settings);
        
        // Update send OTP file
        updateSendOTPFile($settings);
        
    } catch (Exception $e) {
        // Log error but don't fail the save operation
        error_log('Failed to update system files: ' . $e->getMessage());
    }
}

// Function to update the notification service file
function updateNotificationServiceFile($settings) {
    $file_path = __DIR__ . '/send_schedule_notifications.php';
    $file_content = file_get_contents($file_path);
    
    // Update email settings
    $file_content = preg_replace(
        '/\$mail->Username = \'[^\']*\';/',
        '$mail->Username = \'' . $settings['email_username'] . '\';',
        $file_content
    );
    
    $file_content = preg_replace(
        '/\$mail->Password = \'[^\']*\';/',
        '$mail->Password = \'' . $settings['email_password'] . '\';',
        $file_content
    );
    
    $file_content = preg_replace(
        '/\$mail->setFrom\(\'[^\']*\', \'[^\']*\'\);/',
        '$mail->setFrom(\'' . $settings['email_username'] . '\', \'' . $settings['health_center_name'] . '\');',
        $file_content
    );
    
    // Update SMS settings
    $file_content = preg_replace(
        '/\$apiKey = \'[^\']*\';/',
        '$apiKey = \'' . $settings['sms_api_key'] . '\';',
        $file_content
    );
    
    $file_content = preg_replace(
        '/\$deviceId = \'[^\']*\';/',
        '$deviceId = \'' . $settings['sms_device_id'] . '\';',
        $file_content
    );
    
    // Update system name in SMS message
    $file_content = preg_replace(
        '/City Health Department, Ormoc City/',
        $settings['health_center_name'],
        $file_content
    );
    
    file_put_contents($file_path, $file_content);
}

// Function to update the forgot password file
function updateForgotPasswordFile($settings) {
    $file_path = __DIR__ . '/../../forgot_password.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        
        // Update SMS settings
        $file_content = preg_replace(
            '/\$apiKey = \'[^\']*\';/',
            '$apiKey = \'' . $settings['sms_api_key'] . '\';',
            $file_content
        );
        
        $file_content = preg_replace(
            '/\$deviceId = \'[^\']*\';/',
            '$deviceId = \'' . $settings['sms_device_id'] . '\';',
            $file_content
        );
        
        file_put_contents($file_path, $file_content);
    }
}

// Function to update the create account file
function updateCreateAccountFile($settings) {
    $file_path = __DIR__ . '/../../create_account.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        
        // Update email settings
        $file_content = preg_replace(
            '/\$mail->Username = \'[^\']*\';/',
            '$mail->Username = \'' . $settings['email_username'] . '\';',
            $file_content
        );
        
        $file_content = preg_replace(
            '/\$mail->Password = \'[^\']*\';/',
            '$mail->Password = \'' . $settings['email_password'] . '\';',
            $file_content
        );
        
        file_put_contents($file_path, $file_content);
    }
}

// Function to update the send OTP file
function updateSendOTPFile($settings) {
    $file_path = __DIR__ . '/../../send_otp.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        
        // Update SMS settings
        $file_content = preg_replace(
            '/\$apiKey = \'[^\']*\';/',
            '$apiKey = \'' . $settings['sms_api_key'] . '\';',
            $file_content
        );
        
        $file_content = preg_replace(
            '/\$deviceId = \'[^\']*\';/',
            '$deviceId = \'' . $settings['sms_device_id'] . '\';',
            $file_content
        );
        
        file_put_contents($file_path, $file_content);
    }
}
?>
