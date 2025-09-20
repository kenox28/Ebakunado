<?php
// Test Supabase Connection
include "database/SupabaseConfig.php";
include "database/DatabaseHelper.php";

echo "<h2>Supabase Connection Test</h2>";

if ($supabase) {
    echo "<p style='color: green;'>✓ Supabase connection successful!</p>";
    
    // Test a simple query
    $users = supabaseSelect('users', 'id', [], null, 1);
    if ($users !== false) {
        echo "<p style='color: green;'>✓ Database query successful!</p>";
        echo "<p>Query result: " . json_encode($users) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Database query failed</p>";
    }
    
    // Test admin table
    $admins = supabaseSelect('admin', 'admin_id,email', [], null, 1);
    if ($admins !== false) {
        echo "<p style='color: green;'>✓ Admin table query successful!</p>";
        echo "<p>Admin result: " . json_encode($admins) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin table query failed</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Supabase connection failed!</p>";
    echo "<p>Please check your Supabase credentials in SupabaseConfig.php</p>";
}

echo "<hr>";
echo "<h3>Configuration Check:</h3>";
echo "<p>Supabase URL: " . (isset($supabase_url) ? $supabase_url : 'Not set') . "</p>";
echo "<p>Supabase Key: " . (isset($supabase_key) ? substr($supabase_key, 0, 20) . '...' : 'Not set') . "</p>";
echo "<p>Service Key: " . (isset($supabase_service_key) ? substr($supabase_service_key, 0, 20) . '...' : 'Not set') . "</p>";
?>
