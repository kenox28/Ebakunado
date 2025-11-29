<?php
/**
 * Test Session Restoration from JWT Token
 * Place this file in the root directory and access it via: http://localhost/ebakunado/test-session-restore.php
 */

session_start();

echo "<h1>Session Restoration Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

echo "<h2>1. Current Session Status</h2>";
if (isset($_SESSION['user_id']) || isset($_SESSION['bhw_id']) || isset($_SESSION['midwife_id']) || isset($_SESSION['super_admin_id'])) {
    echo "<div class='success'>✓ Session is ACTIVE</div>";
    echo "<pre>";
    echo "User ID: " . ($_SESSION['user_id'] ?? $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? $_SESSION['super_admin_id'] ?? 'N/A') . "\n";
    echo "User Type: " . ($_SESSION['user_type'] ?? 'N/A') . "\n";
    echo "Logged In: " . ($_SESSION['logged_in'] ?? 'false') . "\n";
    echo "Name: " . ($_SESSION['fname'] ?? 'N/A') . " " . ($_SESSION['lname'] ?? '') . "\n";
    echo "Email: " . ($_SESSION['email'] ?? 'N/A') . "\n";
    echo "</pre>";
} else {
    echo "<div class='error'>✗ Session is NOT ACTIVE</div>";
}

echo "<h2>2. JWT Token Cookie Status</h2>";
if (isset($_COOKIE['jwt_token']) && !empty($_COOKIE['jwt_token'])) {
    echo "<div class='success'>✓ JWT Token Cookie EXISTS</div>";
    echo "<pre>";
    echo "Token Length: " . strlen($_COOKIE['jwt_token']) . " characters\n";
    echo "Token (first 50 chars): " . substr($_COOKIE['jwt_token'], 0, 50) . "...\n";
    echo "</pre>";
    
    // Try to verify the token
    require_once __DIR__ . '/php/supabase/JWT.php';
    try {
        $payload = JWT::verifyToken($_COOKIE['jwt_token']);
        if ($payload) {
            echo "<div class='success'>✓ JWT Token is VALID</div>";
            echo "<pre>";
            echo "User ID: " . ($payload['user_id'] ?? 'N/A') . "\n";
            echo "User Type: " . ($payload['user_type'] ?? 'N/A') . "\n";
            echo "Email: " . ($payload['email'] ?? 'N/A') . "\n";
            echo "Name: " . ($payload['fname'] ?? 'N/A') . " " . ($payload['lname'] ?? '') . "\n";
            echo "</pre>";
        } else {
            echo "<div class='error'>✗ JWT Token is INVALID or EXPIRED</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>✗ JWT Token Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='error'>✗ JWT Token Cookie NOT FOUND</div>";
    echo "<div class='info'>You need to log in first to generate a JWT token.</div>";
}

echo "<h2>3. Testing Session Restoration</h2>";
if (!isset($_SESSION['user_id']) && !isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id']) && !isset($_SESSION['super_admin_id'])) {
    if (isset($_COOKIE['jwt_token'])) {
        echo "<div class='info'>Attempting to restore session from JWT token...</div>";
        
        // Load the restoration function
        require_once __DIR__ . '/php/supabase/shared/restore_session_from_jwt.php';
        $restored = restore_session_from_jwt();
        
        if ($restored) {
            echo "<div class='success'>✓ Session RESTORED successfully from JWT token!</div>";
            echo "<pre>";
            echo "User ID: " . ($_SESSION['user_id'] ?? $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? $_SESSION['super_admin_id'] ?? 'N/A') . "\n";
            echo "User Type: " . ($_SESSION['user_type'] ?? 'N/A') . "\n";
            echo "Logged In: " . ($_SESSION['logged_in'] ?? 'false') . "\n";
            echo "Name: " . ($_SESSION['fname'] ?? 'N/A') . " " . ($_SESSION['lname'] ?? '') . "\n";
            echo "</pre>";
        } else {
            echo "<div class='error'>✗ Failed to restore session from JWT token</div>";
            echo "<div class='info'>The token may be expired or invalid. Please log in again.</div>";
        }
    } else {
        echo "<div class='error'>✗ Cannot restore session - No JWT token found</div>";
        echo "<div class='info'>Please log in first to generate a JWT token.</div>";
    }
} else {
    echo "<div class='info'>Session is already active. To test restoration, clear your session first.</div>";
}

echo "<h2>4. Test Instructions</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li><strong>Log in</strong> to your account first</li>";
echo "<li><strong>Check this page</strong> - You should see 'Session is ACTIVE'</li>";
echo "<li><strong>Close your browser completely</strong> (all windows)</li>";
echo "<li><strong>Reopen browser</strong> and come back to this page</li>";
echo "<li><strong>Expected result:</strong> Session should be restored from JWT token automatically</li>";
echo "</ol>";
echo "</div>";

echo "<h2>5. Quick Links</h2>";
echo "<p>";
echo "<a href='login'>Go to Login</a> | ";
echo "<a href='dashboard'>Go to Dashboard</a> | ";
echo "<a href='health-dashboard'>Go to BHW Dashboard</a> | ";
echo "<a href='admin-dashboard'>Go to Admin Dashboard</a>";
echo "</p>";
?>

