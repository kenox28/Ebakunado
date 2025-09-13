<?php
session_start();
$current_page = 'admin-management';
$page_title = 'Admin Management';
$page_js = 'admin-management.js';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>Admin Management</h1>
        <p>Manage administrator accounts and permissions</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Admin Accounts</h2>
            <button type="button" onclick="showAddAdminForm()" class="btn btn-primary">Add New Admin</button>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchAdmins" placeholder="Search admins..." class="search-input">
                <button type="button" onclick="clearSearch('searchAdmins', 'adminsTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- Admins Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllAdmins" onchange="toggleAllAdmins()"></th>
                            <th>Admin ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">
                        <tr>
                            <td colspan="7" class="loading">Loading admins...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedAdmins()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>

    <!-- Add Admin Form -->
    <div id="addAdminForm" class="form-container" style="display: none;">
        <h3>Add New Admin</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="add_admin_id">Admin ID:</label>
                <input type="text" id="add_admin_id" name="admin_id" required>
            </div>
            <div class="form-group">
                <label for="add_admin_fname">First Name:</label>
                <input type="text" id="add_admin_fname" name="fname" required>
            </div>
            <div class="form-group">
                <label for="add_admin_lname">Last Name:</label>
                <input type="text" id="add_admin_lname" name="lname" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_admin_email">Email:</label>
                <input type="email" id="add_admin_email" name="email" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_admin_password">Password:</label>
                <input type="password" id="add_admin_password" name="password" required>
            </div>
        </div>
        <div class="action-buttons">
            <button type="button" onclick="saveAdmin()" class="btn btn-primary">Save Admin</button>
            <button type="button" onclick="cancelAddAdmin()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>

    <!-- Edit Admin Form -->
    <div id="editAdminForm" class="form-container" style="display: none;">
        <!-- Edit admin form will be populated here -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
