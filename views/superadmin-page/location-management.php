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
    <title>Location Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/location-management.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="location-header">
            <h1 class="location-title">Location Management</h1>
            <p class="location-subtitle">Manage locations (Province, City, Barangay, Purok).</p>
        </div>

        <div class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">Location Details</h2>
                    </div>
                    <div class="data-table-actions">
                        <div class="data-table-search" id="locationsSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="searchLocations" class="data-table-search__input" placeholder="Search locations..." />
                            <button type="button" id="searchLocationsClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <button type="button" onclick="showAddLocationForm()" class="btn btn-primary btn-icon">
                            <span class="material-symbols-rounded">add</span>
                            Add New Location
                        </button>
                        <button type="button" onclick="deleteSelectedLocations()" class="btn btn-danger btn-icon">
                            <span class="material-symbols-rounded">delete</span>
                            Delete Selected
                        </button>
                    </div>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAllLocations" onchange="toggleAllLocations()"></th>
                                <th scope="col">ID</th>
                                <th scope="col">Province</th>
                                <th scope="col">City/Municipality</th>
                                <th scope="col">Barangay</th>
                                <th scope="col">Purok</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="locationsTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="8">Loading locations...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Location Modal -->
        <div id="addLocationModal" class="modal-overlay" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addLocationModalTitle">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Location</h3>
                    <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="cancelAddLocation()">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div id="addLocationForm" class="modal-body">
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
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="cancelAddLocation()" class="btn btn-secondary btn-icon">
                        Cancel
                    </button>
                    <button type="button" onclick="saveLocation()" class="btn btn-primary btn-icon">
                        Save Location
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.4"></script>
    <script src="js/supabase_js/superadmin/location-management.js?v=1.0.3"></script>
    <script>
        // Search clear toggle for Locations
        (function() {
            const input = document.getElementById('searchLocations');
            const clearBtn = document.getElementById('searchLocationsClear');
            const wrap = document.getElementById('locationsSearchWrap');

            function toggleClear() {
                const hasValue = input.value.trim().length > 0;
                wrap.classList.toggle('data-table-search--has-value', hasValue);
            }
            input.addEventListener('input', toggleClear);
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClear();
                if (typeof clearSearch === 'function') {
                    clearSearch('searchLocations', 'locationsTableBody');
                }
                input.focus();
            });
            toggleClear();

            // Generic modal helpers for modal popups (if needed in future)
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