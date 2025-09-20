<?php
include "database/SupabaseConfig.php";
include "database/DatabaseHelper.php";

header('Content-Type: application/json');

$email = 'admin@gmail.com';

function summarize($rows) {
    if (!$rows || !is_array($rows)) return [];
    return array_map(function($r){
        $passField = isset($r['pass']) ? 'pass' : (isset($r['passw']) ? 'passw' : null);
        $passVal = $passField ? ($r[$passField] ?? '') : '';
        return [
            'id' => $r['admin_id'] ?? $r['super_admin_id'] ?? $r['user_id'] ?? null,
            'email' => $r['email'] ?? null,
            'has_pass' => isset($r['pass']) || isset($r['passw']),
            'pass_len' => $passVal !== '' ? strlen($passVal) : null,
            'pass_prefix' => $passVal !== '' ? substr($passVal, 0, 4) : null,
            'keys' => array_keys($r)
        ];
    }, $rows);
}

try {
    $admin = supabaseSelect('admin', '*', ['email' => $email]);
    $super = supabaseSelect('super_admin', '*', ['email' => $email]);
    $user  = supabaseSelect('users', '*', ['email' => $email]);

    // Unfiltered samples (first few rows)
    $admin_all = supabaseSelect('admin', '*', []);
    $super_all = supabaseSelect('super_admin', '*', []);
    $users_all = supabaseSelect('users', '*', []);
    
    echo json_encode([
        'using_service_key' => true,
        'admin' => summarize($admin),
        'super_admin' => summarize($super),
        'users' => summarize($user),
        'admin_count' => is_array($admin_all) ? count($admin_all) : null,
        'super_admin_count' => is_array($super_all) ? count($super_all) : null,
        'users_count' => is_array($users_all) ? count($users_all) : null,
        'admin_sample' => summarize(array_slice($admin_all ?: [], 0, 3)),
        'super_admin_sample' => summarize(array_slice($super_all ?: [], 0, 3)),
        'users_sample' => summarize(array_slice($users_all ?: [], 0, 3)),
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>


