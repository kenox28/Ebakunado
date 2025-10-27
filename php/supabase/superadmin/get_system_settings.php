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

try {
    // Get current user info
    $user_id = $_SESSION['super_admin_id'];
    $user_type = 'super_admin';
    
    // Get system settings from database for this user
    $settings = supabaseSelect('system_settings', '*', [
        'user_id' => $user_id,
        'user_type' => $user_type
    ], null, 1);
    
    if ($settings && count($settings) > 0) {
        $settings_data = $settings[0];
        
        // Don't expose the full password in response
        $settings_data['email_password'] = $settings_data['email_password'] ? '••••••••' : '';
        
        echo json_encode([
            'status' => 'success',
            'settings' => $settings_data
        ]);
    } else {
        // Return default settings if none exist
        echo json_encode([
            'status' => 'success',
            'settings' => [
                'user_id' => $user_id,
                'user_type' => $user_type,
                'email_username' => 'iquenxzx@gmail.com',
                'email_password' => '',
                'sms_api_key' => '859e05f9-b29e-4071-b29f-0bd14a273bc2',
                'sms_device_id' => '687e5760c87689a0c22492b3',
                'notification_time' => '02:40',
                'system_name' => 'eBakunado',
                'health_center_name' => 'City Health Department, Ormoc City',
                'updated_at' => 'Never'
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to get system settings: ' . $e->getMessage()
    ]);
}
?>
