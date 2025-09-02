<?php
session_start();

// Check if BHW is logged in
if (!isset($_SESSION['bhw_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>BHW Dashboard - Ebakunado System</title>
		<!-- SweetAlert2 for better notifications -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	</head>
	<body>
		<header>
			<h1>Barangay Health Worker Dashboard</h1>
			<nav>
				<a href="residents.php">Residents</a>
				<a href="health_records.php">Health Records</a>
				<a href="reports.php">Reports</a>
				<a href="profile.php">Profile</a>
				<a href="../logout.php">Logout</a>
			</nav>
		</header>
		
		<main>
			<section>
				<h2>Welcome, BHW!</h2>
				<p>Manage community health records and generate reports from this dashboard.</p>
			</section>
			
			<section>
				<h2>Quick Actions</h2>
				<div>
					<a href="residents.php" class="btn">View Residents</a>
					<a href="health_records.php" class="btn">Health Records</a>
					<a href="reports.php" class="btn">Generate Reports</a>
					<a href="profile.php" class="btn">Update Profile</a>
				</div>
			</section>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	</body>
</html> 