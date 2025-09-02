<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Login - Ebakunado</title>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	</head>
	<body>
		<div class="form-container">
			<h1>Login</h1>
			<form action="" id="LoginForm">
				<!-- CSRF Token for security -->
				<input type="hidden" name="csrf_token" id="csrf_token" value="" />
				
				<div class="form-group">
					<input type="text" name="Email_number" id="Email_number" placeholder="Enter email or phone number" required />
				</div>
				
				<div class="form-group">
					<input type="password" name="password" id="password" placeholder="Enter password" required />
				</div>
				
				<button type="submit" class="submit-btn">Login</button>
			</form>
			
			<div class="links">
				<a href="create_account.php">Create Account</a>
				<a href="landing_page.php">Back to Home</a>
			</div>
		</div>
	</body>
	<script src="../js/login.js"></script>
</html> 