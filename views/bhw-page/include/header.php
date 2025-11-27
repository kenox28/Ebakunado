<?php
// Determine which page is currently loaded
// Use REQUEST_URI to detect the route since PHP_SELF is always index.php for routed pages
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
// Remove query string
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}
// Remove base path if exists
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($basePath !== '/' && $basePath !== '\\' && $basePath !== '.' && !empty($basePath)) {
    $basePath = '/' . trim($basePath, '/');
    if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
}
$requestUri = trim($requestUri, '/');

// Map routes to page names for active state
$currentPage = 'dashboard.php'; // default

// Check for specific routes (order matters - more specific first)
if (preg_match('#^health-child/[^/]+$#', $requestUri)) {
    // Child health record detail page: /health-child/{id}
    $currentPage = 'child-health-list.php'; // Highlight the parent list page
} elseif (strpos($requestUri, 'health-dashboard') === 0 || $requestUri === 'health-dashboard') {
    $currentPage = 'dashboard.php';
} elseif (strpos($requestUri, 'health-vaccination-planner') === 0) {
    $currentPage = 'vaccination-planner.php';
} elseif (strpos($requestUri, 'health-immunizations') === 0) {
    $currentPage = 'immunization.php';
} elseif (strpos($requestUri, 'health-pending') === 0) {
    $currentPage = 'pending-approval.php';
} elseif (strpos($requestUri, 'health-children') === 0 || $requestUri === 'health-children') {
    $currentPage = 'child-health-list.php';
} elseif (strpos($requestUri, 'health-target-client') === 0) {
    $currentPage = 'target-client-list.php';
} elseif (strpos($requestUri, 'health-add-child') === 0) {
    $currentPage = 'add-child.php';
} elseif (strpos($requestUri, 'health-chr-doc-requests') === 0) {
    $currentPage = 'chr-doc-requests.php';
} elseif (strpos($requestUri, 'health-babycard-requests') === 0) {
    $currentPage = 'babycard-doc-requests.php';
} else {
    // Fallback to PHP_SELF for non-routed pages
    $currentPage = basename($_SERVER['PHP_SELF']);
}

$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
if ($user_types != 'midwife') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}

