<?php
/**
 * Access Control System for BHW/Midwives Shared Interface
 * Role-based permissions and feature access control
 */

/**
 * Check if user has permission for specific action
 * @param string $action - The action to check
 * @param string $user_type - 'bhw' or 'midwife'
 * @param array $user_data - User session data
 * @return bool - True if user has permission
 */
function hasPermission($action, $user_type, $user_data = null) {
    switch ($user_type) {
        case 'bhw':
            // BHW permissions
            $bhw_permissions = [
                'view_dashboard' => true,
                'manage_immunization' => true,
                'approve_chr' => true,
                'view_child_records' => true,
                'access_chr_requests' => false, // BHW cannot access CHR requests
                'generate_reports' => false, // BHW cannot generate reports
                'manage_system_settings' => true,
                'view_target_client_list' => true,
                'edit_child_info' => true,
                'manage_feeding_status' => true,
                'manage_td_status' => true,
                'delete_immunization_records' => true,
                'scan_qr_codes' => true,
                'view_notifications' => true,
                'manage_notifications' => true
            ];
            return $bhw_permissions[$action] ?? false;
            
        case 'midwife':
            // Check if midwife is approved first
            if (!isset($user_data['approve']) || $user_data['approve'] != 1) {
                return false; // Not approved - limited access
            }
            
            $permission_level = $user_data['permissions'] ?? 'view';
            
            // Midwife permissions based on permission level
            $midwife_permissions = [
                'view' => [
                    'view_dashboard' => true,
                    'view_immunization' => true,
                    'view_child_records' => true,
                    'access_chr_requests' => true,
                    'generate_reports' => true,
                    'manage_immunization' => false,
                    'approve_chr' => false,
                    'manage_system_settings' => false,
                    'view_target_client_list' => true,
                    'edit_child_info' => false,
                    'manage_feeding_status' => false,
                    'manage_td_status' => false,
                    'delete_immunization_records' => false,
                    'scan_qr_codes' => true,
                    'view_notifications' => true,
                    'manage_notifications' => false
                ],
                'edit' => [
                    'view_dashboard' => true,
                    'view_immunization' => true,
                    'view_child_records' => true,
                    'access_chr_requests' => true,
                    'generate_reports' => true,
                    'manage_immunization' => true,
                    'approve_chr' => true,
                    'manage_system_settings' => false,
                    'view_target_client_list' => true,
                    'edit_child_info' => true,
                    'manage_feeding_status' => true,
                    'manage_td_status' => true,
                    'delete_immunization_records' => true,
                    'scan_qr_codes' => true,
                    'view_notifications' => true,
                    'manage_notifications' => true
                ],
                'admin' => [
                    'view_dashboard' => true,
                    'view_immunization' => true,
                    'view_child_records' => true,
                    'access_chr_requests' => true,
                    'generate_reports' => true,
                    'manage_immunization' => true,
                    'approve_chr' => true,
                    'manage_system_settings' => true,
                    'view_target_client_list' => true,
                    'edit_child_info' => true,
                    'manage_feeding_status' => true,
                    'manage_td_status' => true,
                    'delete_immunization_records' => true,
                    'scan_qr_codes' => true,
                    'view_notifications' => true,
                    'manage_notifications' => true
                ]
            ];
            
            return $midwife_permissions[$permission_level][$action] ?? false;
    }
    
    return false;
}

/**
 * Check if user can access specific feature
 * @param string $feature - The feature to check
 * @param string $user_type - 'bhw' or 'midwife'
 * @param array $user_data - User session data
 * @return bool - True if user can access feature
 */
function canAccessFeature($feature, $user_type, $user_data = null) {
    return hasPermission($feature, $user_type, $user_data);
}

/**
 * Get user type from session
 * @return string|null - 'bhw', 'midwife', or null
 */
function getCurrentUserType() {
    if (isset($_SESSION['bhw_id'])) {
        return 'bhw';
    } elseif (isset($_SESSION['midwife_id'])) {
        return 'midwife';
    }
    return null;
}

/**
 * Get current user data from session
 * @return array|null - User data array or null
 */
function getCurrentUserData() {
    $user_type = getCurrentUserType();
    
    if ($user_type === 'bhw') {
        return [
            'id' => $_SESSION['bhw_id'] ?? '',
            'fname' => $_SESSION['fname'] ?? '',
            'lname' => $_SESSION['lname'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'profileImg' => $_SESSION['profileImg'] ?? 'noprofile.png',
            'role' => 'BHW',
            'permissions' => 'full',
            'approve' => 1 // BHW are always approved
        ];
    } elseif ($user_type === 'midwife') {
        return [
            'id' => $_SESSION['midwife_id'] ?? '',
            'fname' => $_SESSION['fname'] ?? '',
            'lname' => $_SESSION['lname'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'profileImg' => $_SESSION['profileImg'] ?? 'noprofile.png',
            'role' => 'Midwife',
            'permissions' => $_SESSION['permissions'] ?? 'view',
            'approve' => $_SESSION['approve'] ?? 0
        ];
    }
    
    return null;
}

/**
 * Redirect if user doesn't have permission
 * @param string $action - The action to check
 * @param string $redirect_url - URL to redirect to if no permission
 */
function requirePermission($action, $redirect_url = 'home.php') {
    $user_type = getCurrentUserType();
    $user_data = getCurrentUserData();
    
    if (!$user_type || !canAccessFeature($action, $user_type, $user_data)) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Check if current user is midwife and approved
 * @return bool - True if midwife is approved
 */
function isApprovedMidwife() {
    return getCurrentUserType() === 'midwife' && 
           (getCurrentUserData()['approve'] ?? 0) == 1;
}

/**
 * Get permission level description
 * @param string $permission_level - 'view', 'edit', 'admin'
 * @return string - Description of permission level
 */
function getPermissionDescription($permission_level) {
    $descriptions = [
        'view' => 'View Only - Can view records and generate reports',
        'edit' => 'Edit Access - Can view, edit, and manage records',
        'admin' => 'Admin Access - Full system access including settings'
    ];
    
    return $descriptions[$permission_level] ?? 'Unknown permission level';
}
?>
