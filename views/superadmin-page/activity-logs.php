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
    <title>Activity Logs</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/activity-logs.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="activity-logs-header">
            <h1 class="activity-logs-title">Activity Logs</h1>
            <p class="activity-logs-subtitle">Manage user accounts and their information.</p>
        </div>

        <div class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">System Activity Logs</h2>
                    </div>
                    <div class="data-table-actions">
                        <div class="data-table-search" id="activityLogsSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="searchActivityLogs" class="data-table-search__input" placeholder="Search activity logs..." />
                            <button type="button" id="searchActivityLogsClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <button type="button" onclick="deleteSelectedActivityLogs()" class="btn btn-danger btn-icon">
                            <span class="material-symbols-rounded">delete</span>
                            Delete Selected
                        </button>
                    </div>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAllActivityLogs" onchange="toggleAllActivityLogs()"></th>
                                <th scope="col">Log ID</th>
                                <th scope="col">User ID</th>
                                <th scope="col">User Type</th>
                                <th scope="col">Action</th>
                                <th scope="col">Description</th>
                                <th scope="col">IP Address</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activityLogsTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="9">Loading activity logs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.4"></script>
    <script src="js/supabase_js/superadmin/activity-logs.js?v=1.0.3"></script>
    <script>
        // Search clear toggle for Activity Logs
        (function() {
            const input = document.getElementById('searchActivityLogs');
            const clearBtn = document.getElementById('searchActivityLogsClear');
            const wrap = document.getElementById('activityLogsSearchWrap');

            function toggleClear() {
                const hasValue = input.value.trim().length > 0;
                wrap.classList.toggle('data-table-search--has-value', hasValue);
            }
            input.addEventListener('input', toggleClear);
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClear();
                if (typeof clearSearch === 'function') {
                    clearSearch('searchActivityLogs', 'activityLogsTableBody');
                }
                input.focus();
            });
            toggleClear();
        })();
    </script>
</body>

</html>