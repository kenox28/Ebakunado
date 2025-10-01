<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    $results = [
        'test_connection' => false,
        'immunization_records_count' => 0,
        'child_health_records_count' => 0,
        'users_count' => 0,
        'sample_immunization' => [],
        'sample_child' => [],
        'sample_user' => [],
        'tomorrow_date' => date('Y-m-d', strtotime('+1 day')),
        'today_date' => date('Y-m-d')
    ];

    // Test database connection
    $supabase = getSupabase();
    if ($supabase) {
        $results['test_connection'] = true;
        error_log('SMS Test - Database connection successful');
    } else {
        $results['test_connection'] = false;
        error_log('SMS Test - Database connection failed');
    }

    // Test immunization_records table
    $immunization_count = supabaseCount('immunization_records');
    $results['immunization_records_count'] = $immunization_count;
    error_log('SMS Test - Immunization records count: ' . $immunization_count);

    // Test child_health_records table
    $child_count = supabaseCount('child_health_records');
    $results['child_health_records_count'] = $child_count;
    error_log('SMS Test - Child health records count: ' . $child_count);

    // Test users table
    $users_count = supabaseCount('users');
    $results['users_count'] = $users_count;
    error_log('SMS Test - Users count: ' . $users_count);

    // Get sample immunization record
    $sample_immunization = supabaseSelect('immunization_records', '*', [], 'id.asc', 1);
    if ($sample_immunization) {
        $results['sample_immunization'] = $sample_immunization[0];
    }

    // Get sample child health record
    $sample_child = supabaseSelect('child_health_records', '*', [], 'id.asc', 1);
    if ($sample_child) {
        $results['sample_child'] = $sample_child[0];
    }

    // Get sample user
    $sample_user = supabaseSelect('users', '*', [], 'id.asc', 1);
    if ($sample_user) {
        $results['sample_user'] = $sample_user[0];
    }

    // Test specific queries
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $pending_tomorrow = supabaseSelect(
        'immunization_records', 
        'baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $tomorrow, 'status' => 'pending'], 
        'schedule_date.asc'
    );
    $results['pending_tomorrow'] = $pending_tomorrow ?: [];
    $results['pending_tomorrow_count'] = count($results['pending_tomorrow']);

    $missed_records = supabaseSelect(
        'immunization_records', 
        'baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['status' => 'missed'], 
        'schedule_date.desc'
    );
    $results['missed_records'] = $missed_records ?: [];
    $results['missed_records_count'] = count($results['missed_records']);

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (Exception $e) {
    error_log('SMS Test Error: ' . $e->getMessage());
    error_log('SMS Test Stack trace: ' . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'Test failed: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
