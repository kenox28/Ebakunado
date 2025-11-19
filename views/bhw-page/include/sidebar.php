<?php
// Determine which page is currently loaded
$currentPage = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
if ($user_types != 'midwife') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}

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
                        <span class="menu-icon material-symbols-rounded">pending_actions</span>
                        <span class="menu-label">Pending Approval</span>
                    </a>
                </li>

                <!-- Child Health Record -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'child-health-list.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/child-health-list.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
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

                <!-- Added Children -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'bhw-added-children.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/bhw-added-children.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">child_care</span>
                        <span class="menu-label">Added Children</span>
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

                <!-- Baby Card Requests (Midwife only) -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'babycard-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="../../views/bhw-page/babycard-doc-requests.php" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">credit_card</span>
                        <span class="menu-label">Baby Card Requests</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <?php
            $sessionProfileImg = isset($_SESSION['profileimg']) ? trim((string)$_SESSION['profileimg']) : '';
            $sidebarProfileImg = ($sessionProfileImg && $sessionProfileImg !== 'noprofile.png')
                ? $sessionProfileImg
                : '../../assets/images/user-profile.png';
        ?>
        <div class="sidebar-profile">
            <div class="profile-avatar-container">
                <img
                    class="profile-avatar"
                    src="<?php echo htmlspecialchars($sidebarProfileImg); ?>"
                    alt="User Profile" />
            </div>
            <div class="profile-text-block">
                <h2 class="profile-name"><?php echo htmlspecialchars($user_fullname); ?></h2>
                <h3 class="profile-role"><?php echo $user_type; ?></h3>
            </div>
        </div>
    </nav>
</aside>