// Check if user also exists in users table (has Parent role)
// Use available_roles from session (set during login)
$has_user_role = false;
if (isset($_SESSION['available_roles']) && in_array('user', $_SESSION['available_roles'])) {
    $has_user_role = true;
}
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
        <!-- Notification Button with Dropdown -->
        <div class="notification-container">
            <button class="notification-button" onclick="toggleNotificationDropdown()" aria-label="Notifications">
                <span class="material-symbols-rounded">notifications</span>
                <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
            </button>
            <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
                <div class="notification-header">
                    <h4>
                        <span class="material-symbols-rounded">notifications</span>
                        Notifications
                    </h4>
                    <button onclick="markAllAsRead()">Mark all as read</button>
                </div>
                <div class="notification-content" id="notificationContent">
                    <!-- Match user-page structure for loading state -->
                    <div class="notif-loading">
                        <div class="notif-spinner"></div>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $sessionProfileImg = isset($_SESSION['profileimg']) ? trim((string)$_SESSION['profileimg']) : '';
        $headerProfileImg = ($sessionProfileImg && $sessionProfileImg !== 'noprofile.png')
            ? $sessionProfileImg
            : 'assets/images/user-profile.png';
        $emailDisplay = isset($_SESSION['email']) ? htmlspecialchars((string)$_SESSION['email']) : '—';
        
        $has_user_role = false;
        if (isset($_SESSION['available_roles']) && in_array('user', $_SESSION['available_roles'])) {
            $has_user_role = true;
        }
        $current_role_lower = isset($user_type) ? strtolower((string)$user_type) : '';
        $switch_target_label = ($current_role_lower === 'parent' || $current_role_lower === 'parent/user') ? 'BHW' : 'Parent';
        ?>

        <!-- Profile -->
        <div class="dropdown-root" id="profileRoot">
            <button id="profileBtn" class="profile-trigger" type="button" aria-haspopup="menu" aria-expanded="false" aria-controls="profileMenu">
                <img class="user-avatar" alt="User avatar" src="<?php echo htmlspecialchars($headerProfileImg); ?>" />
                <span class="user-meta">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($user_type); ?></span>
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
                        <p class="role"><?php echo htmlspecialchars($user_type); ?></p>
                        <p class="email"><?php echo $emailDisplay; ?></p>

                    </div>
                </div>
                <div class="menu-group" aria-label="Account">
                    <a class="menu-item" href="health-profile" role="menuitem"><span class="material-symbols-rounded">person</span>My Account</a>
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
                    <button class="menu-item danger" type="button" role="menuitem" onclick="logoutBhw()"><span class="material-symbols-rounded">logout</span>Logout</button>
                </div>
            </nav>
        </div>
    </div>

    <script>
        // Notification System for BHW/Midwives (same as Users)
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

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const container = document.querySelector('.notification-container');
            const dropdown = document.getElementById('notificationDropdown');
            if (container && dropdown && !container.contains(event.target)) {
                dropdown.style.display = 'none';
                notificationDropdownOpen = false;
            }
        });

        async function loadNotifications() {
            const content = document.getElementById('notificationContent');
            // Use the same loading structure as user-page header
            content.innerHTML = '<div class="notif-loading"><div class="notif-spinner"></div><p>Loading notifications...</p></div>';

            try {
                // Determine endpoint based on user type
                const isMidwife = <?php echo (isset($_SESSION['midwife_id']) ? 'true' : 'false'); ?>;
                const endpoint = isMidwife ?
                    'php/supabase/midwives/get_notifications.php' :
                    'php/supabase/bhw/get_bhw_notifications.php';

                const response = await fetch(endpoint);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                if (data.status === 'success') {
                    notifications = data.data.notifications || [];
                    unreadCount = data.data.unread_count || 0;
                    updateNotificationBadge(unreadCount);
                    renderNotifications(notifications);
                } else {
                    content.innerHTML = `<div class="no-notifications"><span class="material-symbols-rounded">error</span><p>${data.message || 'Error loading notifications'}</p></div>`;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                content.innerHTML = '<div class="no-notifications"><span class="material-symbols-rounded">error</span><p>Network error: ' + error.message + '</p></div>';
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
            <h4>${notification.icon || '🔔'} ${notification.title}</h4>
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
                // Mark as read on server
                const isMidwife = <?php echo (isset($_SESSION['midwife_id']) ? 'true' : 'false'); ?>;
                const endpoint = isMidwife ?
                    'php/supabase/midwives/mark_notification_read.php' :
                    'php/supabase/bhw/mark_notification_read.php';

                const fd = new FormData();
                fd.append('id', notificationId);
                fetch(endpoint, {
                    method: 'POST',
                    body: fd
                }).catch(e => console.error('Error marking notification as read:', e));

                // Navigate to action URL
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

            // Persist to server
            const isMidwife = <?php echo (isset($_SESSION['midwife_id']) ? 'true' : 'false'); ?>;
            const endpoint = isMidwife ?
                'php/supabase/midwives/mark_notifications_read_all.php' :
                'php/supabase/bhw/mark_notifications_read_all.php';

            fetch(endpoint, {
                method: 'POST'
            }).catch(e => console.error('Error marking all as read:', e));
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

        // Preload unread badge on page load
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const isMidwife = <?php echo (isset($_SESSION['midwife_id']) ? 'true' : 'false'); ?>;
                const endpoint = isMidwife ?
                    'php/supabase/midwives/get_notifications.php' :
                    'php/supabase/bhw/get_bhw_notifications.php';

                const res = await fetch(endpoint);
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.status === 'success' && data.data) {
                    unreadCount = data.data.unread_count || 0;
                    updateNotificationBadge(unreadCount);
                }
            } catch (e) {
                // Silent fail
            }
        });

        <?php if ($has_user_role): ?>
            // Function to switch to Parent/User view
            async function switchToParentView() {
                try {
                    const response = await fetch('php/supabase/shared/switch_role.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        }
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        // Redirect to parent dashboard (new user-page)
                        window.location.href = data.redirect_url || 'health-dashboard';
                    } else {
                        // Show detailed error message
                        let errorMsg = data.message || 'Failed to switch to Parent view';
                        if (data.debug) {
                            console.error('Switch to Parent view debug:', data.debug);
                            errorMsg += '\n\nDebug info logged to console';
                        }
                        alert('Error: ' + errorMsg);
                    }
                } catch (error) {
                    console.error('Error switching to Parent view:', error);
                    alert('Error: Failed to switch to Parent view. Please try again.');
                }
            }
        <?php endif; ?>
    </script>
    <script src="js/utils/ui-feedback.js"></script>
</header>