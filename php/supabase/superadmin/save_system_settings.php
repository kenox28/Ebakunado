<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Check if user is authorized (SuperAdmin only)
if (!isset($_SESSION['super_admin_id'])) {
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
    $user_id = $_SESSION['super_admin_id'];
    $user_type = 'super_admin';
    $user_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
    
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
            // Log activity for super admin settings update
            supabaseInsert('activity_logs', [
                'user_id' => $user_id,
                'user_type' => $user_type,
                'action_type' => 'UPDATE',
                'description' => $user_name . ' updated system SMS settings (API Key and Device ID) for OTP and daily notifications',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'System settings updated successfully. OTP, authentication, and daily notification features will use the new configuration.'
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
            // Log activity for super admin settings creation
            supabaseInsert('activity_logs', [
                'user_id' => $user_id,
                'user_type' => $user_type,
                'action_type' => 'CREATE',
                'description' => $user_name . ' created system SMS settings (API Key and Device ID) for OTP and daily notifications',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'System settings saved successfully. OTP, authentication, and daily notification features will use the new configuration.'
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
?>
