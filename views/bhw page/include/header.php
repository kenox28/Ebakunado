<header class="header">
    <div class="header-left">
        <span class="icon-menu material-symbols-rounded">menu</span>
        <h1 class="header-greeting">Welcome, John Doe!</h1>
    </div>

    <div class="header-user" id="headerUser">
        <img
            class="user-avatar"
            src="/assets/images/user-profile.png"
            alt="User Profile" />
        <h2 class="user-display-name">John Doe</h2>
        <span class="icon-dropdown material-symbols-rounded">keyboard_arrow_down</span>

        <!-- Popover Menu -->
        <div id="profileMenu" class="profile-menu" role="menu" aria-hidden="true">
            <button class="menu-item" type="button">
                <span class="material-symbols-rounded">person</span>
                Account
            </button>
            <button class="menu-item" type="button">
                <span class="material-symbols-rounded">logout</span>
                Logout
            </button>
        </div>
    </div>
</header>