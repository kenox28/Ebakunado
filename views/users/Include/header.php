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
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="../../css/modals.css" />
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
		/* scroll handled in main */
	}

	header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		border-bottom: 1px solid #ddd;
		width: 100%;
		background-color: #fff;
		padding: 10px 20px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
	aside.collapsed+main {
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
		/* contain scroll */
		transition: width 0.3s ease;
		overflow-x: hidden;
		overflow-y: auto;
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
		border: 2px solid #000;
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
		box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
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

	/* Notification System Styles */
	.notification-container {
		position: relative;
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

	/* Notification Dropdown */
	.notification-dropdown {
		position: absolute;
		top: 100%;
		right: 0;
		width: 400px;
		background: white;
		border: 1px solid #ddd;
		border-radius: 8px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		z-index: 1000;
		height: 500px;
		max-height: 500px;
		display: flex;
		flex-direction: column;
		overflow: visible;
	}

	.notification-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px 20px;
		border-bottom: 1px solid #eee;
		background: #f8f9fa;
	}

	.notification-header h4 {
		margin: 0;
		font-size: 16px;
		color: #333;
	}

	.notification-header button {
		background: #007bff;
		color: white;
		border: none;
		padding: 6px 12px;
		border-radius: 4px;
		font-size: 12px;
		cursor: pointer;
	}

	.notification-header button:hover {
		background: #0056b3;
	}

	.notification-content {
		flex: 1;
		overflow-y: scroll;
		border: 1px solid #000;
		padding: 0;
		scrollbar-gutter: stable;
		scrollbar-width: thin;
		scrollbar-color: #ccc #f1f1f1;
		height: 440px;
		/* ensure inner scroll area fits within 500px dropdown */
		overflow-x: hidden;
		overscroll-behavior: contain;
		-webkit-overflow-scrolling: touch;
	}

	/* Webkit scrollbar styles */
	.notification-content::-webkit-scrollbar {
		width: 8px;
	}

	.notification-content::-webkit-scrollbar-track {
		background: #f1f1f1;
		border-radius: 4px;
	}

	.notification-content::-webkit-scrollbar-thumb {
		background: #ccc;
		border-radius: 4px;
	}

	.notification-content::-webkit-scrollbar-thumb:hover {
		background: #999;
	}

	.notification-item {
		padding: 15px 20px;
		border-bottom: 1px solid #f0f0f0;
		cursor: pointer;
		transition: background-color 0.2s ease;
	}

	/* Unread/Read highlight states */
	.notification-item.unread {
		background-color: #eef6ff;
		border-left: 3px solid #1976d2;
	}

	.notification-item.read {
		background-color: #ffffff;
	}

	.notification-item:hover {
		background-color: #f8f9fa;
	}

	.notification-item h4 {
		margin: 0 0 5px 0;
		font-size: 14px;
		color: #333;
	}

	.notification-item p {
		margin: 0 0 8px 0;
		font-size: 13px;
		color: #666;
		line-height: 1.4;
	}

	.notification-time {
		font-size: 11px;
		color: #999;
	}

	.loading-notifications,
	.no-notifications {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 40px 20px;
		text-align: center;
		color: #666;
	}

	.loading-notifications i,
	.no-notifications i {
		font-size: 24px;
		margin-bottom: 10px;
	}

	.loading-notifications p,
	.no-notifications p {
		margin: 0;
		font-size: 14px;
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

	.form-section {
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
			<a href="children_list.php" data-icon="🧒"><span>Child Record</span></a>
			<a href="approved_requests.php" data-icon="✅"><span>Approved Requests</span></a>
			<a href="Request.php" data-icon="➕"><span>Add Child</span></a>
			<a href="profile_management.php" data-icon="👤"><span>Profile Management</span></a>
			<?php
			// Check if user has BHW or Midwife role available (from available_roles session)
			$has_bhw_role = false;
			$has_midwife_role = false;
			$switch_back_role = null;

			if (isset($_SESSION['available_roles'])) {
				if (in_array('bhw', $_SESSION['available_roles'])) {
					$has_bhw_role = true;
					$switch_back_role = 'bhw';
				} elseif (in_array('midwife', $_SESSION['available_roles'])) {
					$has_midwife_role = true;
					$switch_back_role = 'midwife';
				}
			}
			
			if ($has_bhw_role || $has_midwife_role): ?>
			<a href="#" onclick="switchToBHWView(); return false;" data-icon="⬅️"><span>Switch to <?php echo ucfirst($switch_back_role === 'bhw' ? 'BHW' : 'Midwife'); ?> View</span></a>
			<?php endif; ?>
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
				<!-- Notification Button with Dropdown -->
				<div class="notification-container">
					<button class="notification-button" onclick="toggleNotificationDropdown()">
						<i class="fa-solid fa-bell"></i>
						<span class="notification-badge" id="notificationCount">0</span>
					</button>
					<div class="notification-dropdown" id="notificationDropdown" style="display: none;">
						<div class="notification-header">
							<h4><i class="fas fa-bell"></i> Notifications</h4>
							<button onclick="markAllAsRead()">Mark all as read</button>
						</div>
						<div class="notification-content" id="notificationContent">
							<div class="loading-notifications">
								<i class="fas fa-spinner fa-spin"></i>
								<p>Loading notifications...</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>

		<script>
			// Simple User Notification System (same as BHW)
			let notifications = [];
			let notificationDropdownOpen = false;
			let unreadCount = 0;

			function toggleNotificationDropdown() {
				const dropdown = document.getElementById('notificationDropdown');
				notificationDropdownOpen = !notificationDropdownOpen;

				if (notificationDropdownOpen) {
					dropdown.style.display = 'block';
					loadNotifications(); // Always load fresh notifications
				} else {
					dropdown.style.display = 'none';
				}
			}

			async function loadNotifications() {
				const content = document.getElementById('notificationContent');
				content.innerHTML = '<div class="loading-notifications"><i class="fas fa-spinner fa-spin"></i><p>Loading notifications...</p></div>';

				try {
					console.log('Loading user notifications...');
					const startTime = Date.now();

					const response = await fetch('../../php/supabase/users/get_user_notifications.php');
					console.log('Response status:', response.status);

					if (!response.ok) {
						throw new Error(`HTTP ${response.status}: ${response.statusText}`);
					}

					const data = await response.json();
					const loadTime = Date.now() - startTime;
					console.log('Notification response received in', loadTime + 'ms:', data);

					if (data.status === 'success') {
						notifications = data.data.notifications;
						unreadCount = data.data.unread_count;
						updateNotificationBadge(unreadCount);
						renderNotifications(notifications);
						console.log('User notifications loaded successfully:', notifications.length, 'unread:', unreadCount);
					} else {
						console.error('Notification API error:', data);
						let errorMsg = data.message || 'Error loading notifications';
						content.innerHTML = `<div class="no-notifications"><i class="fas fa-exclamation-triangle"></i><p>${errorMsg}</p></div>`;
					}
				} catch (error) {
					console.error('Error loading notifications:', error);
					content.innerHTML = '<div class="no-notifications"><i class="fas fa-exclamation-triangle"></i><p>Network error: ' + error.message + '</p></div>';
				}
			}

			function updateNotificationBadge(count) {
				const badge = document.getElementById('notificationCount');
				if (count > 0) {
					badge.textContent = count > 99 ? '99+' : count;
					badge.style.display = 'flex';
				} else {
					badge.style.display = 'none';
				}
			}

			function markNotificationAsRead(notificationId) {
				// Update local state: set item to read, decrement badge once
				const idx = notifications.findIndex(n => n.id === notificationId);
				if (idx !== -1 && notifications[idx].unread) {
					notifications[idx].unread = false;
					if (unreadCount > 0) {
						unreadCount--;
						updateNotificationBadge(unreadCount);
					}
					// Re-render list to unhighlight the item
					renderNotifications(notifications);
				}
			}

			function renderNotifications(notifications) {
				const content = document.getElementById('notificationContent');

				if (!notifications || notifications.length === 0) {
					content.innerHTML = '<div class="no-notifications"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>';
					return;
				}

				// Simple HTML rendering
				let html = '';
				notifications.forEach(notification => {
					const timeAgo = getTimeAgo(notification.timestamp);
					const cls = notification.unread ? 'notification-item unread' : 'notification-item read';
					html += `
					<div class="${cls}" onclick="handleNotificationClick('${notification.id}')">
						<h4>${notification.icon} ${notification.title}</h4>
						<p>${notification.message}</p>
						<div class="notification-time">${timeAgo}</div>
					</div>
				`;
				});

				content.innerHTML = html;

				// Don't update badge here - it's already updated in loadNotifications
			}

			function handleNotificationClick(notificationId) {
				const notification = notifications.find(n => n.id === notificationId);
				if (notification) {
					// Mark as read locally and on server
					markNotificationAsRead(notificationId);
					try {
						const fd = new FormData();
						fd.append('id', notificationId);
						fetch('../../php/supabase/users/mark_notification_read.php', {
							method: 'POST',
							body: fd
						});
					} catch (e) {}
					// Navigate to the action URL after UI updates
					if (notification.action_url) {
						setTimeout(() => {
							window.location.href = notification.action_url;
						}, 150);
					}
				}
			}

			function markAllAsRead() {
				// Optimistically mark all as read locally
				if (Array.isArray(notifications)) {
					notifications = notifications.map(n => ({
						...n,
						unread: false
					}));
					renderNotifications(notifications);
				}
				// Zero badge/count immediately
				unreadCount = 0;
				updateNotificationBadge(0);
				// Persist to server
				try {
					fetch('../../php/supabase/users/mark_notifications_read_all.php', {
						method: 'POST'
					});
				} catch (e) {}
			}

			function getTimeAgo(timestamp) {
				const now = new Date();
				let time;

				// Handle different timestamp formats
				if (timestamp.includes('T')) {
					// ISO format with T - manually parse to avoid timezone issues
					const parts = timestamp.split('T');
					const datePart = parts[0]; // 2025-10-05
					const timePart = parts[1].split('.')[0]; // 16:51:03 (remove microseconds)

					// Create date in local timezone
					time = new Date(datePart + ' ' + timePart);
				} else {
					// Regular date format (Y-m-d H:i:s)
					time = new Date(timestamp.replace(' ', 'T'));
				}

				// Check if timestamp is valid
				if (isNaN(time.getTime())) {
					console.error('Invalid timestamp:', timestamp);
					return 'Unknown time';
				}

				const diffInSeconds = Math.floor((now - time) / 1000);

				if (diffInSeconds < 0) return 'In the future';
				if (diffInSeconds < 60) return 'Just now';
				if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
				if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
				if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
				return 'Over a month ago';
			}

			// Preload unread badge on every page load (no dropdown auto-open)
			document.addEventListener('DOMContentLoaded', async function() {
				try {
					const res = await fetch('../../php/supabase/users/get_user_notifications.php');
					if (!res.ok) return;
					const data = await res.json();
					if (data && data.status === 'success' && data.data) {
						unreadCount = data.data.unread_count || 0;
						updateNotificationBadge(unreadCount);
					}
				} catch (e) {
					/* silent */ }
			});
		</script>