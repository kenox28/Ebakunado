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
			flex-direction: column;
			
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
		.childrenheader{
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 5px;
			box-shadow: 0 0 10px 0 rgba(145, 76, 76, 0.1);
		}
		.childrenbody{
			width: 100%;
			height: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
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
				<a href="home.php">Children Accounts</a>
				<a href="home.php">Notifications</a>
				<a href="profile.php">Profile</a>
				<a href="settings.php">Settings</a>
				<a href="Request.php">Request Immunization</a>
				<a href="../logout.php">Logout</a>
				<a href="profile.php">
					<img src="../../assets/icons/<?php echo $noprofile; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%;">
				</a>
			</nav>
		</header>
		<main>
				<div class="childrenheader">
					<h3>Children Accounts</h3>
				</div>
                <div class="childrenbody">
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
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
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
                        <td>${child.age} years</td>
                        <td>${child.weeks_old} weeks</td>
                        <td>${child.gender}</td>
                        <td>${child.vaccine || 'N/A'}</td>
                        <td>${child.dose || 'N/A'}</td>
                        <td>${child.upcoming_schedule || 'N/A'}</td>
                        <td>
                            <button onclick="viewChild('${child.baby_id}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                View
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            function viewChild(babyId) {
                // You can implement view functionality here
                alert(`View details for child: ${babyId}`);
            }

            function showError(message) {
                // You can use SweetAlert2 here for better error display
                console.error(message);
                // Swal.fire('Error', message, 'error');
            }

            // Load data when page loads
            getChildren();
        </script>


	</body>
</html> 