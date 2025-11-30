<?php
// Set Philippines timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');
session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";
require_once __DIR__ . '/JWT.php';

// Load PHPMailer for email notifications
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Get client IP address
 */
function getClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Check if account is locked and calculate remaining lockout time
 * Returns: ['locked' => bool, 'remaining_minutes' => int, 'lockout_duration' => int, 'expired' => bool]
 * If lockout expired, also clears it from database
 */
function checkAccountLockout($user_data, $user_type) {
    $failed_attempts = (int)($user_data['failed_attempts'] ?? 0);
    $lockout_time = $user_data['lockout_time'] ?? null;
    
    // If no lockout time set, account is not locked
    if (empty($lockout_time)) {
        return ['locked' => false, 'remaining_minutes' => 0, 'lockout_duration' => 0, 'expired' => false];
    }
    
    // Parse lockout time (handle both string and DateTime)
    $lockout_timestamp = is_string($lockout_time) ? strtotime($lockout_time) : (is_object($lockout_time) ? $lockout_time->getTimestamp() : 0);
    $current_timestamp = time();
    
    // Calculate lockout duration based on failed attempts
    $lockout_duration = 0;
    if ($failed_attempts >= 1 && $failed_attempts <= 5) {
        $lockout_duration = 5 * 60; // 5 minutes in seconds
    } elseif ($failed_attempts >= 6) {
        $lockout_duration = 10 * 60; // 10 minutes in seconds
    }
    
    // Check if lockout period has expired
    $elapsed = $current_timestamp - $lockout_timestamp;
    if ($elapsed >= $lockout_duration) {
        // Lockout expired - clear it from database but keep failed_attempts count
        $table_map = [
            'super_admin' => 'super_admin',
            'admin' => 'admin',
            'bhw' => 'bhw',
            'midwife' => 'midwives',
            'user' => 'users'
        ];
        $table = $table_map[$user_type] ?? null;
        if ($table) {
            $id_column = null;
            switch ($user_type) {
                case 'super_admin':
                    $id_column = 'super_admin_id';
                    break;
                case 'admin':
                    $id_column = 'admin_id';
                    break;
                case 'bhw':
                    $id_column = 'bhw_id';
                    break;
                case 'midwife':
                    $id_column = 'midwife_id';
                    break;
                case 'user':
                    $id_column = 'user_id';
                    break;
            }
            if ($id_column && isset($user_data[$id_column])) {
                try {
                    supabaseUpdate($table, ['lockout_time' => null], [$id_column => $user_data[$id_column]]);
                } catch (Exception $e) {
                    error_log("Failed to clear expired lockout: " . $e->getMessage());
                }
            }
        }
        return ['locked' => false, 'remaining_minutes' => 0, 'lockout_duration' => 0, 'expired' => true];
    }
    
    // Account is still locked
    $remaining_seconds = $lockout_duration - $elapsed;
    $remaining_minutes = (int)ceil($remaining_seconds / 60);
    
    return [
        'locked' => true,
        'remaining_minutes' => $remaining_minutes,
        'lockout_duration' => $lockout_duration / 60, // in minutes
        'expired' => false
    ];
}

/**
 * Calculate lockout duration in seconds based on failed attempts
 */
function getLockoutDuration($failed_attempts) {
    if ($failed_attempts >= 1 && $failed_attempts <= 5) {
        return 5 * 60; // 5 minutes
    } elseif ($failed_attempts >= 6) {
        return 10 * 60; // 10 minutes
    }
    return 0;
}

/**
 * Update failed attempts and lockout time in database
 */
