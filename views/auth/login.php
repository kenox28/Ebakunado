<?php
session_start();

// Load JWT class if needed
require_once __DIR__ . '/../../php/supabase/JWT.php';

$redirect_url = null;

// Helper: get user type from JWT
function get_user_type_from_jwt($token) {
    try {
        $payload = JWT::verifyToken($token);
        if ($payload && isset($payload['user_type'])) {
            return $payload['user_type'];
        }
    } catch (Exception $e) {
        // Invalid token - clear cookie
        if (isset($_COOKIE['jwt_token'])) {
            setcookie('jwt_token', '', time() - 3600, '/');
        }
    }
    return null;
}

// Check session first
if (isset($_SESSION['user_type'])) {
    $user_type = $_SESSION['user_type'];
} else if (isset($_COOKIE['jwt_token'])) {
    $user_type = get_user_type_from_jwt($_COOKIE['jwt_token']);
    // If token is valid, set session for this tab
    if ($user_type) {
        $_SESSION['user_type'] = $user_type;
        // Get user ID based on type
        $payload = JWT::verifyToken($_COOKIE['jwt_token']);
        if ($payload) {
            switch ($user_type) {
                case 'super_admin':
                    $_SESSION['super_admin_id'] = $payload['user_id'];
                    break;
                case 'admin':
                    $_SESSION['admin_id'] = $payload['user_id'];
                    break;
                case 'bhw':
                    $_SESSION['bhw_id'] = $payload['user_id'];
                    break;
                case 'midwife':
                    $_SESSION['midwife_id'] = $payload['user_id'];
                    break;
                case 'user':
                    $_SESSION['user_id'] = $payload['user_id'];
                    break;
            }
            $_SESSION['email'] = $payload['email'] ?? null;
            $_SESSION['fname'] = $payload['fname'] ?? null;
            $_SESSION['lname'] = $payload['lname'] ?? null;
            $_SESSION['logged_in'] = true;
        }
    }
} else {
    $user_type = null;
}

// Use clean routes instead of direct file paths
if ($user_type === 'bhw' || $user_type === 'midwife') {
    $redirect_url = "health-dashboard";
} else if ($user_type === 'admin') {
    $redirect_url = "admin-dashboard";
} else if ($user_type === 'super_admin') {
    $redirect_url = "superadmin-dashboard";
} else if ($user_type === 'user') {
    $redirect_url = "dashboard";
}

if ($redirect_url) {
    header("Location: $redirect_url");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Login Portal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
	<link rel="stylesheet" href="css/fonts.css" />
	<link rel="stylesheet" href="css/variables.css" />
	<link rel="stylesheet" href="css/modals.css?v=1.0.1" />
	<link rel="stylesheet" href="css/login-style.css?v=1.0.3" />
	<link rel="stylesheet" href="css/queries.css?v=1.0.2" />
	<style>
		/* Forgot Password Form Styling */
		#forgotPasswordForm {
			animation: fadeIn 0.3s ease-in-out;
		}
		
		.cancel-wrapper {
			margin-top: 16px;
			text-align: center;
		}
		
		.btn-secondary {
			background-color: #6c757d;
			color: white;
			border: none;
			padding: 12px 24px;
			border-radius: 8px;
			font-size: 16px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.3s ease;
			text-decoration: none;
			display: inline-block;
		}
		
		.btn-secondary:hover {
			background-color: #5a6268;
			transform: translateY(-2px);
		}
		
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(10px); }
			to { opacity: 1; transform: translateY(0); }
		}
	</style>
</head>

