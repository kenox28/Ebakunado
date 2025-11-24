<?php
if (!isset($_SESSION['super_admin_id'])) {
    header("Location: ../login.php");
    exit();
}
// Ensure name variables are available (parallels sidebar.php)
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = trim((($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '')));
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
        <?php
        $sessionProfileImg = isset($_SESSION['profileimg']) ? trim((string)$_SESSION['profileimg']) : '';
        $headerProfileImg = ($sessionProfileImg && $sessionProfileImg !== 'noprofile.png')
            ? $sessionProfileImg
            : '../../assets/images/user-profile.png';
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
                    <span class="user-name"><?php echo htmlspecialchars($user_fullname); ?></span>
                    <span class="user-role">Super Admin</span>
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
                        <p class="role">Super Admin</p>
                        <p class="email"><?php echo $emailDisplay; ?></p>

                    </div>
                </div>
                <div class="menu-group" aria-label="Account">
                    <a class="menu-item" href="./profile-management.php" role="menuitem"><span class="material-symbols-rounded">person</span>My Account</a>
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">badge</span>View Profile</a>
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">settings</span>Settings</a>
                </div>
                <div class="menu-group" aria-label="Context">
                    <a class="menu-item" href="#" role="menuitem"><span class="material-symbols-rounded">lock</span>Privacy & Security</a>
                </div>
                <div class="menu-group" aria-label="Danger zone">
                    <button class="menu-item danger" type="button" role="menuitem" onclick="logoutBhw()"><span class="material-symbols-rounded">logout</span>Logout</button>
                </div>
            </nav>
        </div>
    </div>
</header>