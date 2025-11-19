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

<link rel="stylesheet" href="../../css/modals.css" />

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
        <?php
        // Replicated dropdown-root structure from BHW header
        $sessionProfileImg = isset($_SESSION['profileimg']) ? trim((string)$_SESSION['profileimg']) : '';
        $headerProfileImg = ($sessionProfileImg && $sessionProfileImg !== 'noprofile.png')
            ? $sessionProfileImg
            : '../../assets/images/user-profile.png';
        $emailDisplay = isset($_SESSION['email']) ? htmlspecialchars((string)$_SESSION['email']) : '—';
        $user_fullname = trim(($fname ?? '') . ' ' . ($lname ?? ''));
        $user_type = $_SESSION['user_type'] ?? 'User';
        $has_user_role = false;
        if (isset($_SESSION['available_roles']) && in_array('user', $_SESSION['available_roles'])) {
            $has_user_role = true;
        }
        $current_role_lower = strtolower((string)$user_type);
        $switch_target_label = ($current_role_lower === 'parent' || $current_role_lower === 'parent/user') ? 'BHW' : 'Parent';
        // Derive display role (append / Parent when base role is just User)
        if (!isset($user_role_display)) {
            $user_role_display = $user_type;
            $lower = strtolower($user_type);
            if ($lower === 'user' && stripos($user_type, 'parent') === false) {
                $user_role_display = 'User / Parent';
            } elseif ($lower === 'parent' && stripos($user_type, 'user') === false) {
                $user_role_display = 'Parent / User';
            } elseif (strpos($lower, 'parent') !== false && strpos($lower, 'user') !== false) {
                // Already combined, keep as-is
                $user_role_display = $user_type;
            }
        }
        ?>
        <div class="dropdown-root" id="profileRoot">
            <button id="profileBtn" class="profile-trigger" type="button" aria-haspopup="menu" aria-expanded="false" aria-controls="profileMenu">
                <img class="user-avatar" alt="User avatar" src="<?php echo htmlspecialchars($headerProfileImg); ?>" />
                <span class="user-meta">
                    <span class="user-name"><?php echo htmlspecialchars($fname); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($user_role_display); ?></span>
                </span>
                <span class="icon-dropdown material-symbols-rounded" aria-hidden="true">expand_more</span>
            </button>
            <nav id="profileMenu" class="panel profile" role="menu" aria-label="User menu" aria-hidden="true" hidden>
                <div class="profile-card">
                    <div class="avatar-wrap">
                        <img class="user-avatar lg" alt="User avatar" src="<?php echo htmlspecialchars($headerProfileImg); ?>" />
                    </div>
                    <div class="info">
                        <h3 class="name"><?php echo htmlspecialchars($user_fullname); ?></h3>
                        <p class="role"><?php echo htmlspecialchars($user_role_display); ?></p>
                        <p class="email"><?php echo $emailDisplay; ?></p>
                    </div>
                </div>
                <div class="menu-group" aria-label="Account">
                    <a class="menu-item" href="../../views/user-page/profile-management.php" role="menuitem"><span class="material-symbols-rounded">person</span>My Account</a>
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">badge</span>View Profile</a>
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">settings</span>Settings</a>
                </div>
                <div class="menu-group" aria-label="Context">
                    <?php if ($has_user_role): ?>
                        <a class="menu-item" href="#" role="menuitem" onclick="switchToParentView(); return false;" aria-label="Switch role to <?php echo htmlspecialchars($switch_target_label); ?>">
                            <span class="material-symbols-rounded" aria-hidden="true">account_circle</span>
                            Switch Role
                            <span class="tag" data-target-role="<?php echo htmlspecialchars($switch_target_label); ?>">
                                <span class="material-symbols-rounded" aria-hidden="true">sync_alt</span>
                                <?php echo htmlspecialchars($switch_target_label); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">lock</span>Privacy & Security</a>
                </div>
                <div class="menu-group" aria-label="Danger zone">
                    <button class="menu-item danger" type="button" role="menuitem" onclick="logoutUser()"><span class="material-symbols-rounded">logout</span>Logout</button>
                </div>
            </nav>
        </div>
    </div>
</header>

<script src="../../js/utils/ui-feedback.js"></script>
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
        if (!window.UIFeedback) {
            window.location.href = "../../php/supabase/users/logout.php";
            return;
        }

        const confirmResult = await UIFeedback.showModal({
            title: "Logout",
            message: "You will be logged out of the system.",
            icon: "warning",
            confirmText: "Yes, logout",
            cancelText: "Cancel",
            showCancel: true
        });

        if (confirmResult?.action !== "confirm") return;

        const closeLoader = UIFeedback.showLoader("Logging out...");
        try {
            const response = await fetch("../../php/supabase/users/logout.php", {
                method: "POST"
            });
            const data = await response.json();
            closeLoader();

            if (data.status === "success") {
                UIFeedback.showToast({
                    title: "Logged out",
                    message: "You have been successfully logged out.",
                    variant: "success",
                    duration: 3000
                });
                setTimeout(() => {
                    window.location.href = "../../views/landing-page/landing-page.html";
                }, 800);
            } else {
                UIFeedback.showToast({
                    title: "Logout failed",
                    message: data.message || "Please try again.",
                    variant: "error"
                });
            }
        } catch (error) {
            closeLoader();
            UIFeedback.showToast({
                title: "Network error",
                message: "Please check your connection and try again.",
                variant: "error"
            });
        }
    }
</script>