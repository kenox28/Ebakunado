<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
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
		
	</head>
	<body>
		<header>
			<h1>Welcome to Ebakunado System</h1>
			<nav>
				<a href="profile.php">Profile</a>
				<a href="settings.php">Settings</a>
				<a href="Request.php">Request Immunization</a>
				<a href="../logout.php">Logout</a>
			</nav>
		</header>
		
		<main>
			<h2>My Children's Health Records</h2>
			<div id="childrenTable">
				<table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Baby ID</th>
							<th>Child Name</th>
							<th>Birth Date</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="childrenBody">
						<tr><td colspan="5">Loading...</td></tr>
					</tbody>
				</table>
			</div>
			
			<div id="immunizationSchedule" style="display: none; margin-top: 20px;">
				<h3>Immunization Schedule</h3>
				<table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Vaccine</th>
							<th>Dose #</th>
							<th>Due Date</th>
							<th>Date Given</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="scheduleBody">
					</tbody>
				</table>
			</div>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	<script>
		async function getMyChildren(){
			const body = document.querySelector('#childrenBody');
			body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
			try{
				// const res = await fetch('../../php/users/get_my_children.php');

				const res = await fetch('../../php/mysql/users/get_my_children.php');
				const data = await res.json();
				if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
				if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No records</td></tr>'; return; }
				body.innerHTML = data.data.map(r => {
					const name = (r.child_name ? r.child_name : (r.child_fname + ' ' + r.child_lname));
					return `<tr>
						<td>${r.baby_id || ''}</td>
						<td>${name || ''}</td>
						<td>${r.child_birth_date || ''}</td>
						<td>${r.status || ''}</td>
						<td>
							<button onclick="viewSchedule('${r.baby_id}')">View Schedule</button>
							${r.babys_card ? `<a href="${r.babys_card}" target="_blank">Baby Card</a>` : ''}
						</td>
					</tr>`
				}).join('');
			}catch(e){
				alert('Error loading records');
			}
		}

		async function viewSchedule(baby_id){
			const box = document.querySelector('#immunizationSchedule');
			const body = document.querySelector('#scheduleBody');
			box.style.display = 'block';
			body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
			try{
				// const res = await fetch('../../php/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
				const res = await fetch('../../php/mysql/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
				const data = await res.json();
				if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
				if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No schedule yet</td></tr>'; return; }
				body.innerHTML = data.data.map(v => {
					return `<tr>
						<td>${v.vaccine_name || ''}</td>
						<td>${v.dose_number || ''}</td>
						<td>${v.catch_up_date || ''}</td>
						<td>${v.date_given || ''}</td>
						<td>${v.status || ''}</td>
					</tr>`
				}).join('');
			}catch(e){
				alert('Error loading schedule');
			}
		}

		window.addEventListener('DOMContentLoaded', getMyChildren);
	</script>
	</body>
</html> 