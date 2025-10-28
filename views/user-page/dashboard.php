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
    <title>BHW Dashboard</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/bhw/dashboard.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="dashboard section-title">
                <span class="material-symbols-rounded">dashboard</span>
                Dashboard Overview
            </h2>
        </section>
        <section class="dashboard-section">
            <div class="dashboard-overview">
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">child_care</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="totalChildren">0</p>
                            <p class="card-title">Total Children</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-2">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">hourglass_top</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="approvedChr">0</p>
                            <p class="card-title">Approved CHR Requests</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-3">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">warning</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="missedCount">0</p>
                            <p class="card-title">Missed/Delayed Immunizations</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-4">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">vaccines</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="todaySchedule">0</p>
                            <p class="card-title">Upcoming Schedule for Today</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
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
                /* silent */
            }
        });
    </script>

</body>

</html>