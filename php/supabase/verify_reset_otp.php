<?php
session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Get OTP from POST data
$entered_otp = $_POST['otp'] ?? '';

// Validate OTP
if (empty($entered_otp)) {
    echo json_encode(['status' => 'error', 'message' => 'OTP is required']);
    exit();
}

// Check if OTP session exists
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_otp_expires'])) {
    echo json_encode(['status' => 'error', 'message' => 'No OTP session found. Please request a new OTP.']);
    exit();
}

// Check if OTP has expired
if (time() > $_SESSION['reset_otp_expires']) {
    // Clean up expired session
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_otp_expires']);
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_user_table']);
    unset($_SESSION['reset_contact']);
    
    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.']);
    exit();
}

// Verify OTP
if ($entered_otp !== $_SESSION['reset_otp']) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
    exit();
}

// OTP is valid, set verification flag
$_SESSION['reset_otp_verified'] = true;
$_SESSION['reset_verified_user_id'] = $_SESSION['reset_user_id'];
$_SESSION['reset_verified_user_table'] = $_SESSION['reset_user_table'];

// Clean up OTP session (no longer needed)
unset($_SESSION['reset_otp']);
unset($_SESSION['reset_otp_expires']);

echo json_encode([
    'status' => 'success',
    'message' => 'OTP verified successfully. You can now reset your password.'
]);
?>
