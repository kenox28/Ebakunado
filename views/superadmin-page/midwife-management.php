<?php
session_start();

// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

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
    <title>Midwife Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/midwife-management.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="midwife-header">
            <h1 class="midwife-title">Midwife Management</h1>
            <p class="midwife-subtitle">Manage midwife accounts and their information.</p>
        </div>

        <div class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">Midwife Accounts</h2>
                    </div>
                    <div class="data-table-actions">
                        <div class="data-table-search" id="midwivesSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="searchMidwives" class="data-table-search__input" placeholder="Search midwives..." data-remote-search="true" />
                            <button type="button" id="searchMidwivesClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <button type="button" onclick="deleteSelectedMidwives()" class="btn btn-danger btn-icon">
                            <span class="material-symbols-rounded">delete</span>
                            Delete Selected
                        </button>
                    </div>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAllMidwives" onchange="toggleAllMidwives()"></th>
                                <th scope="col">Midwife ID</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Place</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="midwivesTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="10">Loading midwives...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pager">
                    <p class="page-info" id="midwivesPageInfo">Showing 0-0 of 0</p>
                    <div class="pager-controls">
                        <button type="button" class="pager-btn" id="midwivesPrevBtn" data-page="1" disabled>
                            <span class="material-symbols-rounded">chevron_left</span>
                            Prev
                        </button>
                        <button type="button" class="pager-btn" id="midwivesNextBtn" data-page="1" disabled>
                            Next
                            <span class="material-symbols-rounded">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Midwife Modal -->
        <div id="editMidwifeModal" class="modal-overlay" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editMidwifeModalTitle">
                <div class="modal-header">
                    <h3 id="editMidwifeModalTitle" class="modal-title">Edit Midwife</h3>
                    <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="cancelEditMidwife()">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div id="editMidwifeForm" class="modal-body">
                    <!-- Edit midwife form will be populated here -->
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="cancelEditMidwife()" class="btn btn-secondary btn-icon">
                        Cancel
                    </button>
                    <button type="button" onclick="updateMidwife()" class="btn btn-primary btn-icon">
                        Update Midwife
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.5"></script>
    <script src="js/supabase_js/superadmin/midwife-management.js?v=1.0.5"></script>
    <script>
        // Search clear toggle for Midwives
        (function() {
            const input = document.getElementById('searchMidwives');
            const clearBtn = document.getElementById('searchMidwivesClear');
            const wrap = document.getElementById('midwivesSearchWrap');

            function toggleClear() {
                const hasValue = input.value.trim().length > 0;
                wrap.classList.toggle('data-table-search--has-value', hasValue);
            }
            input.addEventListener('input', toggleClear);
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClear();
                if (typeof getMidwives === 'function') {
                    getMidwives(1);
                }
                input.focus();
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (typeof getMidwives === 'function') {
                        getMidwives(1);
                    }
                }
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