<?php
session_start();

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
$user_fname = $_SESSION['fname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/sidebar.css" />
    <link rel="stylesheet" href="css/notification-style.css" />
    <link rel="stylesheet" href="css/bhw/profile-management.css?v=1.0.4" />
    <link rel="stylesheet" href="css/modals.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <script src="js/utils/ui-feedback.js"></script>

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
                        id="profileImage"
                        src="<?php echo !empty($noprofile) ? htmlspecialchars($noprofile) : 'assets/images/user-profile.png'; ?>"
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="philhealth_no">PhilHealth No.</label>
                        <div class="input-with-icon">
                            <span class="input-icon material-symbols-rounded">badge</span>
                            <input type="text" id="philhealth_no" name="philhealth_no" placeholder="Optional">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="radio-label">National Household Targeting System</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="nhts" value="Yes">
                                Yes
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="nhts" value="No">
                                No
                            </label>
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
        </section>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        // Load profile data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
        });

        async function loadProfileData() {
            try {
                const response = await fetch('php/supabase/users/get_profile_data.php');
                const data = await response.json();

                if (data.status === 'success') {
                    const profile = data.data;

                    // Update display
                    document.getElementById('displayName').textContent = `${profile.fname} ${profile.lname}`;
                    document.getElementById('displayRole').textContent = 'User';
                    document.getElementById('displayEmail').textContent = profile.email;

                    // Update form fields
                    document.getElementById('fname').value = profile.fname || '';
                    document.getElementById('lname').value = profile.lname || '';
                    document.getElementById('email').value = profile.email || '';
                    document.getElementById('phone_number').value = profile.phone_number || '';
                    document.getElementById('gender').value = profile.gender || '';
                    document.getElementById('place').value = profile.place || '';
                    document.getElementById('philhealth_no').value = profile.philhealth_no || '';
                    // Set NHTS radio button
                    const nhtsValue = profile.nhts || '';
                    const nhtsRadio = document.querySelector(`input[name="nhts"][value="${nhtsValue}"]`);
                    if (nhtsRadio) {
                        nhtsRadio.checked = true;
                    }

                    // Update profile image
                    if (profile.profileimg && profile.profileimg !== 'noprofile.png') {
                        document.getElementById('profileImage').src = profile.profileimg;
                    }
                } else {
                    console.error('Error loading profile:', data.message);
                }
            } catch (error) {
                console.error('Error loading profile data:', error);
            }
        }

        async function uploadProfilePhoto() {
            const fileInput = document.getElementById('photoInput');
            const file = fileInput.files[0];

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
                console.error('Error uploading photo:', error);
                UIFeedback.showToast({
                    title: 'Upload failed',
                    message: 'Failed to upload photo.',
                    variant: 'error'
                });
            }
        }

        // Handle form submission
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('php/supabase/users/update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    UIFeedback.showToast({
                        title: 'Profile updated',
                        message: 'Your profile information was saved.',
                        variant: 'success'
                    });
                    loadProfileData();
                } else {
                    UIFeedback.showToast({
                        title: 'Update failed',
                        message: data.message || 'Unable to update profile.',
                        variant: 'error'
                    });
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                UIFeedback.showToast({
                    title: 'Update failed',
                    message: 'Failed to update profile.',
                    variant: 'error'
                });
            }
        });
    </script>
</body>