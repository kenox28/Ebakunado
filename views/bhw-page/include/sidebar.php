<?php
// Determine which page is currently loaded
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sideNav">
    <nav class="sidebar-nav">
        <div class="sidebar-brand">
            <img
                class="brand-mark"
                src="/assets/images/ebakunado-logo-without-label.png"
                alt="eBakunado Logo" />
            <div class="brand-text-block">
                <h1 class="brand-name">eBakunado</h1>
                <h2 class="brand-tagline">Immunization Data Management</h2>
            </div>
        </div>

        <div class="sidebar-profile">
            <img
                class="profile-avatar"
                src="/assets/images/user-profile.png"
                alt="User Profile" />
            <div class="profile-text-block">
                <h2 class="profile-name">John Doe</h2>
                <h3 class="profile-role">Barangay Health Worker</h3>
            </div>
        </div>

        <div class="sidebar-section">
            <h2 class="sidebar-section-title">Main Menu</h2>
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>">
                    <a href="/views/bhw-page/dashboard.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Immunization -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'immunization.php' ? ' active' : ''; ?>">
                    <a href="/views/bhw-page/immunization.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">vaccines</span>
                        <span class="menu-label">Immunization Form</span>
                    </a>
                </li>

                <!-- Pending Approval -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'pending-approval.php' ? ' active' : ''; ?>">
                    <a href="/views/bhw-page/pending-approval.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">hourglass_top</span>
                        <span class="menu-label">Pending Approval</span>
                    </a>
                </li>

                <!-- Others (no active state until pages exist/linked) -->
                <li class="sidebar-menu-item">
                    <a href="#" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">child_care</span>
                        <span class="menu-label">Child Health Record</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">CHR Doc Requests</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">format_list_bulleted</span>
                        <span class="menu-label">Target Client List</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>