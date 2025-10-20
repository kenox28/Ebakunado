<?php session_start(); ?>
<?php 
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') ." ". ($_SESSION['lname'] ?? '');
if($user_types != 'midwifes') { $user_type = 'Barangay Health Worker'; } else { $user_type = 'Midwife'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile Management</title>
    <link rel="stylesheet" href="../../css/main.css?v=1.0.1" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/bhw/profile-management.css?v=1.0.2" />
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <div class="content">
            <h2>👤 Profile Management</h2>
            <p>Manage your personal information and account settings</p>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img id="profileImage" src="../../assets/icons/noprofile.png" alt="Profile" />
                        <button class="change-photo-btn" onclick="document.getElementById('photoInput').click()">
                            <i class="fas fa-camera"></i>
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
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" id="fname" name="fname" required>
                        </div>
                        <div class="form-group">
                            <label for="lname">Last Name</label>
                            <input type="text" id="lname" name="lname" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="place">Place/Barangay</label>
                            <input type="text" id="place" name="place" placeholder="e.g., Barangay Linao">
                        </div>
                    </div>

                    <div class="password-section">
                        <h4>Change Password</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="loadProfileData()" class="btn-secondary">
                            <i class="fas fa-refresh"></i> Refresh
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        } catch (error) { console.error('Error loading profile:', error); }
    }

    async function uploadProfilePhoto() {
        const file = document.getElementById('photoInput').files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('photo', file);
        try {
            const response = await fetch('../../php/supabase/shared/upload_profile_photo.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('profileImage').src = data.imageUrl;
                Swal.fire('Success!', 'Profile photo updated successfully', 'success');
            } else { Swal.fire('Error!', data.message, 'error'); }
        } catch (error) { Swal.fire('Error!', 'Failed to upload photo', 'error'); }
    }

    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('/ebakunado/php/supabase/shared/update_profile.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.status === 'success') { Swal.fire('Success!', 'Profile updated successfully', 'success'); loadProfileData(); }
            else { Swal.fire('Error!', data.message, 'error'); }
        } catch (error) { Swal.fire('Error!', 'Failed to update profile', 'error'); }
    });
    </script>
</body>
</html>

