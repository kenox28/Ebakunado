<?php
session_start();
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
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/bhw/profile-management.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="profile-management-section">
            <div class="profile-header-section">
                <div class="profile-header-content">
                    <div class="profile-header-text">
                        <h1>
                            Profile Management
                        </h1>
                        <p>Manage your personal information and account settings</p>
                    </div>
                </div>
            </div>

            <div class="profile-info-section">
                <div class="profile-avatar">
                    <img
                        class="profile-avatar" id="profileImage"
                        src="../../assets/images/user-profile.png"
                        alt="User Profile" />
                    <button class="change-photo-btn" onclick="document.getElementById('photoInput').click()">
                        <span class="material-symbols-rounded">camera_alt</span>
                    </button>
                    <input type="file" id="photoInput" accept="image/*" style="display: none;" onchange="uploadProfilePhoto()">
                </div>
                <div class="profile-info">
                    <h3 id="displayName">Loading...</h3>
                    <p id="displayRole">Loading...</p>
                    <p id="displayEmail">Loading...</p>
                </div>
            </div>

            <form id="profileForm" class="profile-form">
                <div class="form-section-title">
                    <span class="material-symbols-rounded">person</span>
                    <span>Personal Details</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fname">First Name</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">person</span>
                            <input type="text" id="fname" name="fname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lname">Last Name</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">person</span>
                            <input type="text" id="lname" name="lname" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">email</span>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">phone</span>
                            <input type="tel" id="phone_number" name="phone_number">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">wc</span>
                            <select id="gender" name="gender">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="place">Place/Barangay</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">location_on</span>
                            <input type="text" id="place" name="place" placeholder="e.g., Barangay Linao">
                        </div>
                    </div>
                </div>

                <div class="password-section">
                    <div class="form-section-title">
                        <span class="material-symbols-rounded">lock</span>
                        <span>Change Password</span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="current_password" name="current_password" class="password-input">
                                <span class="material-symbols-rounded password-toggle" data-target="current_password">visibility_off</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password" class="password-input">
                                <span class="material-symbols-rounded password-toggle" data-target="new_password">visibility_off</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="password-input">
                            <span class="material-symbols-rounded password-toggle" data-target="confirm_password">visibility_off</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="loadProfileData()" class="btn btn-secondary">
                        <span class="material-symbols-rounded">refresh</span>
                        Refresh
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-symbols-rounded">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/auth-handler/password-toggle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
        });

        async function loadProfileData() {
            try {
                const response = await fetch('/ebakunado/php/supabase/shared/get_profile_data.php');
                const data = await response.json();

                if (data.status === 'success') {
                    const profile = data.data;
                    document.getElementById('displayName').textContent = `${profile.fname} ${profile.lname}`;
                    document.getElementById('displayRole').textContent = profile.user_type ? profile.user_type.charAt(0).toUpperCase() + profile.user_type.slice(1) : 'User';
                    document.getElementById('displayEmail').textContent = profile.email;
                    document.getElementById('fname').value = profile.fname || '';
                    document.getElementById('lname').value = profile.lname || '';
                    document.getElementById('email').value = profile.email || '';
                    document.getElementById('phone_number').value = profile.phone_number || '';
                    document.getElementById('gender').value = profile.gender || '';
                    document.getElementById('place').value = profile.place || '';
                    if (profile.profileImg && profile.profileImg !== 'noprofile.png') {
                        document.getElementById('profileImage').src = profile.profileImg;
                    }
                }
            } catch (error) {
                console.error('Error loading profile:', error);
            }
        }

        async function uploadProfilePhoto() {
            const file = document.getElementById('photoInput').files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('photo', file);
            try {
                const response = await fetch('../../php/supabase/shared/upload_profile_photo.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    document.getElementById('profileImage').src = data.imageUrl;
                    Swal.fire('Success!', 'Profile photo updated successfully', 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Failed to upload photo', 'error');
            }
        }

        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            try {
                const response = await fetch('/ebakunado/php/supabase/shared/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    Swal.fire('Success!', 'Profile updated successfully', 'success');
                    loadProfileData();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Failed to update profile', 'error');
            }
        });
    </script>
</body>

</html>