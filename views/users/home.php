<?php
session_start();

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
$noprofile = $_SESSION['profileimg']?? '';
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
	<style>
		*{
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Poppins', sans-serif;
		}
		body{
			height: 100vh;
			width: 100%;
			background-color: whitesmoke;

		}
		body header{
			background-color: white;
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 5px;
			box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			z-index: 1000;
		}
		main{
			margin-top: 4%;
			height: 95vh;
			border: 1px solid black;
			background-color: greenyellow;
			display: flex;
			
		}
		aside{
			width: 12vw;
			height: 100%;
			background-color: white;
			border: 1px solid black;
			display: flex;
			flex-direction: column;
			align-items: center;
		}
		aside a{
			height: 10vh;
			width: 100%;
			background-color: white;
			border: 1px solid black;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		footer{
			background-color:green;
			height: 5vh;
			width: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
			position: fixed;
			bottom: 0;
			left: 0;
			z-index: 1000;
		}
		section{
			width: 88vw;
			height: 100%;
			background-color: white;
			border: 1px solid black;
		}
		.childrenheader{
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 5px;
			box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
		}
		.childrenheader nav{
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 5px;
			box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
		}
		.childrenheader nav button{
			text-decoration: none;
			color: black;
			padding: 5px;
			box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
		}
		section{
			width: 100%;
			height: 100%;
			background-color: greenyellow;
		}

		section table{
			width: 100%;
			height: 100%;
			background-color: greenyellow;
			border: 1px solid black;
		}

		section table thead{
			background-color: blue	;
			height: 10px;
			color: white;
			font-weight: bold;
			font-size: 1.2rem;
			text-align: left;
			padding: 5px;
			border: 1px solid black;
		}
		

	</style>
	<body>
		<header>
			<div class="logo">
				<a href="home.php">
					<h1>Ebakunado</h1>
				</a>
			</div>
			<nav>
				<a href="children_account.php">Children Accounts</a>
				<a href="home.php">Notifications</a>
				<a href="profile.php">Profile</a>
				<a href="settings.php">Settings</a>
				<a href="Request.php">Request Immunization</a>
				<a href="../logout.php">Logout</a>
				<a href="profile.php">
					<img src="<?php echo $noprofile; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%;">
				</a>
			</nav>
		</header>

		<main>
			<aside>
					<a href="home.php">View Immunization</a>
					<a href="upcoming_schedule.php">Upcoming Schedule</a>
					<a href="missing_schedule.php">Missing Schedule</a>
					<a href="request_vaccination.php">Request Vaccination</a>
					<a href="#">Vaccination History</a>
			</aside>
			<!-- <h2>My Children's Health Records</h2>
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
			</div> -->

			<section>
                    <table id="childrenTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Vaccine</th>
                                <th>Dose</th>
                                <th>Upcoming Schedule</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="childrenTableBody">


                        </tbody>
                    </table>
				<!-- <div class="childrenheader">
					<h3>Child name</h3>
					<nav>
						<button>qr code</button>
						<button>Switch Account</button>
					</nav>

				</div>
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
			</div> -->
			</section>
		</main>
		
		<footer>
			<p>&copy; 2024 Ebakunado System. All rights reserved.</p>
		</footer>
	<script>
			async function getChildren() {
                try {
                    const response = await fetch('../../php/supabase/users/get_accepted_child.php');
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        populateTable(data.data);
                    } else {
                        console.error('Error:', data.message);
                        showError('Failed to load children data');
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    showError('Network error occurred');
                }
            }

            function populateTable(children) {
                const tbody = document.getElementById('childrenTableBody');
                tbody.innerHTML = '';

                if (children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No children records found</td></tr>';
                    return;
                }

                children.forEach(child => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${child.name}</td>
                        <td>${child.age == 0 ? child.weeks_old + ' weeks' : child.age + ' years'}</td>
                        <td>${child.gender}</td>
                        <td>${child.vaccine || 'N/A'}</td>
                        <td>${child.dose || 'N/A'}</td>
                        <td>${child.schedule_date || 'N/A'}</td>
                        <td>
                            <button onclick="viewChild('${child.baby_id}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                View
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

		async function viewChild(baby_id){
			const formdata = new FormData();
			formdata.append('baby_id', baby_id);
			const response = await fetch('../../php/supabase/users/view_immunization.php', {
				method: 'POST',
				body: formdata
			});
			const data = await response.json();
			if(data.status === 'success'){
				window.location.href = 'view_immunization.php?baby_id=' + baby_id;
			}else{
				alert('Error: ' + data.message);
			}

		}

		// async function getMyChildren(){
		// 	const body = document.querySelector('#childrenBody');
		// 	body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
		// 	try{
		// 		// const res = await fetch('../../php/users/get_my_children.php');

		// 		const res = await fetch('../../php/supabase/users/get_my_children.php');
		// 		const data = await res.json();
		// 		if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
		// 		if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No records</td></tr>'; return; }
		// 		body.innerHTML = data.data.map(r => {
		// 			const name = (r.child_name ? r.child_name : (r.child_fname + ' ' + r.child_lname));
		// 			return `<tr>
		// 				<td>${r.baby_id || ''}</td>
		// 				<td>${name || ''}</td>
		// 				<td>${r.child_birth_date || ''}</td>
		// 				<td>${r.status || ''}</td>
		// 				<td>
		// 					<button onclick="viewSchedule('${r.baby_id}')">View Schedule</button>
		// 					${r.babys_card ? `<a href="${r.babys_card}" target="_blank">Baby Card</a>` : ''}
		// 				</td>
		// 			</tr>`
		// 		}).join('');
		// 	}catch(e){
		// 		alert('Error loading records');
		// 	}
		// }

		// async function viewSchedule(baby_id){
		// 	const box = document.querySelector('#immunizationSchedule');
		// 	const body = document.querySelector('#scheduleBody');
		// 	box.style.display = 'block';
		// 	body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
		// 	try{
		// 		// const res = await fetch('../../php/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
		// 		const res = await fetch('../../php/supabase/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
		// 		const data = await res.json();
		// 		if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
		// 		if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No schedule yet</td></tr>'; return; }
		// 		body.innerHTML = data.data.map(v => {
		// 			return `<tr>
		// 				<td>${v.vaccine_name || ''}</td>
		// 				<td>${v.dose_number || ''}</td>
		// 				<td>${v.catch_up_date || ''}</td>
		// 				<td>${v.date_given || ''}</td>
		// 				<td>${v.status || ''}</td>
		// 			</tr>`
		// 		}).join('');
		// 	}catch(e){
		// 		alert('Error loading schedule');
		// 	}
		// }

		window.addEventListener('DOMContentLoaded', getChildren);
	</script>
	</body>
</html> 





