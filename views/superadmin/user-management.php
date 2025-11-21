<?php
session_start();
$current_page = 'user-management';
$page_title = 'User Management';
$page_js = 'user-management.js?v=1.0.5';

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
            <button type="button" onclick="showAddUserForm()" class="btn btn-primary">Add New User</button>
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

    <!-- Add User Form -->
    <div id="addUserForm" class="form-container" style="display: none;">
        <h3>Add New User</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="add_user_fname">First Name</label>
                <input type="text" id="add_user_fname" name="fname" required>
            </div>
            <div class="form-group">
                <label for="add_user_lname">Last Name</label>
                <input type="text" id="add_user_lname" name="lname" required>
            </div>
            <div class="form-group">
                <label for="add_user_email">Email</label>
                <input type="email" id="add_user_email" name="email" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_user_phone">Phone Number</label>
                <input type="text" id="add_user_phone" name="phone_number" required>
            </div>
            <div class="form-group">
                <label for="add_user_gender">Gender</label>
                <select id="add_user_gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_user_password">Password</label>
                <input type="password" id="add_user_password" name="password" required>
            </div>
            <div class="form-group">
                <label for="add_user_confirm_password">Confirm Password</label>
                <input type="password" id="add_user_confirm_password" name="confirm_password" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_user_province">Province</label>
                <select id="add_user_province" name="province" onchange="loadAddUserCities()" required>
                    <option value="">Select Province</option>
                </select>
            </div>
            <div class="form-group">
                <label for="add_user_city_municipality">City/Municipality</label>
                <select id="add_user_city_municipality" name="city_municipality" onchange="loadAddUserBarangays()" required>
                    <option value="">Select City/Municipality</option>
                </select>
            </div>
            <div class="form-group">
                <label for="add_user_barangay">Barangay</label>
                <select id="add_user_barangay" name="barangay" onchange="loadAddUserPuroks()" required>
                    <option value="">Select Barangay</option>
                </select>
            </div>
            <div class="form-group">
                <label for="add_user_purok">Purok</label>
                <select id="add_user_purok" name="purok" required>
                    <option value="">Select Purok</option>
                </select>
            </div>
        </div>
        <div class="action-buttons">
            <button type="button" onclick="saveUser()" class="btn btn-primary">Save User</button>
            <button type="button" onclick="cancelAddUser()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>

    <!-- Edit User Form -->
    <div id="editUserForm" class="form-container" style="display: none;">
        <!-- Edit user form will be populated here -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
