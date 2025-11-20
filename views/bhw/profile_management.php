<?php include 'Include/header.php'; ?>

<div class="content">
	<h2>👤 Profile Management</h2>
	<p>Manage your personal information and account settings</p>

	<!-- Profile Information Card -->
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

		<!-- Profile Form -->
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

			<!-- Password Change Section -->
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

<style>
.profile-card {
	background: white;
	border-radius: 12px;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	padding: 30px;
	margin: 20px 0;
}

.profile-header {
	display: flex;
	align-items: center;
	margin-bottom: 30px;
	padding-bottom: 20px;
	border-bottom: 2px solid #f0f0f0;
}

.profile-avatar {
	position: relative;
	margin-right: 20px;
}

.profile-avatar img {
	width: 80px;
	height: 80px;
	border-radius: 50%;
	object-fit: cover;
	border: 3px solid #e3f2fd;
}

.change-photo-btn {
	position: absolute;
	bottom: 0;
	right: 0;
	background: #2196f3;
	color: white;
	border: none;
	border-radius: 50%;
	width: 30px;
	height: 30px;
	cursor: pointer;
	font-size: 12px;
}

.profile-info h3 {
	margin: 0 0 5px 0;
	color: #2c5aa0;
	font-size: 24px;
}

.profile-info p {
	margin: 5px 0;
	color: #666;
}

.profile-form {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
}

.form-group {
	display: flex;
	flex-direction: column;
}

.form-group label {
	font-weight: 600;
	margin-bottom: 8px;
	color: #333;
}

.form-group input,
.form-group select {
	padding: 12px;
	border: 2px solid #e0e0e0;
	border-radius: 8px;
	font-size: 14px;
	transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
	outline: none;
	border-color: #2196f3;
}

.password-section {
	background: #f8f9fa;
	padding: 20px;
	border-radius: 8px;
	border-left: 4px solid #2196f3;
}

.password-section h4 {
	margin: 0 0 15px 0;
	color: #2c5aa0;
}

.form-actions {
	display: flex;
	gap: 15px;
	justify-content: flex-end;
	margin-top: 20px;
	padding-top: 20px;
	border-top: 2px solid #f0f0f0;
}

.btn-primary,
.btn-secondary {
	padding: 12px 24px;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.3s;
	display: flex;
	align-items: center;
	gap: 8px;
}

.btn-primary {
	background: #2196f3;
	color: white;
}

.btn-primary:hover {
	background: #1976d2;
}

.btn-secondary {
	background: #f5f5f5;
	color: #666;
	border: 2px solid #e0e0e0;
}

.btn-secondary:hover {
	background: #eeeeee;
}

@media (max-width: 768px) {
	.form-row {
		grid-template-columns: 1fr;
	}
	
	.profile-header {
		flex-direction: column;
		text-align: center;
	}
	
	.profile-avatar {
		margin-right: 0;
		margin-bottom: 15px;
	}
}
</style>

<script>
// Load profile data on page load
document.addEventListener('DOMContentLoaded', function() {
	loadProfileData();
});

async function loadProfileData() {
	try {
		const response = await fetch('/ebakunado/php/supabase/shared/get_profile_data.php');
		const data = await response.json();
		
		if (data.status === 'success') {
			const profile = data.data;
			
			// Update display
			document.getElementById('displayName').textContent = `${profile.fname} ${profile.lname}`;
			document.getElementById('displayRole').textContent = profile.user_type ? profile.user_type.charAt(0).toUpperCase() + profile.user_type.slice(1) : 'User';
			document.getElementById('displayEmail').textContent = profile.email;
			
			// Update form fields
			document.getElementById('fname').value = profile.fname || '';
			document.getElementById('lname').value = profile.lname || '';
			document.getElementById('email').value = profile.email || '';
			document.getElementById('phone_number').value = profile.phone_number || '';
			document.getElementById('gender').value = profile.gender || '';
			document.getElementById('place').value = profile.place || '';
			
			// Update profile image
			if (profile.profileImg && profile.profileImg !== 'noprofile.png') {
				document.getElementById('profileImage').src = profile.profileImg;
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
		const response = await fetch('../../php/supabase/shared/upload_profile_photo.php', {
			method: 'POST',
			body: formData
		});
		
		const data = await response.json();
		
		if (data.status === 'success') {
			document.getElementById('profileImage').src = data.imageUrl;
			if (window.UIFeedback) {
				window.UIFeedback.showToast({
					title: 'Profile photo updated',
					message: 'Your profile photo was updated successfully.',
					variant: 'success'
				});
			}
		} else {
			if (window.UIFeedback) {
				window.UIFeedback.showToast({
					title: 'Upload failed',
					message: data.message || 'Unable to update profile photo.',
					variant: 'error'
				});
			}
		}
	} catch (error) {
		console.error('Error uploading photo:', error);
		if (window.UIFeedback) {
			window.UIFeedback.showToast({
				title: 'Upload failed',
				message: 'Failed to upload photo. Please try again.',
				variant: 'error'
			});
		}
	}
}

// Handle form submission
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
			if (window.UIFeedback) {
				window.UIFeedback.showToast({
					title: 'Profile updated',
					message: 'Your profile information was saved.',
					variant: 'success'
				});
			}
			loadProfileData(); // Refresh the display
		} else {
			if (window.UIFeedback) {
				window.UIFeedback.showToast({
					title: 'Update failed',
					message: data.message || 'Unable to update profile.',
					variant: 'error'
				});
			}
		}
	} catch (error) {
		console.error('Error updating profile:', error);
		if (window.UIFeedback) {
			window.UIFeedback.showToast({
				title: 'Update failed',
				message: 'Failed to update profile.',
				variant: 'error'
			});
		}
	}
});
</script>
<script src="../../js/utils/ui-feedback.js"></script>
<?php include 'Include/footer.php'; ?>
