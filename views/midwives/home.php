<?php
session_start();

// Check if midwife is logged in
if (!isset($_SESSION['midwife_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Midwife Dashboard - Ebakunado System</title>
		<!-- SweetAlert2 for better notifications -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	</head>
	<body>
		<header>
			<h1>Midwife Dashboard</h1>
			<nav>
				<a href="patients.php">Patients</a>
				<a href="appointments.php">Appointments</a>
				<a href="profile.php">Profile</a>
				<a href="../logout.php">Logout</a>
			</nav>
		</header>
		
		<main>
			<section>
				<h2>Welcome, Midwife!</h2>
				<p>Manage your patients and appointments from this dashboard.</p>
			</section>
			
			<section>
				<h2>Quick Actions</h2>
				<div>
					<a href="patients.php" class="btn">View Patients</a>
					<a href="appointments.php" class="btn">Manage Appointments</a>
					<a href="profile.php" class="btn">Update Profile</a>
				</div>
			</section>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	</body>
</html> 