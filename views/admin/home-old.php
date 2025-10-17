<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_fname = $_SESSION['admin_fname'] ?? '';
$admin_lname = $_SESSION['admin_lname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ebakunado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-box {
            margin-bottom: 1rem;
        }
        .btn-group {
            gap: 0.5rem;
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
        .badge {
            font-size: 0.75em;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Admin Dashboard</h4>
                <div class="d-flex align-items-center gap-3">
                    <span>Welcome, <?php echo htmlspecialchars($admin_fname . ' ' . $admin_lname); ?></span>
                    <button class="btn btn-outline-danger btn-sm" onclick="logoutAdmin()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <!-- Users Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-users"></i> User Management</h5>
                <div class="search-box">
                    <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                </div>
            </div>
            <div class="table-responsive">
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
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BHW Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-user-md"></i> BHW Management</h5>
                <div class="search-box">
                    <input type="text" class="form-control" id="bhwSearch" placeholder="Search BHWs...">
                </div>
            </div>
            <div class="table-responsive">
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
                        <!-- BHWs will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Midwives Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-user-nurse"></i> Midwife Management</h5>
                <div class="search-box">
                    <input type="text" class="form-control" id="midwifeSearch" placeholder="Search midwives...">
                </div>
            </div>
            <div class="table-responsive">
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
                        <!-- Midwives will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Location Management -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-map-marker-alt"></i> Location Management</h5>
                <button class="btn btn-primary btn-sm" onclick="showAddLocationForm()">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </div>
            
            <!-- Add Location Form (Initially Hidden) -->
            <div id="addLocationForm" style="display: none;" class="mb-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Add New Location</h6>
                        <form id="locationForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="add_province" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="add_province" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="add_city" class="form-label">City/Municipality</label>
                                    <input type="text" class="form-control" id="add_city" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="add_barangay" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="add_barangay" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="add_purok" class="form-label">Purok</label>
                                    <input type="text" class="form-control" id="add_purok" required>
                                </div>
                            </div>
                            <div class="mt-3">
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

            <div class="table-responsive">
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
                        <!-- Locations will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-history"></i> Activity Logs</h5>
                <div class="search-box">
                    <input type="text" class="form-control" id="activitySearch" placeholder="Search activities...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="activityLogsTable">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>User</th>
                            <th>User Type</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="activityLogsTableBody">
                        <!-- Activity logs will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/supabase_js/admin/home.js?v=1.0.1"></script>
</body>
</html>