<?php
session_start();
$current_page = 'midwife-management';
$page_title = 'Midwife Management';
$page_js = 'midwife-management.js';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>Midwife Management</h1>
        <p>Manage midwife accounts and permissions</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Midwife Accounts</h2>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchMidwives" placeholder="Search midwives..." class="search-input">
                <button type="button" onclick="clearSearch('searchMidwives', 'midwivesTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- Midwives Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllMidwives" onchange="toggleAllMidwives()"></th>
                            <th>Midwife ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Place</th>
                            <th>Permissions</th>
                            <th>Approved</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="midwivesTableBody">
                        <tr>
                            <td colspan="12" class="loading">Loading midwives...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedMidwives()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>

    <!-- Edit Midwife Form -->
    <div id="editMidwifeForm" class="form-container" style="display: none;">
        <!-- Edit midwife form will be populated here -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
