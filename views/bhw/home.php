<?php
session_start();
if (!isset($_SESSION['bhw_id'])) {
	header("Location: ../../views/login.php");
	exit();
} else {
	echo "Welcome BHW " . $_SESSION['bhw_id'] . " " . $_SESSION['fname'] . " " . $_SESSION['lname'];
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>BHW Dashboard</title>
		<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}

			body {
				font-family: Arial, sans-serif;
				line-height: 1.6;
				padding: 20px;
				background-color: #f5f5f5;
			}

			.header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 20px;
				margin-bottom: 30px;
				background: white;
				border-radius: 8px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}

			.welcome-text {
				font-size: 18px;
				font-weight: bold;
			}

			.logout-link {
				text-decoration: none;
				padding: 8px 16px;
				border: 1px solid #ccc;
				border-radius: 4px;
				transition: all 0.3s ease;
			}

			.logout-link:hover {
				background-color: #f0f0f0;
			}

			.section {
				margin-bottom: 40px;
				background: white;
				border-radius: 8px;
				padding: 20px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}

			.section h2 {
				margin-bottom: 20px;
				padding-bottom: 10px;
				border-bottom: 2px solid #eee;
				font-size: 24px;
			}

			.user-info {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 15px;
			}

			.info-item {
				padding: 10px;
				background-color: #f8f9fa;
				border-radius: 4px;
				border-left: 4px solid #007bff;
			}

			.info-label {
				font-weight: bold;
				color: #333;
			}

			.info-value {
				color: #666;
				margin-top: 5px;
			}

			.permissions {
				display: inline-block;
				padding: 4px 8px;
				background-color: #28a745;
				color: white;
				border-radius: 4px;
				font-size: 12px;
				text-transform: uppercase;
			}

			.permissions.view {
				background-color: #6c757d;
			}

			.permissions.edit {
				background-color: #ffc107;
				color: #333;
			}

			.permissions.admin {
				background-color: #dc3545;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<div class="welcome-text">BHW Dashboard</div>
			<a href="#" onclick="logoutBhw()" class="logout-link">Logout</a>
		</div>

		<div class="section">
			<h2>Your Information</h2>
			<div class="user-info">
				<div class="info-item">
					<div class="info-label">BHW ID</div>
					<div class="info-value"><?php echo $_SESSION['bhw_id']; ?></div>
				</div>
				<div class="info-item">
					<div class="info-label">Full Name</div>
					<div class="info-value"><?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></div>
				</div>
				<div class="info-item">
					<div class="info-label">Email</div>
					<div class="info-value"><?php echo $_SESSION['email']; ?></div>
				</div>
				<div class="info-item">
					<div class="info-label">Phone Number</div>
					<div class="info-value"><?php echo $_SESSION['phone_number']; ?></div>
				</div>
				<div class="info-item">
					<div class="info-label">Role</div>
					<div class="info-value"><?php echo ucfirst($_SESSION['role']); ?></div>
				</div>
				<div class="info-item">
					<div class="info-label">Permissions</div>
					<div class="info-value">
						<span class="permissions <?php echo $_SESSION['permissions']; ?>">
							<?php echo ucfirst($_SESSION['permissions']); ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="section">
			<h2>Quick Actions</h2>
			<p>Welcome to your BHW dashboard. Based on your permissions (<?php echo $_SESSION['permissions']; ?>), you can:</p>
			<ul>
				<?php if ($_SESSION['permissions'] === 'view'): ?>
					<li>View immunization records</li>
					<li>View patient information</li>
					<li>Generate reports</li>
				<?php elseif ($_SESSION['permissions'] === 'edit'): ?>
					<li>View and edit immunization records</li>
					<li>Add new patient records</li>
					<li>Update patient information</li>
					<li>Generate reports</li>
				<?php elseif ($_SESSION['permissions'] === 'admin'): ?>
					<li>Full access to all BHW functions</li>
					<li>Manage immunization records</li>
					<li>Manage patient information</li>
					<li>Generate and export reports</li>
					<li>System administration tasks</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="section">
			<h2>System Status</h2>
			<p>Dashboard is ready for use. All systems operational.</p>
		</div>

		<script>
		async function logoutBhw() {
			try {
				const response = await fetch('../../php/bhw/logout.php', {
					method: 'POST'
				});
				
				const data = await response.json();
				
				if (data.status === 'success') {
					// Redirect to login page
					window.location.href = '../../views/login.php';
				} else {
					alert('Logout failed: ' + data.message);
				}
			} catch (error) {
				console.error('Logout error:', error);
				alert('Logout failed. Please try again.');
			}
		}
		</script>
	</body>
</html>