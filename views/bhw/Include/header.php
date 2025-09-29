<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
			background-color: green;
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


	</style>
	<body>
		<aside>
			<h3>Ebakunado</h3>
			<a href="#" data-icon="🏠"><span>Dashboard</span></a>
			<a href="./immunization.php" data-icon="💉"><span>Imuunization form</span></a>
			<a href="./pending_approval.php" data-icon="⏳"><span>Pending Approval</span></a>
			<a href="./child_health_record.php" data-icon="🧒"><span>Child Health Record</span></a>
			<a href="#" data-icon="🎯"><span>Target client list</span></a>
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
					<a href="#" onclick="logoutBhw()">Logout</a>
				</nav>
			</header>