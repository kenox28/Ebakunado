<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <h2><i class="fas fa-map-marker-alt"></i> Location Management</h2>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">All Locations</h3>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="form-control" id="locationSearch" placeholder="Search locations...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary btn-sm" onclick="showAddLocationForm()">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </div>
        </div>
    </div>
    <div class="content-card-body">
        <!-- Add Location Form (Initially Hidden) -->
        <div id="addLocationForm" style="display: none;" class="mb-4">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">Add New Location</h5>
                </div>
                <div class="content-card-body">
                    <form id="locationForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="add_province">Province</label>
                                <input type="text" class="form-control" id="add_province" required>
                            </div>
                            <div class="form-group">
                                <label for="add_city">City/Municipality</label>
                                <input type="text" class="form-control" id="add_city" required>
                            </div>
                            <div class="form-group">
                                <label for="add_barangay">Barangay</label>
                                <input type="text" class="form-control" id="add_barangay" required>
                            </div>
                            <div class="form-group">
                                <label for="add_purok">Purok</label>
                                <input type="text" class="form-control" id="add_purok" required>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-primary btn-sm" onclick="addLocation()">
                                <i class="fas fa-save"></i> Save Location
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddLocationForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover" id="locationsTable">
                <thead>
                    <tr>
                        <th>Location ID</th>
                        <th>Province</th>
                        <th>City/Municipality</th>
                        <th>Barangay</th>
                        <th>Purok</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="locationsTableBody">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading locations...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- <script src="../../js/admin/location-management.js"></script> -->
<script src="../../js/supabase_js/admin/location-management.js?v=1.0.1"></script>
<script>
    // Set active menu item
    document.querySelector('a[href="location-management.php"]').classList.add('active');
    
    // Load locations when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getLocations();
        setupSearchListeners();
    });
</script>

<?php include 'includes/footer.php'; ?>
