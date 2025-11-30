<?php
// Determine which page/route is currently loaded (supports routed URLs)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
// Remove query string
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}
// Normalize and strip base path if app is hosted in a subdirectory
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($basePath !== '/' && $basePath !== '\\' && $basePath !== '.' && !empty($basePath)) {
    $basePath = '/' . trim($basePath, '/');
    if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
}
$requestUri = trim($requestUri, '/');

// Map the route to a page name used by the template. Fall back to PHP_SELF when not routed.
$currentPage = basename($_SERVER['PHP_SELF']); // fallback
if ($requestUri === '' || strpos($requestUri, 'dashboard') === 0) {
    $currentPage = 'dashboard.php';
} elseif (strpos($requestUri, 'children') === 0 || strpos($requestUri, 'children-list') === 0) {
    $currentPage = 'children-list.php';
} elseif (strpos($requestUri, 'approved-requests') === 0) {
    $currentPage = 'approved-requests.php';
} elseif (strpos($requestUri, 'add-child') === 0) {
    $currentPage = 'add-child.php';
}

require_once __DIR__ . '/../../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();
// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
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
            </div>
        </div>

        <div class="sidebar-section">
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>">
                    <a href="dashboard" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">dashboard</span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Add Child -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'add-child.php' ? ' active' : ''; ?>">
                    <a href="add-child" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">add_circle</span>
                        <span class="menu-label">Add Child</span>
                    </a>
                </li>

                <!-- Pending Approval -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'children-list.php' ? ' active' : ''; ?>">
                    <a href="children" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">folder</span>
                        <span class="menu-label">Child Record</span>
                    </a>
                </li>

                <!-- Child Health Record -->
                <li class="sidebar-menu-item<?php echo $currentPage === 'approved-requests.php' ? ' active' : ''; ?>">
                    <a href="approved-requests" class="menu-link">
                        <span class="menu-icon material-symbols-rounded">description</span>
                        <span class="menu-label">Approved Requests</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>