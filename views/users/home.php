<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get user information from session
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Welcome - Ebakunado System</title>
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
				background: linear-gradient(135deg, #28a745, #20c997);
				color: white;
				text-align: center;
				padding: 40px 20px;
			}
			header h1 {
				margin: 0 0 20px 0;
				font-size: 2.2em;
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
				color: #28a745;
			}
			main {
				max-width: 1000px;
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
				background: #28a745;
				color: white;
				text-decoration: none;
				padding: 12px 25px;
				margin: 10px;
				border-radius: 25px;
				transition: background-color 0.3s;
			}
			.btn:hover {
				background: #20c997;
			}
			.quick-actions {
				text-align: center;
			}
			footer {
				background: #333;
				color: white;
				text-align: center;
				padding: 20px;
				margin-top: 40px;
			}
			.user-info {
				background: #f8f9fa;
				padding: 20px;
				border-radius: 8px;
				margin: 20px 0;
				border-left: 4px solid #28a745;
			}
			.user-info h3 {
				color: #28a745;
				margin-bottom: 15px;
			}
			.user-info p {
				margin: 8px 0;
				color: #555;
			}
			.welcome-message {
				text-align: center;
				font-size: 1.5em;
				color: #28a745;
				margin-bottom: 30px;
			}
		</style>
	</head>
	<body>
		<header>
			<h1>Welcome to Ebakunado System</h1>
			<nav>
				<a href="profile.php">Profile</a>
				<a href="settings.php">Settings</a>
				<a href="../logout.php">Logout</a>
			</nav>
		</header>
		
		<main>
			<section>
				<div class="welcome-message">
					<h2>Hello World! 👋</h2>
					<p>Welcome back, <strong><?php echo htmlspecialchars($fname . ' ' . $lname); ?></strong>!</p>
				</div>
				
				<div class="user-info">
					<h3>Your Account Information</h3>
					<p><strong>Name:</strong> <?php echo htmlspecialchars($fname . ' ' . $lname); ?></p>
					<p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
					<p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
					<p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
					<p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?></p>
				</div>
			</section>
			
			<section class="quick-actions">
				<h2>Quick Actions</h2>
				<div>
					<a href="profile.php" class="btn">View Profile</a>
					<a href="settings.php" class="btn">Account Settings</a>
					<a href="../landing_page.php" class="btn">Back to Home</a>
				</div>
			</section>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	</body>
</html> 