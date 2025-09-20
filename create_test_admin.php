<?php
// Create a test admin account for Supabase
include "database/SupabaseConfig.php";
include "database/DatabaseHelper.php";

echo "<h2>Creating Test Admin Account</h2>";

try {
    // Check if admin table exists and create if needed
    if (function_exists('initializeSupabaseTables')) {
        initializeSupabaseTables($supabase);
    }
    
    // Check if admin already exists
    $existing_admin = supabaseSelect('admin', 'admin_id', ['email' => 'admin@gmail.com']);
    
    if ($existing_admin && !empty($existing_admin)) {
        echo "<p style='color: orange;'>⚠️ Admin account already exists!</p>";
        echo "<p>Email: admin@gmail.com</p>";
        echo "<p>Password: admin123456</p>";
    } else {
        // Create test admin account
        $admin_id = 'ADM001';
        $fname = 'Test';
        $lname = 'Admin';
        $email = 'admin@gmail.com';
        $password = 'admin123456';
        
        // Hash password without salt (admin table doesn't have salt column)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $admin_data = [
            'admin_id' => $admin_id,
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'pass' => $hashed_password,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = supabaseInsert('admin', $admin_data);
        
        if ($result !== false) {
            echo "<p style='color: green;'>✅ Test admin account created successfully!</p>";
            echo "<p><strong>Login Credentials:</strong></p>";
            echo "<p>Email: admin@gmail.com</p>";
            echo "<p>Password: admin123456</p>";
            echo "<p>Admin ID: ADM001</p>";
            
            // Log the creation
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            supabaseLogActivity($admin_id, 'admin', 'account_created', 'Test admin account created', $ip);
            
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin account</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='views/login.php'>← Back to Login</a></p>";
?>
