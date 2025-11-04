<?php
session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";
include "../../database/SystemSettingsHelper.php";

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Get phone number from POST data
$phone_number = $_POST['phone_number'] ?? '';

// Validate phone number
if (empty($phone_number)) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number is required']);
    exit();
}

// Clean phone number (remove any non-numeric characters except +)
$phone_number = preg_replace('/[^0-9+]/', '', $phone_number);

// Ensure phone number starts with +63 for Philippines
if (!str_starts_with($phone_number, '+63')) {
    if (str_starts_with($phone_number, '09')) {
        $phone_number = '+63' . substr($phone_number, 1);
    } elseif (str_starts_with($phone_number, '9')) {
        $phone_number = '+63' . $phone_number;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Philippine phone number format']);
        exit();
    }
}

// Generate 6-digit OTP
$otp = sprintf('%06d', mt_rand(0, 999999));

// Store OTP in session with expiration time (5 minutes)
$_SESSION['otp'] = $otp;
$_SESSION['otp_phone'] = $phone_number;
$_SESSION['otp_expires'] = time() + 300; // 5 minutes from now

// Get TextBee credentials from database (SuperAdmin's settings for OTP)
$credentials = getOTPCredentials();
$apiKey = trim($credentials['api_key'] ?? '');
$deviceId = trim($credentials['device_id'] ?? '');

if (empty($apiKey) || empty($deviceId)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'SMS service configuration error. Please contact administrator.'
    ]);
    exit();
}

$url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";

// SMS message
$message = "Your Ebakunado verification code is: $otp\n\nThis code will expire in 5 minutes. Do not share this code with anyone.";

// Prepare SMS data
$smsData = [
    'recipients' => [$phone_number],
    'message' => $message,
    'sender' => 'ebakunado'
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Key: ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($curlError) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to send SMS. Network error occurred.'
    ]);
    exit();
}

// Parse response
$responseData = json_decode($response, true);

// Check for successful response - TextBee.dev returns HTTP 200/201 for successful SMS
if ($httpCode === 200 || $httpCode === 201) {
    // Check if the response indicates success - TextBee API structure may vary
    $isSuccess = false;
    
    if ($responseData) {
        // Check for explicit success flag
        if (isset($responseData['success']) && $responseData['success'] === true) {
            $isSuccess = true;
        } elseif (isset($responseData['data']['success']) && $responseData['data']['success'] === true) {
            $isSuccess = true;
        } elseif (isset($responseData['status']) && ($responseData['status'] === 'success' || $responseData['status'] === 'sent')) {
            $isSuccess = true;
        } elseif (isset($responseData['data']['status']) && ($responseData['data']['status'] === 'success' || $responseData['data']['status'] === 'sent')) {
            $isSuccess = true;
        } elseif (isset($responseData['message_id']) || isset($responseData['data']['message_id'])) {
            $isSuccess = true;
        } elseif (!isset($responseData['error']) && !isset($responseData['data']['error'])) {
            // If HTTP 200/201 and no error field, assume success
            $isSuccess = true;
        }
    } else {
        // Empty response but HTTP 200/201 - might still be success
        $isSuccess = true;
    }
    
    if ($isSuccess) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'OTP sent successfully to ' . $phone_number,
            'expires_in' => 300
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'SMS API returned unexpected response format'
        ]);
    }
} else {
    // Provide specific error messages based on HTTP code
    $errorMessage = 'Failed to send SMS.';
    if ($httpCode === 401) {
        $errorMessage = 'SMS API authentication failed. Please contact your administrator.';
    } elseif ($httpCode === 403) {
        $errorMessage = 'SMS API access denied.';
    } elseif ($httpCode === 404) {
        $errorMessage = 'SMS API endpoint not found.';
    } elseif ($httpCode === 400) {
        $errorMessage = 'Invalid SMS request. Please check your phone number format.';
    } elseif ($httpCode >= 500) {
        $errorMessage = 'SMS service is temporarily unavailable. Please try again later.';
    }
    
    echo json_encode([
        'status' => 'error', 
        'message' => $errorMessage
    ]);
}
?>
