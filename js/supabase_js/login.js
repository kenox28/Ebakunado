// Generate CSRF token on page load
document.addEventListener("DOMContentLoaded", function () {
	generateCSRFToken();
	setupForgotPasswordHandlers();
});

const Feedback = {
	toast({ title, message, variant = "info" }) {
		if (
			window.UIFeedback &&
			typeof window.UIFeedback.showToast === "function"
		) {
			window.UIFeedback.showToast({ title, message, variant });
		} else {
			alert(`${title}\n${message}`);
		}
	},
	modal(options) {
		if (
			window.UIFeedback &&
			typeof window.UIFeedback.showModal === "function"
		) {
			return window.UIFeedback.showModal(options);
		}
		return Promise.resolve(null);
	},
	loader(message) {
		if (
			window.UIFeedback &&
			typeof window.UIFeedback.showLoader === "function"
		) {
			return window.UIFeedback.showLoader(message);
		}
		return () => {};
	},
	closeModal() {
		if (
			window.UIFeedback &&
			typeof window.UIFeedback.closeModal === "function"
		) {
			window.UIFeedback.closeModal();
		}
	},
};

// Fetch CSRF token from server
async function generateCSRFToken() {
	try {
		// Supabase: const response = await fetch("/php/supabase/generate_csrf.php");
		const response = await fetch("../../php/supabase/generate_csrf.php");

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
		Feedback.toast({
			title: "Validation Error",
			message: "Email/Phone and password are required.",
			variant: "error",
		});
		return;
	}

	const formdata = new FormData(loginForm);

	console.log("Form data being sent:");
	for (let [key, value] of formdata.entries()) {
		console.log(key + ": " + value);
	}

	try {
		console.log("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
		// Supabase: const res = await fetch("/php/supabase/login.php", {
		const res = await fetch("../../php/supabase/login.php", {
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
				Feedback.toast({
					title: "Login failed",
					message: data.message,
					variant: "error",
				});
				return;
			}
		} catch (e) {
			Feedback.toast({
				title: "Server error",
				message: text,
				variant: "error",
			});
			return;
		}

		if (data.status === "success") {
			console.log("Login success - user_type:", data.user_type);
			if (data.user_type === "super_admin") {
				console.log("Redirecting to superadmin dashboard");
				window.location.href = "../../views/superadmin/dashboard.php";
			} else if (data.user_type === "admin") {
				console.log("Redirecting to admin home");
				window.location.href = "../../views/admin/home.php";
			} else if (data.user_type === "bhw") {
				console.log("BHW login successful, showing SweetAlert");
				console.log("Redirecting to BHW home");
				window.location.href = "../../views/bhw-page/dashboard.php";
			} else if (data.user_type === "midwife") {
				console.log("Midwife login successful, showing SweetAlert");
				console.log("Redirecting to BHW home");
				window.location.href = "../../views/bhw-page/dashboard.php";
			} else {
				console.log("User login successful, showing SweetAlert");
				console.log("Redirecting to users home");
				window.location.href = "../../views/user-page/dashboard.php";
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
				window.location.href = "../../views/bhw-page/dashboard.php";
			} else if (data.user_type === "midwife") {
				console.log("Redirecting already logged in midwife");
				window.location.href = "../../views/bhw-page/dashboard.php";
			} else {
				console.log("Redirecting already logged in user");
				window.location.href = "../../views/user-page/dashboard.php";
			}
		} else {
			Feedback.toast({
				title: "Login failed",
				message: data.message,
				variant: "error",
			});
		}
	} catch (error) {
		console.error("Network error:", error);
		Feedback.toast({
			title: "Network error",
			message: "Please check your internet connection and try again.",
			variant: "error",
		});
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
		Feedback.toast({
			title: "Validation error",
			message: "Please enter your email or phone number.",
			variant: "error",
		});
		return;
	}

	const closeLoader = Feedback.loader("Sending OTP...");

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

		closeLoader();

		if (data.status === "success") {
			showResetOTPPopup(data.contact_type);
		} else {
			Feedback.toast({
				title: "Failed to send OTP",
				message: data.message,
				variant: "error",
			});
		}
	} catch (error) {
		closeLoader();
		console.error("Network/Fetch error:", error);
		Feedback.toast({
			title: "Network error",
			message: "Failed to send OTP. Please try again.",
			variant: "error",
		});
	}
}

