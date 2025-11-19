<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
	<link rel="stylesheet" href="../../css/fonts.css" />
	<link rel="stylesheet" href="../../css/modals.css" />
	<link rel="stylesheet" href="../../css/variables.css" />
	<link rel="stylesheet" href="../../css/login-style.css?v=1.0.1" />
	<link rel="stylesheet" href="../../css/queries.css?v=1.0.1" />
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
	<title>Health Worker Portal</title>
</head>

<body class="page-login">
	<main class="auth-main">
		<a class="back-to-home" href="../landing-page/landing-page.html">
			&larr; Back to Homepage
		</a>

			<section class="auth-frame">
				<!-- Left Side -->
				<div class="auth-left">
					<header class="auth-header">
						<img
							class="brand-logo"
							src="../../assets/images/white-ebakunado-logo-with-label.png"
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
					<h1 class="portal-title">Health Worker Portal</h1>
				</header>
				<form class="login-form" action="#" id="LoginForm">
					<h2 class="form-title">Sign In</h2>
					<div class="input-group">
						<input type="hidden" name="csrf_token" id="csrf_token" value="" />
						<label class="input-label" for="mobile_number">Mobile No.</label>
						<input
							class="form-input"
							type="text"
							id="Email_number"
							name="Email_number"
							required
							placeholder="+63 9XX XXX XXXX" />
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
					<div class="terms-wrapper">
						<p class="terms-text">
							By logging in, you agree to the Linao Health Center's
							<a class="terms-link" href="#">Terms of Service</a> and
							<a class="terms-link" href="#">Privacy Policy</a>.
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
							<button class="btn btn-primary" type="submit">Send OTP</button>
						</div>
						<div class="cancel-wrapper">
							<button type="button" id="cancelForgotPassword" class="btn btn-secondary">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</section>
	</main>

		<script src="../../js/auth-handler/password-toggle.js"></script>
		<script src="../../js/utils/ui-feedback.js"></script>
		<script src="../../js/supabase_js/login.js?v=1.0.24"></script>
		
	</body>
</html>