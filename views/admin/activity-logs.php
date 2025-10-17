<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-history"></i> Activity Logs</h2>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">All Activity Logs</h3>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="form-control" id="activitySearch" placeholder="Search activities...">
            </div>
        </div>
    </div>
    <div class="content-card-body">
        <div class="table-container">
            <table class="table table-hover" id="activityLogsTable">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>User Type</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="activityLogsTableBody">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading activity logs...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- <script src="../../js/admin/activity-logs.js"></script> -->
<script src="../../js/supabase_js/admin/activity-logs.js?v=1.0.1"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="activity-logs.php"]').classList.add('active');
    
    // Load activity logs when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getActivityLogs();
        setupSearchListeners();
    });
</script>

<?php include 'includes/footer.php'; ?>
