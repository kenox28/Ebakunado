<?php
if (!isset($_SESSION['super_admin_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Super Admin Dashboard'; ?> - EBAKUNADO</title>
    <link rel="stylesheet" href="../../css/superadmin-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1 class="header-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></span>
                    <button onclick="logoutSuperAdmin()" class="btn btn-logout">Logout</button>
                </div>
            </div>
        </header>
