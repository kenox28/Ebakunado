<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Create Account - Ebakunado</title>
		<!-- SweetAlert2 for better notifications -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<style>
			body {
				font-family: Arial, sans-serif;
				max-width: 500px;
				margin: 50px auto;
				padding: 20px;
				background-color: #f5f5f5;
			}
			.form-container {
				background: white;
				padding: 30px;
				border-radius: 10px;
				box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			}
			h1 {
				text-align: center;
				color: #333;
				margin-bottom: 30px;
			}
			.form-group {
				margin-bottom: 20px;
			}
			label {
				display: block;
				margin-bottom: 8px;
				font-weight: bold;
				color: #555;
			}
			input, select {
				width: 100%;
				padding: 12px;
				border: 1px solid #ddd;
				border-radius: 5px;
				font-size: 16px;
				box-sizing: border-box;
			}
			input:focus, select:focus {
				outline: none;
				border-color: #007bff;
				box-shadow: 0 0 5px rgba(0,123,255,0.3);
			}
			.submit-btn {
				background: #007bff;
				color: white;
				padding: 15px 30px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 16px;
				width: 100%;
				transition: background-color 0.3s;
			}
			.submit-btn:hover {
				background: #0056b3;
			}
			.required {
				color: #dc3545;
			}
			.otp-btn {
				background: #28a745;
				color: white;
				padding: 12px 20px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 14px;
				white-space: nowrap;
				transition: background-color 0.3s;
			}
			.otp-btn:hover:not(:disabled) {
				background: #218838;
			}
			.otp-btn:disabled {
				background: #6c757d;
				cursor: not-allowed;
			}
			.verified {
				background: #17a2b8 !important;
			}
			.error-message {
				color: #dc3545;
				font-size: 14px;
				margin-top: 5px;
			}
			.success-message {
				color: #28a745;
				font-size: 14px;
				margin-top: 5px;
			}
		</style>
	</head>
	<body>
		<div class="form-container">
			<h1>Create Account</h1>
			<form action="" id="CreateForm">
				<!-- CSRF Token for security -->
				<input type="hidden" name="csrf_token" id="csrf_token" value="" />

				<div class="form-group">
					<label for="fname">First Name <span class="required">*</span></label>
					<input
						type="text"
						name="fname"
						id="fname"
						placeholder="Enter first name"
						required />
				</div>

				<div class="form-group">
					<label for="lname">Last Name <span class="required">*</span></label>
					<input
						type="text"
						name="lname"
						id="lname"
						placeholder="Enter last name"
						required />
				</div>

				<div class="form-group">
					<label for="email">Email Address <span class="required">*</span></label>
					<input
						type="email"
						name="email"
						id="email"
						placeholder="Enter email address"
						required />
				</div>

				<div class="form-group">
					<label for="number">Phone Number <span class="required">*</span></label>
					<input
						type="tel"
						name="number"
						id="number"
						placeholder="Enter phone number (09xxxxxxxxx)"
						required />
				</div>

				<div class="form-group">
					<label for="gender">Gender</label>
					<select id="gender" name="gender">
						<option value="">Select gender</option>
						<option value="Male">Male</option>
						<option value="Female">Female</option>
						<option value="Other">Other</option>
					</select>
				</div>

				<div class="form-group">
					<label for="province">Province</label>
					<select id="province" name="province" onchange="loadCities()" required>
						<option value="">Select Province</option>
					</select>
				</div>

				<div class="form-group">
					<label for="city_municipality">City/Municipality</label>
					<select id="city_municipality" name="city_municipality" onchange="loadBarangays()" required>
						<option value="">Select City/Municipality</option>
					</select>
				</div>

				<div class="form-group">
					<label for="barangay">Barangay</label>
					<select id="barangay" name="barangay" onchange="loadPuroks()" required>
						<option value="">Select Barangay</option>
					</select>
				</div>

				<div class="form-group">
					<label for="purok">Purok</label>
					<select id="purok" name="purok" required>
						<option value="">Select Purok</option>
					</select>
				</div>

				<div class="form-group">
					<label for="password">Password <span class="required">*</span></label>
					<input
						type="password"
						name="password"
						id="password"
						placeholder="Enter password"
						required />
				</div>

				<div class="form-group">
					<label for="confirm_password">Confirm Password <span class="required">*</span></label>
					<input
						type="password"
						name="confirm_password"
						id="confirm_password"
						placeholder="Confirm password"
						required />
				</div>

				<button type="submit" class="submit-btn">Create Account</button>
			</form>
		</div>
	</body>
	<script src="../js/create_account.js?v=1.0.0"></script>
</html> 