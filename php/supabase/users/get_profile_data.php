<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Please log in first']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Get user data
    $data = supabaseSelect('users', '*', ['user_id' => $user_id]);
    
    if ($data && !empty($data)) {
        // Unwrap first row if supabaseSelect returned an array of rows
        if (is_array($data) && isset($data[0])) {
            $data = $data[0];
        }
        $data['user_type'] = 'user';
        
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Profile not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("User profile data error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load profile data'
    ]);
}
?>
