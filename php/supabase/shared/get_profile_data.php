<?php
session_start();
header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    // Get Supabase instance to verify connection
    $supabase = getSupabase();
    if (!$supabase) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
        exit();
    }

    // Accept possible variations in session value for midwife
    $data = null;
    if ($user_type === 'midwife' || $user_type === 'midwifes' || strtolower($user_type) === 'midwives') {
        // Get midwife data
        $data = supabaseSelect('midwives', '*', ['midwife_id' => $user_id]);
    } else {
        // Get BHW data
        $data = supabaseSelect('bhw', '*', ['bhw_id' => $user_id]);
    }
    
    if ($data === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database query failed'
        ]);
        exit();
    }
    
    if ($data && !empty($data)) {
        // Unwrap first row if supabaseSelect returned an array of rows
        if (is_array($data) && isset($data[0])) {
            $data = $data[0];
        }
        // Ensure user_type is set
        if (!isset($data['user_type'])) {
            $data['user_type'] = $user_type;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        // Try to provide debug info if Supabase helper exposes errors
        $debug = [];
        if (isset($supabase) && is_object($supabase)) {
            if (method_exists($supabase, 'getLastError')) $debug['last_error'] = $supabase->getLastError();
            if (method_exists($supabase, 'getLastStatus')) $debug['last_status'] = $supabase->getLastStatus();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Profile not found or query returned empty result',
            'debug' => $debug,
            'user_id' => $user_id,
            'user_type' => $user_type
        ]);
    }
    
} catch (Exception $e) {
    error_log("Profile data error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load profile data: ' . $e->getMessage()
    ]);
}
?>
