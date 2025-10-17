// Generate CSRF token on page load
document.addEventListener("DOMContentLoaded", function () {
	generateCSRFToken();
	setupForgotPasswordHandlers();
});

// Fetch CSRF token from server
async function generateCSRFToken() {
	try {
		// Supabase: const response = await fetch("/php/supabase/generate_csrf.php");
		const response = await fetch("/ebakunado/php/supabase/generate_csrf.php");

		const data = await response.json();
		document.getElementById("csrf_token").value = data.csrf_token;
	} catch (error) {
		console.error("Failed to generate CSRF token:", error);
		// Fallback to client-side generation if server fails
		const token =
			Math.random().toString(36).substring(2) + Date.now().toString(36);
		document.getElementById("csrf_token").value = token;
	}
}

const loginForm = document.getElementById("LoginForm");

loginForm.addEventListener("submit", loginFun);

async function loginFun(e) {
	e.preventDefault(); // Prevent form submission initially

	// Get form elements
	const emailOrPhone = document.getElementById("Email_number").value;
	const password = document.getElementById("password").value;

	// Client-side validation
	if (!emailOrPhone || !password) {
		Swal.fire({
			icon: "error",
			title: "Validation Error!",
			text: "Email/Phone and password are required.",
		});
		return;
	}

	// Swal.fire({
	// 	title: "Logging in...",
	// 	text: "Please wait while we verify your credentials.",
	// 	allowOutsideClick: false,
	// 	didOpen: () => {
	// 		Swal.showLoading();
	// 	},
	// });

	const formdata = new FormData(loginForm);

	console.log("Form data being sent:");
	for (let [key, value] of formdata.entries()) {
		console.log(key + ": " + value);
	}

	try {
		console.log("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
		// Supabase: const res = await fetch("/php/supabase/login.php", {
		const res = await fetch("/ebakunado/php/supabase/login.php", {
			method: "POST",
			body: formdata,
		});

		const text = await res.text();
		console.log("Server response:", text);

		let data;
		try {
			data = JSON.parse(text);
			if (data.status === "failed") {
				console.log(data);
				Swal.fire({
					icon: "error",
					title: "Login failed!",
					text: data.message,
				});
				return;
			}
			// Do not show SweetAlert on success or already_logged_in; proceed to redirect
		} catch (e) {
			Swal.fire({
				icon: "error",
				title: "Server error",
				text: text,
			});
			return;
		}

		if (data.status === "success") {
			console.log("Login success - user_type:", data.user_type);
			if (data.user_type === "super_admin") {
				// 	Swal.fire({
				// 		icon: "success",
				// 		title: "Welcome Super Admin!",
				// 		text: data.message,
				// 		confirmButtonText: "Continue",
				// 	}).then(() => {
				// 		console.log("Redirecting to superadmin dashboard");
				// 		window.location.href = "superadmin/dashboard.php";
				// 	});
				console.log("Redirecting to superadmin dashboard");
				window.location.href = "../../views/superadmin/dashboard.php";
			} else if (data.user_type === "admin") {
				// Swal.fire({
				// 	icon: "success",
				// 	title: "Welcome Admin!",
				// 	text: data.message,
				// 	confirmButtonText: "Continue",
				// }).then(() => {
				// 	console.log("Redirecting to admin home");
				// 	window.location.href = "../views/admin/home.php";
				// });
				console.log("Redirecting to admin home");
				window.location.href = "../../views/admin/home.php";
			} else if (data.user_type === "bhw") {
				console.log("BHW login successful, showing SweetAlert");
				// Swal.fire({
				// 	icon: "success",
				// 	title: "Welcome BHW!",
				// 	text: data.message,
				// 	confirmButtonText: "Continue",
				// }).then(() => {
				// 	console.log("Redirecting to BHW home");
				// 	window.location.href = "../views/bhw/home.php";
				// });
				console.log("Redirecting to BHW home");
				window.location.href = "../../views/bhw/home.php";
			} else if (data.user_type === "midwife") {
				console.log("Midwife login successful, showing SweetAlert");
				// Swal.fire({
				// 	icon: "success",
				// 	title: "Welcome Midwife!",
				// 	text: data.message,
				// 	confirmButtonText: "Continue",
				// }).then(() => {
				// 	console.log("Redirecting to midwives home");
				// 	window.location.href = "../views/midwives/home.php";
				// });
				console.log("Redirecting to midwives home");
				window.location.href = "../../views/midwives/home.php";
			} else {
				console.log("User login successful, showing SweetAlert");
				// Swal.fire({
				// 	icon: "success",
				// 	title: "Welcome back!",
				// 	text: data.message,
				// 	confirmButtonText: "Continue",
				// }).then(() => {
				// 	console.log("Redirecting to users home");
				// 	window.location.href = "../views/users/home.php";
				// });
				console.log("Redirecting to users home");
				window.location.href = "../../views/users/home.php";
			}
		} else if (data.status === "already_logged_in") {
			console.log("Already logged in - user_type:", data.user_type);
			if (data.user_type === "super_admin") {
				console.log("Redirecting already logged in super_admin");
				window.location.href = "../../views/superadmin/dashboard.php";
			} else if (data.user_type === "admin") {
				console.log("Redirecting already logged in admin");
				window.location.href = "../../views/admin/home.php";
			} else if (data.user_type === "bhw") {
				console.log("Redirecting already logged in BHW");
				window.location.href = "../../views/bhw/home.php";
			} else if (data.user_type === "midwife") {
				console.log("Redirecting already logged in midwife");
				window.location.href = "../../views/midwives/home.php";
			} else {
				console.log("Redirecting already logged in user");
				window.location.href = "../../views/users/home.php";
			}
		} else {
			Swal.fire({
				icon: "error",
				title: "Login Failed!",
				text: data.message,
			});
			// console.log(Date.message);
		}
	} catch (error) {
		console.error("Network error:", error);
		// Swal.fire({
		// 	icon: "error",
		// 	title: "Network Error!",
		// 	text: "Please check your internet connection and try again.",
		// });
	}
}

// Setup forgot password handlers
function setupForgotPasswordHandlers() {
	const forgotPasswordLink = document.getElementById("forgotPasswordLink");
	const forgotPasswordForm = document.getElementById("forgotPasswordForm");
	const loginForm = document.getElementById("LoginForm");
	const cancelButton = document.getElementById("cancelForgotPassword");
	const forgotForm = document.getElementById("ForgotPasswordForm");

	// Show forgot password form
	if (forgotPasswordLink) {
		forgotPasswordLink.addEventListener("click", function (e) {
			e.preventDefault();
			if (loginForm) loginForm.style.display = "none";
			if (forgotPasswordForm) forgotPasswordForm.style.display = "block";
		});
	}

	// Cancel forgot password
	if (cancelButton) {
		cancelButton.addEventListener("click", function () {
			if (forgotPasswordForm) forgotPasswordForm.style.display = "none";
			if (loginForm) loginForm.style.display = "block";
			const emailPhoneInput = document.getElementById("email_phone");
			if (emailPhoneInput) emailPhoneInput.value = "";
		});
	}

	// Handle forgot password form submission
	if (forgotForm) {
		forgotForm.addEventListener("submit", handleForgotPassword);
	}
}

// Handle forgot password form submission
async function handleForgotPassword(e) {
	e.preventDefault();

	const emailPhone = document.getElementById("email_phone").value;

	if (!emailPhone) {
		Swal.fire({
			icon: "error",
			title: "Validation Error!",
			text: "Please enter your email or phone number.",
		});
		return;
	}

	Swal.fire({
		title: "Sending OTP...",
		text: "Please wait while we send your verification code.",
		allowOutsideClick: false,
		didOpen: () => {
			Swal.showLoading();
		},
	});

	try {
		const formData = new FormData();
		formData.append("email_phone", emailPhone);

		// Supabase: const response = await fetch("/php/supabase/forgot_password.php", {
		const response = await fetch(
			"/ebakunado/php/supabase/forgot_password.php",
			{
				method: "POST",
				body: formData,
			}
		);

		const data = await response.json();

		if (data.status === "success") {
			Swal.close();
			showResetOTPPopup(data.contact_type);
		} else {
			Swal.fire({
				icon: "error",
				title: "Failed to Send OTP!",
				text: data.message,
			});
		}
	} catch (error) {
		console.error("Network/Fetch error:", error);
		Swal.fire({
			icon: "error",
			title: "Network Error!",
			text: "Failed to send OTP. Please try again.",
		});
	}
}

// Show OTP popup for password reset
async function showResetOTPPopup(contactType) {
	return new Promise((resolve) => {
		let timeLeft = 300; // 5 minutes

		const { value: otp } = Swal.fire({
			title: "Enter Verification Code",
			html: `
				<p>We sent a verification code to your ${
					contactType === "email" ? "email address" : "phone number"
				}.</p>
				<input type="text" id="reset-otp-input" class="swal2-input" placeholder="Enter 6-digit code" maxlength="6" style="text-align: center; font-size: 18px; letter-spacing: 3px;">
				<div id="reset-timer" style="margin-top: 10px; color: #666;">Time remaining: <span id="reset-countdown">5:00</span></div>
			`,
			focusConfirm: false,
			showCancelButton: true,
			confirmButtonText: "Verify",
			cancelButtonText: "Cancel",
			allowOutsideClick: false,
			didOpen: () => {
				const input = document.getElementById("reset-otp-input");
				const countdown = document.getElementById("reset-countdown");

				input.focus();

				// Auto-format OTP input
				input.addEventListener("input", function (e) {
					this.value = this.value.replace(/[^0-9]/g, "");
				});

				// Start countdown timer
				const timer = setInterval(() => {
					timeLeft--;
					const minutes = Math.floor(timeLeft / 60);
					const seconds = timeLeft % 60;
					countdown.textContent = `${minutes}:${seconds
						.toString()
						.padStart(2, "0")}`;

					if (timeLeft <= 0) {
						clearInterval(timer);
						Swal.close();
						Swal.fire({
							icon: "error",
							title: "OTP Expired",
							text: "The verification code has expired. Please request a new one.",
						});
					}
				}, 1000);

				// Clean up timer when modal closes
				Swal.getConfirmButton().addEventListener("click", () =>
					clearInterval(timer)
				);
				Swal.getCancelButton().addEventListener("click", () =>
					clearInterval(timer)
				);
			},
			preConfirm: () => {
				const otp = document.getElementById("reset-otp-input").value;
				if (!otp || otp.length !== 6) {
					Swal.showValidationMessage("Please enter a valid 6-digit code");
					return false;
				}
				return otp;
			},
		}).then(async (result) => {
			if (result.isConfirmed) {
				await verifyResetOTP(result.value);
			}
		});
	});
}

// Verify reset OTP
async function verifyResetOTP(otp) {
	Swal.fire({
		title: "Verifying...",
		text: "Please wait while we verify your code.",
		allowOutsideClick: false,
		didOpen: () => {
			Swal.showLoading();
		},
	});

	try {
		const formData = new FormData();
		formData.append("otp", otp);

		// Supabase: const response = await fetch("/php/supabase/verify_reset_otp.php", {
		const response = await fetch(
			"/ebakunado/php/supabase/verify_reset_otp.php",
			{
				method: "POST",
				body: formData,
			}
		);

		const data = await response.json();

		if (data.status === "success") {
			Swal.close();
			showNewPasswordForm();
		} else {
			Swal.fire({
				icon: "error",
				title: "Verification Failed!",
				text: data.message,
			}).then(() => {
				// Show OTP popup again
				showResetOTPPopup();
			});
		}
	} catch (error) {
		console.error("Error verifying OTP:", error);
		Swal.fire({
			icon: "error",
			title: "Error!",
			text: "Failed to verify OTP. Please try again.",
		});
	}
}

// Show new password form
async function showNewPasswordForm() {
	const { value: formValues } = await Swal.fire({
		title: "Set New Password",
		html: `
			<input type="password" id="new-password" class="swal2-input" placeholder="Enter new password" style="margin-bottom: 10px;">
			<input type="password" id="confirm-password" class="swal2-input" placeholder="Confirm new password">
			<div style="margin-top: 10px; font-size: 12px; color: #666;">
				Password must be at least 8 characters long and contain:<br>
				• At least 1 uppercase letter<br>
				• At least 1 lowercase letter<br>
				• At least 1 number<br>
				• At least 1 special character
			</div>
		`,
		focusConfirm: false,
		showCancelButton: true,
		confirmButtonText: "Reset Password",
		cancelButtonText: "Cancel",
		allowOutsideClick: false,
		preConfirm: () => {
			const newPassword = document.getElementById("new-password").value;
			const confirmPassword = document.getElementById("confirm-password").value;

			if (!newPassword || !confirmPassword) {
				Swal.showValidationMessage("Both password fields are required");
				return false;
			}

			if (newPassword !== confirmPassword) {
				Swal.showValidationMessage("Passwords do not match");
				return false;
			}

			// Password strength validation (same as create_account.php)
			const passwordErrors = [];
			if (newPassword.length < 8) {
				passwordErrors.push("at least 8 characters long");
			}
			if (!/[A-Z]/.test(newPassword)) {
				passwordErrors.push("at least one uppercase letter");
			}
			if (!/[a-z]/.test(newPassword)) {
				passwordErrors.push("at least one lowercase letter");
			}
			if (!/[0-9]/.test(newPassword)) {
				passwordErrors.push("at least one number");
			}
			if (!/[^A-Za-z0-9]/.test(newPassword)) {
				passwordErrors.push("at least one special character");
			}

			if (passwordErrors.length > 0) {
				Swal.showValidationMessage(
					"Password must contain " + passwordErrors.join(", ")
				);
				return false;
			}

			return { newPassword, confirmPassword };
		},
	});

	if (formValues) {
		await resetPassword(formValues.newPassword, formValues.confirmPassword);
	}
}

// Reset password
async function resetPassword(newPassword, confirmPassword) {
	Swal.fire({
		title: "Resetting Password...",
		text: "Please wait while we update your password.",
		allowOutsideClick: false,
		didOpen: () => {
			Swal.showLoading();
		},
	});

	try {
		const formData = new FormData();
		formData.append("new_password", newPassword);
		formData.append("confirm_password", confirmPassword);

		// Supabase: const response = await fetch("/php/supabase/reset_password.php", {
		const response = await fetch("/ebakunado/php/supabase/reset_password.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			Swal.fire({
				icon: "success",
				title: "Password Reset Successful!",
				text: data.message,
				confirmButtonText: "Login Now",
			}).then(() => {
				// Return to login form
				document.getElementById("forgotPasswordForm").style.display = "none";
				document.getElementById("LoginForm").style.display = "block";
				document.getElementById("email_phone").value = "";
			});
		} else {
			Swal.fire({
				icon: "error",
				title: "Reset Failed!",
				text: data.message,
			});
		}
	} catch (error) {
		console.error("Error resetting password:", error);
		Swal.fire({
			icon: "error",
			title: "Error!",
			text: "Failed to reset password. Please try again.",
		});
	}
}
