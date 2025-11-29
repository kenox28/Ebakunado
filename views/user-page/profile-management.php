<?php
session_start();

// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login");
    exit();
}

// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fullname = trim(($fname ?? '') . ' ' . ($lname ?? ''));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css?v=1.0.2" />
    <link rel="stylesheet" href="css/header.css?v=1.0.2" />
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.3" />
    <link rel="stylesheet" href="css/notification-style.css?v=1.0.2" />
    <link rel="stylesheet" href="css/bhw/profile-management.css?v=1.0.5" />
    <link rel="stylesheet" href="css/bhw/table-style.css?v=1.0.4" />
    <style>
        .password-requirements .requirement-item.met {
            color: #28a745;
        }
        .password-requirements .requirement-item.met .requirement-icon {
            color: #28a745;
        }
        .password-requirements .requirement-item.met .requirement-icon::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
        }
        .password-requirements .requirement-item:not(.met) .requirement-icon::before {
            content: "";
        }
    </style>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="profile-management-section">
            <div class="page-header">
                <h1 class="page-title">Profile Management</h1>
                <p class="page-subtitle">Manage your personal information and account settings.</p>
            </div>

            <div class="profile-info-section">
                <div class="profile-avatar">
                    <?php $profile_img = $_SESSION['profileimg'] ?? 'assets/images/user-profile.png'; ?>
                    <img
                        class="avatar-img" id="profileImage"
                        src="<?php echo htmlspecialchars($profile_img); ?>"
                        alt="<?php echo htmlspecialchars($user_fullname ?: 'User'); ?>" />

                    <!-- overlay camera button -->
                    <button class="change-photo-btn" onclick="document.getElementById('photoInput').click()" aria-label="Change profile photo">
                        <span class="material-symbols-rounded">camera_alt</span>
                    </button>
                    <input type="file" id="photoInput" accept="image/*" style="display: none;" onchange="uploadProfilePhoto()">
                </div>

                <div class="profile-info">
                    <div class="profile-info-item">
                        <div class="profile-info-label">Name</div>
                        <h3 class="profile-name"><?php echo htmlspecialchars($user_fullname ?: $fname); ?></h3>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">Role</div>
                        <p class="profile-role">User / Parent</p>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">Email</div>
                        <p class="profile-email"><?php echo htmlspecialchars($email ?: '—'); ?></p>
                    </div>
                </div>
            </div>

            <div class="profile-panels">
                <div class="panel">
                    <div class="panel-header">
                        <span class="material-symbols-rounded">person</span>
                        <span>Personal information</span>
                    </div>
                    <div class="panel-body">
                        <div class="panel-row" data-field="name" tabindex="0" role="button" aria-label="Edit name">
                            <div class="row-label">Name</div>
                            <div class="row-value" id="row-name">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="place" tabindex="0" role="button" aria-label="Edit place">
                            <div class="row-label">Place</div>
                            <div class="row-value" id="row-place">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="phone_number" tabindex="0" role="button" aria-label="Edit phone number">
                            <div class="row-label">Phone number</div>
                            <div class="row-value" id="row-phone">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="gender" tabindex="0" role="button" aria-label="Edit gender">
                            <div class="row-label">Gender</div>
                            <div class="row-value" id="row-gender">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="philhealth_no" tabindex="0" role="button" aria-label="Edit PhilHealth number">
                            <div class="row-label">PhilHealth No.</div>
                            <div class="row-value" id="row-philhealth">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="nhts" tabindex="0" role="button" aria-label="Edit NHTS">
                            <div class="row-label">National Household Targeting System</div>
                            <div class="row-value" id="row-nhts">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <span class="material-symbols-rounded">settings</span>
                        <span>Account settings</span>
                    </div>
                    <div class="panel-body">
                        <div class="panel-row" data-field="email" tabindex="0" role="button" aria-label="Edit email">
                            <div class="row-label">Email</div>
                            <div class="row-value" id="row-email">Loading...</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                        <div class="panel-row" data-field="password" tabindex="0" role="button" aria-label="Change password">
                            <div class="row-label">Password</div>
                            <div class="row-value">••••••••</div>
                            <div class="row-action">
                                <span class="material-symbols-rounded">
                                    chevron_forward
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for editing a single row -->
            <div id="editModal" class="modal-overlay" hidden>
                <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
                    <div class="modal-header">
                        <h3 id="editModalTitle" class="modal-title">Edit</h3>
                        <button type="button" class="modal-close action-icon-btn" id="modalClose" aria-label="Close">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="modalForm" novalidate>
                            <div class="form-row" id="modalFields">
                                <!-- fields will be injected/shown by JS -->
                                <div class="form-group" id="group-fname" style="display: none;">
                                    <label for="modal_fname">First name</label>
                                    <input type="text" id="modal_fname" class="form-control" data-field="fname">
                                </div>
                                <div class="form-group" id="group-lname" style="display: none;">
                                    <label for="modal_lname">Last name</label>
                                    <input type="text" id="modal_lname" class="form-control" data-field="lname">
                                </div>
                                <div class="form-group" id="group-email" style="display: none;">
                                    <label for="modal_email">Email</label>
                                    <input type="email" id="modal_email" class="form-control" data-field="email">
                                </div>
                                <div class="form-group" id="group-phone" style="display: none;">
                                    <label for="modal_phone">Phone number</label>
                                    <input type="tel" id="modal_phone" class="form-control" data-field="phone_number">
                                </div>
                                <div class="form-group" id="group-place" style="display: none;">
                                    <label>Address</label>
                                    <div class="address-input-group" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        <input type="text" id="modal_place_province" class="form-control" placeholder="Province" style="flex: 1; min-width: 120px;">
                                        <span style="color: #666; font-weight: 500;">,</span>
                                        <input type="text" id="modal_place_city" class="form-control" placeholder="City" style="flex: 1; min-width: 120px;">
                                        <span style="color: #666; font-weight: 500;">,</span>
                                        <input type="text" id="modal_place_barangay" class="form-control" placeholder="Barangay" style="flex: 1; min-width: 120px;">
                                        <span style="color: #666; font-weight: 500;">,</span>
                                        <input type="text" id="modal_place_purok" class="form-control" placeholder="Purok" style="flex: 1; min-width: 120px;">
                                    </div>
                                    <input type="hidden" id="modal_place" data-field="place">
                                </div>
                                <div class="form-group" id="group-gender" style="display: none;">
                                    <label for="modal_gender">Gender</label>
                                    <select id="modal_gender" class="form-control" data-field="gender">
                                        <option value="" disabled selected>Select gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group" id="group-philhealth_no" style="display: none;">
                                    <label for="modal_philhealth_no">PhilHealth No.</label>
                                    <input type="text" id="modal_philhealth_no" class="form-control" data-field="philhealth_no" placeholder="Optional">
                                </div>
                                <div class="form-group" id="group-nhts" style="display: none;">
                                    <label for="modal_nhts">National Household Targeting System</label>
                                    <select id="modal_nhts" class="form-control" data-field="nhts">
                                        <option value="" disabled selected>Select option</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div id="password-fields">
                                    <div class="form-group" id="group-current_password" style="display: none;">
                                        <label for="modal_current_password">Current password</label>
                                        <input type="password" id="modal_current_password" class="form-control" data-field="current_password">
                                    </div>
                                    <div class="form-group" id="group-new_password" style="display: none;">
                                        <label for="modal_new_password">New password</label>
                                        <input type="password" id="modal_new_password" class="form-control" data-field="new_password">
                                        <!-- Password Requirements List -->
                                        <div id="passwordRequirements" class="password-requirements" style="display: none; margin-top: 0.8rem; padding: 1rem; background: #f8f9fa; border-radius: 6px; border: 1px solid #e0e0e0;">
                                            <div class="requirement-item" id="req-length" style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.2rem; color: #666; margin-bottom: 0.6rem; transition: all 0.3s ease;">
                                                <span class="requirement-icon" style="font-size: 1.4rem; color: #999; transition: all 0.3s ease; width: 1.6rem; text-align: center;"></span>
                                                <span class="requirement-text" style="flex: 1;">At least 8 characters</span>
                                            </div>
                                            <div class="requirement-item" id="req-uppercase" style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.2rem; color: #666; margin-bottom: 0.6rem; transition: all 0.3s ease;">
                                                <span class="requirement-icon" style="font-size: 1.4rem; color: #999; transition: all 0.3s ease; width: 1.6rem; text-align: center;"></span>
                                                <span class="requirement-text" style="flex: 1;">1 uppercase letter</span>
                                            </div>
                                            <div class="requirement-item" id="req-lowercase" style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.2rem; color: #666; margin-bottom: 0.6rem; transition: all 0.3s ease;">
                                                <span class="requirement-icon" style="font-size: 1.4rem; color: #999; transition: all 0.3s ease; width: 1.6rem; text-align: center;"></span>
                                                <span class="requirement-text" style="flex: 1;">1 lowercase letter</span>
                                            </div>
                                            <div class="requirement-item" id="req-number" style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.2rem; color: #666; margin-bottom: 0.6rem; transition: all 0.3s ease;">
                                                <span class="requirement-icon" style="font-size: 1.4rem; color: #999; transition: all 0.3s ease; width: 1.6rem; text-align: center;"></span>
                                                <span class="requirement-text" style="flex: 1;">1 number</span>
                                            </div>
                                            <div class="requirement-item" id="req-special" style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.2rem; color: #666; margin-bottom: 0; transition: all 0.3s ease;">
                                                <span class="requirement-icon" style="font-size: 1.4rem; color: #999; transition: all 0.3s ease; width: 1.6rem; text-align: center;"></span>
                                                <span class="requirement-text" style="flex: 1;">1 special character</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id="group-confirm_password" style="display: none;">
                                        <label for="modal_confirm_password">Confirm new password</label>
                                        <input type="password" id="modal_confirm_password" class="form-control" data-field="confirm_password">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary btn-icon" id="modalCancel">Cancel</button>
                        <button type="button" class="btn btn-primary btn-icon" id="modalSave">Save</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="js/utils/ui-feedback.js"></script>
    <script src="js/header-handler/profile-menu.js?v=1.0.2" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/auth-handler/password-toggle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
        });

        async function loadProfileData() {
            try {
                const response = await fetch('php/supabase/users/get_profile_data.php');
                const data = await response.json();
                console.debug('get_profile_data response:', data);

                if (data.status === 'success') {
                    const profile = data.data;
                    const fname = profile.fname || '';
                    const lname = profile.lname || '';
                    const email = profile.email || '';
                    const phone = profile.phone_number || '';
                    const gender = profile.gender || '';
                    const place = profile.place || '';
                    const philhealth_no = profile.philhealth_no || '';
                    const nhts = profile.nhts || '';

                    // header/profile area
                    const displayNameEl = document.querySelector('.profile-name');
                    if (displayNameEl) displayNameEl.textContent = `${fname} ${lname}`.trim() || 'Loading...';
                    const displayRoleEl = document.querySelector('.profile-role');
                    if (displayRoleEl) displayRoleEl.textContent = 'User / Parent';
                    const displayEmailEl = document.querySelector('.profile-email');
                    if (displayEmailEl) displayEmailEl.textContent = email || 'Loading...';

                    // panel values - populate .row-value elements
                    const nameEl = document.getElementById('row-name');
                    if (nameEl) nameEl.textContent = (fname + ' ' + lname).trim() || '—';
                    const emailEl = document.getElementById('row-email');
                    if (emailEl) emailEl.textContent = email || '—';
                    const phoneEl = document.getElementById('row-phone');
                    if (phoneEl) phoneEl.textContent = phone || '—';
                    const genderEl = document.getElementById('row-gender');
                    if (genderEl) genderEl.textContent = gender || '—';
                    const placeEl = document.getElementById('row-place');
                    if (placeEl) placeEl.textContent = place || '—';
                    const philhealthEl = document.getElementById('row-philhealth');
                    if (philhealthEl) philhealthEl.textContent = philhealth_no || '—';
                    const nhtsEl = document.getElementById('row-nhts');
                    if (nhtsEl) nhtsEl.textContent = nhts || '—';

                    // Prefill modal inputs so editing works immediately
                    const safe = v => v === null || v === undefined ? '' : v;
                    const setIf = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.value = value;
                    };
                    setIf('modal_fname', safe(fname));
                    setIf('modal_lname', safe(lname));
                    setIf('modal_email', safe(email));
                    setIf('modal_phone', safe(phone));
                    setIf('modal_gender', safe(gender));
                    setIf('modal_place', safe(place));
                    setIf('modal_philhealth_no', safe(philhealth_no));
                    setIf('modal_nhts', safe(nhts));

                    // Update profile image
                    if (profile.profileimg && profile.profileimg !== 'noprofile.png') {
                        const img = document.getElementById('profileImage');
                        if (img) img.src = profile.profileimg;
                        // also update header and sidebar avatars if present
                        try {
                            const headerAvatar = document.querySelector('.header .user-avatar');
                            if (headerAvatar) headerAvatar.src = profile.profileimg;
                            const sidebarAvatar = document.querySelector('.sidebar .profile-avatar');
                            if (sidebarAvatar && sidebarAvatar.tagName === 'IMG') sidebarAvatar.src = profile.profileimg;
                        } catch (e) {}
                    }

                    // Also sync header/profile-menu display values so header shows the same info
                    try {
                        const headerName = document.querySelector('.header .user-name');
                        const headerRole = document.querySelector('.header .user-role');
                        const profileMenuName = document.querySelector('#profileMenu .profile-card .info .name');
                        const profileMenuRole = document.querySelector('#profileMenu .profile-card .info .role');
                        const profileMenuEmail = document.querySelector('#profileMenu .profile-card .info .email');
                        const fullName = ((fname || '') + ' ' + (lname || '')).trim();
                        if (headerName && fullName) headerName.textContent = fullName;
                        if (headerRole) headerRole.textContent = 'User / Parent';
                        if (profileMenuName && fullName) profileMenuName.textContent = fullName;
                        if (profileMenuRole) profileMenuRole.textContent = 'User / Parent';
                        if (profileMenuEmail && email) profileMenuEmail.textContent = email;
                    } catch (e) {}
                } else {
                    console.warn('Profile load warning:', data);
                    try {
                        if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                            UIFeedback.showToast({
                                title: 'Profile load',
                                message: data.message || 'No profile data',
                                variant: 'warning'
                            });
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                    UIFeedback.showToast({
                        title: 'Error',
                        message: 'Failed to load profile data',
                        variant: 'error'
                    });
                }
            }
        }

        async function uploadProfilePhoto() {
            const file = document.getElementById('photoInput').files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('photo', file);
            try {
                const response = await fetch('php/supabase/users/upload_profile_photo.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    document.getElementById('profileImage').src = data.imageUrl;
                    try {
                        var headerAvatar = document.querySelector('.header .user-avatar');
                        if (headerAvatar) headerAvatar.src = data.imageUrl;
                        var sidebarAvatar = document.querySelector('.sidebar .profile-avatar');
                        if (sidebarAvatar && sidebarAvatar.tagName === 'IMG') sidebarAvatar.src = data.imageUrl;
                    } catch (e) {}
                    UIFeedback.showToast({
                        title: 'Profile updated',
                        message: 'Profile photo updated successfully.',
                        variant: 'success'
                    });
                } else {
                    UIFeedback.showToast({
                        title: 'Upload failed',
                        message: data.message || 'Unable to update profile photo.',
                        variant: 'error'
                    });
                }
            } catch (error) {
                UIFeedback.showToast({
                    title: 'Upload failed',
                    message: 'Failed to upload photo.',
                    variant: 'error'
                });
            }
        }
    </script>
    <script>
        // Panel/modal handling
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPanelClicks, 100);
        });

        function initPanelClicks() {
            const rows = document.querySelectorAll('.panel-row');
            if (!rows || rows.length === 0) {
                console.log('initPanelClicks: no .panel-row found');
                return;
            }
            rows.forEach(el => {
                try {
                    el.removeEventListener('click', onPanelClick);
                    el.addEventListener('click', onPanelClick);
                    el.removeEventListener('keydown', onPanelKeyDown);
                    el.addEventListener('keydown', onPanelKeyDown);
                } catch (e) {
                    console.error('initPanelClicks attach error', e);
                }
            });
        }

        function onPanelClick(e) {
            const field = e.currentTarget ? e.currentTarget.dataset.field : this.dataset.field;
            openEditModal(field);
        }

        function onPanelKeyDown(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const field = e.currentTarget.dataset.field;
                openEditModal(field);
            }
        }

        function openEditModal(field) {
            // hide all groups first
            ['fname', 'lname', 'email', 'phone', 'place', 'gender', 'philhealth_no', 'nhts', 'current_password', 'new_password', 'confirm_password'].forEach(k => {
                const g = document.getElementById('group-' + k);
                if (g) {
                    g.style.display = 'none';
                    const input = g.querySelector('input, select');
                    if (input && input.hasAttribute('name')) {
                        input.removeAttribute('name');
                    }
                }
            });

            // Show groups depending on field and add name attributes for visible fields
            if (field === 'name') {
                document.getElementById('group-fname').style.display = '';
                document.getElementById('group-lname').style.display = '';
                document.getElementById('modal_fname').setAttribute('name', 'fname');
                document.getElementById('modal_lname').setAttribute('name', 'lname');
                document.getElementById('editModalTitle').textContent = 'Edit name';
            } else if (field === 'email') {
                document.getElementById('group-email').style.display = '';
                document.getElementById('modal_email').setAttribute('name', 'email');
                document.getElementById('editModalTitle').textContent = 'Edit email';
            } else if (field === 'phone_number') {
                document.getElementById('group-phone').style.display = '';
                document.getElementById('modal_phone').setAttribute('name', 'phone_number');
                document.getElementById('editModalTitle').textContent = 'Edit phone number';
            } else if (field === 'gender') {
                document.getElementById('group-gender').style.display = '';
                document.getElementById('modal_gender').setAttribute('name', 'gender');
                document.getElementById('editModalTitle').textContent = 'Edit gender';
            } else if (field === 'place') {
                document.getElementById('group-place').style.display = '';
                document.getElementById('modal_place').setAttribute('name', 'place');
                document.getElementById('editModalTitle').textContent = 'Edit place';
            } else if (field === 'philhealth_no') {
                document.getElementById('group-philhealth_no').style.display = '';
                document.getElementById('modal_philhealth_no').setAttribute('name', 'philhealth_no');
                document.getElementById('editModalTitle').textContent = 'Edit PhilHealth number';
            } else if (field === 'nhts') {
                document.getElementById('group-nhts').style.display = '';
                document.getElementById('modal_nhts').setAttribute('name', 'nhts');
                document.getElementById('editModalTitle').textContent = 'Edit NHTS';
            } else if (field === 'password') {
                document.getElementById('group-current_password').style.display = '';
                document.getElementById('group-new_password').style.display = '';
                document.getElementById('group-confirm_password').style.display = '';
                document.getElementById('modal_current_password').setAttribute('name', 'current_password');
                document.getElementById('modal_new_password').setAttribute('name', 'new_password');
                document.getElementById('modal_confirm_password').setAttribute('name', 'confirm_password');
                document.getElementById('editModalTitle').textContent = 'Change password';
                // Initialize password validation
                initPasswordValidation();
            }

            // Prefill modal inputs
            const readFrom = (id) => {
                const el = document.getElementById(id);
                return el ? el.value : null;
            };

            const rowText = (id) => {
                const el = document.getElementById(id);
                return el ? el.textContent.trim() : '';
            };

            let fnameVal = readFrom('modal_fname');
            let lnameVal = readFrom('modal_lname');
            let emailVal = readFrom('modal_email');
            let phoneVal = readFrom('modal_phone');
            let genderVal = readFrom('modal_gender');
            let placeVal = readFrom('modal_place');
            let philhealthVal = readFrom('modal_philhealth_no');
            let nhtsVal = readFrom('modal_nhts');

            if (!fnameVal || !lnameVal) {
                const nameParts = rowText('row-name').split(' ');
                fnameVal = fnameVal || nameParts[0] || '';
                lnameVal = lnameVal || nameParts.slice(1).join(' ') || '';
            }
            emailVal = emailVal || rowText('row-email');
            phoneVal = phoneVal || rowText('row-phone');
            genderVal = genderVal || rowText('row-gender');
            placeVal = placeVal || rowText('row-place');
            philhealthVal = philhealthVal || rowText('row-philhealth');
            nhtsVal = nhtsVal || rowText('row-nhts');

            // Parse place value (comma-separated: province, city, barangay, purok)
            let placeParts = ['', '', '', ''];
            if (placeVal) {
                const parts = placeVal.split(',').map(p => p.trim());
                placeParts[0] = parts[0] || '';
                placeParts[1] = parts[1] || '';
                placeParts[2] = parts[2] || '';
                placeParts[3] = parts[3] || '';
            }

            const setModalValue = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.value = value || '';
            };
            setModalValue('modal_fname', fnameVal);
            setModalValue('modal_lname', lnameVal);
            setModalValue('modal_email', emailVal);
            setModalValue('modal_phone', phoneVal);
            setModalValue('modal_gender', genderVal);
            setModalValue('modal_place_province', placeParts[0]);
            setModalValue('modal_place_city', placeParts[1]);
            setModalValue('modal_place_barangay', placeParts[2]);
            setModalValue('modal_place_purok', placeParts[3]);
            setModalValue('modal_philhealth_no', philhealthVal);
            setModalValue('modal_nhts', nhtsVal);
            setModalValue('modal_current_password', '');
            setModalValue('modal_new_password', '');
            setModalValue('modal_confirm_password', '');

            document.getElementById('modalForm').dataset.field = field;

            const overlay = document.getElementById('editModal');
            if (overlay) {
                overlay.removeAttribute('hidden');
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                try {
                    document.body.style.overflow = 'hidden';
                } catch (e) {}
                setTimeout(() => {
                    const visibleInputs = overlay.querySelectorAll('input:not([type="hidden"]), select, textarea');
                    for (let input of visibleInputs) {
                        if (input.offsetParent !== null) {
                            input.focus();
                            break;
                        }
                    }
                }, 50);
            }
        }

        function closeModal() {
            const overlay = document.getElementById('editModal');
            if (overlay) {
                overlay.classList.remove('is-open');
                setTimeout(() => {
                    overlay.setAttribute('hidden', 'true');
                    overlay.setAttribute('aria-hidden', 'true');
                    // Reset password validation flag when modal closes
                    passwordValidationInitialized = false;
                    // Hide password requirements
                    const passwordRequirements = document.getElementById('passwordRequirements');
                    if (passwordRequirements) {
                        passwordRequirements.style.display = 'none';
                    }
                }, 180);
                try {
                    document.body.style.overflow = '';
                } catch (e) {}
            }
        }

        // Password validation functions
        let passwordValidationInitialized = false;
        function initPasswordValidation() {
            const passwordInput = document.getElementById('modal_new_password');
            const passwordRequirements = document.getElementById('passwordRequirements');
            
            if (!passwordInput || !passwordRequirements || passwordValidationInitialized) return;

            // Show requirements when password field is focused
            passwordInput.addEventListener('focus', function() {
                if (passwordRequirements) {
                    passwordRequirements.style.display = 'block';
                }
            });

            // Hide requirements when password field is blurred (if password is valid)
            passwordInput.addEventListener('blur', function() {
                if (passwordRequirements) {
                    const password = this.value;
                    const isValid = validatePasswordRequirements(password);
                    if (isValid) {
                        setTimeout(() => {
                            if (passwordInput !== document.activeElement) {
                                passwordRequirements.style.display = 'none';
                            }
                        }, 200);
                    }
                }
            });

            // Real-time password validation
            passwordInput.addEventListener('input', function() {
                validatePasswordRealTime(this.value);
            });

            passwordValidationInitialized = true;
        }

        function validatePasswordRealTime(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            // Update each requirement indicator
            const reqLength = document.getElementById('req-length');
            if (reqLength) {
                if (requirements.length) {
                    reqLength.classList.add('met');
                } else {
                    reqLength.classList.remove('met');
                }
            }

            const reqUppercase = document.getElementById('req-uppercase');
            if (reqUppercase) {
                if (requirements.uppercase) {
                    reqUppercase.classList.add('met');
                } else {
                    reqUppercase.classList.remove('met');
                }
            }

            const reqLowercase = document.getElementById('req-lowercase');
            if (reqLowercase) {
                if (requirements.lowercase) {
                    reqLowercase.classList.add('met');
                } else {
                    reqLowercase.classList.remove('met');
                }
            }

            const reqNumber = document.getElementById('req-number');
            if (reqNumber) {
                if (requirements.number) {
                    reqNumber.classList.add('met');
                } else {
                    reqNumber.classList.remove('met');
                }
            }

            const reqSpecial = document.getElementById('req-special');
            if (reqSpecial) {
                if (requirements.special) {
                    reqSpecial.classList.add('met');
                } else {
                    reqSpecial.classList.remove('met');
                }
            }
        }

        function validatePasswordRequirements(password) {
            return password.length >= 8 &&
                   /[A-Z]/.test(password) &&
                   /[a-z]/.test(password) &&
                   /[0-9]/.test(password) &&
                   /[^A-Za-z0-9]/.test(password);
        }

        const modalCloseBtn = document.getElementById('modalClose');
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
        const modalCancelBtn = document.getElementById('modalCancel');
        if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);

        const modalSaveBtn = document.getElementById('modalSave');
        if (modalSaveBtn) {
            modalSaveBtn.addEventListener('click', function() {
                const form = document.getElementById('modalForm');
                if (form && typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else if (form) {
                    form.dispatchEvent(new Event('submit', {
                        cancelable: true
                    }));
                }
            });
        }

        const modalFormEl = document.getElementById('modalForm');
        if (modalFormEl) {
            modalFormEl.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const field = this.dataset.field;
                if (!field) {
                    console.error('No field specified for update');
                    return;
                }

                let isValid = true;
                let errorMessage = '';

                if (field === 'name') {
                    const fname = document.getElementById('modal_fname').value.trim();
                    const lname = document.getElementById('modal_lname').value.trim();
                    if (!fname || !lname) {
                        isValid = false;
                        errorMessage = 'First name and last name are required';
                    }
                } else if (field === 'email') {
                    const email = document.getElementById('modal_email').value.trim();
                    if (!email) {
                        isValid = false;
                        errorMessage = 'Email is required';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                } else if (field === 'phone_number') {
                    const phone = document.getElementById('modal_phone').value.trim();
                    if (!phone) {
                        isValid = false;
                        errorMessage = 'Phone number is required';
                    }
                } else if (field === 'gender') {
                    const gender = document.getElementById('modal_gender').value;
                    if (!gender) {
                        isValid = false;
                        errorMessage = 'Please select a gender';
                    }
                } else if (field === 'place') {
                    const province = document.getElementById('modal_place_province').value.trim();
                    const city = document.getElementById('modal_place_city').value.trim();
                    const barangay = document.getElementById('modal_place_barangay').value.trim();
                    const purok = document.getElementById('modal_place_purok').value.trim();
                    if (!province && !city && !barangay && !purok) {
                        isValid = false;
                        errorMessage = 'Please fill in at least one address field';
                    }
                } else if (field === 'philhealth_no') {
                    // PhilHealth is optional, no validation needed
                } else if (field === 'nhts') {
                    const nhts = document.getElementById('modal_nhts').value;
                    if (!nhts) {
                        isValid = false;
                        errorMessage = 'Please select an option';
                    }
                } else if (field === 'password') {
                    const current = document.getElementById('modal_current_password').value;
                    const newPass = document.getElementById('modal_new_password').value;
                    const confirm = document.getElementById('modal_confirm_password').value;
                    if (!current || !newPass || !confirm) {
                        isValid = false;
                        errorMessage = 'All password fields are required';
                    } else if (newPass !== confirm) {
                        isValid = false;
                        errorMessage = 'New passwords do not match';
                    } else if (!validatePasswordRequirements(newPass)) {
                        isValid = false;
                        errorMessage = 'Password must meet all requirements: at least 8 characters, 1 uppercase, 1 lowercase, 1 number, and 1 special character';
                    }
                }

                if (!isValid) {
                    if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                        UIFeedback.showToast({
                            title: 'Validation Error',
                            message: errorMessage,
                            variant: 'error'
                        });
                    }
                    return;
                }

                const fd = new FormData();

                if (field === 'name') {
                    fd.append('fname', document.getElementById('modal_fname').value.trim());
                    fd.append('lname', document.getElementById('modal_lname').value.trim());
                } else if (field === 'email') {
                    fd.append('email', document.getElementById('modal_email').value.trim());
                } else if (field === 'phone_number') {
                    fd.append('phone_number', document.getElementById('modal_phone').value.trim());
                } else if (field === 'gender') {
                    fd.append('gender', document.getElementById('modal_gender').value);
                } else if (field === 'place') {
                    const province = document.getElementById('modal_place_province').value.trim();
                    const city = document.getElementById('modal_place_city').value.trim();
                    const barangay = document.getElementById('modal_place_barangay').value.trim();
                    const purok = document.getElementById('modal_place_purok').value.trim();
                    const combinedPlace = [province, city, barangay, purok].filter(p => p).join(', ');
                    fd.append('place', combinedPlace);
                } else if (field === 'philhealth_no') {
                    fd.append('philhealth_no', document.getElementById('modal_philhealth_no').value.trim());
                } else if (field === 'nhts') {
                    fd.append('nhts', document.getElementById('modal_nhts').value);
                } else if (field === 'password') {
                    fd.append('current_password', document.getElementById('modal_current_password').value);
                    fd.append('new_password', document.getElementById('modal_new_password').value);
                    fd.append('confirm_password', document.getElementById('modal_confirm_password').value);
                }

                const saveBtn = document.getElementById('modalSave');
                const originalText = saveBtn ? saveBtn.textContent : '';
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';
                }

                try {
                    const res = await fetch('php/supabase/users/update_profile.php', {
                        method: 'POST',
                        body: fd
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }

                    const data = await res.json();
                    console.log('Update response:', data);

                    if (data.status === 'success') {
                        if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                            UIFeedback.showToast({
                                title: 'Profile Updated',
                                message: data.message || 'Your profile has been updated successfully',
                                variant: 'success'
                            });
                        }

                        if (field === 'name') {
                            const fname = document.getElementById('modal_fname').value.trim();
                            const lname = document.getElementById('modal_lname').value.trim();
                            const nameEl = document.getElementById('row-name');
                            if (nameEl) nameEl.textContent = (fname + ' ' + lname).trim() || '—';
                            const displayNameEl = document.querySelector('.profile-name');
                            if (displayNameEl) displayNameEl.textContent = (fname + ' ' + lname).trim();
                            try {
                                const headerName = document.querySelector('.header .user-name');
                                if (headerName) headerName.textContent = (fname + ' ' + lname).trim();
                                const profileMenuName = document.querySelector('#profileMenu .profile-card .info .name');
                                if (profileMenuName) profileMenuName.textContent = (fname + ' ' + lname).trim();
                            } catch (e) {}
                        }
                        if (field === 'email') {
                            const email = document.getElementById('modal_email').value.trim();
                            const emailEl = document.getElementById('row-email');
                            if (emailEl) emailEl.textContent = email || '—';
                            const displayEmailEl = document.querySelector('.profile-email');
                            if (displayEmailEl) displayEmailEl.textContent = email;
                            try {
                                const profileMenuEmail = document.querySelector('#profileMenu .profile-card .info .email');
                                if (profileMenuEmail) profileMenuEmail.textContent = email;
                            } catch (e) {}
                        }
                        if (field === 'phone_number') {
                            const phone = document.getElementById('modal_phone').value.trim();
                            const phoneEl = document.getElementById('row-phone');
                            if (phoneEl) phoneEl.textContent = phone || '—';
                        }
                        if (field === 'gender') {
                            const gender = document.getElementById('modal_gender').value;
                            const genderEl = document.getElementById('row-gender');
                            if (genderEl) genderEl.textContent = gender || '—';
                        }
                        if (field === 'place') {
                            const province = document.getElementById('modal_place_province').value.trim();
                            const city = document.getElementById('modal_place_city').value.trim();
                            const barangay = document.getElementById('modal_place_barangay').value.trim();
                            const purok = document.getElementById('modal_place_purok').value.trim();
                            const combinedPlace = [province, city, barangay, purok].filter(p => p).join(', ');
                            const placeEl = document.getElementById('row-place');
                            if (placeEl) placeEl.textContent = combinedPlace || '—';
                        }
                        if (field === 'philhealth_no') {
                            const philhealth = document.getElementById('modal_philhealth_no').value.trim();
                            const philhealthEl = document.getElementById('row-philhealth');
                            if (philhealthEl) philhealthEl.textContent = philhealth || '—';
                        }
                        if (field === 'nhts') {
                            const nhts = document.getElementById('modal_nhts').value;
                            const nhtsEl = document.getElementById('row-nhts');
                            if (nhtsEl) nhtsEl.textContent = nhts || '—';
                        }

                        setTimeout(() => {
                            loadProfileData();
                        }, 500);
                        
                        setTimeout(() => {
                            closeModal();
                        }, 300);
                    } else {
                        if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                            UIFeedback.showToast({
                                title: 'Update Failed',
                                message: data.message || 'Unable to update profile. Please try again.',
                                variant: 'error'
                            });
                        }
                    }
                } catch (err) {
                    console.error('Update error:', err);
                    if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                        UIFeedback.showToast({
                            title: 'Update Failed',
                            message: 'Network error. Please check your connection and try again.',
                            variant: 'error'
                        });
                    }
                } finally {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = originalText;
                    }
                }
            });
        }

        const overlayEl = document.getElementById('editModal');
        if (overlayEl) {
            overlayEl.addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const overlay = document.getElementById('editModal');
                if (overlay && !overlay.hidden) closeModal();
            }
        });
    </script>
</body>

</html>
