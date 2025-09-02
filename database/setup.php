<?php
// Database setup file for Ebakunado
// Run this file once to set up your database

echo "<h2>Ebakunado Database Setup</h2>";

// Include the database file
include "Database.php";

if (isset($connect) && $connect) {
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    echo "<p>Database: <strong>$database</strong></p>";
    
    // Initialize database tables
    if (function_exists('initializeDatabase')) {
        echo "<p>Initializing database tables...</p>";
        if (initializeDatabase($connect)) {
            echo "<p style='color: green;'>✓ Database tables initialized successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to initialize database tables</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ initializeDatabase function not found</p>";
    }
    
    // Show table status
    echo "<h3>Database Tables Status:</h3>";
    $tables = ['users', 'midwives', 'bhw', 'immunization_records', 'admin', 'super_admin', 'activity_logs'];
    
    foreach ($tables as $table) {
        $result = mysqli_query($connect, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
    
    echo "<h3>Default Admin Accounts:</h3>";
    echo "<p><strong>Admin:</strong> admin@gmail.com / admin123456</p>";
    echo "<p><strong>Super Admin:</strong> superadmin@gmail.com / superadmin123456</p>";
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Username and password are correct</li>";
    echo "<li>MySQL service is accessible</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><em>Setup complete. You can now use the system.</em></p>";
?> 