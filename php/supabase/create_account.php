<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";

// Set Philippines timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

// Initialize database tables if they don't exist
if (function_exists('initializeSupabaseTables')) {
    initializeSupabaseTables($supabase);
}
require_once __DIR__ . '/../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Protection
// Mobile App Support - Add this BEFORE the CSRF token check
$is_mobile_app = isset($_POST['mobile_app_request']) && $_POST['mobile_app_request'] === 'true';

if ($is_mobile_app) {
    // For mobile app requests, we'll use a special token system
    $expected_mobile_token = 'BYPASS_FOR_MOBILE_APP'; // You can make this more secure
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $expected_mobile_token) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid mobile app token."
        ]);
        exit();
    }
    
    // Skip OTP verification for mobile app if requested
    if (isset($_POST['skip_otp']) && $_POST['skip_otp'] === 'true') {
        // Set session variables to simulate OTP verification
        $_SESSION['otp_verified'] = true;
        $_SESSION['verified_phone'] = $_POST['number'];
    }
} else {
    // Original CSRF Protection for web requests
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid request token."
        ]);
        exit();
    }
}
// Rate Limiting - Check if too many requests from this IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = "rate_limit_" . $ip;
$current_time = time();
$max_attempts = 5; // Maximum attempts per hour
$time_window = 3600; // 1 hour in seconds

if (isset($_SESSION[$rate_limit_key])) {
    $attempts = $_SESSION[$rate_limit_key];
    if ($attempts['count'] >= $max_attempts && ($current_time - $attempts['timestamp']) < $time_window) {
        echo json_encode([
            "status" => "failed",
            "message" => "Too many attempts. Please try again later."
        ]);
        exit();
    }
}

// Check if OTP was verified (match MySQL)
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    echo json_encode([
        "status" => "failed",
        "message" => "Phone number verification required. Please verify your phone number with OTP."
    ]);
    exit();
}

// Get form data (mirror MySQL logic and sanitization level)
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$number = isset($_POST['number']) ? preg_replace('/[^0-9]/', '', $_POST['number']) : '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$fname = isset($_POST['fname']) ? $_POST['fname'] : '';
$lname = isset($_POST['lname']) ? $_POST['lname'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$province = isset($_POST['province']) ? $_POST['province'] : '';
$city_municipality = isset($_POST['city_municipality']) ? $_POST['city_municipality'] : '';
$barangay = isset($_POST['barangay']) ? $_POST['barangay'] : '';
$purok = isset($_POST['purok']) ? $_POST['purok'] : '';

// Combine place information into a single place field
$place = trim($province . ", " . $city_municipality . ", " . $barangay . ", " . $purok);

// Validation
$errors = [];

if (empty($fname)) $errors[] = "First name is required.";
if (empty($lname)) $errors[] = "Last name is required.";
if (empty($email)) $errors[] = "Email is required.";
if (empty($number)) $errors[] = "Phone number is required.";
if (empty($password)) $errors[] = "Password is required.";
if (empty($confirm_password)) $errors[] = "Confirm password is required.";
if (empty($gender)) $errors[] = "Gender is required.";
if (empty($place)) $errors[] = "Place is required.";

if (!empty($errors)) {
    echo json_encode([
        "status" => "failed",
        "message" => implode(" ", $errors)
    ]);
    exit();
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid email format."
    ]);
    exit();
}

// Password validation
if (strlen($password) < 8) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password must be at least 8 characters long."
    ]);
    exit();
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one uppercase letter."
    ]);
    exit();
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one lowercase letter."
    ]);
    exit();
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one number."
    ]);
    exit();
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one special character."
    ]);
    exit();
}

if ($password !== $confirm_password) {
    echo json_encode([
        "status" => "failed",
        "message" => "Passwords do not match."
    ]);
    exit();
}

// Phone number validation
if (!preg_match('/^[0-9]{10,15}$/', $number)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid phone number format."
    ]);
    exit();
}

