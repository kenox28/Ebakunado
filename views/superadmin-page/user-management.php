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
    <title>User Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/user-management.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="user-header">
            <h1 class="user-title">User Management</h1>
            <p class="user-subtitle">Manage user accounts and their information.</p>
        </div>

        <div class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">User Accounts</h2>
                    </div>
                    <div class="data-table-actions">
                        <div class="data-table-search" id="usersSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="searchUsers" class="data-table-search__input" placeholder="Search users..." data-remote-search="true" />
                            <button type="button" id="searchUsersClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <button type="button" onclick="showAddUserForm()" class="btn btn-primary btn-icon">
                            <span class="material-symbols-rounded">add</span>
                            Add New User
                        </button>
                        <button type="button" onclick="deleteSelectedUsers()" class="btn btn-danger btn-icon">
                            <span class="material-symbols-rounded">delete</span>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()"></th>
                                <th scope="col">User ID</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Place</th>
                                <th scope="col">Role</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="11">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pager">
                    <p class="page-info" id="usersPageInfo">Showing 0-0 of 0</p>
                    <div class="pager-controls">
                        <button type="button" class="pager-btn" id="usersPrevBtn" data-page="1" disabled>
                            <span class="material-symbols-rounded">chevron_left</span>
                            Prev
                        </button>
                        <button type="button" class="pager-btn" id="usersNextBtn" data-page="1" disabled>
                            Next
                            <span class="material-symbols-rounded">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div id="addUserModal" class="modal-overlay" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addUserModalTitle">
                <div class="modal-header">
                    <h3 class="modal-title">Add New User</h3>
                    <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="cancelAddUser()">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div id="addUserForm" class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_user_fname">First Name</label>
                            <input type="text" id="add_user_fname" name="fname" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_lname">Last Name</label>
                            <input type="text" id="add_user_lname" name="lname" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_email">Email</label>
                            <input type="email" id="add_user_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_user_phone">Phone Number</label>
                            <input type="text" id="add_user_phone" name="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_gender">Gender</label>
                            <select id="add_user_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_user_password">Password</label>
                            <input type="password" id="add_user_password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_confirm_password">Confirm Password</label>
                            <input type="password" id="add_user_confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_user_province">Province</label>
                            <input type="text" id="add_user_province" name="province" placeholder="Enter province" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_city_municipality">City/Municipality</label>
                            <input type="text" id="add_user_city_municipality" name="city_municipality" placeholder="Enter city/municipality" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_barangay">Barangay</label>
                            <input type="text" id="add_user_barangay" name="barangay" placeholder="Enter barangay" required>
                        </div>
                        <div class="form-group">
                            <label for="add_user_purok">Purok</label>
                            <input type="text" id="add_user_purok" name="purok" placeholder="Enter purok" required>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="cancelAddUser()" class="btn btn-secondary btn-icon">
                        Cancel
                    </button>
                    <button type="button" onclick="saveUser()" class="btn btn-primary btn-icon">
                        Save User
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div id="editUserModal" class="modal-overlay" hidden>
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editUserModalTitle">
                <div class="modal-header">
                    <h3 id="editUserModalTitle" class="modal-title">Edit User</h3>
                    <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="cancelEditUser()">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div id="editUserForm" class="modal-body">
                    <!-- Dynamic edit form will be injected here -->
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="cancelEditUser()" class="btn btn-secondary btn-icon">
                        Cancel
                    </button>
                    <button type="button" onclick="updateUser()" class="btn btn-primary btn-icon">
                        Update User
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/common.js?v=1.0.5"></script>
    <script src="js/supabase_js/superadmin/user-management.js?v=1.0.5"></script>
    <script>
        (function() {
            // Search clear toggle
            const input = document.getElementById('searchUsers');
            const clearBtn = document.getElementById('searchUsersClear');
            const wrap = document.getElementById('usersSearchWrap');

            function toggleClear() {
                const hasValue = input.value.trim().length > 0;
                wrap.classList.toggle('data-table-search--has-value', hasValue);
            }
            input.addEventListener('input', toggleClear);
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClear();
                if (typeof getUsers === 'function') {
                    getUsers(1);
                }
                input.focus();
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (typeof getUsers === 'function') {
                        getUsers(1);
                    }
                }
            });
            toggleClear();

            // Generic modal helpers
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