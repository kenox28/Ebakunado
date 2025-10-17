<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-user-md"></i> BHW Management</h2>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">All BHWs</h3>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="form-control" id="bhwSearch" placeholder="Search BHWs...">
            </div>
        </div>
    </div>
    <div class="content-card-body">
        <div class="table-container">
            <table class="table table-hover" id="bhwTable">
                <thead>
                    <tr>
                        <th>BHW ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Permissions</th>
                        <th>Place</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bhwTableBody">
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading BHWs...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit BHW Modal -->
<div class="modal fade" id="editBhwModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit BHW</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editBhwForm">
                    <!-- Form will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script src="../../js/admin/bhw-management.js"></script> -->
<script src="../../js/supabase_js/admin/bhw-management.js?v=1.0.1"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="bhw-management.php"]').classList.add('active');
    
    // Load BHWs when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getBhws();
        setupSearchListeners();
    });
</script>

<?php include 'includes/footer.php'; ?>
