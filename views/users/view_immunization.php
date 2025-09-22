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
		.header{
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
            display: flex;
            flex-direction: column;
		}

        .childHeader{
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
        }
        .childHeader nav{
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 5px;
            box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
        }
        .childHeader nav button{
            text-decoration: none;
            color: black;
            padding: 5px;
            box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
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
        
        #childQrCodeContainer{
            display: none;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        #closeQrCodeButton{
            position: fixed;
            top: 0;
            right: 0;
        }

	</style>
	<body>
		<header class="header">
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
			</nav>
		</header>

		<main>
			<aside> 
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
                <header class="childHeader">
                    <nav>
                        <h3>Child Name: <span id="childName"></span></h3>
                        <h3>Child Age: <span id="childAge"></span></h3>
                        <h3>Child Gender: <span id="childGender"></span></h3>
                    </nav>
                    <nav>
                        <button id="childQrCodeButton"><img id="childQrCode" src="" alt="QR Code" style="width: 100px; height: 100px;"></button>
                        <div id="childQrCodeContainer" style="display: none;"><button id="closeQrCodeButton">Close</button> <img id="childQrCodeImage" src="" alt="QR Code" style="width: 500px; height: 500px;"></div>
                        <a href="home.php">Switch Account</a>
                </nav>
                </header>
                <table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Vaccine</th>
							<th>Dose #</th>
							<th>Schedule Date</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="scheduleBody">
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
        async function getChildDetails(){
            const baby_id = '<?php echo $_GET['baby_id']; ?>';

            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('../../php/supabase/users/get_child_details.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            console.log(data);
            if(data.status === 'success'){
                const child = data.data[0];
                document.getElementById('childName').innerHTML = child.name;
                document.getElementById('childAge').innerHTML = child.age == 0 ? child.weeks_old + ' weeks ' : child.age + ' years ';
                document.getElementById('childGender').innerHTML = child.gender;
                document.getElementById('childQrCode').src = child.qr_code;
                console.log(child.qr_code);
                document.getElementById('childQrCodeButton').addEventListener('click', () => {
                    document.getElementById('childQrCodeContainer').style.display = 'block';
                    document.getElementById('childQrCodeImage').src = child.qr_code;
                    document.getElementById('childQrCodeButton').style.display = 'none';



                });

                // child.age == 0 ? child.weeks_old + ' weeks' : child.age + ' years'
            }
        }
        getChildDetails();



        document.getElementById('closeQrCodeButton').addEventListener('click', () => {
            document.getElementById('childQrCodeContainer').style.display = 'none';
            document.getElementById('childQrCodeButton').style.display = 'block';
        });


        async function getImmunizationSchedule(){
            const baby_id = '<?php echo $_GET['baby_id']; ?>';
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('../../php/supabase/users/get_my_immunization_records.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            console.log(data);
            if(data.status === 'success'){
                const immunizationSchedule = data.data;
                const scheduleBody = document.getElementById('scheduleBody');
                scheduleBody.innerHTML = '';
                immunizationSchedule.forEach(immunization => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${immunization.vaccine_name}</td>
                        <td>${immunization.dose_number}</td>
                        <td>${immunization.schedule_date}</td>
                        <td>${immunization.status}</td>
                    `;
                    scheduleBody.appendChild(row);
                });
                console.log(immunizationSchedule);
            }
        }
        getImmunizationSchedule();
	</script>
	</body>
</html> 