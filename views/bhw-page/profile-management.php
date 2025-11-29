<?php
session_start();
// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();
?>
<?php
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
if ($user_types != 'midwifes') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}
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
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.2" />

    <link rel="stylesheet" href="css/notification-style.css?v=1.0.2" />
    <link rel="stylesheet" href="css/bhw/profile-management.css?v=1.0.5" />
    <link rel="stylesheet" href="css/bhw/table-style.css?v=1.0.4" />
    <!-- <link rel="stylesheet" href="css/modals.css?v=1.0.2" /> -->
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
                        alt="<?php echo htmlspecialchars(trim($user_fullname) ?: $user_name); ?>" />

                    <!-- overlay camera button -->
                    <button class="change-photo-btn" onclick="document.getElementById('photoInput').click()" aria-label="Change profile photo">
                        <span class="material-symbols-rounded">camera_alt</span>
                    </button>
                    <input type="file" id="photoInput" accept="image/*" style="display: none;" onchange="uploadProfilePhoto()">
                </div>

                <div class="profile-info">
                    <div class="profile-info-item">
                        <div class="profile-info-label">Name</div>
                        <h3 class="profile-name"><?php echo htmlspecialchars(trim($user_fullname) !== '' ? $user_fullname : $user_name); ?></h3>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">Role</div>
                        <p class="profile-role"><?php echo htmlspecialchars($user_type); ?></p>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">Email</div>
                        <p class="profile-email"><?php echo htmlspecialchars($_SESSION['email'] ?? '—'); ?></p>
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

            <!-- Modal for editing a single row (consistent with user-management) -->
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
                                <div id="password-fields">
                                    <div class="form-group" id="group-current_password" style="display: none;">
                                        <label for="modal_current_password">Current password</label>
                                        <input type="password" id="modal_current_password" class="form-control" data-field="current_password">
                                    </div>
                                    <div class="form-group" id="group-new_password" style="display: none;">
                                        <label for="modal_new_password">New password</label>
                                        <input type="password" id="modal_new_password" class="form-control" data-field="new_password">
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
    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/auth-handler/password-toggle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
        });

        async function loadProfileData() {
            try {
                const response = await fetch('php/supabase/shared/get_profile_data.php');
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

                    // header/profile area
                    // Update server-rendered profile-info elements (class-based)
                    const displayNameEl = document.querySelector('.profile-name');
                    if (displayNameEl) displayNameEl.textContent = `${fname} ${lname}`.trim() || 'Loading...';
                    const displayRoleEl = document.querySelector('.profile-role');
                    if (displayRoleEl) displayRoleEl.textContent = profile.user_type ? profile.user_type.charAt(0).toUpperCase() + profile.user_type.slice(1) : 'User';
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
                        if (headerRole && profile.user_type) headerRole.textContent = profile.user_type.charAt(0).toUpperCase() + profile.user_type.slice(1);
                        if (profileMenuName && fullName) profileMenuName.textContent = fullName;
                        if (profileMenuRole && profile.user_type) profileMenuRole.textContent = profile.user_type.charAt(0).toUpperCase() + profile.user_type.slice(1);
                        if (profileMenuEmail && email) profileMenuEmail.textContent = email;
                    } catch (e) {}
                } else {
                    console.warn('Profile load warning:', data);
                    // show a non-blocking toast if UIFeedback is available
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
                const response = await fetch('php/supabase/shared/upload_profile_photo.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                // Log debug info to console
                if (data.debug) {
                    console.log('=== Profile Photo Upload Debug Info ===');
                    console.log('User exists in users table:', data.debug.user_exists_in_users_table);
                    console.log('User user_id:', data.debug.user_user_id);
                    console.log('Email checked:', data.debug.email_checked);
                    console.log('Phone checked:', data.debug.phone_checked);
                    console.log('Sync attempted:', data.debug.sync_attempted);
                    console.log('Sync success:', data.debug.sync_success);
                    console.log('Full debug:', data.debug);
                }

                if (data.status === 'success') {
                    document.getElementById('profileImage').src = data.imageUrl;
                    try {
                        var headerAvatar = document.querySelector('.header .user-avatar');
                        if (headerAvatar) headerAvatar.src = data.imageUrl;
                        var sidebarAvatar = document.querySelector('.sidebar .profile-avatar');
                        if (sidebarAvatar) sidebarAvatar.src = data.imageUrl;
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

        // The original inline form was removed; guard any references to it.
        const profileFormEl = document.getElementById('profileForm');
        if (profileFormEl) {
            profileFormEl.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                try {
                    const response = await fetch('php/supabase/shared/update_profile.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        UIFeedback.showToast({
                            title: 'Profile updated',
                            message: 'Profile updated successfully.',
                            variant: 'success'
                        });
                        // reload profile data
                        loadProfileData();
                    } else {
                        UIFeedback.showToast({
                            title: 'Update failed',
                            message: data.message || 'Unable to update profile.',
                            variant: 'error'
                        });
                    }
                } catch (error) {
                    UIFeedback.showToast({
                        title: 'Update failed',
                        message: 'Failed to update profile.',
                        variant: 'error'
                    });
                }
            });
        }
    </script>
    <script>
        // Panel/modal handling
        document.addEventListener('DOMContentLoaded', function() {
            // loadProfileData already invoked above; ensure panels get values after data loads
            setTimeout(initPanelClicks, 100);
        });

        function populatePanels() {
            // Read from modal inputs (which are pre-filled by loadProfileData)
            const fname = document.getElementById('modal_fname') ? document.getElementById('modal_fname').value : '';
            const lname = document.getElementById('modal_lname') ? document.getElementById('modal_lname').value : '';
            const email = document.getElementById('modal_email') ? document.getElementById('modal_email').value : '';
            const phone = document.getElementById('modal_phone') ? document.getElementById('modal_phone').value : '';
            const gender = document.getElementById('modal_gender') ? document.getElementById('modal_gender').value : '';
            const place = document.getElementById('modal_place') ? document.getElementById('modal_place').value : '';

            const nameEl = document.getElementById('row-name');
            if (nameEl) nameEl.textContent = (fname || '') + (lname ? ' ' + lname : '') || '—';
            const emailEl = document.getElementById('row-email');
            if (emailEl) emailEl.textContent = email || '—';
            const phoneEl = document.getElementById('row-phone');
            if (phoneEl) phoneEl.textContent = phone || '—';
            const genderEl = document.getElementById('row-gender');
            if (genderEl) genderEl.textContent = gender || '—';
            const placeEl = document.getElementById('row-place');
            if (placeEl) placeEl.textContent = place || '—';
        }

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
                    // allow keyboard activation (enter / space)
                    el.removeEventListener('keydown', onPanelKeyDown);
                    el.addEventListener('keydown', onPanelKeyDown);
                    console.log('initPanelClicks: attached click to', el.dataset.field);
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
            // Enter or Space should open the modal
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const field = e.currentTarget.dataset.field;
                openEditModal(field);
            }
        }

        function openEditModal(field) {
            console.log('openEditModal called for field:', field);
            // hide all groups first
            ['fname', 'lname', 'email', 'phone', 'place', 'gender', 'current_password', 'new_password', 'confirm_password'].forEach(k => {
                const g = document.getElementById('group-' + k);
                if (g) {
                    g.style.display = 'none';
                    // Remove name attributes from hidden inputs to prevent validation errors
                    const input = g.querySelector('input, select');
                    if (input && input.hasAttribute('name')) {
                        input.removeAttribute('name');
                    }
                }
            });

            // Show groups depending on field and add name attributes for visible fields
            if (field === 'name') {
                const fnameGroup = document.getElementById('group-fname');
                const lnameGroup = document.getElementById('group-lname');
                fnameGroup.style.display = '';
                lnameGroup.style.display = '';
                document.getElementById('modal_fname').setAttribute('name', 'fname');
                document.getElementById('modal_lname').setAttribute('name', 'lname');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Edit name';
            } else if (field === 'email') {
                const emailGroup = document.getElementById('group-email');
                emailGroup.style.display = '';
                document.getElementById('modal_email').setAttribute('name', 'email');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Edit email';
            } else if (field === 'phone_number') {
                const phoneGroup = document.getElementById('group-phone');
                phoneGroup.style.display = '';
                document.getElementById('modal_phone').setAttribute('name', 'phone_number');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Edit phone number';
            } else if (field === 'gender') {
                const genderGroup = document.getElementById('group-gender');
                genderGroup.style.display = '';
                document.getElementById('modal_gender').setAttribute('name', 'gender');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Edit gender';
            } else if (field === 'place') {
                const placeGroup = document.getElementById('group-place');
                placeGroup.style.display = '';
                document.getElementById('modal_place').setAttribute('name', 'place');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Edit place';
            } else if (field === 'password') {
                document.getElementById('group-current_password').style.display = '';
                document.getElementById('group-new_password').style.display = '';
                document.getElementById('group-confirm_password').style.display = '';
                document.getElementById('modal_current_password').setAttribute('name', 'current_password');
                document.getElementById('modal_new_password').setAttribute('name', 'new_password');
                document.getElementById('modal_confirm_password').setAttribute('name', 'confirm_password');
                const titleEl = document.getElementById('editModalTitle');
                if (titleEl) titleEl.textContent = 'Change password';
            }

            // Prefill modal inputs: use values from modal inputs (pre-filled by loadProfileData)
            // or fall back to the visible panel values
            const readFrom = (id) => {
                const el = document.getElementById(id);
                return el ? el.value : null;
            };

            const rowText = (id) => {
                const el = document.getElementById(id);
                return el ? el.textContent.trim() : '';
            };

            // Try to read from modal inputs first (they should be pre-filled by loadProfileData)
            let fnameVal = readFrom('modal_fname');
            let lnameVal = readFrom('modal_lname');
            let emailVal = readFrom('modal_email');
            let phoneVal = readFrom('modal_phone');
            let genderVal = readFrom('modal_gender');
            let placeVal = readFrom('modal_place');

            // If modal inputs are empty, fall back to panel text values
            if (!fnameVal || !lnameVal) {
                const nameParts = rowText('row-name').split(' ');
                fnameVal = fnameVal || nameParts[0] || '';
                lnameVal = lnameVal || nameParts.slice(1).join(' ') || '';
            }
            emailVal = emailVal || rowText('row-email');
            phoneVal = phoneVal || rowText('row-phone');
            genderVal = genderVal || rowText('row-gender');
            placeVal = placeVal || rowText('row-place');

            // Parse place value (comma-separated: province, city, barangay, purok)
            let placeParts = ['', '', '', ''];
            if (placeVal) {
                const parts = placeVal.split(',').map(p => p.trim());
                placeParts[0] = parts[0] || '';
                placeParts[1] = parts[1] || '';
                placeParts[2] = parts[2] || '';
                placeParts[3] = parts[3] || '';
            }

            // Set modal input values
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
            setModalValue('modal_current_password', '');
            setModalValue('modal_new_password', '');
            setModalValue('modal_confirm_password', '');

            // store which field we're editing
            document.getElementById('modalForm').dataset.field = field;

            // open modal (overlay style) and lock background scrolling
            const overlay = document.getElementById('editModal');
            if (overlay) {
                overlay.removeAttribute('hidden');
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                // lock body scroll while modal is open
                try {
                    document.body.style.overflow = 'hidden';
                } catch (e) {}
                // Small delay to ensure CSS transition works
                setTimeout(() => {
                    // focus first visible input inside modal
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
                // Wait for transition before hiding
                setTimeout(() => {
                    overlay.setAttribute('hidden', 'true');
                    overlay.setAttribute('aria-hidden', 'true');
                }, 180);
                try {
                    document.body.style.overflow = '';
                } catch (e) {}
            }
        }

        const modalCloseBtn = document.getElementById('modalClose');
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
        const modalCancelBtn = document.getElementById('modalCancel');
        if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);

        // wire modal save button to submit the modal form
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

        // handle submit from modal - reuses existing update_profile endpoint
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

                // Basic validation for visible fields
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
                    } else if (newPass.length < 6) {
                        isValid = false;
                        errorMessage = 'New password must be at least 6 characters';
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

                // Build FormData with only the relevant fields
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
                } else if (field === 'password') {
                    fd.append('current_password', document.getElementById('modal_current_password').value);
                    fd.append('new_password', document.getElementById('modal_new_password').value);
                    fd.append('confirm_password', document.getElementById('modal_confirm_password').value);
                }

                // Show loading state
                const saveBtn = document.getElementById('modalSave');
                const originalText = saveBtn ? saveBtn.textContent : '';
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';
                }

                try {
                    const res = await fetch('php/supabase/shared/update_profile.php', {
                        method: 'POST',
                        body: fd
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }

                    const data = await res.json();
                    console.log('Update response:', data);

                    if (data.status === 'success') {
                        // Show success message
                        if (typeof UIFeedback !== 'undefined' && UIFeedback.showToast) {
                            UIFeedback.showToast({
                                title: 'Profile Updated',
                                message: data.message || 'Your profile has been updated successfully',
                                variant: 'success'
                            });
                        }

                        // Update panel values directly from modal inputs
                        if (field === 'name') {
                            const fname = document.getElementById('modal_fname').value.trim();
                            const lname = document.getElementById('modal_lname').value.trim();
                            const nameEl = document.getElementById('row-name');
                            if (nameEl) nameEl.textContent = (fname + ' ' + lname).trim() || '—';
                            // Update profile-management display (class-based)
                            const displayNameEl = document.querySelector('.profile-name');
                            if (displayNameEl) displayNameEl.textContent = (fname + ' ' + lname).trim();
                            // also update header (top-right) and profile menu in header
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
                            // Update profile-management display (class-based)
                            const displayEmailEl = document.querySelector('.profile-email');
                            if (displayEmailEl) displayEmailEl.textContent = email;
                            // also update profile menu in header
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

                        // Reload profile data to ensure everything is in sync
                        setTimeout(() => {
                            loadProfileData();
                        }, 500);
                        
                        // Close modal after a brief delay to show the success message
                        setTimeout(() => {
                            closeModal();
                        }, 300);
                    } else {
                        // Show error message
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
                    // Restore button state
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = originalText;
                    }
                }
            });
        }

        // close modal when clicking outside content
        const overlayEl = document.getElementById('editModal');
        if (overlayEl) {
            overlayEl.addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        }

        // close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const overlay = document.getElementById('editModal');
                if (overlay && !overlay.hidden) closeModal();
            }
        });

        // loadProfileData will populate panels directly, no need for separate populatePanels call
    </script>
</body>

</html>