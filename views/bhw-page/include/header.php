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
</header>
<script>
    async function logoutBhw() {
            // const response = await fetch('../../php/bhw/logout.php', { method: 'POST' });
            const response = await fetch('../../php/supabase/bhw/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = '../../views/auth/login.php';
            } else {
                alert('Logout failed: ' + data.message);
            }
        }
</script>