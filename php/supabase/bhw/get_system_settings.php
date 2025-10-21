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

try {
    // Get system settings from database
    $settings = supabaseSelect('system_settings', '*', [], null, 1);
    
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
