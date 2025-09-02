<?php
session_start();
include "../database/Database.php";

// Initialize database tables if they don't exist
if (function_exists('initializeDatabase')) {
    initializeDatabase($connect);
}
require_once __DIR__ . '/../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid request token."
    ]);
    exit();
}

// Rate Limiting - Check if too many requests from this IP
$ip = $_SERVER['REMOTE_ADDR'];
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

// Input validation and sanitization
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$number = filter_var($_POST['number'], FILTER_SANITIZE_NUMBER_INT);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$fname = isset($_POST['fname']) ? filter_var($_POST['fname'], FILTER_SANITIZE_STRING) : '';
$lname = isset($_POST['lname']) ? filter_var($_POST['lname'], FILTER_SANITIZE_STRING) : '';
$gender = isset($_POST['gender']) ? filter_var($_POST['gender'], FILTER_SANITIZE_STRING) : '';
$bdate = isset($_POST['bdate']) ? filter_var($_POST['bdate'], FILTER_SANITIZE_STRING) : '';

// Additional input validation
if (empty($email) || empty($number) || empty($password) || empty($confirm_password)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Email, phone number, password, and confirm password are required."
    ]);
    exit();
}

// Phone number validation (basic format check)
if (!preg_match('/^[0-9]{10,15}$/', $number)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid phone number format."
    ]);
    exit();
}

// Enhanced email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid email format."
    ]);
    exit();
}

// Password strength validation
$password_errors = [];
if (strlen($password) < 8) {
    $password_errors[] = "Password must be at least 8 characters long.";
}
if (!preg_match('/[A-Z]/', $password)) {
    $password_errors[] = "Password must contain at least one uppercase letter.";
}
if (!preg_match('/[a-z]/', $password)) {
    $password_errors[] = "Password must contain at least one lowercase letter.";
}
if (!preg_match('/[0-9]/', $password)) {
    $password_errors[] = "Password must contain at least one number.";
}
if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $password_errors[] = "Password must contain at least one special character.";
}

if (!empty($password_errors)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password requirements not met: " . implode(" ", $password_errors)
    ]);
    exit();
}

// Check if passwords match
if ($password !== $confirm_password) {
    echo json_encode([
        "status" => "failed",
        "message" => "Passwords do not match."
    ]);
    exit();
}

// Check for common weak passwords
$weak_passwords = ['password', '123456', 'qwerty', 'admin', 'letmein', 'welcome'];
if (in_array(strtolower($password), $weak_passwords)) {
    echo json_encode([
        "status" => "failed",
        "message" => "Password is too common. Please choose a stronger password."
    ]);
    exit();
}

// Basic domain validation
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX")) {
    echo json_encode([
        "status" => "failed",
        "message" => "Email domain does not exist or cannot receive email."
    ]);
    exit();
}

// Use MailboxLayer API for advanced validation
// $access_key = '077ad33104db39bfa9b71cd938ea2c00'; // <-- Replace with your key
// $validate_url = "http://apilayer.net/api/check?access_key=$access_key&email=" . urlencode($email) . "&smtp=1&format=1";
// $api_response = file_get_contents($validate_url);
// $api_result = json_decode($api_response, true);

// if (!$api_result['format_valid'] || !$api_result['mx_found'] || $api_result['disposable'] || !$api_result['smtp_check']) {
//     echo json_encode([
//         "status" => "failed",
//         "message" => "Email address is invalid, disposable, or undeliverable."
//     ]);
//     exit();
// }

// Check if email exists in users table with prepared statement
$check_user = $connect->prepare("SELECT email FROM users WHERE email = ?");
$check_user->bind_param("s", $email);
$check_user->execute();
$check_user->store_result();

if ($check_user->num_rows > 0) {
    echo json_encode(array(
        'status' => 'failed',
        'message' => 'Email already exists'
    ));
    exit();
}
$check_user->close();

// Generate secure random user ID
$userid = bin2hex(random_bytes(16)); // 32 character hex string

// Generate secure random salt for additional security
$salt = bin2hex(random_bytes(32)); // 64 character hex string

// Use bcrypt for password hashing (more secure than MD5)
$hashed_password = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);

// Generate secure profile image name
$img = 'noprofile.png';

// Try to send welcome email
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

// Insert user with prepared statement for SQL injection prevention
$sql = "INSERT INTO users (user_id, fname, lname, email, phone_number, passw, salt, profileImg, failed_attempts, lockout_time, gender, bdate, created_at, updated, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NULL, ?, ?, NOW(), NOW(), 'user')";

$stmt = $connect->prepare($sql);
$stmt->bind_param("ssssssssss", $userid, $fname, $lname, $email, $number, $hashed_password, $salt, $img, $gender, $bdate);

if ($stmt->execute()) {
    // Update rate limiting
    $_SESSION[$rate_limit_key] = [
        'count' => isset($_SESSION[$rate_limit_key]) ? $_SESSION[$rate_limit_key]['count'] + 1 : 1,
        'timestamp' => $current_time
    ];
    
    // Log successful account creation
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, 'user', 'account_created', 'New user account created', ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $log_stmt->bind_param("ss", $userid, $ip);
    $log_stmt->execute();
    $log_stmt->close();
    
    echo json_encode(["status" => "success", "message" => "Successfully created account"]);
} else {
    echo json_encode(["status" => "failed", "message" => "Failed to create account"]);
}

$stmt->close();

// Clear sensitive data from memory
unset($password, $confirm_password, $hashed_password, $salt);
?>
