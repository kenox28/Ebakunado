<?php
session_start();
$current_page = 'user-management';
$page_title = 'User Management';
$page_js = 'user-management.js?v=1.0.3';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>User Management</h1>
        <p>Manage user accounts and their information</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">User Accounts</h2>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchUsers" placeholder="Search users..." class="search-input">
                <button type="button" onclick="clearSearch('searchUsers', 'usersTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- Users Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()"></th>
                            <th>User ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Place</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="11" class="loading">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedUsers()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>

    <!-- Edit User Form -->
    <div id="editUserForm" class="form-container" style="display: none;">
        <!-- Edit user form will be populated here -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
