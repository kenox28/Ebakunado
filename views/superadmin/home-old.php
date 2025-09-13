<?php
session_start();
if (!isset($_SESSION['super_admin_id'])) {
	header("Location: ../login.php");
	exit();
} else {
	echo "Welcome Super Admin " . $_SESSION['super_admin_id'] . " " . $_SESSION['fname'];
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Super Admin Dashboard</title>
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
				background: #dc3545;
				color: white;
				padding: 8px 16px;
				border-radius: 4px;
				font-size: 14px;
			}

			.logout-link:hover {
				background: #c82333;
			}

			.section {
				margin-bottom: 40px;
				background: white;
				border-radius: 8px;
				padding: 20px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}

			h2 {
				font-size: 16px;
				margin-bottom: 20px;
				color: #333;
				border-bottom: 2px solid #007bff;
				padding-bottom: 5px;
			}

			.search-container {
				display: flex;
				gap: 10px;
				margin-bottom: 15px;
			}

			.search-input {
				flex: 1;
				padding: 8px;
				border: 1px solid #ddd;
				border-radius: 4px;
				font-size: 14px;
			}

			.table-container {
				overflow-x: auto;
				margin-bottom: 15px;
			}

			table {
				width: 100%;
				border-collapse: collapse;
				min-width: 800px;
			}

			th, td {
				padding: 8px 12px;
				text-align: left;
				border-bottom: 1px solid #ddd;
				font-size: 12px;
			}

			th {
				background-color: #f8f9fa;
				font-weight: bold;
				position: sticky;
				top: 0;
			}

			tr:hover {
				background-color: #f5f5f5;
			}

			.btn {
				padding: 6px 12px;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				text-decoration: none;
				display: inline-block;
				font-size: 12px;
				margin: 2px;
			}

			.btn-primary {
				background: #007bff;
				color: white;
			}

			.btn-primary:hover {
				background: #0056b3;
			}

			.btn-secondary {
				background: #6c757d;
				color: white;
			}

			.btn-secondary:hover {
				background: #545b62;
			}

			.btn-danger {
				background: #dc3545;
				color: white;
			}

			.btn-danger:hover {
				background: #c82333;
			}

			.btn-warning {
				background: #ffc107;
				color: #212529;
			}

			.btn-warning:hover {
				background: #e0a800;
			}

			.action-buttons {
				margin-top: 15px;
			}

			.form-container {
				background: #f8f9fa;
				padding: 20px;
				border-radius: 4px;
				margin-top: 20px;
			}

			.form-group {
				margin-bottom: 15px;
			}

			.form-group label {
				display: block;
				margin-bottom: 5px;
				font-weight: bold;
				font-size: 12px;
			}

			.form-group input, .form-group select {
				width: 100%;
				padding: 8px;
				border: 1px solid #ddd;
				border-radius: 4px;
				font-size: 12px;
			}

			.form-row {
				display: flex;
				gap: 15px;
			}

			.form-row .form-group {
				flex: 1;
			}

			.checkbox-cell {
				text-align: center;
				width: 40px;
			}

			.actions-cell {
				white-space: nowrap;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<div class="welcome-text">Super Admin Dashboard</div>
			<a href="#" onclick="logoutSuperAdmin()" class="logout-link">Logout</a>
		</div>

		<!-- Admin Management Section -->
		<div class="section">
			<h2>Admin Management</h2>
			<div class="search-container">
				<input type="text" id="searchAdmins" placeholder="Search admins..." class="search-input">
				<button type="button" onclick="clearSearch('searchAdmins', 'adminsTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<button type="button" onclick="showAddAdminForm()" class="btn btn-primary" style="margin: 10px 0;">Add New Admin</button>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllAdmins" onchange="toggleAllAdmins()"></th>
							<th>Admin ID</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="adminsTableBody">
						<!-- Admin data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedAdmins()" class="btn btn-danger">Delete Selected</button>
			</div>

			<form id="addAdminForm" method="post" class="form-container" style="display: none;">
				<h3>Add New Admin</h3>
				<div class="form-row">
					<div class="form-group">
						<label for="add_admin_id">Admin ID:</label>
						<input type="text" id="add_admin_id" name="admin_id" required>
					</div>
					<div class="form-group">
						<label for="add_admin_fname">First Name:</label>
						<input type="text" id="add_admin_fname" name="fname" required>
					</div>
					<div class="form-group">
						<label for="add_admin_lname">Last Name:</label>
						<input type="text" id="add_admin_lname" name="lname" required>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="add_admin_email">Email:</label>
						<input type="email" id="add_admin_email" name="email" required>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="add_admin_password">Password:</label>
						<input type="password" id="add_admin_password" name="password" required>
					</div>
				</div>
				<div class="form-group">
					<button type="button" onclick="saveAdmin()" class="btn btn-primary">Save Admin</button>
					<button type="button" onclick="cancelAddAdmin()" class="btn btn-secondary">Cancel</button>
				</div>
			</form>

			<div id="editAdminForm" class="form-container" style="display: none;">
				<!-- Edit admin form will be populated here -->
			</div>
		</div>

		<!-- Users Management Section -->
		<div class="section">
			<h2>Users Management</h2>
			<div class="search-container">
				<input type="text" id="searchUsers" placeholder="Search users..." class="search-input">
				<button type="button" onclick="clearSearch('searchUsers', 'usersTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()"></th>
							<th>User ID</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Gender</th>
							<th>Place</th>
							<th>Role</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="usersTableBody">
						<!-- User data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedUsers()" class="btn btn-danger">Delete Selected</button>
			</div>

			<div id="editUserForm" class="form-container" style="display: none;">
				<!-- Edit user form will be populated here -->
			</div>
		</div>

		<!-- BHW Management Section -->
		<div class="section">
			<h2>BHW Management</h2>
			<div class="search-container">
				<input type="text" id="searchBhw" placeholder="Search BHW..." class="search-input">
				<button type="button" onclick="clearSearch('searchBhw', 'bhwTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllBhw" onchange="toggleAllBhw()"></th>
							<th>BHW ID</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Gender</th>
							<th>Place</th>
							<th>Permissions</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="bhwTableBody">
						<!-- BHW data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedBhw()" class="btn btn-danger">Delete Selected</button>
			</div>

			<div id="editBhwForm" class="form-container" style="display: none;">
				<!-- Edit BHW form will be populated here -->
			</div>
		</div>

		<!-- Midwives Management Section -->
		<div class="section">
			<h2>Midwives Management</h2>
			<div class="search-container">
				<input type="text" id="searchMidwives" placeholder="Search midwives..." class="search-input">
				<button type="button" onclick="clearSearch('searchMidwives', 'midwivesTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllMidwives" onchange="toggleAllMidwives()"></th>
							<th>Midwife ID</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Gender</th>
							<th>Place</th>
							<th>Permissions</th>
							<th>Approved</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="midwivesTableBody">
						<!-- Midwife data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedMidwives()" class="btn btn-danger">Delete Selected</button>
			</div>

			<div id="editMidwifeForm" class="form-container" style="display: none;">
				<!-- Edit midwife form will be populated here -->
			</div>
		</div>

		<!-- Activity Logs Section -->
		<div class="section">
			<h2>Activity Logs</h2>
			<div class="search-container">
				<input type="text" id="searchActivityLogs" placeholder="Search activity logs..." class="search-input">
				<button type="button" onclick="clearSearch('searchActivityLogs', 'activityLogsTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllActivityLogs" onchange="toggleAllActivityLogs()"></th>
							<th>Log ID</th>
							<th>User ID</th>
							<th>User Type</th>
							<th>Action</th>
							<th>Description</th>
							<th>IP Address</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="activityLogsTableBody">
						<!-- Activity logs data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedActivityLogs()" class="btn btn-danger">Delete Selected</button>
			</div>
		</div>

		<!-- Location Management Section -->
		<div class="section">
			<h2>Location Management</h2>
			<div class="search-container">
				<input type="text" id="searchLocations" placeholder="Search locations..." class="search-input">
				<button type="button" onclick="clearSearch('searchLocations', 'locationsTableBody')" class="btn btn-secondary">Clear</button>
			</div>
			
			<button type="button" onclick="showAddLocationForm()" class="btn btn-primary" style="margin: 10px 0;">Add New Location</button>
			
			<div class="table-container">
				<table>
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllLocations" onchange="toggleAllLocations()"></th>
							<th>Province</th>
							<th>City/Municipality</th>
							<th>Barangay</th>
							<th>Purok</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="locationsTableBody">
						<!-- Location data will be populated here -->
					</tbody>
				</table>
			</div>
			
			<div class="action-buttons">
				<button type="button" onclick="deleteSelectedLocations()" class="btn btn-danger">Delete Selected</button>
			</div>

			<form id="addLocationForm" method="post" class="form-container" style="display: none;">
				<h3>Add New Location</h3>
				<div class="form-group">
					<label for="add_province">Province:</label>
					<input type="text" id="add_province" name="province" required>
				</div>
				<div class="form-group">
					<label for="add_city_municipality">City/Municipality:</label>
					<input type="text" id="add_city_municipality" name="city_municipality" required>
				</div>
				<div class="form-group">
					<label for="add_barangay">Barangay:</label>
					<input type="text" id="add_barangay" name="barangay" required>
				</div>
				<div class="form-group">
					<label for="add_purok">Purok:</label>
					<input type="text" id="add_purok" name="purok" required>
				</div>
				<div class="form-group">
					<button type="button" onclick="saveLocation()" class="btn btn-primary">Save Location</button>
					<button type="button" onclick="cancelAddLocation()" class="btn btn-secondary">Cancel</button>
				</div>
			</form>
		</div>

		<script src="../../js/superadmin/home.js?v=1.0.1"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	</body>
</html>
