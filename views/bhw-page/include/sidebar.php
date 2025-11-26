<?php
// Determine which page is currently loaded
// Use REQUEST_URI to detect the route since PHP_SELF is always index.php for routed pages
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
// Remove query string
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}
// Remove base path if exists
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($basePath !== '/' && $basePath !== '\\' && $basePath !== '.' && !empty($basePath)) {
    $basePath = '/' . trim($basePath, '/');
    if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
}
$requestUri = trim($requestUri, '/');

// Map routes to page names for active state
$currentPage = 'dashboard.php'; // default

// Check for specific routes (order matters - more specific first)
if (preg_match('#^health-child/[^/]+$#', $requestUri)) {
    // Child health record detail page: /health-child/{id}
    $currentPage = 'child-health-list.php'; // Highlight the parent list page
} elseif (strpos($requestUri, 'health-dashboard') === 0 || $requestUri === 'health-dashboard') {
    $currentPage = 'dashboard.php';
} elseif (strpos($requestUri, 'health-vaccination-planner') === 0) {
    $currentPage = 'vaccination-planner.php';
} elseif (strpos($requestUri, 'health-immunizations') === 0) {
    $currentPage = 'immunization.php';
} elseif (strpos($requestUri, 'health-pending') === 0) {
    $currentPage = 'pending-approval.php';
} elseif (strpos($requestUri, 'health-children') === 0 || $requestUri === 'health-children') {
    $currentPage = 'child-health-list.php';
} elseif (strpos($requestUri, 'health-target-client') === 0) {
    $currentPage = 'target-client-list.php';
} elseif (strpos($requestUri, 'health-add-child') === 0) {
    $currentPage = 'add-child.php';
} elseif (strpos($requestUri, 'health-chr-doc-requests') === 0) {
    $currentPage = 'chr-doc-requests.php';
} elseif (strpos($requestUri, 'health-babycard-requests') === 0) {
    $currentPage = 'babycard-doc-requests.php';
} else {
    // Fallback to PHP_SELF for non-routed pages
    $currentPage = basename($_SERVER['PHP_SELF']);
}

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
                    src="assets/images/ebakunado-logo-without-label.png"
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
                    <a href="health-dashboard" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Vaccination Planner -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'vaccination-planner.php' ? ' active' : ''; ?>">
                    <a href="health-vaccination-planner" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">event_note</span>
                        <span class="menu-label">Vaccination Planner</span>
                    </a>
                </li>

                <!-- Immunization -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'immunization.php' ? ' active' : ''; ?>">
                    <a href="health-immunizations" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">vaccines</span>
                        <span class="menu-label">Immunization Form</span>
                    </a>
                </li>

                <!-- Pending Approval -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'pending-approval.php' ? ' active' : ''; ?>">
                    <a href="health-pending" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">pending_actions</span>
                        <span class="menu-label">Pending Approval</span>
                    </a>
                </li>

                <!-- Child Health Record -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'child-health-list.php' ? ' active' : ''; ?>">
                    <a href="health-children" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">Child Health Record</span>
                    </a>
                </li>

                <!-- Target Client List -->
                <li class="sidebar-menu-item <?php echo $currentPage === 'target-client-list.php' ? ' active' : ''; ?>">
                    <a href="health-target-client" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">format_list_bulleted</span>
                        <span class="menu-label">Target Client List</span>
                    </a>
                </li>
                
                <!-- Add Child -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'add-child.php' ? ' active' : ''; ?>">
                    <a href="health-add-child" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">add_circle</span>
                        <span class="menu-label">Add Child</span>
                    </a>
                </li>

                <?php if (isset($_SESSION['midwife_id'])): ?>
                <!-- CHR Doc Requests (Midwife only) - HIDDEN -->
                <!-- <li class="sidebar-menu-item<?php echo $currentPage === 'chr-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="health-chr-doc-requests" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">CHR Doc Requests</span>
                    </a>
                </li> -->

                <!-- Baby Card Requests (Midwife only) -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'babycard-doc-requests.php' ? ' active' : ''; ?>">
                    <a href="health-babycard-requests" class="menu-link">
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
                : 'assets/images/user-profile.png';
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