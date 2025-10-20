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

    <div
        class="header-user"
        id="headerUser"
        role="button"
        tabindex="0"
        aria-haspopup="menu"
        aria-expanded="false"
        aria-controls="profileMenu"
    >
        <img
            class="user-avatar"
            src="../../assets/images/user-profile.png"
            alt="User Profile" />
        <h2 class="user-display-name"><?php echo htmlspecialchars($user_name);?></h2>
        <span class="icon-dropdown material-symbols-rounded">keyboard_arrow_down</span>

        <!-- Popover Menu -->
        <div id="profileMenu" class="profile-menu" role="menu" aria-hidden="true">
            <a class="menu-item" href="#" role="menuitem">
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