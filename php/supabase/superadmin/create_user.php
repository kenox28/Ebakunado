<?php
// Start output buffering to catch any errors
ob_start();

// Set JSON content type header first
header('Content-Type: application/json');

// Turn off display_errors to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Error handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            "status" => "failed",
            "message" => "Server error occurred. Please check server logs.",
            "debug" => [
                "error_type" => $error['type'],
                "error_message" => $error['message'],
                "error_file" => basename($error['file']),
                "error_line" => $error['line']
            ]
        ]);
        exit();
    }
});

// Include files and set up - must be before use statements
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

// Set Philippines timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

// Initialize database tables if they don't exist
if (function_exists('initializeSupabaseTables')) {
    initializeSupabaseTables($supabase);
}
// Fix path for Windows compatibility - need one more level up since we're in superadmin subdirectory
$vendor_path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
if ($vendor_path === false) {
    // Fallback to forward slashes
    $vendor_path = __DIR__ . '/../../../vendor/autoload.php';
}
require_once $vendor_path; 

// Use statements must be at top level, not inside try block
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {

// Check if user is superadmin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'super_admin') {
    ob_end_clean();
    http_response_code(403);
    echo json_encode([
        "status" => "failed",
        "message" => "Unauthorized access. Superadmin privileges required."
    ]);
    exit();
}

// Skip OTP verification for superadmin - set session variables to bypass
$number = isset($_POST['number']) ? preg_replace('/[^0-9]/', '', $_POST['number']) : '';
$_SESSION['otp_verified'] = true;
$_SESSION['verified_phone'] = $number;

// Get form data
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
$lname = isset($_POST['lname']) ? trim($_POST['lname']) : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$province = isset($_POST['province']) ? $_POST['province'] : '';
$city_municipality = isset($_POST['city_municipality']) ? $_POST['city_municipality'] : '';
$barangay = isset($_POST['barangay']) ? $_POST['barangay'] : '';
$purok = isset($_POST['purok']) ? $_POST['purok'] : '';

$profileimg = isset($_POST['profileimg']) ? $_POST['profileimg'] : 'https://res.cloudinary.com/dvecrmrst/image/upload/v1758548365/noprofile_pjqduv.png';

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
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => implode(" ", $errors)
    ]);
    exit();
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid email format."
    ]);
    exit();
}

// Password validation
if (strlen($password) < 8) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Password must be at least 8 characters long."
    ]);
    exit();
}

if (!preg_match('/[A-Z]/', $password)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one uppercase letter."
    ]);
    exit();
}

if (!preg_match('/[a-z]/', $password)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one lowercase letter."
    ]);
    exit();
}

if (!preg_match('/[0-9]/', $password)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one number."
    ]);
    exit();
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Password must contain at least one special character."
    ]);
    exit();
}

if ($password !== $confirm_password) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Passwords do not match."
    ]);
    exit();
}

// Phone number validation
if (!preg_match('/^[0-9]{10,15}$/', $number)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid phone number format."
    ]);
    exit();
}

// Role is always 'user' for superadmin-created accounts
$role = 'user';

// Email domain MX check (optional but recommended)
$domain = substr(strrchr($email, "@"), 1);
if (!$domain || !checkdnsrr($domain, "MX")) {
    // Warning but don't block - superadmin can create with any email
    // Just log it
    error_log("Warning: Email domain MX check failed for: $email");
}

// Check if email already exists using Supabase
$existing_email = supabaseSelect('users', 'user_id', ['email' => $email]);
if ($existing_email && !empty($existing_email)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Email already exists."
    ]);
    exit();
}

// Check if phone number already exists using Supabase
$existing_phone = supabaseSelect('users', 'user_id', ['phone_number' => $number]);
if ($existing_phone && !empty($existing_phone)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Phone number already exists."
    ]);
    exit();
}

// Generate secure random user ID
$user_id = bin2hex(random_bytes(16));

// Hash password with salt
$salt = bin2hex(random_bytes(32)); // 64 character hex string
$hashed_password = password_hash($password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);

// Try to send welcome email before insert
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
    $mail->Body    = "Hello " . ($fname ? $fname : 'there') . ",<br><br>Welcome to the Ebakunado System! Your account has been successfully created by the administrator.<br><br>Name: " . ($fname ? $fname . ' ' . $lname : 'Not provided') . "<br>Email: $email<br>Phone Number: $number<br><br>Please keep your credentials safe and never share them with anyone.";
    $mail->send();
} catch (Exception $e) {
    // Don't block user creation if email fails - just log it
    error_log("Failed to send welcome email to $email: " . $e->getMessage());
}

// Get current Philippines time
$current_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
$philippines_time = $current_time->format('Y-m-d H:i:s');

// Prepare user data for Supabase (matching create_account.php structure exactly)
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
    'profileimg' => $profileimg,
    'created_at' => $philippines_time,
    'updated' => $philippines_time,
    'role' => $role
];

// Insert user using Supabase
$result = supabaseInsert('users', $user_data);

if ($result !== false) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Log activity (wrap in try-catch to prevent failure)
    try {
        supabaseLogActivity($user_id, 'user', 'account_created', 'New user account created by superadmin', $ip);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }

    // Clear OTP session (cleanup)
    unset($_SESSION['otp_verified']);
    unset($_SESSION['verified_phone']);

    // Clear output buffer before sending success response
    ob_end_clean();
    echo json_encode([
        "status" => "success",
        "message" => "User created successfully"
    ]);
    exit();
} else {
    // Get detailed error information
    global $supabase;
    $detailed = null;
    $status = null;
    
    if (isset($supabase) && method_exists($supabase, 'getLastError')) {
        $detailed = $supabase->getLastError();
    }
    if (isset($supabase) && method_exists($supabase, 'getLastStatus')) {
        $status = $supabase->getLastStatus();
    }
    
    error_log("Failed to create user. Email: $email, Error: " . ($detailed ? json_encode($detailed) : 'Unknown error'));
    
    // Clear output buffer before sending error response
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        "status" => "failed",
        "message" => "Failed to create user. Please check if email or phone number already exists.",
        "debug" => [
            "http_status" => $status,
            "supabase_error" => $detailed
        ]
    ]);
    exit();
}

} catch (Exception $e) {
    ob_end_clean();
    error_log("Create user error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        "status" => "failed",
        "message" => "An error occurred: " . $e->getMessage(),
        "debug" => [
            "error_file" => basename($e->getFile()),
            "error_line" => $e->getLine()
        ]
    ]);
    exit();
} catch (Error $e) {
    ob_end_clean();
    error_log("Create user PHP error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        "status" => "failed",
        "message" => "PHP Error: " . $e->getMessage(),
        "debug" => [
            "error_file" => basename($e->getFile()),
            "error_line" => $e->getLine()
        ]
    ]);
    exit();
}

// Clear sensitive data from memory (if we reach here somehow)
unset($password, $confirm_password, $hashed_password, $salt);
?>

