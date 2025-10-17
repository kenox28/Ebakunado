<?php session_start(); ?>
<?php
// Debug BHW session
if (isset($_SESSION['bhw_id'])) {
	echo "<!-- BHW Session Active: " . $_SESSION['bhw_id'] . " -->";
} else {
	echo "<!-- BHW Session: NOT FOUND - Available sessions: " . implode(', ', array_keys($_SESSION)) . " -->";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Document</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
	aside.collapsed+main {
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

	/* Notification Styles */
	.notification-button {
		position: relative;
		cursor: pointer;
		padding: 8px;
		margin: 0 10px;
		border-radius: 50%;
		transition: background-color 0.3s ease;
	}

	.notification-button:hover {
		background-color: #e0e0e0;
	}

	.notification-button i {
		font-size: 18px;
		color: #333;
	}

	.notification-badge {
		position: absolute;
		top: 5px;
		right: 5px;
		background-color: #dc3545;
		color: white;
		border-radius: 50%;
		width: 18px;
		height: 18px;
		font-size: 11px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: bold;
	}

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
		overflow: hidden;
	}

	.notification-header {
		padding: 15px;
		border-bottom: 1px solid #eee;
		display: flex;
		justify-content: space-between;
		align-items: center;
		background-color: #f8f9fa;
	}

	.notification-header h3 {
		margin: 0;
		font-size: 16px;
		color: #333;
	}

	.notification-header button {
		background: none;
		border: none;
		color: #007bff;
		cursor: pointer;
		font-size: 12px;
		text-decoration: underline;
	}

	.notification-header button:hover {
		color: #0056b3;
	}

	.notification-content {
		flex: 1;
		overflow-y: auto;
		padding: 0;
		/* Custom scrollbar styling */
		scrollbar-width: thin;
		scrollbar-color: #ccc #f1f1f1;
	}

	/* Custom scrollbar for webkit browsers */
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
		padding: 12px 15px;
		border-bottom: 1px solid #f0f0f0;
		cursor: pointer;
		transition: background-color 0.2s ease;
	}

	.notification-item:hover {
		background-color: #f8f9fa;
	}

	.notification-item.unread {
		background-color: #fff3cd;
		border-left: 4px solid #ffc107;
	}

	.notification-item.urgent {
		background-color: #f8d7da;
		border-left: 4px solid #dc3545;
	}

	.notification-item h4 {
		margin: 0 0 5px 0;
		font-size: 14px;
		color: #333;
		font-weight: 600;
	}

	.notification-item p {
		margin: 0 0 5px 0;
		font-size: 12px;
		color: #666;
		line-height: 1.4;
	}

	.notification-item .notification-time {
		font-size: 11px;
		color: #999;
	}

	.notification-footer {
		padding: 10px 15px;
		border-top: 1px solid #eee;
		text-align: center;
		background-color: #f8f9fa;
	}

	.notification-footer a {
		color: #007bff;
		text-decoration: none;
		font-size: 12px;
	}

	.notification-footer a:hover {
		text-decoration: underline;
	}

	.loading-notifications {
		padding: 20px;
		text-align: center;
		color: #666;
	}

	.loading-notifications i {
		font-size: 16px;
		margin-bottom: 8px;
	}

	.no-notifications {
		padding: 20px;
		text-align: center;
		color: #666;
	}

	.no-notifications i {
		font-size: 24px;
		margin-bottom: 8px;
		color: #ccc;
	}
</style>

