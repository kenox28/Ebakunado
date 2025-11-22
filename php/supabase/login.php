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
        // Match exactly what create_account.php does: FILTER_SANITIZE_EMAIL
        $email_or_phone = filter_var($email_or_phone, FILTER_SANITIZE_EMAIL);
    }

    if ($is_flutter) {
        // FLUTTER APP: Only check users table (parents only)
        if ($is_email) {
            $email_variations = [
                $email_or_phone,
                strtolower(trim($_POST['Email_number'] ?? '')),
                trim($_POST['Email_number'] ?? '')
            ];
            
            foreach ($email_variations as $email_to_try) {
                $rows = supabaseSelect('users', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0) {
                    $user_data = $rows[0];
                    $user_type = 'user';
                    $user_found = true;
                    break;
                }
            }
        } else {
            // Phone login for Flutter
            $rows = supabaseSelect('users', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    } else {
        // WEB LOGIN: Check all tables and support multiple roles
        if ($is_email) {
            // Try multiple email variations for compatibility with older records
            $email_variations = [
                $email_or_phone, // sanitized version
                strtolower(trim($_POST['Email_number'] ?? '')), // original lowercase
                trim($_POST['Email_number'] ?? '') // original as-is
            ];
            
            // Check all tables to find user and collect all roles
            foreach ($email_variations as $email_to_try) {
                // Check super_admin
                $rows = supabaseSelect('super_admin', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0 && !$user_found) {
                    $user_data = $rows[0];
                    $user_type = 'super_admin';
                    $user_found = true;
                    $available_roles[] = 'super_admin';
                    break; // Super admin is highest priority, stop here
                }

                // Check admin
                $rows = supabaseSelect('admin', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0) {
                    if (!$user_found) {
                        $user_data = $rows[0];
                        $user_type = 'admin';
                        $user_found = true;
                    }
                    $available_roles[] = 'admin';
                }

                // Check bhw
                $rows = supabaseSelect('bhw', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0) {
                    if (!$user_found) {
                        $user_data = $rows[0];
                        $user_type = 'bhw';
                        $user_found = true;
                    }
                    $available_roles[] = 'bhw';
                }

                // Check midwives
                $rows = supabaseSelect('midwives', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0) {
                    if (!$user_found) {
                        $user_data = $rows[0];
                        $user_type = 'midwife';
                        $user_found = true;
                    }
                    $available_roles[] = 'midwife';
                }

                // Check users
                $rows = supabaseSelect('users', '*', ['email' => $email_to_try]);
                if ($rows && count($rows) > 0) {
                    if (!$user_found) {
                        $user_data = $rows[0];
                        $user_type = 'user';
                        $user_found = true;
                    }
                    $available_roles[] = 'user';
                }
            }
        } else {
            // Phone login: Check all tables
            // Check bhw
            $rows = supabaseSelect('bhw', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'bhw';
                $user_found = true;
                $available_roles[] = 'bhw';
            }

            // Check midwives
            $rows = supabaseSelect('midwives', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                if (!$user_found) {
                    $user_data = $rows[0];
                    $user_type = 'midwife';
                    $user_found = true;
                }
                $available_roles[] = 'midwife';
            }

            // Check users
            $rows = supabaseSelect('users', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                if (!$user_found) {
                    $user_data = $rows[0];
                    $user_type = 'user';
                    $user_found = true;
                }
                $available_roles[] = 'user';
            }

            // Check admin (for phone login if needed)
            $rows = supabaseSelect('admin', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                if (!$user_found) {
                    $user_data = $rows[0];
                    $user_type = 'admin';
                    $user_found = true;
                }
                $available_roles[] = 'admin';
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
                "searched_email" => $is_email ? $email_or_phone : null,
                "is_email" => $is_email,
                "is_flutter" => $is_flutter,
                "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
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
        echo json_encode([
            "status" => "failed",
            "message" => "Incorrect password. Please try again."
        ]);
        exit();
    }

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