function updateFailedAttempts($user_data, $user_type, $ip_address) {
    $table_map = [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'bhw' => 'bhw',
        'midwife' => 'midwives',
        'user' => 'users'
    ];
    
    $table = $table_map[$user_type] ?? null;
    if (!$table) {
        return false;
    }
    
    // Get ID column name
    $id_column = null;
    switch ($user_type) {
        case 'super_admin':
            $id_column = 'super_admin_id';
            break;
        case 'admin':
            $id_column = 'admin_id';
            break;
        case 'bhw':
            $id_column = 'bhw_id';
            break;
        case 'midwife':
            $id_column = 'midwife_id';
            break;
        case 'user':
            $id_column = 'user_id';
            break;
    }
    
    if (!$id_column || !isset($user_data[$id_column])) {
        return false;
    }
    
    $user_id = $user_data[$id_column];
    $current_attempts = (int)($user_data['failed_attempts'] ?? 0);
    $new_attempts = $current_attempts + 1;
    
    // Calculate lockout duration
    $lockout_duration = getLockoutDuration($new_attempts);
    $lockout_time = null;
    
    // Set lockout time if threshold reached (5th, 10th, 15th, etc.)
    if ($new_attempts % 5 == 0 && $lockout_duration > 0) {
        $lockout_time = date('Y-m-d H:i:s');
    } else {
        // Keep existing lockout time if still within lockout period
        $existing_lockout = $user_data['lockout_time'] ?? null;
        if (!empty($existing_lockout)) {
            $lockout_check = checkAccountLockout($user_data, $user_type);
            if ($lockout_check['locked']) {
                $lockout_time = $existing_lockout; // Keep existing lockout
            }
        }
    }
    
    // Prepare update data
    $update_data = [
        'failed_attempts' => $new_attempts
    ];
    
    if ($lockout_time !== null) {
        $update_data['lockout_time'] = $lockout_time;
    }
    
    // Update database
    try {
        supabaseUpdate($table, $update_data, [$id_column => $user_id]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to update failed attempts: " . $e->getMessage());
        return false;
    }
}

/**
 * Reset failed attempts and lockout time on successful login
 */
function resetFailedAttempts($user_data, $user_type) {
    $table_map = [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'bhw' => 'bhw',
        'midwife' => 'midwives',
        'user' => 'users'
    ];
    
    $table = $table_map[$user_type] ?? null;
    if (!$table) {
        return false;
    }
    
    // Get ID column name
    $id_column = null;
    switch ($user_type) {
        case 'super_admin':
            $id_column = 'super_admin_id';
            break;
        case 'admin':
            $id_column = 'admin_id';
            break;
        case 'bhw':
            $id_column = 'bhw_id';
            break;
        case 'midwife':
            $id_column = 'midwife_id';
            break;
        case 'user':
            $id_column = 'user_id';
            break;
    }
    
    if (!$id_column || !isset($user_data[$id_column])) {
        return false;
    }
    
    $user_id = $user_data[$id_column];
    
    // Reset failed attempts and lockout time
    $update_data = [
        'failed_attempts' => 0,
        'lockout_time' => null
    ];
    
    try {
        supabaseUpdate($table, $update_data, [$id_column => $user_id]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to reset failed attempts: " . $e->getMessage());
        return false;
    }
}

/**
 * Send lockout notification email
 */
function sendLockoutEmail($user_data, $user_type, $failed_attempts, $lockout_duration_minutes, $ip_address) {
    $email = $user_data['email'] ?? null;
    if (empty($email)) {
        return false; // No email to send to
    }
    
    $fname = $user_data['fname'] ?? 'User';
    $lname = $user_data['lname'] ?? '';
    $full_name = trim($fname . ' ' . $lname);
    if (empty($full_name)) {
        $full_name = 'User';
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iquenxzx@gmail.com';
        $mail->Password   = 'lews hdga hdvb glym';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Recipients
        $mail->setFrom('iquenxzx@gmail.com', 'Ebakunado System');
        $mail->addAddress($email, $full_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Locked - Security Alert | Ebakunado';
        
        $current_time = date('F j, Y, g:i a');
        $user_type_label = ucfirst(str_replace('_', ' ', $user_type));
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-top: none; }
                .alert-box { background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .info-box { background-color: #e7f3ff; border: 1px solid #0d6efd; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
                .highlight { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔒 Account Locked - Security Alert</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>$full_name</strong>,</p>
                    
                    <div class='alert-box'>
                        <p><strong>⚠️ Your account has been temporarily locked due to multiple failed login attempts.</strong></p>
                    </div>
                    
                    <p>We detected <span class='highlight'>$failed_attempts failed login attempts</span> on your account. For your security, your account has been locked for <strong>$lockout_duration_minutes minutes</strong>.</p>
                    
                    <div class='info-box'>
                        <h3>Login Attempt Details:</h3>
                        <ul>
                            <li><strong>Account Type:</strong> $user_type_label</li>
                            <li><strong>Email:</strong> $email</li>
                            <li><strong>Failed Attempts:</strong> $failed_attempts</li>
                            <li><strong>Lockout Duration:</strong> $lockout_duration_minutes minutes</li>
                            <li><strong>IP Address:</strong> $ip_address</li>
                            <li><strong>Time:</strong> $current_time</li>
                        </ul>
                    </div>
                    
                    <h3>What to do:</h3>
                    <ul>
                        <li>Wait for the lockout period to expire ($lockout_duration_minutes minutes)</li>
                        <li>Make sure you're using the correct password</li>
                        <li>If you forgot your password, use the 'Forgot Password' feature</li>
                        <li>If you did not attempt to log in, please contact support immediately</li>
                    </ul>
                    
                    <p><strong>Security Recommendation:</strong> If you did not attempt these logins, please change your password immediately after regaining access to your account.</p>
                    
                    <p>This is an automated security notification from the Ebakunado System.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Linao Health Center | eBakunado System</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send lockout email: " . $e->getMessage());
        return false;
    }
}

// Robust function to detect Flutter/mobile app requests
function isFlutterRequest() {
    // Method 1: Check explicit source parameter
    if (isset($_POST['source']) && $_POST['source'] === 'flutter') {
        return true;
    }
    
    // Method 2: Check User-Agent for Flutter/Dio patterns (most reliable for Flutter)
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    
    // Flutter/Dio HTTP client patterns (definitive indicators)
    if (strpos($user_agent, 'dio') !== false || 
        strpos($user_agent, 'flutter') !== false ||
        strpos($user_agent, 'dart') !== false) {
        return true;
    }
    
    // Method 3: Check if it's a mobile device (iOS/Android)
    $is_mobile_device = (
        strpos($user_agent, 'android') !== false ||
        strpos($user_agent, 'iphone') !== false ||
        strpos($user_agent, 'ipad') !== false ||
        strpos($user_agent, 'ipod') !== false ||
        strpos($user_agent, 'mobile') !== false
    );
    
    // Method 4: Check for browser-specific headers that mobile apps typically don't send
    $has_browser_headers = (
        isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && 
        strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) > 10 && // Browsers send detailed language headers
        (
            strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ',') !== false || // Multiple languages
            strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ';') !== false   // With quality values
        )
    ) || (
        isset($_SERVER['HTTP_SEC_FETCH_DEST']) || // Modern browser security headers
        isset($_SERVER['HTTP_SEC_FETCH_MODE']) ||
        isset($_SERVER['HTTP_SEC_FETCH_SITE']) ||
        isset($_SERVER['HTTP_SEC_FETCH_USER'])
    ) || (
        isset($_SERVER['HTTP_REFERER']) && // Browsers send referer
        strpos($_SERVER['HTTP_REFERER'], 'http') === 0
    );
    
    // Method 5: Check for headers that mobile apps typically don't have
    $has_web_browser_patterns = (
        isset($_SERVER['HTTP_ACCEPT']) &&
        (
            strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false ||
            strpos($_SERVER['HTTP_ACCEPT'], 'application/xhtml') !== false
        )
    ) && (
        isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
        (
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false ||
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false
        )
    );
    
    // Method 6: Check if it's a mobile device but NOT a browser
    // Mobile browsers have specific patterns, pure mobile apps don't
    $is_mobile_browser = $is_mobile_device && (
        strpos($user_agent, 'chrome') !== false ||
        strpos($user_agent, 'safari') !== false ||
        strpos($user_agent, 'firefox') !== false ||
        strpos($user_agent, 'edge') !== false ||
        strpos($user_agent, 'opera') !== false ||
        strpos($user_agent, 'samsungbrowser') !== false ||
        strpos($user_agent, 'ucbrowser') !== false
    );
    
    // Decision logic:
    // 1. If it's a mobile device AND has browser headers → it's a mobile browser (web)
    // 2. If it's a mobile device AND NO browser headers → it's likely a mobile app (Flutter)
    // 3. If it has Dio/Flutter patterns → definitely Flutter
    // 4. If it's mobile browser → web login
    
    if ($is_mobile_device && !$has_browser_headers && !$is_mobile_browser) {
        // Mobile device without browser headers = mobile app
        return true;
    }
    
    // If it's clearly a mobile browser, it's web login
    if ($is_mobile_browser || $has_web_browser_patterns) {
        return false;
    }
    
    // If no clear indicators, default to web (safer for backward compatibility)
    return false;
}

function buildEmailVariants($input) {
    $variants = [];
    $original = trim((string)($input ?? ''));
    if ($original !== '') {
        $variants[] = $original;
        $variants[] = strtolower($original);
    }
    $sanitized = filter_var($original, FILTER_SANITIZE_EMAIL);
    if ($sanitized) {
        $variants[] = $sanitized;
        $variants[] = strtolower($sanitized);
    }
    return array_values(array_unique(array_filter($variants)));
}

function buildPhoneVariants($input) {
    $variants = [];
    $trimmed = trim((string)($input ?? ''));
    if ($trimmed === '') {
        return [];
    }

    $digitsOnly = preg_replace('/\D+/', '', $trimmed);
    if ($digitsOnly !== '') {
        $variants[] = $digitsOnly;
    }

    if (strlen($digitsOnly) === 11 && $digitsOnly[0] === '0') {
        $variants[] = '+63' . substr($digitsOnly, 1);
        $variants[] = substr($digitsOnly, 1);
    } elseif (strlen($digitsOnly) === 10) {
        $variants[] = '0' . $digitsOnly;
        $variants[] = '+63' . $digitsOnly;
    } elseif (strlen($digitsOnly) === 12 && strncmp($digitsOnly, '63', 2) === 0) {
        $variants[] = '+' . $digitsOnly;
        $variants[] = '0' . substr($digitsOnly, 2);
    } elseif (strpos($trimmed, '+') === 0 && $digitsOnly !== '') {
        $variants[] = '+' . $digitsOnly;
    }

    $variants[] = $trimmed;

    return array_values(array_unique(array_filter($variants)));
}

function attemptUserLookupByEmail(array $variants, bool $caseInsensitive = false) {
    $result = [
        'found' => false,
        'user_data' => null,
        'user_type' => null,
        'available_roles' => []
    ];

    if (empty($variants)) {
        return $result;
    }

    $tables = [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'bhw' => 'bhw',
        'midwives' => 'midwife',
        'users' => 'user'
    ];

    foreach ($tables as $table => $role) {
        foreach ($variants as $variant) {
            $conditionValue = $caseInsensitive ? ['operator' => 'ilike', 'value' => $variant] : $variant;
            $rows = supabaseSelect($table, '*', ['email' => $conditionValue], null, 1);
            if ($rows && count($rows) > 0) {
                if (!in_array($role, $result['available_roles'], true)) {
                    $result['available_roles'][] = $role;
                }
                if (!$result['found']) {
                    $result['found'] = true;
                    $result['user_data'] = $rows[0];
                    $result['user_type'] = $role;
                    if ($role === 'super_admin') {
                        return $result;
                    }
                }
                break;
            }
        }
    }

    return $result;
}

function attemptUserLookupByPhone(array $variants) {
    $result = [
        'found' => false,
        'user_data' => null,
        'user_type' => null,
        'available_roles' => []
    ];

    if (empty($variants)) {
        return $result;
    }

    $tables = [
        'bhw' => 'bhw',
        'midwives' => 'midwife',
        'users' => 'user',
        'admin' => 'admin'
    ];

    foreach ($tables as $table => $role) {
        foreach ($variants as $variant) {
            $rows = supabaseSelect($table, '*', ['phone_number' => $variant], null, 1);
            if ($rows && count($rows) > 0) {
                if (!in_array($role, $result['available_roles'], true)) {
                    $result['available_roles'][] = $role;
                }
                if (!$result['found']) {
                    $result['found'] = true;
                    $result['user_data'] = $rows[0];
                    $result['user_type'] = $role;
                }
                break;
            }
        }
    }

    return $result;
}

// Initialize (reference only)
if (function_exists('initializeSupabaseTables')) {
    initializeSupabaseTables($supabase);
}

// Already logged in checks
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

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid request method"
    ]);
    exit();
}

