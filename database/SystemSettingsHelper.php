<?php
/**
 * System Settings Helper
 * 
 * This helper provides functions to retrieve TextBee credentials for different purposes:
 * - getOTPCredentials() - For OTP/Send OTP (uses SuperAdmin's credentials)
 * - getNotificationCredentials() - For daily vaccination notifications (uses Midwife's credentials)
 */

/**
 * Get TextBee credentials for OTP/Authentication (SuperAdmin's credentials)
 * 
 * @return array Returns ['api_key' => '', 'device_id' => ''] or default values
 */
function getOTPCredentials() {
    try {
        // Get SuperAdmin's settings
        $settings = supabaseSelect('system_settings', 'sms_api_key,sms_device_id', [
            'user_type' => 'super_admin'
        ], null, 1);
        
        if ($settings && count($settings) > 0) {
            return [
                'api_key' => $settings[0]['sms_api_key'] ?? '859e05f9-b29e-4071-b29f-0bd14a273bc2',
                'device_id' => $settings[0]['sms_device_id'] ?? '687e5760c87689a0c22492b3'
            ];
        }
        
        // Return default credentials if no settings found
        return [
            'api_key' => '859e05f9-b29e-4071-b29f-0bd14a273bc2',
            'device_id' => '687e5760c87689a0c22492b3'
        ];
        
    } catch (Exception $e) {
        error_log('Error getting OTP credentials: ' . $e->getMessage());
        // Return default credentials on error
        return [
            'api_key' => '859e05f9-b29e-4071-b29f-0bd14a273bc2',
            'device_id' => '687e5760c87689a0c22492b3'
        ];
    }
}

/**
 * Get TextBee credentials for Daily Notifications (Midwife's credentials)
 * 
 * @return array Returns ['api_key' => '', 'device_id' => ''] or default values
 */
function getNotificationCredentials() {
    try {
        // Get Midwife's settings (try both 'midwife' and 'midwifes' for compatibility)
        $settings = supabaseSelect('system_settings', 'sms_api_key,sms_device_id', [
            'user_type' => 'midwife'
        ], null, 1);
        
        // If not found, try with 'midwifes'
        if (!$settings || count($settings) === 0) {
            $settings = supabaseSelect('system_settings', 'sms_api_key,sms_device_id', [
                'user_type' => 'midwifes'
            ], null, 1);
        }
        
        if ($settings && count($settings) > 0) {
            return [
                'api_key' => $settings[0]['sms_api_key'] ?? '859e05f9-b29e-4071-b29f-0bd14a273bc2',
                'device_id' => $settings[0]['sms_device_id'] ?? '687e5760c87689a0c22492b3'
            ];
        }
        
        // Return default credentials if no settings found
        return [
            'api_key' => '859e05f9-b29e-4071-b29f-0bd14a273bc2',
            'device_id' => '687e5760c87689a0c22492b3'
        ];
        
    } catch (Exception $e) {
        error_log('Error getting notification credentials: ' . $e->getMessage());
        // Return default credentials on error
        return [
            'api_key' => '859e05f9-b29e-4071-b29f-0bd14a273bc2',
            'device_id' => '687e5760c87689a0c22492b3'
        ];
    }
}
?>
