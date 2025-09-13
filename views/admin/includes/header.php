<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_fname = $_SESSION['admin_fname'] ?? '';
$admin_lname = $_SESSION['admin_lname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ebakunado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/admin-dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="header-title">Admin Dashboard</h1>
                <div class="header-actions">
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars($admin_fname . ' ' . $admin_lname); ?></span>
                    <button class="logout-btn" onclick="logoutAdmin()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </header>