// Inputs
$email_or_phone = trim($_POST['Email_number'] ?? '');
$password = $_POST['password'] ?? '';
$is_flutter = isFlutterRequest(); // Use the robust detection function
$email_variants = [];
$phone_variants = [];

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
    $available_roles = []; // Track all roles user has (for web login)

    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        $email_variants = buildEmailVariants($_POST['Email_number'] ?? $email_or_phone);
    }

    if ($is_flutter) {
        // FLUTTER APP: Only check users table (parents only)
        if ($is_email) {
            $lookup = attemptUserLookupByEmail($email_variants, false);
            if (!$lookup['found']) {
                $lookup = attemptUserLookupByEmail($email_variants, true);
            }
            if ($lookup['found']) {
                $user_data = $lookup['user_data'];
                $user_type = 'user';
                $user_found = true;
            }
        } else {
            // Phone login for Flutter
            $phone_variants = buildPhoneVariants($email_or_phone);
            $lookup = attemptUserLookupByPhone($phone_variants);
            if ($lookup['found']) {
                $user_data = $lookup['user_data'];
                $user_type = 'user';
                $user_found = true;
            }
        }
    } else {
        // WEB LOGIN: Check all tables and support multiple roles
        if ($is_email) {
            $lookup = attemptUserLookupByEmail($email_variants, false);
            if (!$lookup['found']) {
                $lookup = attemptUserLookupByEmail($email_variants, true);
            }
            if ($lookup['found']) {
                $user_found = true;
                $user_data = $lookup['user_data'];
                $user_type = $lookup['user_type'];
                $available_roles = $lookup['available_roles'];
            }
        } else {
            $phone_variants = buildPhoneVariants($email_or_phone);
            $lookup = attemptUserLookupByPhone($phone_variants);
            if ($lookup['found']) {
                $user_found = true;
                $user_data = $lookup['user_data'];
                $user_type = $lookup['user_type'];
                $available_roles = $lookup['available_roles'];
            }
        }

        // Determine primary role based on priority (for web login)
        // Priority: super_admin > admin > bhw > midwife > user
        if (count($available_roles) > 0) {
            if (in_array('super_admin', $available_roles)) {
                $primary_role = 'super_admin';
            } elseif (in_array('admin', $available_roles)) {
                $primary_role = 'admin';
            } elseif (in_array('bhw', $available_roles)) {
                $primary_role = 'bhw';
            } elseif (in_array('midwife', $available_roles)) {
                $primary_role = 'midwife';
            } else {
                $primary_role = 'user';
            }

            // Get data for primary role if we haven't already
            if ($user_type !== $primary_role) {
                $email_to_use = $is_email ? $email_or_phone : null;
                $phone_to_use = $is_email ? null : $email_or_phone;
                
                if ($primary_role === 'super_admin' && $email_to_use) {
                    $rows = supabaseSelect('super_admin', '*', ['email' => $email_to_use]);
                    if ($rows && count($rows) > 0) {
                        $user_data = $rows[0];
                        $user_type = 'super_admin';
                    }
                } elseif ($primary_role === 'admin') {
                    if ($email_to_use) {
                        $rows = supabaseSelect('admin', '*', ['email' => $email_to_use]);
                    } else {
                        $rows = supabaseSelect('admin', '*', ['phone_number' => $phone_to_use]);
                    }
                    if ($rows && count($rows) > 0) {
                        $user_data = $rows[0];
                        $user_type = 'admin';
                    }
                } elseif ($primary_role === 'bhw') {
                    if ($email_to_use) {
                        $rows = supabaseSelect('bhw', '*', ['email' => $email_to_use]);
                    } else {
                        $rows = supabaseSelect('bhw', '*', ['phone_number' => $phone_to_use]);
                    }
                    if ($rows && count($rows) > 0) {
                        $user_data = $rows[0];
                        $user_type = 'bhw';
                    }
                } elseif ($primary_role === 'midwife') {
                    if ($email_to_use) {
                        $rows = supabaseSelect('midwives', '*', ['email' => $email_to_use]);
                    } else {
                        $rows = supabaseSelect('midwives', '*', ['phone_number' => $phone_to_use]);
                    }
                    if ($rows && count($rows) > 0) {
                        $user_data = $rows[0];
                        $user_type = 'midwife';
                    }
                }
            }
        }
    }

    if (!$user_found) {
        echo json_encode([
            "status" => "failed",
            "message" => "Email/phone not found. Please check your credentials.",
            "debug" => [
                "searched_email" => $is_email ? ($email_variants[0] ?? $email_or_phone) : null,
                "is_email" => (bool)$is_email,
                "email_variants" => $is_email ? $email_variants : [],
                "phone_variants" => !$is_email ? $phone_variants : [],
                "is_flutter" => $is_flutter,
                "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        ]);
        exit();
    }

    // Get IP address for tracking
    $ip_address = getClientIP();

    // Check if account is locked BEFORE password verification
    $lockout_check = checkAccountLockout($user_data, $user_type);
    if ($lockout_check['locked']) {
        $remaining_minutes = $lockout_check['remaining_minutes'];
        echo json_encode([
            "status" => "failed",
            "message" => "Account is temporarily locked due to multiple failed login attempts. Please try again after {$remaining_minutes} minute(s).",
            "locked" => true,
            "remaining_minutes" => $remaining_minutes
        ]);
        exit();
    }

    // Password verification matching your logic
    $password_valid = false;

    if ($user_type === 'super_admin' || $user_type === 'admin') {
        // Admins use md5 in your seed/reference
        $password_valid = (md5($password) === ($user_data['pass'] ?? '')) || password_verify($password, ($user_data['pass'] ?? ''));
        if (!$password_valid && $password === ($user_data['pass'] ?? '')) {
            $password_valid = true; // plain fallback if any seed row
        }
    } elseif ($user_type === 'bhw' || $user_type === 'midwife') {
        $stored_salt = $user_data['salt'] ?? '';
        $stored_hash = $user_data['pass'] ?? '';
        if (!empty($stored_salt)) {
            $password_valid = password_verify($password . $stored_salt, $stored_hash);
            if (!$password_valid) {
                $password_valid = (md5($password . $stored_salt) === $stored_hash);
            }
        } else {
            $password_valid = password_verify($password, $stored_hash) || (md5($password) === $stored_hash) || ($password === $stored_hash);
        }
    } else {
        // users: passw + salt
        $stored_salt = $user_data['salt'] ?? '';
        $stored_hash = $user_data['passw'] ?? '';
        if (!empty($stored_salt)) {
            $password_valid = password_verify($password . $stored_salt, $stored_hash);
            if (!$password_valid) {
                $password_valid = (md5($password . $stored_salt) === $stored_hash);
            }
        } else {
            $password_valid = password_verify($password, $stored_hash) || (md5($password) === $stored_hash) || ($password === $stored_hash);
        }
    }

    if (!$password_valid) {
        // Increment failed attempts and set lockout if needed
        $current_attempts = (int)($user_data['failed_attempts'] ?? 0);
        $new_attempts = $current_attempts + 1;
        
        // Update failed attempts in database
        updateFailedAttempts($user_data, $user_type, $ip_address);
        
        // Reload user data to get updated failed_attempts and lockout_time
        $table_map = [
            'super_admin' => 'super_admin',
            'admin' => 'admin',
            'bhw' => 'bhw',
            'midwife' => 'midwives',
            'user' => 'users'
        ];
        $table = $table_map[$user_type] ?? null;
        if ($table) {
            $id_column = null;
            switch ($user_type) {
                case 'super_admin':
                    $id_column = 'super_admin_id';
                    break;
                case 'admin':
                    $id_column = 'admin_id';
                    break;
                case 'bhw':
                    $id_column = 'bhw_id';
                    break;
                case 'midwife':
                    $id_column = 'midwife_id';
                    break;
                case 'user':
                    $id_column = 'user_id';
                    break;
            }
            if ($id_column && isset($user_data[$id_column])) {
                $updated_user = supabaseSelect($table, '*', [$id_column => $user_data[$id_column]], null, 1);
                if ($updated_user && count($updated_user) > 0) {
                    $user_data = $updated_user[0];
                }
            }
        }
        
        // Check if we just hit a lockout threshold (5th, 10th, 15th, etc.)
        $should_send_email = ($new_attempts % 5 == 0);
        $lockout_duration = getLockoutDuration($new_attempts);
        
        // Send email notification if threshold reached
        if ($should_send_email && $lockout_duration > 0) {
            $lockout_duration_minutes = $lockout_duration / 60;
            sendLockoutEmail($user_data, $user_type, $new_attempts, $lockout_duration_minutes, $ip_address);
        }
        
        // Prepare error message
        $error_message = "Incorrect password. Please try again.";
        
        echo json_encode([
            "status" => "failed",
            "message" => $error_message,
            "failed_attempts" => $new_attempts
        ]);
        exit();
    }

    // Reset failed attempts on successful login
    resetFailedAttempts($user_data, $user_type);

    // Sessions matching your original code style
    session_unset();

    // Store available roles for web logins (not Flutter)
    if (!$is_flutter && count($available_roles) > 0) {
        $_SESSION['available_roles'] = $available_roles;
    }

    if ($user_type === 'super_admin') {
        $_SESSION['super_admin_id'] = $user_data['super_admin_id'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['user_type'] = 'super_admin';
        $_SESSION['logged_in'] = true;
    } elseif ($user_type === 'admin') {
        $_SESSION['admin_id'] = $user_data['admin_id'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['profileimg'] = $user_data['profileimg'] ?? 'noprofile.png';
        $_SESSION['user_type'] = 'admin';
        $_SESSION['logged_in'] = true;
    } elseif ($user_type === 'bhw') {
        $_SESSION['bhw_id'] = $user_data['bhw_id'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['phone_number'] = $user_data['phone_number'] ?? null;
        $_SESSION['permissions'] = $user_data['permissions'] ?? null;
        $_SESSION['role'] = $user_data['role'] ?? null;
        $_SESSION['user_type'] = 'bhw';
        $_SESSION['logged_in'] = true;
    } elseif ($user_type === 'midwife') {
        $_SESSION['midwife_id'] = $user_data['midwife_id'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['phone_number'] = $user_data['phone_number'] ?? null;
        $_SESSION['permissions'] = $user_data['permissions'] ?? null;
        $_SESSION['approve'] = 1; // Midwives are approved by default
        $_SESSION['role'] = $user_data['role'] ?? null;
        $_SESSION['profileimg'] = $user_data['profileimg'] ?? null;
        $_SESSION['user_type'] = 'midwife';
        $_SESSION['logged_in'] = true;
    } else {
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['phone_number'] = $user_data['phone_number'] ?? null;
        $_SESSION['role'] = $user_data['role'] ?? null;
        $_SESSION['gender'] = $user_data['gender'] ?? null;
        $_SESSION['place'] = $user_data['place'] ?? null;
        $_SESSION['profileimg'] = $user_data['profileimg'] ?? null;
        $_SESSION['user_type'] = 'user';
        $_SESSION['logged_in'] = true;
    }

    // activity log
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $actor_id = $_SESSION['super_admin_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? $_SESSION['user_id'] ?? null;
    supabaseLogActivity($actor_id, $user_type, 'login_success', ucfirst($user_type) . ' logged in successfully', $ip);

    // Generate JWT token for API authentication
    $jwt_token = JWT::generateToken($user_data, $user_type);

    // Set redirect URLs based on user type
    $redirect_url = '';
    switch ($user_type) {
        case 'bhw':
            $redirect_url = '../../views/bhw/home.php';
            break;
        case 'midwife':
            $redirect_url = '../../views/bhw/home.php';
            break;
        case 'super_admin':
            $redirect_url = '../../views/super_admin/dashboard.php';
            break;
        case 'admin':
            $redirect_url = '../../views/admin/dashboard.php';
            break;
        case 'user':
            $redirect_url = '../../views/users/dashboard.php';
            break;
        default:
            $redirect_url = '../../views/auth/login.php';
    }

    // Build response with JWT token (backward compatible - token is optional)
    $response = [
        "status" => "success",
        "message" => "Login successful",
        "user_type" => $user_type,
        "redirect_url" => $redirect_url,
        "user" => [
            "fname" => $_SESSION['fname'],
            "lname" => $_SESSION['lname'],
            "email" => $_SESSION['email']
        ]
    ];
    
    // Add JWT token to response if generated successfully
    if ($jwt_token) {
        $response["token"] = $jwt_token;
        $response["token_expires_in"] = 86400; // 24 hours in seconds
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        "status" => "failed",
        "message" => "Login error occurred. Please try again."
    ]);
}
?>