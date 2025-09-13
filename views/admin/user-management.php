<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-users"></i> User Management</h2>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">All Users</h3>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
            </div>
        </div>
    </div>
    <div class="content-card-body">
        <div class="table-container">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Place</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading users...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editUserForm">
                    <!-- Form will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/user-management.js"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="user-management.php"]').classList.add('active');
    
    // Load users when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getUsers();
        setupSearchListeners();
    });
</script>

<?php include 'includes/footer.php'; ?>
