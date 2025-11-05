<?php
// Determine which page is currently loaded
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sideNav">
    <nav class="sidebar-nav">
        <div class="sidebar-brand">
            <div class="brand-mark">
                <img
                    class="brand-logo"
                    src="../../assets/images/ebakunado-logo-without-label.png"
                    alt="eBakunado Logo" />
            </div>
            <div class="brand-text-block">
                <h1 class="brand-name">eBakunado</h1>
                <h2 class="brand-tagline">Immunization Data Management</h2>
            </div>
        </div>

        <div class="sidebar-section">
            <h2 class="sidebar-section-title">Main Menu</h2>
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>">
                    <a href="../../views/user-page/dashboard.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Pending Approval -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'children-list.php' ? ' active' : ''; ?>">
                    <a href="../../views/user-page/children-list.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">folder</span>
                        <span class="menu-label">Child Record</span>
                    </a>
                </li>

                <!-- Child Health Record -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'approved-requests.php' ? ' active' : ''; ?>">
                    <a href="../../views/user-page/approved-requests.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">Approved Requests</span>
                    </a>
                </li>
                
                <!-- Add Child -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'add-child.php' ? ' active' : ''; ?>">
                    <a href="../../views/user-page/add-child-request.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">add_circle</span>
                        <span class="menu-label">Add Child</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-profile">
            <div class="profile-avatar-container">
                <img
                    class="profile-avatar"
                    src="<?php echo !empty($noprofile) ? htmlspecialchars($noprofile) : '../../assets/images/user-profile.png'; ?>"
                    alt="User Profile" />
            </div>
            <div class="profile-text-block">
                <h2 class="profile-name"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h2>
                <h3 class="profile-role">Parent</h3>
            </div>
        </div>
    </nav>
</aside>