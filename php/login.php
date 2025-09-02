<?php
session_start();
include "../database/Database.php";

// Initialize database tables if they don't exist
if (function_exists('initializeDatabase')) {
    initializeDatabase($connect);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log session state
error_log("Session state - user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("Session state - admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'not set'));
error_log("Session state - super_admin_id: " . (isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : 'not set'));

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid request method"
    ]);
    exit();
}

// Get form data
$email_or_phone = trim($_POST['Email_number']);
$password = $_POST['password'];

// Validate input
if (empty($email_or_phone) || empty($password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Email/Phone and password are required"
    ]);
    exit();
}

try {
    $user_found = false;
    $user_data = null;
    $user_type = null;
    
    // Check if input is email or phone number
    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        // First check super admin table
        $sql = "SELECT super_admin_id as id, fname, lname, email, pass FROM super_admin WHERE email = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("s", $email_or_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $user_type = 'super_admin';
            $user_found = true;
        }
        $stmt->close();
        
        // If not found in super admin, check admin table
        if (!$user_found) {
            $sql = "SELECT admin_id as id, fname, lname, email, pass FROM admin WHERE email = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("s", $email_or_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $user_type = 'admin';
                $user_found = true;
            }
            $stmt->close();
        }
        
        // If not found in admin tables, check users table
        if (!$user_found) {
            $sql = "SELECT user_id as id, fname, lname, email, phone_number, passw, salt, role FROM users WHERE email = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("s", $email_or_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $user_type = 'user';
                $user_found = true;
            }
            $stmt->close();
        }
    } else {
        // Phone number - only check users table
        $sql = "SELECT user_id as id, fname, lname, email, phone_number, passw, salt, role FROM users WHERE phone_number = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $user_type = 'user';
            $user_found = true;
        }
        $stmt->close();
    }
    
    // Debug: Log what was found
    error_log("User found: " . ($user_found ? 'YES' : 'NO'));
    error_log("User type: " . ($user_type ? $user_type : 'NONE'));
    
    if (!$user_found) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid email/phone or password"
        ]);
        exit();
    }
    
    // Verify password based on user type
    $password_valid = false;
    
    if ($user_type === 'super_admin' || $user_type === 'admin') {
        // Admin and super admin use MD5
        $password_valid = (md5($password) === $user_data['pass']);
        error_log("MD5 password check - Input: " . md5($password) . ", Stored: " . $user_data['pass'] . ", Valid: " . ($password_valid ? 'YES' : 'NO'));
    } else {
        // Regular users use password_verify with salt
        $stored_salt = $user_data['salt'];
        $stored_hash = $user_data['passw'];
        $password_with_salt = $password . $stored_salt;
        $password_valid = password_verify($password_with_salt, $stored_hash);
        error_log("Password verify check - Valid: " . ($password_valid ? 'YES' : 'NO'));
    }
    
    if ($password_valid) {
        // Clear any existing session data first
        session_unset();
        
        // Password is correct - create session based on user type
        if ($user_type === 'super_admin') {
            $_SESSION['super_admin_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['user_type'] = 'super_admin';
            $_SESSION['logged_in'] = true;
            
            $log_user_type = 'super_admin';
            $log_description = 'Super admin logged in successfully';
        } elseif ($user_type === 'admin') {
            $_SESSION['admin_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['logged_in'] = true;
            
            $log_user_type = 'admin';
            $log_description = 'Admin logged in successfully';
        } else {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['phone_number'] = $user_data['phone_number'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['user_type'] = 'user';
        $_SESSION['logged_in'] = true;
            
            $log_user_type = 'user';
            $log_description = 'User logged in successfully';
        }
        
        // Log successful login
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'login_success', ?, ?, NOW())";
        $log_stmt = $connect->prepare($log_sql);
        $log_stmt->bind_param("ssss", $user_data['id'], $log_user_type, $log_description, $ip);
        $log_stmt->execute();
        $log_stmt->close();
        
        error_log("Login successful for user type: " . $user_type);
        
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user_type" => $user_type,
            "user" => [
                "fname" => $user_data['fname'],
                "lname" => $user_data['lname'],
                "email" => $user_data['email']
            ]
        ]);
    } else {
        // Password is incorrect
        error_log("Password verification failed for: " . $email_or_phone);
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid email/phone or password"
        ]);
    }
    
} catch (Exception $e) {
    error_log("Login exception: " . $e->getMessage());
    echo json_encode([
        "status" => "failed",
        "message" => "Login error occurred. Please try again."
    ]);
}
?>
