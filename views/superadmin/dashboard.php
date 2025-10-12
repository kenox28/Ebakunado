<?php
session_start();
$current_page = 'dashboard';
$page_title = 'Dashboard';
$page_js = 'dashboard.js?v=1.0.2';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome to the EBAKUNADO Super Admin Dashboard</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3 id="totalUsers">-</h3>
            <p>Total Users</p>
            <span class="trend up" id="usersTrend">Loading...</span>
        </div>
        
        <div class="stat-card">
            <h3 id="totalAdmins">-</h3>
            <p>Total Admins</p>
            <span class="trend up" id="adminsTrend">Loading...</span>
        </div>
        
        <div class="stat-card">
            <h3 id="totalBhw">-</h3>
            <p>Total BHW</p>
            <span class="trend up" id="bhwTrend">Loading...</span>
        </div>
        
        <div class="stat-card">
            <h3 id="totalMidwives">-</h3>
            <p>Total Midwives</p>
            <span class="trend up" id="midwivesTrend">Loading...</span>
        </div>
        
        <div class="stat-card">
            <h3 id="totalLocations">-</h3>
            <p>Total Locations</p>
            <span class="trend up" id="locationsTrend">Loading...</span>
        </div>
        
        <div class="stat-card">
            <h3 id="totalLogs">-</h3>
            <p>Activity Logs</p>
            <span class="trend up" id="logsTrend">Loading...</span>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Recent Activity</h2>
        </div>
        <div class="section-content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="recentActivityTable">
                        <tr>
                            <td colspan="4" class="loading">Loading recent activity...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Quick Actions</h2>
        </div>
        <div class="section-content">
            <div class="action-buttons">
                <a href="admin-management.php" class="btn btn-primary">Add New Admin</a>
                <a href="user-management.php" class="btn btn-primary">Manage Users</a>
                <a href="location-management.php" class="btn btn-success">Add Location</a>
                <a href="activity-logs.php" class="btn btn-secondary">View All Logs</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
