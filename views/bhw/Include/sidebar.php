<?php
// Determine active page for highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
// Fallbacks if variables are not set by including scope
$user_fullname = isset($user_fullname) ? $user_fullname : (($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
$user_type_label = isset($user_type_label) ? $user_type_label : ((($_SESSION['user_type'] ?? '') != 'midwifes') ? 'Barangay Health Worker' : 'Midwife');
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
                <li class="sidebar-menu-item<?php echo $currentPage === 'home.php' ? ' active' : ''; ?>">
                    <a href="./home.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'immunization.php' ? ' active' : ''; ?>">
                    <a href="./immunization.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">vaccines</span>
                        <span class="menu-label">Immunization Form</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'pending_approval.php' ? ' active' : ''; ?>">
                    <a href="./pending_approval.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">hourglass_top</span>
                        <span class="menu-label">Pending Approval</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'child_health_record.php' ? ' active' : ''; ?>">
                    <a href="./child_health_record.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">child_care</span>
                        <span class="menu-label">Child Health Record</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'target_client.php' ? ' active' : ''; ?>">
                    <a href="./target_client.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">format_list_bulleted</span>
                        <span class="menu-label">Target Client List</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'add_child.php' ? ' active' : ''; ?>">
                    <a href="./add_child.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">add_circle</span>
                        <span class="menu-label">Add Child</span>
                    </a>
                </li>

                <li class="sidebar-menu-item<?php echo $currentPage === 'profile_management.php' ? ' active' : ''; ?>">
                    <a href="./profile_management.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">person</span>
                        <span class="menu-label">Profile Management</span>
                    </a>
                </li>

                <?php if (isset($_SESSION['midwife_id'])): ?>
                <li class="sidebar-menu-item<?php echo $currentPage === 'chr-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="./chr-doc-requests.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">CHR Doc Requests</span>
                    </a>
                </li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'babycard-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="./babycard-doc-requests.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">credit_card</span>
                        <span class="menu-label">Baby Card Requests</span>
                    </a>
                </li>
                <li class="sidebar-menu-item<?php echo $currentPage === 'system_settings.php' ? ' active' : ''; ?>">
                    <a href="./system_settings.php" class="menu-link">
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
                <h2 class="profile-name"><?php echo htmlspecialchars(trim($user_fullname)) ?: 'User'; ?></h2>
                <h3 class="profile-role"><?php echo htmlspecialchars($user_type_label); ?></h3>
            </div>
        </div>
    </nav>
</aside>

