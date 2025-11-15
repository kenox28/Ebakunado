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

<header class="header">
    <div class="header-left">
        <button
            id="menuToggle"
            class="icon-menu material-symbols-rounded"
            type="button"
            aria-label="Open menu"
            aria-expanded="false"
            aria-controls="sideNav">menu</button>
    </div>

    <div class="header-right">
        <div class="notification-container">
            <button class="notification-button" onclick="toggleNotificationDropdown()">
                <span class="material-symbols-rounded">notifications</span>
                <span class="notification-badge" id="notificationCount">0</span>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h4>
                        <span class="material-symbols-rounded">notifications</span>
                        Notifications
                    </h4>
                    <button onclick="markAllAsRead()">Mark all as read</button>
                </div>
                <div class="notification-content" id="notificationContent">
                    <div class="notif-loading">
                        <div class="notif-spinner"></div>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="header-user"
            id="headerUser"
            role="button"
            tabindex="0"
            aria-haspopup="menu"
            aria-expanded="false"
            aria-controls="profileMenu">
            <img
                class="user-avatar"
                src="<?php echo !empty($noprofile) ? htmlspecialchars($noprofile) : '../../assets/images/user-profile.png'; ?>"
                alt="User Profile" />
            <h2 class="user-display-name"><?php echo htmlspecialchars($fname); ?></h2>
            <span class="icon-dropdown material-symbols-rounded">keyboard_arrow_down</span>

            <!-- Popover Menu -->
            <div id="profileMenu" class="profile-menu" role="menu" aria-hidden="true">
                <a class="menu-item" href="../../views/user-page/profile-management.php" role="menuitem">
                    <span class="material-symbols-rounded">person</span>
                    My Account
                </a>
                <a class="menu-item" href="#" role="menuitem" onclick="logoutUser()">
                    <span class="material-symbols-rounded">logout</span>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
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
        content.innerHTML = '<div class="notif-loading"><div class="notif-spinner"></div><p>Loading notifications...</p></div>';

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
        const idx = notifications.findIndex(n => n.id === notificationId);
        if (idx !== -1 && notifications[idx].unread) {
            notifications[idx].unread = false;
            if (unreadCount > 0) {
                unreadCount--;
                updateNotificationBadge(unreadCount);
            }
            renderNotifications(notifications);
        }
    }

    function renderNotifications(notifications) {
        const content = document.getElementById('notificationContent');

        if (!notifications || notifications.length === 0) {
            content.innerHTML = '<div class="no-notifications"><span class="material-symbols-rounded">notifications_off</span><p>No notifications</p></div>';
            return;
        }

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

    }

    function handleNotificationClick(notificationId) {
        const notification = notifications.find(n => n.id === notificationId);
        if (notification) {
            markNotificationAsRead(notificationId);
            try {
                const fd = new FormData();
                fd.append('id', notificationId);
                fetch('../../php/supabase/users/mark_notification_read.php', {
                    method: 'POST',
                    body: fd
                });
            } catch (e) {}
            if (notification.action_url) {
                setTimeout(() => {
                    window.location.href = notification.action_url;
                }, 150);
            }
        }
    }

    function markAllAsRead() {
        if (Array.isArray(notifications)) {
            notifications = notifications.map(n => ({
                ...n,
                unread: false
            }));
            renderNotifications(notifications);
        }
        unreadCount = 0;
        updateNotificationBadge(0);
        try {
            fetch('../../php/supabase/users/mark_notifications_read_all.php', {
                method: 'POST'
            });
        } catch (e) {}
    }

    function getTimeAgo(timestamp) {
        const now = new Date();
        let time;

        if (timestamp.includes('T')) {
            const parts = timestamp.split('T');
            const datePart = parts[0];
            const timePart = parts[1].split('.')[0];

            time = new Date(datePart + ' ' + timePart);
        } else {
            time = new Date(timestamp.replace(' ', 'T'));
        }

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

    document.addEventListener('DOMContentLoaded', async function() {
        try {
            const res = await fetch('../../php/supabase/users/get_user_notifications.php');
            if (!res.ok) return;
            const data = await res.json();
            if (data && data.status === 'success' && data.data) {
                unreadCount = data.data.unread_count || 0;
                updateNotificationBadge(unreadCount);
            }
        } catch (e) {}

        document.addEventListener('click', function(e) {
            const container = document.querySelector('.notification-container');
            const dropdown = document.getElementById('notificationDropdown');
            if (!container || !dropdown) return;
            if (!container.contains(e.target)) {
                dropdown.style.display = 'none';
                notificationDropdownOpen = false;
            }
        });
    });
    async function logoutUser() {

    // window.location.href = "/ebakunado/php/supabase/users/logout.php"
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You will be logged out of the system",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#e74c3c",
        cancelButtonColor: "#95a5a6",
        confirmButtonText: "Yes, logout",
    });

    if (result.isConfirmed) {
        const response = await fetch("../../php/supabase/users/logout.php", {
            method: "POST",
        });

        const data = await response.json();

        if (data.status === "success") {
            Swal.fire({
                icon: "success",
                title: "Logged Out",
                text: "You have been successfully logged out",
                showConfirmButton: false,
                timer: 1500,
            }).then(() => {
                window.location.href = "../../views/landing-page/landing-page.html";
            });
        } else {
            Swal.fire("Error!", data.message, "error");
        }
    }
    }
</script>