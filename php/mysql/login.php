<?php
session_start();
include "../../database/Database.php";

// Initialize database tables if they don't exist
if (function_exists('initializeDatabase')) {
    initializeDatabase($connect);
}



// Check if user is already logged in
if (isset($_SESSION['super_admin_id'])) {
    echo json_encode([
        "status" => "already_logged_in",
        "message" => "Already logged in as Super Admin",
        "user_type" => "super_admin"
    ]);
    exit();
} elseif (isset($_SESSION['admin_id'])) {
    echo json_encode([
        "status" => "already_logged_in",
        "message" => "Already logged in as Admin",
        "user_type" => "admin"
    ]);
    exit();
} elseif (isset($_SESSION['bhw_id'])) {
    echo json_encode([
        "status" => "already_logged_in",
        "message" => "Already logged in as BHW",
        "user_type" => "bhw"
    ]);
    exit();
} elseif (isset($_SESSION['midwife_id'])) {
    echo json_encode([
        "status" => "already_logged_in",
        "message" => "Already logged in as Midwife",
        "user_type" => "midwife"
    ]);
    exit();
} elseif (isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "already_logged_in",
        "message" => "Already logged in as User",
        "user_type" => "user"
    ]);
    exit();
}

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
        
        // If not found in admin tables, check each table directly (since users now exist in only one table)
        if (!$user_found) {
            // First check BHW table
            $bhw_sql = "SELECT bhw_id as id, fname, lname, email, phone_number, pass, salt, permissions, role FROM bhw WHERE email = ?";
            $bhw_stmt = $connect->prepare($bhw_sql);
            $bhw_stmt->bind_param("s", $email_or_phone);
            $bhw_stmt->execute();
            $bhw_result = $bhw_stmt->get_result();
            
            if ($bhw_result->num_rows > 0) {
                $user_data = $bhw_result->fetch_assoc();
                $user_type = 'bhw';
                $user_found = true;
                error_log("DEBUG: Found BHW user: " . $user_data['email']);
            }
            $bhw_stmt->close();
            
            // If not found in BHW, check Midwives table
            if (!$user_found) {
                $midwife_sql = "SELECT midwife_id as id, fname, lname, email, phone_number, pass, salt, permissions, Approve, role FROM midwives WHERE email = ?";
                $midwife_stmt = $connect->prepare($midwife_sql);
                $midwife_stmt->bind_param("s", $email_or_phone);
                $midwife_stmt->execute();
                $midwife_result = $midwife_stmt->get_result();
                
                if ($midwife_result->num_rows > 0) {
                    $user_data = $midwife_result->fetch_assoc();
                    $user_type = 'midwife';
                    $user_found = true;
                    error_log("DEBUG: Found Midwife user: " . $user_data['email'] . ", approval status: " . $user_data['Approve']);
                }
                $midwife_stmt->close();
            }
            
            // If not found in BHW or Midwives, check Users table
            if (!$user_found) {
                $user_sql = "SELECT user_id as id, fname, lname, email, phone_number, passw, salt, role, gender, place FROM users WHERE email = ?";
                $user_stmt = $connect->prepare($user_sql);
                $user_stmt->bind_param("s", $email_or_phone);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                
                if ($user_result->num_rows > 0) {
                    $user_data = $user_result->fetch_assoc();
                    $user_type = 'user';
                    $user_found = true;
                    error_log("DEBUG: Found regular user: " . $user_data['email']);
                }
                $user_stmt->close();
            }
        }
    } else {
        // Phone number login - check each table directly (since users now exist in only one table)
        // First check BHW table
        $bhw_sql = "SELECT bhw_id as id, fname, lname, email, phone_number, pass, salt, permissions, role FROM bhw WHERE phone_number = ?";
        $bhw_stmt = $connect->prepare($bhw_sql);
        $bhw_stmt->bind_param("s", $email_or_phone);
        $bhw_stmt->execute();
        $bhw_result = $bhw_stmt->get_result();
        
        if ($bhw_result->num_rows > 0) {
            $user_data = $bhw_result->fetch_assoc();
            $user_type = 'bhw';
            $user_found = true;
            error_log("DEBUG: Found BHW user by phone: " . $user_data['phone_number']);
        }
        $bhw_stmt->close();
        
        // If not found in BHW, check Midwives table
        if (!$user_found) {
            $midwife_sql = "SELECT midwife_id as id, fname, lname, email, phone_number, pass, salt, permissions, Approve, role FROM midwives WHERE phone_number = ?";
            $midwife_stmt = $connect->prepare($midwife_sql);
            $midwife_stmt->bind_param("s", $email_or_phone);
            $midwife_stmt->execute();
            $midwife_result = $midwife_stmt->get_result();
            
            if ($midwife_result->num_rows > 0) {
                $user_data = $midwife_result->fetch_assoc();
                $user_type = 'midwife';
                $user_found = true;
                error_log("DEBUG: Found Midwife user by phone: " . $user_data['phone_number'] . ", approval status: " . $user_data['Approve']);
            }
            $midwife_stmt->close();
        }
        
        // If not found in BHW or Midwives, check Users table
        if (!$user_found) {
            $user_sql = "SELECT user_id as id, fname, lname, email, phone_number, passw, salt, role, gender , place FROM users WHERE phone_number = ?";
            $user_stmt = $connect->prepare($user_sql);
            $user_stmt->bind_param("s", $email_or_phone);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            
            if ($user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $user_type = 'user';
                $user_found = true;
                error_log("DEBUG: Found regular user by phone: " . $user_data['phone_number']);
            }
            $user_stmt->close();
        }
    }
    


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
        error_log("Admin login attempt - Email: $email_or_phone, User type: $user_type");
        error_log("Admin data - ID: " . $user_data['admin_id'] . ", Email: " . $user_data['email']);
        $password_valid = (md5($password) === $user_data['pass']);
        error_log("MD5 password check - Input: " . md5($password) . ", Stored: " . $user_data['pass'] . ", Valid: " . ($password_valid ? 'YES' : 'NO'));
        
        // If MD5 doesn't work, also check if password is stored as plain text (for debugging)
        if (!$password_valid && $password === $user_data['pass']) {
            $password_valid = true;
            error_log("Plain text password match found for admin");
        }
    } elseif ($user_type === 'bhw' || $user_type === 'midwife') {
        // BHW and Midwives use password_verify with salt (same as users)
        $stored_salt = $user_data['salt'] ?? '';
        $stored_hash = $user_data['pass']; // Note: BHW/Midwives use 'pass' column
        error_log("BHW/Midwife login - Salt: '$stored_salt', Hash length: " . strlen($stored_hash));
        
        if (!empty($stored_salt)) {
            $password_with_salt = $password . $stored_salt;
            $password_valid = password_verify($password_with_salt, $stored_hash);
            error_log("BHW/Midwife password verify check - Valid: " . ($password_valid ? 'YES' : 'NO'));
        } else {
            // Fallback: check if password is stored as plain text or MD5
            $password_valid = ($password === $stored_hash) || (md5($password) === $stored_hash);
            error_log("BHW/Midwife fallback password check - Valid: " . ($password_valid ? 'YES' : 'NO'));
        }
    } else {
        // Regular users use password_verify with salt
        $stored_salt = $user_data['salt'] ?? '';
        $stored_hash = $user_data['passw']; // Note: Users use 'passw' column
        error_log("User login - Salt: '$stored_salt', Hash length: " . strlen($stored_hash));
        
        if (!empty($stored_salt)) {
            $password_with_salt = $password . $stored_salt;
            $password_valid = password_verify($password_with_salt, $stored_hash);
            error_log("User password verify check - Valid: " . ($password_valid ? 'YES' : 'NO'));
        } else {
            // Fallback: check if password is stored as plain text or MD5
            $password_valid = ($password === $stored_hash) || (md5($password) === $stored_hash);
            error_log("User fallback password check - Valid: " . ($password_valid ? 'YES' : 'NO'));
        }
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
        } elseif ($user_type === 'bhw') {
            $_SESSION['bhw_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['phone_number'] = $user_data['phone_number'];
            $_SESSION['permissions'] = $user_data['permissions'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['user_type'] = 'bhw';
            $_SESSION['logged_in'] = true;
            
            $log_user_type = 'bhw';
            $log_description = 'BHW logged in successfully';
        } elseif ($user_type === 'midwife') {
            $_SESSION['midwife_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['phone_number'] = $user_data['phone_number'];
            $_SESSION['permissions'] = $user_data['permissions'];
            $_SESSION['approve'] = $user_data['Approve'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['user_type'] = 'midwife';
            $_SESSION['logged_in'] = true;
            
            $log_user_type = 'midwife';
            $log_description = 'Midwife logged in successfully';
        } else {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['fname'] = $user_data['fname'];
            $_SESSION['lname'] = $user_data['lname'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['phone_number'] = $user_data['phone_number'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['gender'] = $user_data['gender'];
            $_SESSION['place'] = $user_data['place'];
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