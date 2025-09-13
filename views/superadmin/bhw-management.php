<?php
session_start();
$current_page = 'bhw-management';
$page_title = 'BHW Management';
$page_js = 'bhw-management.js';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>BHW Management</h1>
        <p>Manage Barangay Health Worker accounts</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">BHW Accounts</h2>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchBhw" placeholder="Search BHW..." class="search-input">
                <button type="button" onclick="clearSearch('searchBhw', 'bhwTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- BHW Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllBhw" onchange="toggleAllBhw()"></th>
                            <th>BHW ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Place</th>
                            <th>Permissions</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bhwTableBody">
                        <tr>
                            <td colspan="11" class="loading">Loading BHW...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedBhw()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>

    <!-- Edit BHW Form -->
    <div id="editBhwForm" class="form-container" style="display: none;">
        <!-- Edit BHW form will be populated here -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