<body class="page-login">
	<main class="auth-main">
		<a class="back-to-home" href="home">
			&larr; Back to Homepage
		</a>

			<section class="auth-frame">
				<!-- Left Side -->
				<div class="auth-left">
					<header class="auth-header">
						<img
							class="brand-logo"
							src="assets/images/white-ebakunado-logo-with-label.png"
							alt="Ebakunado Logo" />
						<div class="brand-text">
							<h2 class="brand-title">Immunization Data Management</h2>
							<h2 class="brand-subtitle">Linao Health Center</h2>
						</div>
					</header>
					<footer class="auth-footer">
						<p class="copyright-text">
							&copy; 2025 Linao Health Center | eBakunado System
						</p>
					</footer>
				</div>

			<!-- Right Side -->
			<div class="auth-right">
				<header class="login-header">
					<h1 class="portal-title">Welcome to eBakunado</h1>
					<h2 class="form-title">Login to your account</h2>
				</header>
				<form class="login-form" action="#" id="LoginForm">
					<div class="input-group">
						<input type="hidden" name="csrf_token" id="csrf_token" value="" />
						<label class="input-label" for="Email_number">Email or Phone Number</label>
						<input
							class="form-input"
							type="text"
							id="Email_number"
							name="Email_number"
							required
							placeholder="Enter your email or phone number" />
					</div>
					<div class="input-group password-group">
						<label class="input-label" for="password">Password</label>
						<div class="password-wrapper">
							<input
								class="form-input password-input"
								type="password"
								id="password"
								name="password"
								required
								placeholder="Enter your password" />
							<span class="material-symbols-rounded password-toggle">visibility_off</span>
						</div>
					</div>
					<div class="forgot-password-wrapper">
						<a class="forgot-password-link" href="#" id="forgotPasswordLink">Forgot Password?</a>
					</div>
					<div class="submit-wrapper">
						<button class="btn btn-primary login-btn" type="submit">
							Login
						</button>
					</div>
					<div class="signup-wrapper">
						<p class="signup-text">Don't have an account?<a href="register">Create an account</a></p>
					</div>
					<div class="terms-wrapper">
						<p class="terms-text">
							By logging in, you agree to the Linao Health Center's
							<a class="terms-link" href="privacy-terms-linao">Privacy Policy & Terms of Service</a>.
						</p>
					</div>
				</form>

				<!-- Forgot Password Form (Hidden by default) -->
				<div id="forgotPasswordForm" style="display: none;">
					<h2 class="form-title">Forgot Password</h2>
					<form id="ForgotPasswordForm">
						<div class="input-group">
							<label class="input-label" for="email_phone">Email or Phone Number</label>
							<input
								class="form-input"
								type="text"
								id="email_phone"
								name="email_phone"
								required
								placeholder="Enter your email or phone number" />
						</div>
						<div class="submit-wrapper">
							<button class="btn btn-primary forgot-btn" type="submit">Send OTP</button>
						</div>
						<div class="cancel-wrapper">
							<button type="button" id="cancelForgotPassword" class="btn btn-secondary">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</section>
	</main>

		<script src="js/auth-handler/password-toggle.js"></script>
		<script src="js/utils/ui-feedback.js"></script>
		
		<script>
		// Check if user is already logged in (via JWT token in localStorage or cookie)
		(function() {
			// Check localStorage first (faster)
			const token = localStorage.getItem('jwt_token');
			
			// If no token in localStorage, check cookie
			const cookieToken = document.cookie.split('; ').find(row => row.startsWith('jwt_token='));
			const jwtToken = token || (cookieToken ? cookieToken.split('=')[1] : null);
			
			if (jwtToken) {
				// Verify token and get user type
				fetch('php/supabase/test_jwt.php?action=verify&token=' + encodeURIComponent(jwtToken))
					.then(res => res.json())
					.then(data => {
						if (data.status === 'success' && data.payload) {
							const userType = data.payload.user_type;
							let redirectUrl = null;
							
							// Determine redirect URL based on user type, using clean routes
							if (userType === 'bhw' || userType === 'midwife') {
								redirectUrl = 'health-dashboard';
							} else if (userType === 'admin') {
								redirectUrl = 'admin-dashboard';
							} else if (userType === 'super_admin') {
								redirectUrl = 'superadmin-dashboard';
							} else if (userType === 'user') {
								redirectUrl = 'dashboard';
							}
							
							if (redirectUrl) {
								window.location.href = redirectUrl;
							}
						}
					})
					.catch(error => {
						// If token verification fails, remove invalid token
						console.error('Token verification failed:', error);
						localStorage.removeItem('jwt_token');
						// Clear cookie
						document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
					});
			}
		})();
		</script>
		
		<script src="js/supabase_js/login.js?v=1.0.30"></script>
		
	</body>
</html>