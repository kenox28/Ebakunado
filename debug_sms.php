<?php
// Direct debug file - access this in your browser to see errors
// URL: http://localhost/ebakunado/debug_sms.php

session_start();
include 'database/SupabaseConfig.php';
include 'database/DatabaseHelper.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SMS Notifications Debug</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Tomorrow: " . date('Y-m-d', strtotime('+1 day')) . "</p>";

// Check session
echo "<h2>Session Check</h2>";
if (isset($_SESSION['bhw_id'])) {
    echo "<p>✅ BHW ID: " . $_SESSION['bhw_id'] . "</p>";
} else {
    echo "<p>❌ No BHW session found</p>";
    echo "<p>Available session variables: " . print_r($_SESSION, true) . "</p>";
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $supabase = getSupabase();
    if ($supabase) {
        echo "<p>✅ Supabase connection successful</p>";
    } else {
        echo "<p>❌ Supabase connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection error: " . $e->getMessage() . "</p>";
}

// Test table access
echo "<h2>Table Access Test</h2>";
$tables_to_test = ['immunization_records', 'child_health_records', 'users'];

foreach ($tables_to_test as $table) {
    try {
        $count = supabaseCount($table);
        echo "<p>✅ Table '$table': $count records</p>";
        
        // Get sample data
        $sample = supabaseSelect($table, '*', [], 'id.asc', 1);
        if ($sample && count($sample) > 0) {
            echo "<p>Sample record columns: " . implode(', ', array_keys($sample[0])) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Table '$table' error: " . $e->getMessage() . "</p>";
    }
}

// Test specific queries
echo "<h2>Specific Query Test</h2>";
$tomorrow = date('Y-m-d', strtotime('+1 day'));

try {
    echo "<p>Testing upcoming schedules for date: $tomorrow</p>";
    $upcoming_data = supabaseSelect(
        'immunization_records', 
        'baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $tomorrow, 'status' => 'pending'], 
        'schedule_date.asc'
    );
    
    if ($upcoming_data !== false) {
        echo "<p>✅ Upcoming query successful: " . count($upcoming_data) . " records</p>";
        if (count($upcoming_data) > 0) {
            echo "<pre>" . print_r($upcoming_data[0], true) . "</pre>";
        }
    } else {
        echo "<p>❌ Upcoming query failed or returned false</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Upcoming query error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}

try {
    echo "<p>Testing missed schedules</p>";
    $missed_data = supabaseSelect(
        'immunization_records', 
        'baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['status' => 'missed'], 
        'schedule_date.desc'
    );
    
    if ($missed_data !== false) {
        echo "<p>✅ Missed query successful: " . count($missed_data) . " records</p>";
        if (count($missed_data) > 0) {
            echo "<pre>" . print_r($missed_data[0], true) . "</pre>";
        }
    } else {
        echo "<p>❌ Missed query failed or returned false</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Missed query error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}

// Test the actual endpoint
echo "<h2>Actual Endpoint Test</h2>";
echo "<p>Testing the actual SMS endpoint...</p>";

// Simulate the request
$_GET['type'] = 'both';

try {
    ob_start();
    include 'php/supabase/bhw/get_sms_notifications.php';
    $output = ob_get_clean();
    
    echo "<p>Endpoint response:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $json_response = json_decode($output, true);
    if ($json_response) {
        echo "<p>✅ Valid JSON response</p>";
        echo "<pre>" . print_r($json_response, true) . "</pre>";
    } else {
        echo "<p>❌ Invalid JSON response</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Endpoint error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
