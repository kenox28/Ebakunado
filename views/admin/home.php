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
		<title>admin home</title>
	</head>
	<body>
		<!-- <h1>admin home</h1> -->
		<div style="text-align: right; padding: 10px;">
			<a href="../../php/admin/logout.php" style="color: red; text-decoration: none; font-weight: bold;">Logout</a>
		</div>
		<table>
			<thead>
				<tr>
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


		<table>
			<thead>
				<tr>
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
					<th>Edit</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody id="users">
			</tbody>
		</table>

		<form id="editUserForm" method="post" style="display: none;">

		</form>
	</body>
	<script>
		async function getActivityLogs() {
			const response = await fetch('../../php/admin/show_activitylog.php');
			const data = await response.json();
			console.log(data);
			const tbody = document.querySelector('#activityLogs');
			for (const log of data) {
				tbody.innerHTML += `<tr>
					<td>${log.log_id}</td>
					<td>${log.user_id}</td>
					<td>${log.user_type}</td>
					<td>${log.action_type}</td>
					<td>${log.description}</td>
					<td>${log.ip_address}</td>
					<td>${log.created_at}</td>
				</tr>`;
			}
		}
		getActivityLogs();

		async function getUsers() {
			const response = await fetch('../../php/admin/show_users.php');
			const data = await response.json();
			console.log(data);
			const tbody = document.querySelector('#users');
			for (const user of data) {
				tbody.innerHTML += `<tr>

					<td>${user.user_id}</td>
					<td>${user.fname}</td>
					<td>${user.lname}</td>
					<td>${user.email}</td>
					<td>${user.phone_number}</td>
					<td>${user.profileImg}</td>
					<td>${user.failed_attempts}</td>
					<td>${user.lockout_time}</td>
					<td>${user.gender}</td>
					<td>${user.bdate}</td>
					<td>${user.created_at}</td>
					<td>${user.updated}</td>
					<td>${user.role}</td>
					<td><button onclick="editUser('${user.user_id}')">Edit</button></td>
					<td><button onclick="deleteUser('${user.user_id}')">Delete</button></td>
				</tr>`;
			}
		}
		getUsers();

		async function editUser(user_id) {
			try {
				const formData = new FormData();
				formData.append('user_id', user_id);
				const response = await fetch('../../php/admin/edit_user.php', {
					method: 'POST',
					body: formData
				});
				
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				
				const data = await response.json();
				console.log(data);
				
				if (data.status && data.status === 'error') {
					alert('Error: ' + data.message);
					return;
				}
				
				const form = document.querySelector('#editUserForm');
				form.innerHTML = `
					<input type="hidden" id="user_id" name="user_id" value="${data.user_id || ''}">
					
					<input type="text" id="fname" name="fname" placeholder="First Name" value="${data.fname || ''}" required>
					
					<input type="text" id="lname" name="lname" placeholder="Last Name" value="${data.lname || ''}" required>
					
					<input type="email" id="email" name="email" placeholder="Email" value="${data.email || ''}" required>
					
					<input type="text" id="phone_number" name="phone_number" placeholder="Phone Number" value="${data.phone_number || ''}" required>
					
					<div>
						<input type="radio" id="role_user" name="role" value="user" ${(data.role === 'user' || !data.role) ? 'checked' : ''}>
						<label for="role_user">User</label>
						
						<input type="radio" id="role_bhw" name="role" value="bhw" ${data.role === 'bhw' ? 'checked' : ''}>
						<label for="role_bhw">BHW (Barangay Health Worker)</label>
						
						<input type="radio" id="role_midwife" name="role" value="midwife" ${data.role === 'midwife' ? 'checked' : ''}>
						<label for="role_midwife">Midwife</label>
					</div>
					<br>
					<button type="button" onclick="saveUser()">Save</button>
					<button type="button" onclick="cancelEdit()">Cancel</button>
				`;
				form.style.display = 'block';
			} catch (error) {
				console.error('Error fetching user data:', error);
				alert('Failed to load user data. Please try again.');
			}
		}



		async function saveUser() {
			try {
				const formData = new FormData();
				formData.append('user_id', document.getElementById('user_id').value);
				formData.append('fname', document.getElementById('fname').value);
				formData.append('lname', document.getElementById('lname').value);
				formData.append('email', document.getElementById('email').value);
				formData.append('phone_number', document.getElementById('phone_number').value);
				
				// Get selected radio button value
				const selectedRole = document.querySelector('input[name="role"]:checked').value;
				formData.append('role', selectedRole);
				
				const response = await fetch('../../php/admin/save_user.php', {
					method: 'POST',
					body: formData
				});
				
				const data = await response.json();
				
				if (data.status === 'success') {
					alert('User updated successfully');
					cancelEdit();
					location.reload(); // Refresh the page to update the table
				} else {
					alert('Error: ' + data.message);
				}
			} catch (error) {
				console.error('Error saving user:', error);
				alert('Failed to save user. Please try again.');
			}
		}
		
		function cancelEdit() {
			const form = document.querySelector('#editUserForm');
			form.style.display = 'none';
		}
		
		async function deleteUser(user_id) {
			if (!confirm('Are you sure you want to delete this user?')) {
				return;
			}
			
			try {
				const formData = new FormData();
				formData.append('user_id', user_id);
				const response = await fetch('../../php/admin/delete_user.php', {
					method: 'POST',
					body: formData
				});
				
				const data = await response.json();
				
				if (data.status === 'success') {
					alert('User deleted successfully');
					location.reload(); // Refresh the page to update the table
				} else {
					alert('Error: ' + data.message);
				}
			} catch (error) {
				console.error('Error deleting user:', error);
				alert('Failed to delete user. Please try again.');
			}
		}
		
		

	</script>
</html>
