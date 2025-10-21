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
                    <a href="../../views/bhw-page/dashboard.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Immunization -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'immunization.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/immunization.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">vaccines</span>
                        <span class="menu-label">Immunization Form</span>
                    </a>
                </li>

                <!-- Pending Approval -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'pending-approval.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/pending-approval.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">hourglass_top</span>
                        <span class="menu-label">Pending Approval</span>
                    </a>
                </li>

                <!-- Child Health Record -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'child-health-list.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/child-health-list.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">child_care</span>
                        <span class="menu-label">Child Health Record</span>
                    </a>
                </li>

                <!-- Target Client List -->
                <li class="sidebar-menu-item <?php echo $currentPage === 'target-client-list.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/target-client-list.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">format_list_bulleted</span>
                        <span class="menu-label">Target Client List</span>
                    </a>
                </li>
                
                <!-- Add Child -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'add-child.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/add-child.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">add_circle</span>
                        <span class="menu-label">Add Child</span>
                    </a>
                </li>

                <!-- Profile Management -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'profile-management.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/profile-management.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">person</span>
                        <span class="menu-label">Profile Management</span>
                    </a>
                </li>

                <?php if (isset($_SESSION['midwife_id'])): ?>
                <!-- CHR Doc Requests (Midwife only) -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'chr-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/chr-doc-requests.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">CHR Doc Requests</span>
                    </a>
                </li>

                <!-- System Settings (Midwife only) -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'system-settings.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/system-settings.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">settings</span>
                        <span class="menu-label">System Settings</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-profile">
            <div class="profile-avatar-container">
                <img
                    class="profile-avatar"
                    src="../../assets/images/user-profile.png"
                    alt="User Profile" />
            </div>
            <div class="profile-text-block">
                <h2 class="profile-name"><?php echo htmlspecialchars($user_fullname); ?></h2>
                <h3 class="profile-role"><?php echo htmlspecialchars($user_type); ?></h3>
            </div>
        </div>
    </nav>
</aside>