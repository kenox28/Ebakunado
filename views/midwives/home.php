<?php
session_start();
if (!isset($_SESSION['midwife_id'])) {
	header("Location: ../../views/login.php");
	exit();
} else {
	echo "Welcome Midwife " . $_SESSION['midwife_id'] . " " . $_SESSION['fname'] . " " . $_SESSION['lname'];
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Midwife Dashboard</title>
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

			.approval-status {
				display: inline-block;
				padding: 4px 8px;
				border-radius: 4px;
				font-size: 12px;
				text-transform: uppercase;
				font-weight: bold;
			}

			.approval-status.approved {
				background-color: #28a745;
				color: white;
			}

			.approval-status.pending {
				background-color: #ffc107;
				color: #333;
			}

			.alert {
				padding: 15px;
				margin-bottom: 20px;
				border-radius: 4px;
			}

			.alert.warning {
				background-color: #fff3cd;
				border: 1px solid #ffeaa7;
				color: #856404;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<div class="welcome-text">Midwife Dashboard</div>
			<a href="#" onclick="logoutMidwife()" class="logout-link">Logout</a>
		</div>

		<?php if ($_SESSION['approve'] == 0): ?>
		<div class="alert warning">
			<strong>Account Pending Approval:</strong> Your midwife account is pending approval from an administrator. Some features may be limited until approved.
		</div>
		<?php endif; ?>

		<div class="section">
			<h2>Your Information</h2>
			<div class="user-info">
				<div class="info-item">
					<div class="info-label">Midwife ID</div>
					<div class="info-value"><?php echo $_SESSION['midwife_id']; ?></div>
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
				<div class="info-item">
					<div class="info-label">Approval Status</div>
					<div class="info-value">
						<span class="approval-status <?php echo $_SESSION['approve'] == 1 ? 'approved' : 'pending'; ?>">
							<?php echo $_SESSION['approve'] == 1 ? 'Approved' : 'Pending'; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="section">
			<h2>Quick Actions</h2>
			<?php if ($_SESSION['approve'] == 1): ?>
				<p>Welcome to your Midwife dashboard. Based on your permissions (<?php echo $_SESSION['permissions']; ?>), you can:</p>
				<ul>
					<?php if ($_SESSION['permissions'] === 'view'): ?>
						<li>View patient records</li>
						<li>View immunization schedules</li>
						<li>View maternal health records</li>
						<li>Generate reports</li>
					<?php elseif ($_SESSION['permissions'] === 'edit'): ?>
						<li>View and edit patient records</li>
						<li>Manage immunization schedules</li>
						<li>Update maternal health records</li>
						<li>Add new patient information</li>
						<li>Generate reports</li>
					<?php elseif ($_SESSION['permissions'] === 'admin'): ?>
						<li>Full access to all midwife functions</li>
						<li>Manage all patient records</li>
						<li>Oversee immunization programs</li>
						<li>Manage maternal health programs</li>
						<li>Generate and export all reports</li>
						<li>System administration tasks</li>
					<?php endif; ?>
				</ul>
			<?php else: ?>
				<p><strong>Limited Access:</strong> Your account is pending approval. Please contact an administrator to approve your account for full access.</p>
				<ul>
					<li>View basic information only</li>
					<li>Contact administrator for approval</li>
				</ul>
			<?php endif; ?>
		</div>

		<div class="section">
			<h2>System Status</h2>
			<p>Dashboard is ready for use. 
			<?php if ($_SESSION['approve'] == 1): ?>
				All systems operational.
			<?php else: ?>
				Awaiting account approval for full functionality.
			<?php endif; ?>
			</p>
		</div>

		<script>
		async function logoutMidwife() {
			try {
				const response = await fetch('../../php/midwives/logout.php', {
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