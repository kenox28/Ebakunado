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
    <title>BHW Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/bhw-management.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="bhw-header">
            <h1 class="bhw-title">BHW Management</h1>
            <p class="bhw-subtitle">Manage BHW accounts and their information.</p>
        </div>

        <div class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">BHW Accounts</h2>
                    </div>
                    <div class="data-table-actions">
                        <div class="data-table-search" id="bhwSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="searchBhw" class="data-table-search__input" placeholder="Search BHW..." />
                            <button type="button" id="searchBhwClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <button type="button" onclick="deleteSelectedBhw()" class="btn btn-danger btn-icon">
                            <span class="material-symbols-rounded">delete</span>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAllBhw" onchange="toggleAllBhw()"></th>
                                <th scope="col">BHW ID</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Place</th>
                                <th scope="col">Permissions</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bhwTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="11">Loading BHW...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Edit BHW Form -->
        <div id="editBhwModal" class="modal-overlay" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editBHWModalTitle">
                <div class="modal-header">
                    <h3 id="editBHWModalTitle" class="modal-title">Edit BHW</h3>
                    <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="cancelEditBhw()">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div id="editBhwForm" class="modal-body">
                    <!-- Edit BHW form will be populated here -->
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="cancelEditBhw()" class="btn btn-secondary btn-icon">
                        Cancel
                    </button>
                    <button type="button" onclick="updateBhw()" class="btn btn-primary btn-icon">
                        Update BHW
                    </button>
                </div>
            </div>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.4"></script>
    <script src="js/supabase_js/superadmin/bhw-management.js?v=1.0.3"></script>
    <script>
        // Search clear toggle for BHW
        (function() {
            const input = document.getElementById('searchBhw');
            const clearBtn = document.getElementById('searchBhwClear');
            const wrap = document.getElementById('bhwSearchWrap');

            function toggleClear() {
                const hasValue = input.value.trim().length > 0;
                wrap.classList.toggle('data-table-search--has-value', hasValue);
            }
            input.addEventListener('input', toggleClear);
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClear();
                if (typeof clearSearch === 'function') {
                    clearSearch('searchBhw', 'bhwTableBody');
                }
                input.focus();
            });
            toggleClear();

            // Generic modal helpers for modal popups
            window.openModal = function(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.hidden = false;
                el.classList.add('is-open');
                // Focus first input inside
                const firstInput = el.querySelector('input, select, textarea, button');
                if (firstInput) firstInput.focus();
            };
            window.closeModal = function(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('is-open');
                el.hidden = true;
            };
            // ESC key closes topmost open modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const openModalEl = document.querySelector('.modal-overlay.is-open');
                    if (openModalEl) {
                        openModalEl.hidden = true;
                        openModalEl.classList.remove('is-open');
                    }
                }
            });
        })();
    </script>
</body>

</html>