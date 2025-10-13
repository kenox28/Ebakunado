<?php
session_start();
$current_page = 'location-management';
$page_title = 'Location Management';
$page_js = 'location-management.js?v=1.0.4';

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1>Location Management</h1>
        <p>Manage locations (Province, City, Barangay, Purok)</p>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Location Data</h2>
            <button type="button" onclick="showAddLocationForm()" class="btn btn-primary">Add New Location</button>
        </div>
        
        <div class="section-content">
            <!-- Search -->
            <div class="search-container">
                <input type="text" id="searchLocations" placeholder="Search locations..." class="search-input">
                <button type="button" onclick="clearSearch('searchLocations', 'locationsTableBody')" class="btn btn-secondary">Clear</button>
            </div>
            
            <!-- Locations Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllLocations" onchange="toggleAllLocations()"></th>
                            <th>ID</th>
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
                            <td colspan="8" class="loading">Loading locations...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" onclick="deleteSelectedLocations()" class="btn btn-danger">Delete Selected</button>
            </div>
        </div>
    </div>

    <!-- Add Location Form -->
    <div id="addLocationForm" class="form-container" style="display: none;">
        <h3>Add New Location</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="add_province">Province:</label>
                <input type="text" id="add_province" name="province" required>
            </div>
            <div class="form-group">
                <label for="add_city_municipality">City/Municipality:</label>
                <input type="text" id="add_city_municipality" name="city_municipality" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="add_barangay">Barangay:</label>
                <input type="text" id="add_barangay" name="barangay" required>
            </div>
            <div class="form-group">
                <label for="add_purok">Purok:</label>
                <input type="text" id="add_purok" name="purok" required>
            </div>
        </div>
        <div class="action-buttons">
            <button type="button" onclick="saveLocation()" class="btn btn-primary">Save Location</button>
            <button type="button" onclick="cancelAddLocation()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
