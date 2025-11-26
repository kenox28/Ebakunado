<?php
session_start();

if (!isset($_SESSION['super_admin_id'])) {
    header("Location: login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/dashboard.css?v=1.0.4" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.2">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <section class="dashboard-overview">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard Overview</h1>
                <p class="dashboard-subtitle">Welcome to the Super Admin Dashboard, your centralized workspace for monitoring system activity and managing platform resources.</p>
            </div>


            <!-- Stats Cards -->
            <div class="card-wrapper">
                <div class="card card-1">
                    <div class="card-top">
                        <div class="card-info">
                            <h3 class="card-title">Total Users</h3>
                            <p class="card-number" id="totalUsers">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">person</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <span class="trend up" id="usersTrend">Loading...</span>
                        <a class="card-link" href="superadmin-users">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-2">
                    <div class="card-top">
                        <div class="card-info">
                            <h3 class="card-title">Total BHW</h3>
                            <p class="card-number" id="totalBhw">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">diversity_3</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <span class="trend up" id="bhwTrend">Loading...</span>
                        <a class="card-link" href="superadmin-bhw">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-3">
                    <div class="card-top">
                        <div class="card-info">
                            <h3 class="card-title">Total Midwives</h3>
                            <p class="card-number" id="totalMidwives">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">clinical_notes</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <span class="trend up" id="midwivesTrend">Loading...</span>
                        <a class="card-link" href="superadmin-midwives">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-4">
                    <div class="card-top">
                        <div class="card-info">
                            <h3 class="card-title">Total Locations</h3>
                            <p class="card-number" id="totalLocations">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">location_on</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <span class="trend up" id="locationsTrend">Loading...</span>
                        <a class="card-link" href="superadmin-locations">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-5">
                    <div class="card-top">
                        <div class="card-info">
                            <h3 class="card-title">Activity Logs</h3>
                            <p class="card-number" id="totalLogs">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">history</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <span class="trend up" id="logsTrend">Loading...</span>
                        <a class="card-link" href="superadmin-activity-logs">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Activity (refactored with global data-table classes) -->
        <section class="recent-activity">
            <div class="data-table-card">
                <div class="data-table-toolbar">
                    <h3 class="data-table-title">Recent Activity</h3>
                    <p class="data-count-label">Showing 10 recent entries</p>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">User</th>
                                <th scope="col">Action</th>
                                <th scope="col">Description</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentActivityTable">
                            <tr class="data-table__message-row loading">
                                <td colspan="4">Loading recent activity...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.4"></script>
    <script src="js/supabase_js/superadmin/dashboard.js?v=1.0.4"></script>
</body>

</html>