async function showResetOTPPopup(contactType) {
	let countdownTimer = null;
	let timeLeft = 300;

	const result = await Feedback.modal({
		title: "Enter Verification Code",
		message: `We sent a verification code to your ${
			contactType === "email" ? "email address" : "phone number"
		}.`,
		icon: "info",
		confirmText: "Verify",
		cancelText: "Cancel",
		showCancel: true,
		autoClose: 300000,
		html: `
			<input type="text" id="reset-otp-input" class="modal-otp-input" placeholder="Enter 6-digit code" maxlength="6" autocomplete="one-time-code" />
			<p class="modal-otp-helper" data-role="otpHelper">Enter the 6-digit code sent to you.</p>
			<div class="modal-otp-timer">Time remaining: <span id="reset-countdown">5:00</span></div>
		`,
		onOpen(card) {
			const input = card.querySelector("#reset-otp-input");
			const countdown = card.querySelector("#reset-countdown");
			const helper = card.querySelector("[data-role='otpHelper']");

			if (input) {
				input.focus();
				input.addEventListener("input", () => {
					input.value = input.value.replace(/[^0-9]/g, "");
					helper.textContent = "Enter the 6-digit code sent to you.";
					helper.classList.remove("is-error");
				});
			}

			countdownTimer = setInterval(() => {
				timeLeft--;
				const minutes = Math.floor(timeLeft / 60);
				const seconds = timeLeft % 60;
				countdown.textContent = `${minutes}:${seconds
					.toString()
					.padStart(2, "0")}`;
				if (timeLeft <= 0) {
					clearInterval(countdownTimer);
				}
			}, 1000);
		},
		onClose() {
			if (countdownTimer) {
				clearInterval(countdownTimer);
			}
		},
		beforeConfirm(card) {
			const input = card.querySelector("#reset-otp-input");
			const helper = card.querySelector("[data-role='otpHelper']");
			const value = input.value.trim();
			if (value.length !== 6) {
				helper.textContent = "Please enter a valid 6-digit code.";
				helper.classList.add("is-error");
				return false;
			}
			return value;
		},
	});

	if (result?.action === "confirm" && result.data) {
		await verifyResetOTP(result.data, contactType);
	} else if (result?.action === "timeout") {
		Feedback.toast({
			title: "OTP expired",
			message: "The verification code has expired. Please request a new one.",
			variant: "error",
		});
	}
}

// Verify reset OTP
async function verifyResetOTP(otp, contactType) {
	const closeLoader = Feedback.loader("Verifying code...");

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

		closeLoader();

		if (data.status === "success") {
			showNewPasswordForm();
		} else {
			Feedback.toast({
				title: "Verification failed",
				message: data.message,
				variant: "error",
			});
			showResetOTPPopup(contactType);
		}
	} catch (error) {
		closeLoader();
		console.error("Error verifying OTP:", error);
		Feedback.toast({
			title: "Error verifying OTP",
			message: "Please try again.",
			variant: "error",
		});
	}
}

// Show new password form
async function showNewPasswordForm() {
	const result = await Feedback.modal({
		title: "Set New Password",
		message: "Create a strong password for your account.",
		icon: "info",
		confirmText: "Reset Password",
		cancelText: "Cancel",
		showCancel: true,
		html: `
			<div class="modal-field-group">
				<label for="new-password">New Password</label>
				<input type="password" id="new-password" class="modal-input" placeholder="Enter new password" />
			</div>
			<div class="modal-field-group">
				<label for="confirm-password">Confirm Password</label>
				<input type="password" id="confirm-password" class="modal-input" placeholder="Confirm new password" />
			</div>
			<p class="modal-hint" data-role="passwordHelper">
				Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
			</p>
		`,
		beforeConfirm(card) {
			const newPassword = card.querySelector("#new-password").value;
			const confirmPassword = card.querySelector("#confirm-password").value;
			const helper = card.querySelector("[data-role='passwordHelper']");

			const setError = (msg) => {
				helper.textContent = msg;
				helper.classList.add("is-error");
			};

			if (!newPassword || !confirmPassword) {
				setError("Both password fields are required.");
				return false;
			}

			if (newPassword !== confirmPassword) {
				setError("Passwords do not match.");
				return false;
			}

			const passwordErrors = [];
			if (newPassword.length < 8)
				passwordErrors.push("at least 8 characters long");
			if (!/[A-Z]/.test(newPassword))
				passwordErrors.push("one uppercase letter");
			if (!/[a-z]/.test(newPassword))
				passwordErrors.push("one lowercase letter");
			if (!/[0-9]/.test(newPassword)) passwordErrors.push("one number");
			if (!/[^A-Za-z0-9]/.test(newPassword))
				passwordErrors.push("one special character");

			if (passwordErrors.length > 0) {
				setError("Password must contain " + passwordErrors.join(", ") + ".");
				return false;
			}

			return { newPassword, confirmPassword };
		},
		onOpen(card) {
			const helper = card.querySelector("[data-role='passwordHelper']");
			card.querySelectorAll(".modal-input").forEach((input) => {
				input.addEventListener("input", () => {
					helper.classList.remove("is-error");
					helper.textContent =
						"Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
				});
			});
		},
	});

	if (result?.action === "confirm") {
		await resetPassword(result.data.newPassword, result.data.confirmPassword);
	}
}

// Reset password
async function resetPassword(newPassword, confirmPassword) {
	const closeLoader = Feedback.loader("Resetting password...");

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

		closeLoader();

		if (data.status === "success") {
			await Feedback.modal({
				title: "Password reset successful",
				message: data.message,
				icon: "success",
				confirmText: "Login now",
				showCancel: false,
			});

			document.getElementById("forgotPasswordForm").style.display = "none";
			document.getElementById("LoginForm").style.display = "block";
			document.getElementById("email_phone").value = "";
		} else {
			Feedback.toast({
				title: "Reset failed",
				message: data.message,
				variant: "error",
			});
		}
	} catch (error) {
		closeLoader();
		console.error("Error resetting password:", error);
		Feedback.toast({
			title: "Error",
			message: "Failed to reset password. Please try again.",
			variant: "error",
		});
	}
}
