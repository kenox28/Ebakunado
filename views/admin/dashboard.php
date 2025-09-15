<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
</div>

<!-- Dashboard Statistics -->
<div class="stats-grid" id="dashboardStats">
    <div class="stat-card">
        <h3 id="totalUsers">Loading...</h3>
        <p>Total Users</p>
        <i class="fas fa-users"></i>
    </div>
    <div class="stat-card">
        <h3 id="totalBhws">Loading...</h3>
        <p>Total BHWs</p>
        <i class="fas fa-user-md"></i>
    </div>
    <div class="stat-card">
        <h3 id="totalMidwives">Loading...</h3>
        <p>Total Midwives</p>
        <i class="fas fa-user-nurse"></i>
    </div>
    <div class="stat-card">
        <h3 id="totalLocations">Loading...</h3>
        <p>Total Locations</p>
        <i class="fas fa-map-marker-alt"></i>
    </div>
    <div class="stat-card">
        <h3 id="totalActivityLogs">Loading...</h3>
        <p>Activity Logs</p>
        <i class="fas fa-history"></i>
    </div>
</div>

<!-- Recent Activity -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">Recent Activity</h3>
        <a href="activity-logs.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    <div class="content-card-body">
        <div id="recentActivity">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading recent activity...</p>
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/dashboard.js?v=1.0.1"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="dashboard.php"]').classList.add('active');
    
    // Load dashboard data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardStats();
        loadRecentActivity();
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            loadDashboardStats();
            loadRecentActivity();
        }, 30000);
    });
</script>

<?php include 'includes/footer.php'; ?>
