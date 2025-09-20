<?php
// Set Philippines timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";

// Keep your original flow and session behavior, swapping SQL for Supabase helpers

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

    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);

    if ($is_email) {
        // super_admin
        $rows = supabaseSelect('super_admin', '*', ['email' => $email_or_phone]);
        if ($rows && count($rows) > 0) {
            $user_data = $rows[0];
            $user_type = 'super_admin';
            $user_found = true;
        }

        // admin
        if (!$user_found) {
            $rows = supabaseSelect('admin', '*', ['email' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'admin';
                $user_found = true;
            }
        }

        // bhw
        if (!$user_found) {
            $rows = supabaseSelect('bhw', '*', ['email' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'bhw';
                $user_found = true;
            }
        }

        // midwives
        if (!$user_found) {
            $rows = supabaseSelect('midwives', '*', ['email' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'midwife';
                $user_found = true;
            }
        }

        // users
        if (!$user_found) {
            $rows = supabaseSelect('users', '*', ['email' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    } else {
        // phone: bhw -> midwives -> users (to match your logic)
        $rows = supabaseSelect('bhw', '*', ['phone_number' => $email_or_phone]);
        if ($rows && count($rows) > 0) {
            $user_data = $rows[0];
            $user_type = 'bhw';
            $user_found = true;
        }

        if (!$user_found) {
            $rows = supabaseSelect('midwives', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'midwife';
                $user_found = true;
            }
        }

        if (!$user_found) {
            $rows = supabaseSelect('users', '*', ['phone_number' => $email_or_phone]);
            if ($rows && count($rows) > 0) {
                $user_data = $rows[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    }

    if (!$user_found) {
        echo json_encode([
            "status" => "failed",
            "message" => "Invalid email/phone or password"
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
            "message" => "Invalid email/phone or password"
        ]);
        exit();
    }

    // Sessions matching your original code style
    session_unset();

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
        $_SESSION['approve'] = $user_data['Approve'] ?? null;
        $_SESSION['role'] = $user_data['role'] ?? null;
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
        $_SESSION['user_type'] = 'user';
        $_SESSION['logged_in'] = true;
    }

    // activity log
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $actor_id = $_SESSION['super_admin_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? $_SESSION['user_id'] ?? null;
    supabaseLogActivity($actor_id, $user_type, 'login_success', ucfirst($user_type) . ' logged in successfully', $ip);

    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user_type" => $user_type,
        "user" => [
            "fname" => $_SESSION['fname'],
            "lname" => $_SESSION['lname'],
            "email" => $_SESSION['email']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "failed",
        "message" => "Login error occurred. Please try again."
    ]);
}
?>
