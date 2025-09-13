<?php
session_start();
$current_page = 'activity-logs';
$page_title = 'Activity Logs';
$page_js = 'activity-logs.js';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>Activity Logs</h1>
        <p>View and manage system activity logs</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">System Activity Logs</h2>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchActivityLogs" placeholder="Search activity logs..." class="search-input">
                <button type="button" onclick="clearSearch('searchActivityLogs', 'activityLogsTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- Activity Logs Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllActivityLogs" onchange="toggleAllActivityLogs()"></th>
                            <th>Log ID</th>
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activityLogsTableBody">
                        <tr>
                            <td colspan="9" class="loading">Loading activity logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedActivityLogs()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
