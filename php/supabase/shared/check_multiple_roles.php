<?php
/**
 * Helper function to check which tables a user exists in
 * Returns array of roles the user has
 * 
 * @param string $identifier - Can be user_id, bhw_id, midwife_id, email, or phone_number
 * @param string $type - Type of identifier: 'id', 'email', or 'phone'
 * @return array - Array of roles: ['user', 'bhw', 'midwife']
 */
function checkMultipleRoles($identifier, $type = 'id') {
    include_once "../../database/SupabaseConfig.php";
    include_once "../../database/DatabaseHelper.php";
    
    $roles = [];
    
    if ($type === 'id') {
        // Check by ID (user_id, bhw_id, midwife_id are the same value)
        $rows = supabaseSelect('users', '*', ['user_id' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'user';
        }
        
        $rows = supabaseSelect('bhw', '*', ['bhw_id' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'bhw';
        }
        
        $rows = supabaseSelect('midwives', '*', ['midwife_id' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'midwife';
        }
    } elseif ($type === 'email') {
        // Check by email
        $rows = supabaseSelect('users', '*', ['email' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'user';
        }
        
        $rows = supabaseSelect('bhw', '*', ['email' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'bhw';
        }
        
        $rows = supabaseSelect('midwives', '*', ['email' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'midwife';
        }
    } elseif ($type === 'phone') {
        // Check by phone_number
        $rows = supabaseSelect('users', '*', ['phone_number' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'user';
        }
        
        $rows = supabaseSelect('bhw', '*', ['phone_number' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'bhw';
        }
        
        $rows = supabaseSelect('midwives', '*', ['phone_number' => $identifier]);
        if ($rows && count($rows) > 0) {
            $roles[] = 'midwife';
        }
    }
    
    return $roles;
}
?>

