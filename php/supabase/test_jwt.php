<?php
/**
 * JWT Test Endpoint - For testing JWT functionality
 * This file helps verify if JWT is working correctly
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');

// Include JWT helper
require_once __DIR__ . '/JWT.php';

// Test JWT library installation
function testJWTInstallation() {
    try {
        // Check if Firebase JWT classes exist
        if (!class_exists('Firebase\JWT\JWT')) {
            return [
                'status' => 'error',
                'message' => 'JWT library not installed. Run: composer require firebase/php-jwt',
                'installed' => false
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'JWT library is installed correctly',
            'installed' => true
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'JWT library check failed: ' . $e->getMessage(),
            'installed' => false
        ];
    }
}

// Test token generation
function testTokenGeneration() {
    try {
        $test_user = [
            'user_id' => 'TEST123',
            'email' => 'test@example.com',
            'fname' => 'Test',
            'lname' => 'User'
        ];
        
        $token = JWT::generateToken($test_user, 'user');
        
        if ($token) {
            return [
                'status' => 'success',
                'message' => 'Token generated successfully',
                'token' => $token,
                'token_length' => strlen($token)
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Token generation failed'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Token generation error: ' . $e->getMessage()
        ];
    }
}

// Test token verification
function testTokenVerification($token) {
    try {
        $payload = JWT::verifyToken($token);
        
        if ($payload) {
            return [
                'status' => 'success',
                'message' => 'Token verified successfully',
                'payload' => $payload,
                'user_id' => $payload['user_id'] ?? null,
                'user_type' => $payload['user_type'] ?? null,
                'expires_at' => isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : null
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Token verification failed (token may be invalid or expired)'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Token verification error: ' . $e->getMessage()
        ];
    }
}

// Handle request
$action = $_GET['action'] ?? $_POST['action'] ?? 'test';

switch ($action) {
    case 'install_check':
        // Check if JWT library is installed
        echo json_encode(testJWTInstallation(), JSON_PRETTY_PRINT);
        break;
        
    case 'generate':
        // Test token generation
        echo json_encode(testTokenGeneration(), JSON_PRETTY_PRINT);
        break;
        
    case 'verify':
        // Test token verification
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        
        if (!$token) {
            // Try to extract from header
            $token = JWT::extractToken();
        }
        
        if (!$token) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No token provided. Send token via GET/POST parameter or Authorization header.',
                'hint' => 'Use: ?action=verify&token=YOUR_TOKEN_HERE'
            ], JSON_PRETTY_PRINT);
            break;
        }
        
        echo json_encode(testTokenVerification($token), JSON_PRETTY_PRINT);
        break;
        
    case 'full_test':
        // Run all tests
        $results = [
            'installation_check' => testJWTInstallation(),
            'token_generation' => null,
            'token_verification' => null
        ];
        
        // Only test generation/verification if library is installed
        if ($results['installation_check']['installed'] ?? false) {
            $gen_result = testTokenGeneration();
            $results['token_generation'] = $gen_result;
            
            // Verify the generated token
            if (isset($gen_result['token'])) {
                $results['token_verification'] = testTokenVerification($gen_result['token']);
            }
        }
        
        echo json_encode([
            'status' => 'complete',
            'message' => 'All tests completed',
            'results' => $results
        ], JSON_PRETTY_PRINT);
        break;
        
    default:
        // Default: show test options
        echo json_encode([
            'status' => 'info',
            'message' => 'JWT Test Endpoint - Available actions',
            'actions' => [
                'install_check' => 'Check if JWT library is installed - ?action=install_check',
                'generate' => 'Test token generation - ?action=generate',
                'verify' => 'Test token verification - ?action=verify&token=YOUR_TOKEN',
                'full_test' => 'Run all tests - ?action=full_test'
            ],
            'usage' => [
                'Check installation: php/supabase/test_jwt.php?action=install_check',
                'Generate token: php/supabase/test_jwt.php?action=generate',
                'Verify token: php/supabase/test_jwt.php?action=verify&token=YOUR_TOKEN',
                'Full test: php/supabase/test_jwt.php?action=full_test'
            ]
        ], JSON_PRETTY_PRINT);
        break;
}
?>

