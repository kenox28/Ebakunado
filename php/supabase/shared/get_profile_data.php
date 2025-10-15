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
    $supabase = getSupabase();
    
    if ($user_type === 'midwife') {
        // Get midwife data
        $response = $supabase->from('midwives')
            ->select('*')
            ->eq('midwife_id', $user_id)
            ->single();
    } else {
        // Get BHW data
        $response = $supabase->from('bhw')
            ->select('*')
            ->eq('bhw_id', $user_id)
            ->single();
    }
    
    if ($response->getData()) {
        $data = $response->getData();
        $data['user_type'] = $user_type;
        
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
    error_log("Profile data error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load profile data'
    ]);
}
?>