<body>
	<aside>
		<h3>Ebakunado</h3>
		<a href="./home.php" data-icon="🏠"><span>Dashboard</span></a>
		<a href="./immunization.php" data-icon="💉"><span>Imuunization form</span></a>
		<a href="./pending_approval.php" data-icon="⏳"><span>Pending Approval</span></a>
		<a href="./child_health_record.php" data-icon="🧒"><span>Child Health Record</span></a>
		<a href="./chr-doc-requests.php" data-icon="📄"><span>CHR Doc Requests</span></a>
		<a href="./target_client.php" data-icon="🎯"><span>Target Client List</span></a>
		<a href="./system_settings.php" data-icon="⚙️"><span>System Settings</span></a>
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
				<div class="notification-button" onclick="toggleNotificationDropdown()">
					<i class="fas fa-bell"></i>
					<span class="notification-badge" id="notificationCount" style="display: none;">0</span>
					<div class="notification-dropdown" id="notificationDropdown" style="display: none;">
						<div class="notification-header">
							<h3>Notifications</h3>
							<button onclick="markAllAsRead()">Mark all as read</button>
						</div>
						<div class="notification-content" id="notificationContent">
							<div class="loading-notifications">
								<i class="fas fa-spinner fa-spin"></i>
								<p>Loading notifications...</p>
							</div>
						</div>
						<div class="notification-footer">
							<a href="#" onclick="viewAllNotifications()">View all notifications</a>
						</div>
					</div>
				</div>
				<a href="#" onclick="logoutBhw()">Logout</a>
			</nav>
		</header>


		<script>
			// Simple notification system - easy to redesign
			let notificationDropdownOpen = false;

			function toggleNotificationDropdown() {
				const dropdown = document.getElementById('notificationDropdown');
				notificationDropdownOpen = !notificationDropdownOpen;

				if (notificationDropdownOpen) {
					dropdown.style.display = 'flex';
					loadNotifications();
				} else {
					dropdown.style.display = 'none';
				}
			}

			async function loadNotifications() {
				const content = document.getElementById('notificationContent');

				try {
					const response = await fetch('../../php/supabase/bhw/get_bhw_notifications_simple.php');
					const data = await response.json();

					if (data.status === 'success' && data.data.length > 0) {
						let html = '';
						data.data.forEach(notification => {
							const isUnread = notification.is_read === false;
							html += `
									<div class="notification-item ${isUnread ? 'unread' : ''}" onclick="markAsRead(${notification.id})">
										<h4>${notification.title}</h4>
										<p>${notification.message}</p>
										<div class="notification-time">${formatTime(notification.created_at)}</div>
									</div>
								`;
						});
						content.innerHTML = html;

						// Update badge count
						const unreadCount = data.data.filter(n => !n.is_read).length;
						const badge = document.getElementById('notificationCount');
						if (unreadCount > 0) {
							badge.textContent = unreadCount;
							badge.style.display = 'flex';
						} else {
							badge.style.display = 'none';
						}
					} else {
						content.innerHTML = `
								<div class="no-notifications">
									<i class="fas fa-bell-slash"></i>
									<p>No notifications</p>
								</div>
							`;
						document.getElementById('notificationCount').style.display = 'none';
					}
				} catch (error) {
					console.error('Error loading notifications:', error);
					content.innerHTML = `
							<div class="no-notifications">
								<i class="fas fa-exclamation-triangle"></i>
								<p>Error loading notifications</p>
							</div>
						`;
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

			async function markAsRead(notificationId) {
				try {
					await fetch('../../php/supabase/bhw/mark_notification_read.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({
							notification_id: notificationId
						})
					});

					// Reload notifications
					loadNotifications();
				} catch (error) {
					console.error('Error marking notification as read:', error);
				}
			}

			async function markAllAsRead() {
				try {
					await fetch('../../php/supabase/bhw/mark_notifications_read_all.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						}
					});

					// Reload notifications
					loadNotifications();
				} catch (error) {
					console.error('Error marking all notifications as read:', error);
				}
			}

			function viewAllNotifications() {
				// Could redirect to a dedicated notifications page
				console.log('View all notifications clicked');
			}

			// Close dropdown when clicking outside
			document.addEventListener('click', function(event) {
				const notificationButton = document.querySelector('.notification-button');
				const dropdown = document.getElementById('notificationDropdown');

				if (notificationDropdownOpen && !notificationButton.contains(event.target)) {
					toggleNotificationDropdown();
				}
			});

			// Simple notification system - easy to redesign
		</script>