<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$entered_otp = $_POST['otp'] ?? '';

if (empty($entered_otp)) {
    echo json_encode(['status' => 'error', 'message' => 'OTP is required']);
    exit();
}

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expires'])) {
    echo json_encode(['status' => 'error', 'message' => 'No OTP found. Please request a new one.']);
    exit();
}

if (time() > $_SESSION['otp_expires']) {
    // Clear expired OTP
    unset($_SESSION['otp']);
    unset($_SESSION['otp_phone']);
    unset($_SESSION['otp_expires']);
    
    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.']);
    exit();
}

if ($entered_otp === $_SESSION['otp']) {
    // OTP is correct - mark as verified
    $_SESSION['otp_verified'] = true;
    $_SESSION['verified_phone'] = $_SESSION['otp_phone'];
    
    // Clear OTP from session for security
    unset($_SESSION['otp']);
    unset($_SESSION['otp_phone']);
    unset($_SESSION['otp_expires']);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'OTP verified successfully',
        'verified_phone' => $_SESSION['verified_phone']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
}
?>