try {
    // Check if the phone number matches the verified one
    if (!isset($_SESSION['verified_phone']) || ($_SESSION['verified_phone'] !== $number && $_SESSION['verified_phone'] !== '+63' . substr($number, 1))) {
        echo json_encode([
            "status" => "failed",
            "message" => "Phone number doesn't match the verified number."
        ]);
        exit();
    }

    // Email domain MX check
    $domain = substr(strrchr($email, "@"), 1);
    if (!$domain || !checkdnsrr($domain, "MX")) {
        echo json_encode([
            "status" => "failed",
            "message" => "Email domain does not exist or cannot receive email."
        ]);
        exit();
    }

    // Check if email already exists using Supabase
    $existing_email = supabaseSelect('users', 'user_id', ['email' => $email]);
    if ($existing_email && !empty($existing_email)) {
        echo json_encode([
            "status" => "failed",
            "message" => "Email already exists."
        ]);
        exit();
    }

    // Check if phone number already exists using Supabase
    $existing_phone = supabaseSelect('users', 'user_id', ['phone_number' => $number]);
    if ($existing_phone && !empty($existing_phone)) {
        echo json_encode([
            "status" => "failed",
            "message" => "Phone number already exists."
        ]);
        exit();
    }

    // Generate secure random user ID (match MySQL version style: 32 hex)
    $user_id = bin2hex(random_bytes(16));

    // Hash password with salt
    $salt = bin2hex(random_bytes(32)); // 64 character hex string
    $hashed_password = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);

    // Try to send welcome email before insert (match MySQL)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iquenxzx@gmail.com';
        $mail->Password   = 'lews hdga hdvb glym';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('iquenxzx@gmail.com', 'Ebakunado System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Ebakunado System';
        $mail->Body    = "Hello " . ($fname ? $fname : 'there') . ",<br><br>Welcome to the Ebakunado System! Your account has been successfully created.<br><br>Name: " . ($fname ? $fname . ' ' . $lname : 'Not provided') . "<br>Email: $email<br>Phone Number: $number<br><br>Please keep your credentials safe and never share them with anyone.";
        $mail->send();
    } catch (Exception $e) {
        echo json_encode([
            "status" => "failed",
            "message" => "Failed to send email. Please use a real email address."
        ]);
        exit();
    }

    // Get current Philippines time
    $current_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $philippines_time = $current_time->format('Y-m-d H:i:s');
    
    // Prepare user data for Supabase
    $user_data = [
        'user_id' => $user_id,
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'phone_number' => $number,
        'passw' => $hashed_password,
        'salt' => $salt,
        'gender' => $gender,
        'place' => $place,
        'created_at' => $philippines_time,
        'updated' => $philippines_time,
        'role' => 'user'
    ];

    // Insert user using Supabase
    $result = supabaseInsert('users', $user_data);

    if ($result !== false) {
        // Update rate limiting window to track successful create
        $_SESSION[$rate_limit_key] = [
            'count' => isset($_SESSION[$rate_limit_key]) ? $_SESSION[$rate_limit_key]['count'] + 1 : 1,
            'timestamp' => $current_time
        ];

        // Log activity
        supabaseLogActivity($user_id, 'user', 'account_created', 'New user account created', $ip);

        // Clear OTP session
        unset($_SESSION['otp_verified']);
        unset($_SESSION['verified_phone']);

        echo json_encode([
            "status" => "success",
            "message" => "Successfully created account"
        ]);
    } else {
        $detailed = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
        $status = isset($supabase) && method_exists($supabase, 'getLastStatus') ? $supabase->getLastStatus() : null;
        echo json_encode([
            "status" => "failed",
            "message" => "Failed to create account. Please try again.",
            "debug" => [
                "http_status" => $status,
                "supabase_error" => $detailed
            ]
        ]);
    }

} catch (Exception $e) {
    error_log("Create account error: " . $e->getMessage());
    echo json_encode([
        "status" => "failed",
        "message" => "An error occurred. Please try again."
    ]);
}

// Clear sensitive data from memory
unset($password, $confirm_password, $hashed_password, $salt);
?>
