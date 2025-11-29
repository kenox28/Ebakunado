<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Welcome to Ebakunado System</title>
		<!-- SweetAlert2 for better notifications -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<style>
			body {
				font-family: Arial, sans-serif;
				margin: 0;
				padding: 0;
				background-color: #f5f5f5;
				line-height: 1.6;
			}
			header {
				background: linear-gradient(135deg, #007bff, #0056b3);
				color: white;
				text-align: center;
				padding: 60px 20px;
			}
			header h1 {
				margin: 0 0 20px 0;
				font-size: 2.5em;
			}
			nav {
				margin-top: 20px;
			}
			nav a {
				color: white;
				text-decoration: none;
				margin: 0 15px;
				padding: 10px 20px;
				border: 2px solid white;
				border-radius: 25px;
				transition: all 0.3s;
			}
			nav a:hover {
				background: white;
				color: #007bff;
			}
			main {
				max-width: 1200px;
				margin: 0 auto;
				padding: 40px 20px;
			}
			section {
				background: white;
				margin: 20px 0;
				padding: 30px;
				border-radius: 10px;
				box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			}
			section h2 {
				color: #333;
				margin-bottom: 20px;
				text-align: center;
			}
			section p {
				color: #666;
				text-align: center;
				font-size: 1.1em;
			}
			.btn {
				display: inline-block;
				background: #007bff;
				color: white;
				text-decoration: none;
				padding: 15px 30px;
				margin: 10px;
				border-radius: 25px;
				transition: background-color 0.3s;
			}
			.btn:hover {
				background: #0056b3;
			}
			.get-started {
				text-align: center;
			}
			footer {
				background: #333;
				color: white;
				text-align: center;
				padding: 20px;
				margin-top: 40px;
			}
			.features {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
				gap: 20px;
				margin-top: 30px;
			}
			.feature {
				background: #f8f9fa;
				padding: 20px;
				border-radius: 8px;
				text-align: center;
				border-left: 4px solid #007bff;
			}
			.feature h3 {
				color: #007bff;
				margin-bottom: 15px;
			}
			.download-app {
				background: linear-gradient(135deg, #28a745, #20c997);
				color: white;
			}
			.download-app h2 {
				color: white;
			}
			.download-app p {
				color: rgba(255, 255, 255, 0.9);
			}
			.download-app .btn {
				background: white;
				color: #28a745;
				font-weight: bold;
			}
			.download-app .btn:hover {
				background: #f8f9fa;
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(0,0,0,0.2);
			}
		</style>
	</head>
	<body>
		<header>
			<h1>Welcome to Ebakunado System</h1>
			<nav>
				<a href="login.php">Login</a>
				<a href="create_account.php">Create Account</a>
			</nav>
		</header>
		
		<main>
			<section>
				<h2>About Our System</h2>
				<p>Welcome to the Ebakunado System - your comprehensive healthcare management solution designed to streamline maternal and child health services, immunization tracking, and healthcare provider management.</p>
				
				<div class="features">
					<div class="feature">
						<h3>User Management</h3>
						<p>Secure account creation and management for patients, healthcare providers, and administrators.</p>
					</div>
					<div class="feature">
						<h3>Healthcare Records</h3>
						<p>Comprehensive tracking of immunization records and healthcare services.</p>
					</div>
					<div class="feature">
						<h3>Provider Management</h3>
						<p>Efficient management of midwives and barangay health workers.</p>
					</div>
				</div>
			</section>
			
			<section class="get-started">
				<h2>Get Started</h2>
				<div>
					<a href="create_account.php" class="btn">Create New Account</a>
					<a href="login.php" class="btn">Login to Existing Account</a>
				</div>
			</section>
			
			<section class="download-app">
				<h2>Download Our Mobile App</h2>
				<p>Get the Ebakunado mobile application for easy access to your healthcare records and immunization schedules on the go.</p>
				<div style="text-align: center; margin-top: 30px;">
					<a href="https://drive.google.com/uc?export=download&id=1M6u_cztd9qIsQ95wRXUk08CXTF7MB_Lc" class="btn" style="background: #28a745; font-size: 1.1em; padding: 18px 40px;" target="_blank">
						<span style="margin-right: 10px;">📱</span>
						Download Mobile App
					</a>
				</div>
			</section>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	</body>
</html> 