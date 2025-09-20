<?php
// Check what tables exist in Supabase
include "database/SupabaseConfig.php";

echo "<h2>Checking Supabase Tables</h2>";

try {
    // Try to query different tables to see which ones exist
    $tables_to_check = ['users', 'admin', 'super_admin', 'bhw', 'midwives', 'activity_logs'];
    
    foreach ($tables_to_check as $table) {
        echo "<h3>Checking table: $table</h3>";
        try {
            $result = $supabase->from($table)->select('*')->limit(1)->execute();
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            if (isset($result->data) && !empty($result->data)) {
                echo "<p>Sample data: " . json_encode($result->data[0]) . "</p>";
            } else {
                echo "<p>Table is empty</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '$table' does not exist or error: " . $e->getMessage() . "</p>";
        }
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
