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
			width: 100vw;
			background-color: #f0f0f0;
			display: flex;
			margin: 0;
			padding: 0;
			overflow: hidden;
		}
		header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			border-bottom: 1px solid #ddd;
			width: 100%;
			background-color: #fff;
			padding: 10px 20px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			box-sizing: border-box;
		}
		
		.header-left {
			display: flex;
			align-items: center;
			gap: 15px;
		}
		
		.header-left h1 {
			font-size: 24px;
			color: #1976d2;
			margin: 0;
		}
		
		.header-right {
			display: flex;
			align-items: center;
			gap: 10px;
		}
		aside {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding: 10px;
			width: 15%;
			height: 100vh;
			background-color: #e3f2fd;
			transition: width 0.3s ease;
			overflow-y: auto;
			box-sizing: border-box;
		}
		
		.profile-section {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 15px 10px;
			margin-bottom: 20px;
			border-bottom: 1px solid #ccc;
		}
		
		.profile-section img {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			margin-bottom: 10px;
		}
		
		.profile-section label {
			font-weight: bold;
			text-align: center;
			font-size: 14px;
		}
		
		.aside-nav {
			display: flex;
			flex-direction: column;
			width: 100%;
			gap: 5px;
		}

		/* Menu links styling */
		.aside-nav a {
			display: flex;
			align-items: center;
			gap: 10px;
			text-decoration: none;
			color: #333;
			font-weight: 500;
			padding: 12px 15px;
			font-size: 16px;
			border-radius: 8px;
			transition: all 0.2s ease;
		}
		
		.aside-nav a:hover {
			background-color: #bbdefb;
			color: #1976d2;
		}
		
		/* Icon before text using data-icon */
		.aside-nav a::before {
			content: attr(data-icon);
			font-size: 20px;
			line-height: 1;
		}

		/* Collapsed state */
		aside.collapsed {
			width: 60px;
			padding: 5px;
			align-items: center;
		}
		
		/* Hide profile section when collapsed */
		aside.collapsed .profile-section {
			display: none;
		}
		
		/* Hide link text when collapsed */
		aside.collapsed .aside-nav a span {
			display: none;
		}
		
		/* Center icons when collapsed */
		aside.collapsed .aside-nav a {
			justify-content: center;
			gap: 0;
			padding: 12px 8px;
			font-size: 20px;
		}
		
		/* Adjust main area width when collapsed */
		aside.collapsed + main {
			width: calc(100vw - 60px);
		}
		/* Content area inside main */
		.content {
			flex: 1;
			padding: 20px;
			overflow-y: auto;
			box-sizing: border-box;
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
			padding: 0;
			width: 85%;
			height: 100vh;
			transition: width 0.3s ease;
			overflow: hidden;
			box-sizing: border-box;
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
			position: relative;
			background: none;
			border: none;
			padding: 8px;
			cursor: pointer;
			border-radius: 50%;
			transition: background-color 0.2s ease;
		}
		
		.notification-button:hover {
			background-color: #f5f5f5;
		}
		
		.notification-button i {
			font-size: 20px;
			color: #666;
		}
		
		.notification-button .notification-badge {
			position: absolute;
			top: 2px;
			right: 2px;
			background: #dc3545;
			color: white;
			border-radius: 50%;
			width: 18px;
			height: 18px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 10px;
			font-weight: bold;
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

		/* Radio button groups styling */
		.radio-group {
			margin: 10px 0;
			padding: 10px;
			background: #f8f9fa;
			border: 1px solid #e9ecef;
			border-radius: 3px;
		}
		.radio-group label {
			display: inline-block;
			margin: 5px 10px 5px 5px;
			font-weight: normal;
			cursor: pointer;
		}
		.radio-group input[type="radio"] {
			margin-right: 5px;
			transform: scale(1.1);
		}
		.radio-group input[type="text"] {
			width: 150px;
			padding: 4px 8px;
			margin-left: 5px;
			border: 1px solid #ccc;
			border-radius: 3px;
		}


		/* Aside notification badge */
		aside .notification-badge {
			background: #dc3545;
			color: white;
			border-radius: 50%;
			width: 18px;
			height: 18px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 10px;
			font-weight: bold;
			margin-left: 5px;
		}
	</style>
	<body>
		<aside>
			<div class="profile-section">
				<img class="profile-img" src="<?php echo $noprofile; ?>" alt="profile">
				<label><?php echo $fname . ' ' . $lname; ?></label>
			</div>
			
			<nav class="aside-nav">
				<a href="home.php" data-icon="🏠"><span>Dashboard</span></a>
				<a href="upcoming_schedule.php" data-icon="📅"><span>Upcoming</span></a>
				<a href="missed_immunization.php" data-icon="⚠️"><span>Missed</span></a>
				<a href="#" onclick="addChild()" data-icon="➕"><span>Add Child</span></a>
				<a href="#" onclick="logoutUser()" data-icon="🚪"><span>Logout</span></a>
			</nav>
		</aside>
		<main>
			<header>
				<div class="header-left">
					<button class="menu-button" style="padding: 6px 10px; margin-right: 8px">
						☰
					</button>
					<h1>ebakunado</h1>
				</div>
				<div class="header-right">
					<button class="notification-button" onclick="showNotifications()">
						<i class="fa-solid fa-bell"></i>
						<span class="notification-badge" id="notificationCount">0</span>
					</button>
				</div>
			</header>

<script>
		function showNotifications() {
			// Mark notifications as read when user opens the modal
			notificationRead = true;
			const badge = document.getElementById('notificationCount');
			if (badge) {
				badge.textContent = '0';
				badge.style.display = 'none';
			}

			// Create notification modal
			const modal = document.createElement('div');
			modal.style.cssText = `
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(0,0,0,0.5);
				z-index: 1000;
				display: flex;
				align-items: center;
				justify-content: center;
			`;

			const modalContent = document.createElement('div');
			modalContent.style.cssText = `
				background: white;
				padding: 20px;
				border-radius: 10px;
				max-width: 500px;
				width: 90%;
				max-height: 80vh;
				overflow-y: auto;
			`;

			// Get dashboard data from home page or fetch it
			fetchNotificationData().then(dashboardData => {
				let notificationHTML = `
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
						<h3><i class="fas fa-bell"></i> Notifications</h3>
						<button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
					</div>
					<div id="notificationsList">
				`;

				// Add pending approvals notifications
				if (dashboardData.pendingApprovals > 0) {
					notificationHTML += `
						<div class="notification-item" style="padding: 10px; border-left: 4px solid #ffc107; background: #fff8e1; margin-bottom: 10px;">
							<h4><i class="fas fa-clock"></i> Pending Approvals (${dashboardData.pendingApprovals})</h4>
							<p>You have ${dashboardData.pendingApprovals} child registration(s) waiting for BHW approval.</p>
						</div>
					`;
				}

				// Add upcoming vaccines notifications
				if (dashboardData.upcomingVaccines > 0) {
					notificationHTML += `
						<div class="notification-item" style="padding: 10px; border-left: 4px solid #28a745; background: #e8f5e8; margin-bottom: 10px;">
							<h4><i class="fas fa-calendar-alt"></i> Upcoming Vaccines (${dashboardData.upcomingVaccines})</h4>
							<p>You have ${dashboardData.upcomingVaccines} vaccination(s) scheduled in the next 30 days.</p>
						</div>
					`;
				}

				// Add today's and tomorrow's schedules
				if (dashboardData.todaySchedules && dashboardData.todaySchedules.length > 0) {
					notificationHTML += `
						<div class="notification-item" style="padding: 10px; border-left: 4px solid #dc3545; background: #fff5f5; margin-bottom: 10px;">
							<h4><i class="fas fa-calendar-day"></i> Today's Schedule (${dashboardData.todaySchedules.length})</h4>
							<p>You have ${dashboardData.todaySchedules.length} vaccination(s) scheduled for today!</p>
						</div>
					`;
				}

				if (dashboardData.tomorrowSchedules && dashboardData.tomorrowSchedules.length > 0) {
					notificationHTML += `
						<div class="notification-item" style="padding: 10px; border-left: 4px solid #ffc107; background: #fffdf0; margin-bottom: 10px;">
							<h4><i class="fas fa-calendar-plus"></i> Tomorrow's Schedule (${dashboardData.tomorrowSchedules.length})</h4>
							<p>You have ${dashboardData.tomorrowSchedules.length} vaccination(s) scheduled for tomorrow.</p>
						</div>
					`;
				}

				// Add recent activities as notifications
				if (dashboardData.activities && dashboardData.activities.length > 0) {
					notificationHTML += `<h4 style="margin-top: 20px;"><i class="fas fa-history"></i> Recent Updates</h4>`;
					dashboardData.activities.slice(0, 5).forEach(activity => {
						const iconClass = getActivityIcon(activity.type);
						const iconBg = getActivityIconBg(activity.type);
						
						notificationHTML += `
							<div class="notification-item" style="padding: 10px; border-left: 4px solid ${iconBg}; background: #f8f9fa; margin-bottom: 10px;">
								<div style="display: flex; align-items: center; gap: 10px;">
									<div style="width: 30px; height: 30px; border-radius: 50%; background: ${iconBg}; display: flex; align-items: center; justify-content: center; color: white;">
										<i class="${iconClass}" style="font-size: 14px;"></i>
									</div>
									<div>
										<h5 style="margin: 0; font-size: 14px;">${activity.title}</h5>
										<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">${activity.description}</p>
										<small style="color: #999;">${formatTime(new Date(activity.timestamp))}</small>
									</div>
								</div>
							</div>
						`;
					});
				}

				if ((!dashboardData.pendingApprovals || dashboardData.pendingApprovals === 0) && 
					(!dashboardData.upcomingVaccines || dashboardData.upcomingVaccines === 0) && 
					(!dashboardData.activities || dashboardData.activities.length === 0)) {
					notificationHTML += `
						<div style="text-align: center; padding: 40px; color: #666;">
							<i class="fas fa-bell-slash" style="font-size: 48px; margin-bottom: 10px;"></i>
							<p>No notifications at the moment</p>
						</div>
					`;
				}

				notificationHTML += `</div>`;
				modalContent.innerHTML = notificationHTML;
			}).catch(error => {
				modalContent.innerHTML = `
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
						<h3><i class="fas fa-bell"></i> Notifications</h3>
						<button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
					</div>
					<div style="text-align: center; padding: 40px; color: #666;">
						<p>Unable to load notifications</p>
					</div>
				`;
			});

			modal.appendChild(modalContent);
			modal.className = 'modal';
			
			document.body.appendChild(modal);
			
			// Close modal when clicking outside
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					modal.remove();
				}
			});
		}

		async function fetchNotificationData() {
			try {
				const [pendingResponse, statsResponse, activityResponse, scheduleResponse] = await Promise.all([
					fetch('../../php/supabase/users/get_pending_requests.php'),
					fetch('../../php/supabase/users/get_vaccination_stats.php'),
					fetch('../../php/supabase/users/get_recent_activity.php'),
					fetch('../../php/supabase/users/get_today_tomorrow_schedules.php')
				]);

				const pendingData = await pendingResponse.json();
				const statsData = await statsResponse.json();
				const activityData = await activityResponse.json();
				const scheduleData = await scheduleResponse.json();

				return {
					pendingApprovals: pendingData.status === 'success' ? pendingData.data.length : 0,
					upcomingVaccines: statsData.status === 'success' ? statsData.data.upcoming : 0,
					completedVaccines: statsData.status === 'success' ? statsData.data.completed : 0,
					activities: activityData.status === 'success' ? activityData.data : [],
					todaySchedules: scheduleData.status === 'success' ? scheduleData.data.today : [],
					tomorrowSchedules: scheduleData.status === 'success' ? scheduleData.data.tomorrow : []
				};
			} catch (error) {
				console.error('Error fetching notification data:', error);
				return {
					pendingApprovals: 0,
					upcomingVaccines: 0,
					completedVaccines: 0,
					activities: [],
					todaySchedules: [],
					tomorrowSchedules: []
				};
			}
		}

		function getActivityIcon(type) {
			switch(type) {
				case 'approval': return 'fas fa-check-circle';
				case 'rejection': return 'fas fa-times-circle';
				case 'vaccine': return 'fas fa-syringe';
				case 'schedule': return 'fas fa-calendar';
				default: return 'fas fa-info-circle';
			}
		}

		function getActivityIconBg(type) {
			switch(type) {
				case 'approval': return '#28a745';
				case 'rejection': return '#dc3545';
				case 'vaccine': return '#007bff';
				case 'schedule': return '#ffc107';
				default: return '#6c757d';
			}
		}

		function formatTime(timestamp) {
			const date = new Date(timestamp);
			const now = new Date();
			const diffInMinutes = Math.floor((now - date) / (1000 * 60));
			
			if (diffInMinutes < 1) return 'Just now';
			if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
			if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
			return `${Math.floor(diffInMinutes / 1440)}d ago`;
		}

		let notificationRead = false;

		// Update notification badge on page load
		document.addEventListener('DOMContentLoaded', function() {
			updateNotificationBadge();
			// Auto-refresh notifications every 30 seconds
			setInterval(updateNotificationBadge, 30000);
		});

		async function updateNotificationBadge() {
			if (notificationRead) return; // Don't update if user has already seen notifications
			
			try {
				const data = await fetchNotificationData();
				// Count pending approvals + upcoming vaccines + recent activities + today's + tomorrow's schedules
				const notificationCount = data.pendingApprovals + 
										 data.upcomingVaccines + 
										 data.activities.length +
										 data.todaySchedules.length +
										 data.tomorrowSchedules.length;
				const badge = document.getElementById('notificationCount');
				if (badge) {
					badge.textContent = notificationCount;
					badge.style.display = notificationCount > 0 ? 'inline-flex' : 'none';
				}
			} catch (error) {
				console.error('Error updating notification badge:', error);
				}
			}
		</script>

