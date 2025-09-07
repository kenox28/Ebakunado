<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header("Location: ../login.php");
	exit();
} else {
	echo "Welcome " . $_SESSION['admin_id'] . " " . $_SESSION['fname'];

	
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Admin Dashboard</title>
		<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
				font-size: 12px;
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

			.section h3 {
				margin-bottom: 15px;
				font-size: 20px;
			}

			table {
				width: 100%;
				border-collapse: collapse;
				margin-bottom: 20px;
			}

			table th,
			table td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}

			table th {
				background-color: #f8f9fa;
				font-weight: bold;
				position: sticky;
				top: 0;
			}

			table tr:hover {
				background-color: #f5f5f5;
			}

			.btn {
				padding: 8px 16px;
				border: 1px solid #ccc;
				border-radius: 4px;
				cursor: pointer;
				text-decoration: none;
				display: inline-block;
				margin: 2px;
				transition: all 0.3s ease;
			}

			.btn:hover {
				background-color: #f0f0f0;
			}

			.btn-danger {
				border-color: #dc3545;
			}

			.btn-danger:hover {
				background-color: #f8d7da;
			}

			.btn-primary {
				border-color: #007bff;
			}

			.btn-primary:hover {
				background-color: #cce7ff;
			}

			.form-container {
				background: #f8f9fa;
				padding: 20px;
				border-radius: 8px;
				margin: 20px 0;
				border: 1px solid #ddd;
			}

			.form-group {
				margin-bottom: 15px;
			}

			.form-group label {
				display: block;
				margin-bottom: 5px;
				font-weight: bold;
			}

			.form-group input,
			.form-group select {
				width: 100%;
				padding: 8px 12px;
				border: 1px solid #ccc;
				border-radius: 4px;
				font-size: 14px;
			}

			.form-group input[type="radio"] {
				width: auto;
				margin-right: 8px;
			}

			.radio-group {
				display: flex;
				gap: 20px;
				flex-wrap: wrap;
			}

			.radio-item {
				display: flex;
				align-items: center;
			}

			.actions {
				margin: 20px 0;
			}

			.search-container {
				display: flex;
				gap: 10px;
				margin-bottom: 20px;
				align-items: center;
			}

			.search-input {
				flex: 1;
				padding: 8px 12px;
				border: 1px solid #ccc;
				border-radius: 4px;
				font-size: 14px;
			}

			.search-input:focus {
				outline: none;
				border-color: #007bff;
			}

			.table-container {
				overflow-x: auto;
			}

			.checkbox-column {
				width: 50px;
			}

			.action-column {
				width: 80px;
			}

			@media (max-width: 768px) {
				body {
					padding: 10px;
				}
				
				.header {
					flex-direction: column;
					gap: 10px;
				}
				
				.radio-group {
					flex-direction: column;
					gap: 10px;
				}
				
				table th,
				table td {
					padding: 8px;
					font-size: 12px;
				}
			}
		</style>
	</head>
	<body>
		<div class="header">
			<div class="welcome-text">Admin Dashboard</div>
			<a href="../../php/admin/logout.php" class="logout-link">Logout</a>
		</div>

		<div class="section">
			<h2>Activity Logs</h2>
			<div class="search-container">
				<input type="text" id="searchLogs" placeholder="Search activity logs..." class="search-input">
				<button onclick="clearSearch('searchLogs', 'activityLogs')" class="btn">Clear</button>
			</div>
			<div class="table-container">
		<table>
			<thead>
				<tr>
					<th class="checkbox-column"><input type="checkbox" id="selectAll" onchange="toggleAllLogs()"> Select All</th>
					<th>Log ID</th>
					<th>User ID</th>
					<th>User Type</th>
					<th>Action Type</th>
					<th>Description</th>
					<th>IP Address</th>
					<th>Created At</th>
				</tr>
			</thead>
		<tbody id="activityLogs">
		</tbody>
		</table>
			</div>
		
			<div class="actions">
				<button onclick="deleteSelectedLogs()" class="btn btn-danger">Delete Selected Logs</button>
			</div>
		</div>

		<div class="section">
			<h2>Users Management</h2>
			<div class="search-container">
				<input type="text" id="searchUsers" placeholder="Search users..." class="search-input">
				<button onclick="clearSearch('searchUsers', 'users')" class="btn">Clear</button>
			</div>
			<div class="table-container">
		<table>
			<thead>
				<tr>
					<th class="checkbox-column"><input type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()"> Select All</th>
					<th>User ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone Number</th>
					<th>Profile Image</th>
					<th>Failed Attempts</th>
					<th>Lockout Time</th>
					<th>Gender</th>
					<th>Birth Date</th>
					<th>Created At</th>
					<th>Updated At</th>
					<th>Role</th>
					<th class="action-column">Edit</th>
					<th class="action-column">Delete</th>
				</tr>
				
			</thead>
			<tbody id="users">
			</tbody>
		</table>
			</div>
			
			<div class="actions">
				<button onclick="deleteSelectedUsers()" class="btn btn-danger">Delete Selected Users</button>
			</div>
		
			<form id="editUserForm" method="post" class="form-container" style="display: none;">
			</form>
		</div>

		<div class="section">
			<h2>Barangay Health Workers (BHW)</h2>
			<div class="search-container">
				<input type="text" id="searchBhw" placeholder="Search BHW..." class="search-input">
				<button onclick="clearSearch('searchBhw', 'bhwTable')" class="btn">Clear</button>
			</div>
			<div class="table-container">
				<table>
			<thead>
				<tr>
					<th class="checkbox-column"><input type="checkbox" id="selectAllBhw" onchange="toggleAllBhw()"> Select All</th>
					<th>BHW ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone Number</th>
					<th>Profile Image</th>
					<th>Gender</th>
					<th>Birth Date</th>
					<th>Permissions</th>
					<th>Last Active</th>
					<th>Created At</th>
					<th>Updated</th>
					<th>Role</th>
					<th class="action-column">Edit</th>
					<th class="action-column">Delete</th>
				</tr>
			</thead>
			<tbody id="bhwTable">
			</tbody>
			</table>
			</div>
			
			<div class="actions">
				<button onclick="deleteSelectedBhw()" class="btn btn-danger">Delete Selected BHW</button>
			</div>

			<form id="editBhwForm" method="post" class="form-container" style="display: none;">
			</form>
		</div>

		<div class="section">
			<h2>Midwives</h2>
			<div class="search-container">
				<input type="text" id="searchMidwives" placeholder="Search midwives..." class="search-input">
				<button onclick="clearSearch('searchMidwives', 'midwivesTable')" class="btn">Clear</button>
			</div>
			<div class="table-container">
				<table>
			<thead>
				<tr>
					<th class="checkbox-column"><input type="checkbox" id="selectAllMidwives" onchange="toggleAllMidwives()"> Select All</th>
					<th>Midwife ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone Number</th>
					<th>Profile Image</th>
					<th>Gender</th>
					<th>Birth Date</th>
					<th>Permissions</th>
					<th>Approve</th>
					<th>Last Active</th>
					<th>Created At</th>
					<th>Updated</th>
					<th>Role</th>
					<th class="action-column">Edit</th>
					<th class="action-column">Delete</th>
				</tr>
			</thead>
			<tbody id="midwivesTable">
			</tbody>
			</table>
			</div>
			
			<div class="actions">
				<button onclick="deleteSelectedMidwives()" class="btn btn-danger">Delete Selected Midwives</button>
			</div>

			<form id="editMidwifeForm" method="post" class="form-container" style="display: none;">
			</form>
					</div>
	</body>
	<script src="../../js/admin/home.js"></script>

	</script>
</html>
