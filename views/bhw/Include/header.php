<?php session_start(); ?>
<?php 
// Handle both BHW and Midwife sessions (but BHW should only see BHW features)
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw'; // Default to bhw for BHW pages
$user_name = $_SESSION['fname'] ?? 'User';

// Debug session
if ($user_id) {
    echo "<!-- Session Active: " . $user_type . " - " . $user_id . " -->";
} else {
    echo "<!-- Session: NOT FOUND - Available sessions: " . implode(', ', array_keys($_SESSION)) . " -->";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>BHW</title>
	<link rel="stylesheet" href="../../css/main.css" />
	<link rel="stylesheet" href="../../css/header.css" />
	<link rel="stylesheet" href="../../css/sidebar.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main>
        <header class="header">
            <div class="header-left">
                <button
                    id="menuToggle"
                    class="icon-menu material-symbols-rounded"
                    type="button"
                    aria-label="Open menu"
                    aria-expanded="false"
                    aria-controls="sideNav"
                >menu</button>
                <h1 class="header-greeting">Welcome, <?php echo htmlspecialchars($user_name);?>!</h1>
            </div>

            <div class="header-user" id="headerUser" role="button" tabindex="0" aria-haspopup="menu" aria-expanded="false" aria-controls="profileMenu">
                <img class="user-avatar" src="../../assets/images/user-profile.png" alt="User Profile" />
                <h2 class="user-display-name"><?php echo htmlspecialchars($user_name);?></h2>
                <span class="icon-dropdown material-symbols-rounded">keyboard_arrow_down</span>

                <div id="profileMenu" class="profile-menu" role="menu" aria-hidden="true">
                    <a class="menu-item" href="./profile_management.php" role="menuitem">
                        <span class="material-symbols-rounded">person</span>
                        Account
                    </a>
                    <a class="menu-item" href="#" role="menuitem" onclick="logoutBhw()">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </a>
                </div>
            </div>
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
					const response = await fetch('../../php/supabase/shared/get_bhw_notifications_simple.php');
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
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({ notification_id: notificationId })
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
							headers: { 'Content-Type': 'application/json' }
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