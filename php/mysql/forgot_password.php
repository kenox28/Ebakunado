<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);

// Start output buffering to prevent any accidental output
ob_start();

try {
    include "../../database/Database.php";
    
    // Test if database connection was established
    if (!isset($connect)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection variable not found',
            'debug' => '$connect variable not set after including Database.php'
        ]);
        exit();
    }
    
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database include failed',
        'debug' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
}

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}


$email_phone = $_POST['email_phone'] ?? '';

// Validate input
if (empty($email_phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Email or phone number is required']);
    exit();
}

// Check if it's email or phone
$is_email = filter_var($email_phone, FILTER_VALIDATE_EMAIL);
$is_phone = preg_match('/^[0-9+\-\s()]+$/', $email_phone);

if (!$is_email && !$is_phone) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email or phone number']);
    exit();
}

// Check if user exists in database
$user_found = false;
$user_data = null;

try {
    // Check database connection
    if (!$connect) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed',
            'debug' => 'Connection variable is null or false'
        ]);
        exit();
    }
    
    // Check for connection errors
    if ($connect->connect_error) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed',
            'debug' => 'MySQL Error: ' . $connect->connect_error
        ]);
        exit();
    }

    // Check each table directly (since users now exist in only one table based on role)
    // First check BHW table
    $stmt = $connect->prepare("SELECT bhw_id as id, fname, lname, email, phone_number FROM bhw WHERE email = ? OR phone_number = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed']);
        exit();
    }
    
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_found = true;
        $user_data = $result->fetch_assoc();
        $user_data['table'] = 'bhw';
    } else {
        // Check in midwives table
        $stmt = $connect->prepare("SELECT midwife_id as id, fname, lname, email, phone_number FROM midwives WHERE email = ? OR phone_number = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed']);
            exit();
        }
        
        $stmt->bind_param("ss", $email_phone, $email_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user_found = true;
            $user_data = $result->fetch_assoc();
            $user_data['table'] = 'midwives';
        } else {
            // Check in users table
            $stmt = $connect->prepare("SELECT user_id as id, fname, lname, email, phone_number FROM users WHERE email = ? OR phone_number = ?");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed']);
                exit();
            }
            
            $stmt->bind_param("ss", $email_phone, $email_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user_found = true;
                $user_data = $result->fetch_assoc();
                $user_data['table'] = 'users';
            }
        }
    }
} catch (Exception $e) {
    error_log("Forgot password database error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error occurred',
        'debug' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
}

if (!$user_found) {
    echo json_encode(['status' => 'error', 'message' => 'No account found with this email or phone number']);
    exit();
}

// Generate 6-digit OTP
$otp = sprintf('%06d', mt_rand(0, 999999));

// Store OTP in session with expiration time (5 minutes)
$_SESSION['reset_otp'] = $otp;
$_SESSION['reset_user_id'] = $user_data['id'];
$_SESSION['reset_user_table'] = $user_data['table'] ?? 'users';
$_SESSION['reset_contact'] = $email_phone;
$_SESSION['reset_otp_expires'] = time() + 300; // 5 minutes from now

// Send OTP based on contact method
if ($is_email) {
    // Send email OTP
    $to = $user_data['email'];
    $subject = "Ebakunado - Password Reset OTP";
    $message = "Hello " . $user_data['fname'] . " " . $user_data['lname'] . ",\n\n";
    $message .= "Your password reset verification code is: " . $otp . "\n\n";
    $message .= "This code will expire in 5 minutes. Do not share this code with anyone.\n\n";
    $message .= "If you didn't request this, please ignore this email.\n\n";
    $message .= "Best regards,\nEbakunado Team";
    
    $headers = "From: noreply@ebakunado.com\r\n";
    $headers .= "Reply-To: noreply@ebakunado.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent to your email address',
            'contact_type' => 'email',
            'expires_in' => 300
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please try again.']);
    }
} else {
    // Send SMS OTP
    $phone_number = $user_data['phone_number'];
    
    // Clean phone number
    $phone_number = preg_replace('/[^0-9+]/', '', $phone_number);
    
    // Ensure phone number starts with +63 for Philippines
    if (substr($phone_number, 0, 3) !== '+63') {
        if (substr($phone_number, 0, 2) === '09') {
            $phone_number = '+63' . substr($phone_number, 1);
        } elseif (substr($phone_number, 0, 1) === '9') {
            $phone_number = '+63' . $phone_number;
        }
    }
    
    // TextBee.dev API configuration
    $apiKey = '859e05f9-b29e-4071-b29f-0bd14a273bc2';
    $deviceId = '687e5760c87689a0c22492b3';
    $url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";
    
    // SMS message
    $sms_message = "EBAKUNADO: Your password reset code is: $otp\n\nThis code will expire in 5 minutes. Do not share this code with anyone.";
    
    // Prepare SMS data
    $smsData = [
        'recipients' => [$phone_number],
        'message' => $sms_message
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curlError) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send SMS. Please try again.']);
        exit();
    }
    
    // Parse response
    $responseData = json_decode($response, true);
    
    // Check for successful response
    if ($httpCode === 200 || $httpCode === 201) {
        $isSuccess = false;
        
        if ($responseData && isset($responseData['data']['success']) && $responseData['data']['success'] === true) {
            $isSuccess = true;
        } elseif ($responseData && isset($responseData['success']) && $responseData['success'] === true) {
            $isSuccess = true;
        } elseif ($httpCode === 200 || $httpCode === 201) {
            $isSuccess = true;
        }
        
        if ($isSuccess) {
            echo json_encode([
                'status' => 'success',
                'message' => 'OTP sent to your phone number',
                'contact_type' => 'phone',
                'expires_in' => 300
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'SMS API returned unexpected response format']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send SMS. Please check your phone number.']);
    }
}

// Catch any uncaught errors
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error in forgot_password.php: $message in $file on line $line");
    if (!headers_sent()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Server error occurred']);
    }
    exit();
});

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR) {
        error_log("Fatal error in forgot_password.php: " . $error['message']);
        if (!headers_sent()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Fatal server error occurred']);
        }
    }
});
?>
