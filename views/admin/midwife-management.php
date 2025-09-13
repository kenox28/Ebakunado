<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-user-nurse"></i> Midwife Management</h2>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">All Midwives</h3>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="form-control" id="midwifeSearch" placeholder="Search midwives...">
            </div>
        </div>
    </div>
    <div class="content-card-body">
        <div class="table-container">
            <table class="table table-hover" id="midwivesTable">
                <thead>
                    <tr>
                        <th>Midwife ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Permissions</th>
                        <th>Approved</th>
                        <th>Place</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="midwivesTableBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading midwives...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Midwife Modal -->
<div class="modal fade" id="editMidwifeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Midwife</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editMidwifeForm">
                    <!-- Form will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/admin/midwife-management.js"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="midwife-management.php"]').classList.add('active');
    
    // Load midwives when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getMidwives();
        setupSearchListeners();
    });
</script>

<?php include 'includes/footer.php'; ?>
