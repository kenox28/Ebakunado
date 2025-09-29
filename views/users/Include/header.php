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
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
		<title>Document</title>
	</head>
	<style>
		* {
			padding: 0;
			margin: 0;
		}
		body {
			height: 100vh;
			width: 100%;
			background-color: #f0f0f0;
			display: flex;
		}
		header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			border: 1px solid #000;
			width: 100%;
            
			background-color: #f0f0f0;
		}
		nav {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 10px;
		}
		aside {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding: 10px;
			width: 15%;
			height: 100%;
			background-color: aqua;
		}

		/* Menu links styling */
		aside a {
			display: flex;
			align-items: center;
			gap: 10px;
			text-decoration: none;
			color: #000;
			font-weight: 600;
			padding: 8px 10px;
			height: 20px;
			font-size: 18px;
			border: 1px solid #000;
		}
		/* Icon before text using data-icon */
		aside a::before {
			content: attr(data-icon);
			font-size: 18px;
			line-height: 1;
		}

		/* Collapsed state */
		aside.collapsed {
			width: 50px;
			padding-left: 6px;
			padding-right: 6px;
			align-items: center;
		}
		/* Hide link text when collapsed */
		aside.collapsed a span {
			display: none;
		}
		/* Center icons when collapsed */
		aside.collapsed a {
			justify-content: center;
			gap: 0;
		}
		/* Optionally hide the large title when collapsed */
		aside.collapsed h3 {
			display: none;
		}
		/* Adjust main area width only when collapsed */
		aside.collapsed + main {
			width: calc(100% - 50px);
		}
		/* Table container to prevent overlap and allow horizontal scroll */
		.table-container {
			width: 100%;
			max-width: 100%;
            height: 100%;
			overflow-x: auto;

		}
		.table-container table {
			width: 100%;
			border-collapse: collapse;
		}
		.table-container th,
		.table-container td {
			white-space: nowrap;
			border: 1px solid #000;
            text-align: center;
		}
		h3 {
			display: flex;
			justify-content: center;
			align-items: center;
			height: 70px;
			width: 100%;
			font-size: 18px;
			border: 1px solid #000;

			margin-bottom: 10%;
		}
		main {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 10px;
			width: 85%;
		}   
		.profile-link {
			display: flex;
			align-items: center;
			text-decoration: none;
			color: #000;
			flex-direction: column;
			padding-left: 10px;
			cursor: pointer;
		}
		.profile-img {
			width: 40px;
			height: 40px;
			border-radius: 50%;
		}
		.profile-img:hover {
			transform: scale(1.1);
		}
		.dropdown {
			position: relative;
		}
		.dropdown-button {
			background: none;
			border: none;
			cursor: pointer;
			padding: 5px;
		}
		.dropdown-content {
			border: 1px solid #000;
			display: none;
			position: absolute;
			right: 0;
			top: 100%;
			background-color: #f9f9f9;
			min-width: 120px;
			box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
			z-index: 1;
		}
		.dropdown-content a {
			color: black;
			padding: 12px 16px;
			text-decoration: none;
			display: block;
			border: none;
			font-size: 14px;
		}
		.dropdown-content a:hover {
			background-color: #f1f1f1;
		}
		.dropdown.active .dropdown-content {
			display: block;
		}

		/* Table styling for user pages */
		#childrenTable {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}
		#childrenTable th,
		#childrenTable td {
			border: 1px solid #000;
			padding: 8px 12px;
			text-align: center;
		}
		#childrenTable th {
			background-color: #f0f0f0;
			font-weight: bold;
		}
		#childrenTable tr:nth-child(even) {
			background-color: #f9f9f9;
		}
		#childrenTable tr:hover {
			background-color: #e9e9e9;
		}

		/* Button styling */
		button {
			cursor: pointer;
		}
		button:hover {
			opacity: 0.8;
		}
		.notification-button {
			padding-left: 10px;
			padding-right: 10px;
			cursor: pointer;
		}
		.notification-button:hover {
			opacity: 0.8;
		}
		.notification-button i {
			font-size: 18px;
		}

		/* Form styling */
		form {
			max-width: 100%;
			max-height: 100%;
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 5px;
		}
		form input[type="text"],
		form input[type="date"],
		form input[type="file"] {
			width: 100%;
			padding: 8px 12px;
			margin: 5px 0;
			border: 1px solid #ccc;
			border-radius: 3px;
			box-sizing: border-box;
		}
		form input[type="radio"] {
			margin: 0 5px 0 0;
		}
		form label {
			display: block;
			margin: 10px 0 5px 0;
			font-weight: bold;
		}
		form button {
			width: 100%;
			padding: 12px;
			background: #007bff;
			color: white;
			border: none;
			border-radius: 3px;
			cursor: pointer;
			font-size: 16px;
			margin-top: 20px;
		}
		form button:hover {
			background: #0056b3;
		}

		/* Vaccine checkboxes styling */
		.vaccine-checkboxes {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 10px;
			margin: 15px 0;
			padding: 15px;
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 3px;
		}
		.vaccine-checkboxes label {
			display: flex;
			align-items: center;
			margin: 5px 0;
			font-weight: normal;
			cursor: pointer;
		}
		.vaccine-checkboxes input[type="checkbox"] {
			margin-right: 8px;
			transform: scale(1.2);
		}
		.vaccine-checkboxes label:hover {
			background: #e9e9e9;
			padding: 2px 5px;
			border-radius: 3px;
		}
		.form-section{
			display: flex;
		}
	</style>
	<body>
		<aside>
			<h3>Ebakunado</h3>
			<a href="#" data-icon="🏠"><span>Dashboard</span></a>
			<a href="./home.php" data-icon="💉"><span>Imuunization</span></a>
			<a href="#" data-icon="⏳"><span>missing immunization</span></a>
			<a href="./Request.php" data-icon="🧒"><span>Request for immunization</span></a>
		</aside>
		<main>
			<header>
				<nav>
					<button
						class="menu-button"
						style="padding: 6px 10px; margin-right: 8px">
						☰
					</button>
					<a href="#">ebakunado</a>
	
				</nav>
				<nav>

					<input
						type="text"
						id="searchInput"
						placeholder="Search by Baby ID, Name, or User ID"
						style="padding: 6px 10px; width: 260px"
						oninput="filterTable()" />
					<button
						onclick="openScanner()"
						style="padding: 6px 10px; margin-left: 8px">
						Scan QR
					</button>
					<div class="notification-button">
						<i class="fa-solid fa-bell"></i>
					</div>
					<div class="dropdown" id="dropdown">
						<a class="profile-link" href="#" onclick="toggleDropdown(); return false;">
							<img class="profile-img" src="<?php echo $noprofile; ?>" alt="profile">
							<label for="profile"><?php echo $fname . ' ' . $lname; ?></label>
						</a>
						<div class="dropdown-content">
							<a href="#" onclick="logoutUser()">Logout</a>
						</div>
					</div>
				</nav>
			</header>


