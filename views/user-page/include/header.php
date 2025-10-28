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
                <a class="menu-item" href="../../views/bhw-page/profile-management.php" role="menuitem">
                    <span class="material-symbols-rounded">person</span>
                    My Account
                </a>
                <a class="menu-item" href="#" role="menuitem" onclick="logoutBhw()">
                    <span class="material-symbols-rounded">logout</span>
                    Logout
                </a>
            </div>
        </div>

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
                    <div class="loading-notifications">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>