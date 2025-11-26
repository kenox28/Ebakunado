<?php
// Determine which page is currently loaded
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user also exists in users table (has Parent role)
// Use available_roles from session (set during login)
$has_user_role = false;
if (isset($_SESSION['available_roles']) && in_array('user', $_SESSION['available_roles'])) {
    $has_user_role = true;
}
?>
<aside class="sidebar" id="sideNav">
    <nav class="sidebar-nav">
        <div class="sidebar-brand">
            <div class="brand-mark">
                <img
                    class="brand-logo"
                    src="assets/images/ebakunado-logo-without-label.png"
                    alt="eBakunado Logo" />
            </div>
            <div class="brand-text-block">
                <h1 class="brand-name">e-Bakunado</h1>
            </div>
        </div>

        <div class="sidebar-section">
            <ul class="sidebar-menu">
                <!-- Main Menu -->
                 <li class="sidebar-group-label">Overview</li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>">
                    <a href="superadmin-dashboard" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Group: User Management -->
                <li class="sidebar-group-label">User Management</li>
                <!-- <li class="sidebar-menu-item<?php echo $currentPage === 'admin-management.php' ? ' active' : ''; ?>">
                    <a href="../../views/superadmin-page/admin-management.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">admin_panel_settings</span>
                        <span class="menu-label">Admin</span>
                    </a>
                </li> -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'user-management.php' ? ' active' : ''; ?>">
                    <a href="superadmin-users" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">person</span>
                        <span class="menu-label">Users</span>
                    </a>
                </li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'bhw-management.php' ? ' active' : ''; ?>">
                    <a href="superadmin-bhw" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">groups</span>
                        <span class="menu-label">BHW</span>
                    </a>
                </li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'midwife-management.php' ? ' active' : ''; ?>">
                    <a href="superadmin-midwives" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">clinical_notes</span>
                        <span class="menu-label">Midwife</span>
                    </a>
                </li>

                <!-- Group: Location Management -->
                <li class="sidebar-group-label">Location Management</li>
                <li class="sidebar-menu-item <?php echo $currentPage === 'location-management.php' ? ' active' : ''; ?>">
                    <a href="superadmin-locations" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">map</span>
                        <span class="menu-label">Locations</span>
                    </a>
                </li>

                <!-- Group: Logs -->
                <li class="sidebar-group-label">Logs</li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'activity-logs.php' ? ' active' : ''; ?>">
                    <a href="superadmin-activity-logs" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">history</span>
                        <span class="menu-label">Activity Logs</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>