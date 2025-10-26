<?php
session_start();

// Start output buffering to prevent any accidental output
ob_start();

try {
    include "../../database/SupabaseConfig.php";
    include "../../database/DatabaseHelper.php";
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    // Import PHPMailer classes at top-level (avoid using inside blocks)
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        // Autoloader should handle this; check exists to avoid redeclare issues
    }
    
    // Test if Supabase connection was established
    if (!isset($supabase)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Supabase connection variable not found',
            'debug' => '$supabase variable not set after including SupabaseConfig.php'
        ]);
        exit();
    }
    
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Supabase include failed',
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
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Email or phone number is required'
    ]);
    exit();
}

// Validate email format if it looks like an email
if (filter_var($email_phone, FILTER_VALIDATE_EMAIL)) {
    $email = $email_phone;
    $phone = null;
} else {
    // Assume it's a phone number
    $phone = $email_phone;
    $email = null;
}

try {
    // Check if user exists in any table using Supabase
    $user_found = false;
    $user_data = null;
    $user_type = null;
    
    if (!empty($email)) {
        // Email-based lookup
        $result = supabaseSelect('super_admin', '*', ['email' => $email]);
        if ($result && count($result) > 0) {
            $user_data = $result[0];
            $user_type = 'super_admin';
            $user_found = true;
        }
        
        if (!$user_found) {
            $result = supabaseSelect('admin', '*', ['email' => $email]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'admin';
                $user_found = true;
            }
        }
        
        if (!$user_found) {
            $result = supabaseSelect('bhw', '*', ['email' => $email]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'bhw';
                $user_found = true;
            }
        }
        
        if (!$user_found) {
            $result = supabaseSelect('midwives', '*', ['email' => $email]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'midwife';
                $user_found = true;
            }
        }
        
        if (!$user_found) {
            $result = supabaseSelect('users', '*', ['email' => $email]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    } else {
        // Phone-based lookup
        if (!$user_found) {
            $result = supabaseSelect('bhw', '*', ['phone_number' => $phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'bhw';
                $user_found = true;
            }
        }
        
        if (!$user_found) {
            $result = supabaseSelect('midwives', '*', ['phone_number' => $phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'midwife';
                $user_found = true;
            }
        }
        
        if (!$user_found) {
            $result = supabaseSelect('users', '*', ['phone_number' => $phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    }
    
    if (!$user_found) {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Account not found in our system'
        ]);
        exit();
    }
    
    // Generate OTP (random for both email and phone)
    $otp = sprintf('%06d', mt_rand(0, 999999));
    
    // Store OTP in session with expiration (5 minutes) and track user identity
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_email'] = !empty($email) ? $email : ($user_data['email'] ?? null);
    $_SESSION['reset_user_type'] = $user_type;
    // Map user id and table for later reset
    $id_map = [
        'super_admin' => ['col' => 'super_admin_id', 'table' => 'super_admin'],
        'admin' => ['col' => 'admin_id', 'table' => 'admin'],
        'bhw' => ['col' => 'bhw_id', 'table' => 'bhw'],
        'midwife' => ['col' => 'midwife_id', 'table' => 'midwives'],
        'user' => ['col' => 'user_id', 'table' => 'users']
    ];
    $id_info = $id_map[$user_type] ?? null;
    if ($id_info && isset($user_data[$id_info['col']])) {
        $_SESSION['reset_user_id'] = $user_data[$id_info['col']];
        $_SESSION['reset_user_table'] = $id_info['table'];
    }
    $_SESSION['reset_otp_time'] = time();
    $_SESSION['reset_otp_expires'] = time() + 300; // 5 minutes
    
    // NEW: Send to BOTH email AND SMS
    $email_sent = false;
    $sms_sent = false;
    $errors = [];
    
    // 1. SEND EMAIL (if email available)
    $destination_email = $user_data['email'] ?? null;
    if (!empty($destination_email)) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'iquenxzx@gmail.com';
            $mail->Password   = 'lews hdga hdvb glym';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            $mail->setFrom('iquenxzx@gmail.com', 'Ebakunado System');
            $mail->addAddress($destination_email);
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - Ebakunado';
            $mail->Body    = "
                <h2>Password Reset Request</h2>
                <p>Hello " . $user_data['fname'] . ",</p>
                <p>You have requested to reset your password. Please use the following OTP to proceed:</p>
                <h3 style='color: #2c3e50; font-size: 24px; letter-spacing: 3px;'>" . $otp . "</h3>
                <p><strong>This OTP will expire in 5 minutes.</strong></p>
                <p>If you did not request this password reset, please ignore this email.</p>
                <p>Best regards,<br>Ebakunado System</p>
            ";
            
            $mail->send();
            $email_sent = true;
        } catch (Exception $e) {
            $errors[] = 'Email: ' . $e->getMessage();
        }
    }
    
    // 2. SEND SMS (if phone available)
    $phone_number = $user_data['phone_number'] ?? '';
    if (!empty($phone_number)) {
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
        
        // Check for success
        if (!$curlError && ($httpCode === 200 || $httpCode === 201)) {
            $responseData = json_decode($response, true);
            if ($responseData && 
                (isset($responseData['data']['success']) && $responseData['data']['success'] === true ||
                 isset($responseData['success']) && $responseData['success'] === true ||
                 $httpCode === 200 || $httpCode === 201)) {
                $sms_sent = true;
            } else {
                $errors[] = 'SMS: Unexpected response format';
            }
        } else {
            $errors[] = 'SMS: ' . ($curlError ?: 'HTTP ' . $httpCode);
        }
    }
    
    // 3. SEND RESPONSE
    if ($email_sent && $sms_sent) {
        // Both sent successfully
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent to your email and phone number',
            'user_type' => $user_type,
            'contact_type' => 'both',
            'expires_in' => 300
        ]);
    } elseif ($email_sent) {
        // Only email sent
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent to your email address',
            'user_type' => $user_type,
            'contact_type' => 'email',
            'expires_in' => 300
        ]);
    } elseif ($sms_sent) {
        // Only SMS sent
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent to your phone number',
            'user_type' => $user_type,
            'contact_type' => 'phone',
            'expires_in' => 300
        ]);
    } else {
        // Neither sent
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to send OTP. ' . implode(', ', $errors)
        ]);
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Forgot password error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}

// Clean output buffer
ob_end_flush();
?>