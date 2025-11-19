// Global variables for OTP
let otpTimer = null;
let otpVerified = false;

// Password validation state
let passwordValid = false;
let passwordsMatch = false;

// Generate CSRF token on page load
document.addEventListener("DOMContentLoaded", function () {
	generateCSRFToken();
	loadProvinces();

	// Initialize password validation
	initPasswordValidation();

	// No need for OTP field listeners since we use popup now
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
		const response = await fetch("../php/supabase/generate_csrf.php");
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

const createForm = document.getElementById("CreateForm");

createForm.addEventListener("submit", CreateFun);

// Automatic OTP Functions
async function sendOTPAutomatically(phoneNumber) {
	// Validate Philippine phone number format
	const phoneRegex = /^(09|\+639)\d{9}$/;
	if (!phoneRegex.test(phoneNumber)) {
		Feedback.toast({
			title: "Invalid phone number",
			message: "Please enter a valid Philippine phone number (09xxxxxxxxx).",
			variant: "error",
		});
		return false;
	}

	const closeLoader = Feedback.loader("Sending OTP...");

	try {
		const formData = new FormData();
		formData.append("phone_number", phoneNumber);

		const response = await fetch("../php/supabase/send_otp.php", {
			method: "POST",
			body: formData,
		});

		const responseText = await response.text();
		let data;
		try {
			data = JSON.parse(responseText);
		} catch (e) {
			closeLoader();
			Feedback.toast({
				title: "Failed to send OTP",
				message: "Invalid server response. Please try again.",
				variant: "error",
			});
			return false;
		}

		closeLoader();

		if (data.status === "success") {
			Feedback.toast({
				title: "OTP sent",
				message: "Please check your phone for the verification code.",
				variant: "success",
			});
			return true;
		} else {
			Feedback.toast({
				title: "Failed to send OTP",
				message: data.message || "Please try again.",
				variant: "error",
			});
			return false;
		}
	} catch (error) {
		closeLoader();
		console.error("Error sending OTP:", error);
		console.error("Error details:", error.message, error.stack);
		Feedback.toast({
			title: "Error sending OTP",
			message: "Failed to send OTP. Please try again.",
			variant: "error",
		});
		return false;
	}
}

async function showOTPPopup() {
	let timeLeft = 300;
	let timerInterval = null;

	const result = await Feedback.modal({
		title: "Enter Verification Code",
		message: "We've sent a 6-digit verification code to your phone number.",
		icon: "info",
		confirmText: "Verify",
		cancelText: "Cancel",
		showCancel: true,
		autoClose: 300000,
		html: `
			<input type="text" id="swal-otp-input" class="modal-otp-input" placeholder="Enter 6-digit code" maxlength="6" autocomplete="one-time-code" />
			<p class="modal-otp-helper" data-role="otpHelper">Enter the 6-digit code sent to you.</p>
			<div class="modal-otp-timer">Code expires in: <span id="swal-timer">5:00</span></div>
		`,
		onOpen(card) {
			const input = card.querySelector("#swal-otp-input");
			const helper = card.querySelector("[data-role='otpHelper']");
			const countdown = card.querySelector("#swal-timer");

			if (input) {
				input.focus();
				input.addEventListener("input", () => {
					input.value = input.value.replace(/[^0-9]/g, "");
					helper.textContent = "Enter the 6-digit code sent to you.";
					helper.classList.remove("is-error");
				});
			}

			timerInterval = setInterval(() => {
				timeLeft--;
				const minutes = Math.floor(timeLeft / 60);
				const seconds = timeLeft % 60;
				countdown.textContent = `${minutes}:${seconds
					.toString()
					.padStart(2, "0")}`;
				if (timeLeft <= 0) {
					clearInterval(timerInterval);
				}
			}, 1000);
		},
		onClose() {
			if (timerInterval) {
				clearInterval(timerInterval);
			}
		},
		beforeConfirm: async (card) => {
			const input = card.querySelector("#swal-otp-input");
			const helper = card.querySelector("[data-role='otpHelper']");
			const otpCode = input.value.trim();

			if (!otpCode || otpCode.length !== 6) {
				helper.textContent = "Please enter the 6-digit verification code.";
				helper.classList.add("is-error");
				return false;
			}

			helper.textContent = "Verifying code...";
			helper.classList.remove("is-error");

			try {
				const formData = new FormData();
				formData.append("otp", otpCode);

				const response = await fetch("../php/supabase/verify_otp.php", {
					method: "POST",
					body: formData,
				});

				const data = await response.json();

				if (data.status === "success") {
					return true;
				} else {
					helper.textContent = data.message;
					helper.classList.add("is-error");
					return false;
				}
			} catch (error) {
				console.error("Error verifying OTP:", error);
				helper.textContent = "Failed to verify OTP. Please try again.";
				helper.classList.add("is-error");
				return false;
			}
		},
	});

	if (result?.action === "confirm" && result.data) {
		Feedback.toast({
			title: "Verified",
			message: "Phone number verified successfully!",
			variant: "success",
		});
		return true;
	} else if (result?.action === "timeout") {
		Feedback.toast({
			title: "OTP expired",
			message: "The verification code has expired. Please request a new one.",
			variant: "error",
		});
	}
	return false;
}

// Initialize password validation
function initPasswordValidation() {
	const passwordInput = document.getElementById("password");
	const confirmPasswordInput = document.getElementById("confirm_password");
	const passwordRequirements = document.getElementById("passwordRequirements");
	const passwordMatchIndicator = document.getElementById(
		"passwordMatchIndicator"
	);

	if (!passwordInput || !confirmPasswordInput) return;

	// Show requirements when password field is focused
	passwordInput.addEventListener("focus", function () {
		if (passwordRequirements) {
			passwordRequirements.style.display = "block";
		}
	});

	// Hide requirements when password field is blurred (if password is valid)
	passwordInput.addEventListener("blur", function () {
		if (passwordRequirements && passwordValid) {
			// Keep it visible if password is valid, or hide after a delay
			setTimeout(() => {
				if (passwordInput !== document.activeElement) {
					passwordRequirements.style.display = "none";
				}
			}, 200);
		}
	});

	// Real-time password validation
	passwordInput.addEventListener("input", function () {
		validatePasswordRealTime(this.value);
	});

	// Real-time password match validation
	confirmPasswordInput.addEventListener("input", function () {
		checkPasswordMatch();
	});

	passwordInput.addEventListener("input", function () {
		checkPasswordMatch();
	});
}

// Real-time password validation - shows all requirements at once
function validatePasswordRealTime(password) {
	const requirements = {
		length: password.length >= 8,
		uppercase: /[A-Z]/.test(password),
		lowercase: /[a-z]/.test(password),
		number: /[0-9]/.test(password),
		special: /[^A-Za-z0-9]/.test(password),
	};

	// Check and update length requirement
	const reqLength = document.getElementById("req-length");
	if (reqLength) {
		if (requirements.length) {
			reqLength.classList.add("met");
		} else {
			reqLength.classList.remove("met");
		}
	}

	// Check and update uppercase requirement
	const reqUppercase = document.getElementById("req-uppercase");
	if (reqUppercase) {
		if (requirements.uppercase) {
			reqUppercase.classList.add("met");
		} else {
			reqUppercase.classList.remove("met");
		}
	}

	// Check and update lowercase requirement
	const reqLowercase = document.getElementById("req-lowercase");
	if (reqLowercase) {
		if (requirements.lowercase) {
			reqLowercase.classList.add("met");
		} else {
			reqLowercase.classList.remove("met");
		}
	}

	// Check and update number requirement
	const reqNumber = document.getElementById("req-number");
	if (reqNumber) {
		if (requirements.number) {
			reqNumber.classList.add("met");
		} else {
			reqNumber.classList.remove("met");
		}
	}

	// Check and update special character requirement
	const reqSpecial = document.getElementById("req-special");
	if (reqSpecial) {
		if (requirements.special) {
			reqSpecial.classList.add("met");
		} else {
			reqSpecial.classList.remove("met");
		}
	}

	// Check if all requirements are met
	passwordValid =
		requirements.length &&
		requirements.uppercase &&
		requirements.lowercase &&
		requirements.number &&
		requirements.special;
}

// Real-time password match validation (silent - no visual indicator)
function checkPasswordMatch() {
	const passwordInput = document.getElementById("password");
	const confirmPasswordInput = document.getElementById("confirm_password");

	if (!passwordInput || !confirmPasswordInput) return;

	const password = passwordInput.value;
	const confirmPassword = confirmPasswordInput.value;

	if (confirmPassword.length === 0) {
		passwordsMatch = false;
		return;
	}

	if (password === confirmPassword && password.length > 0) {
		passwordsMatch = true;
	} else {
		passwordsMatch = false;
	}
}

// Client-side password validation (for form submission)
function validatePassword(password) {
	const errors = [];

	if (password.length < 8) {
		errors.push("Password must be at least 8 characters long.");
	}
	if (!/[A-Z]/.test(password)) {
		errors.push("Password must contain at least one uppercase letter.");
	}
	if (!/[a-z]/.test(password)) {
		errors.push("Password must contain at least one lowercase letter.");
	}
	if (!/[0-9]/.test(password)) {
		errors.push("Password must contain at least one number.");
	}
	if (!/[^A-Za-z0-9]/.test(password)) {
		errors.push("Password must contain at least one special character.");
	}

	return errors;
}

// Check for weak passwords
function isWeakPassword(password) {
	const weakPasswords = [
		"password",
		"123456",
		"qwerty",
		"admin",
		"letmein",
		"welcome",
	];
	return weakPasswords.includes(password.toLowerCase());
}

async function CreateFun(e) {
	e.preventDefault(); // Prevent form submission initially

	// Get form elements
	const fname = document.getElementById("fname").value;
	const lname = document.getElementById("lname").value;
	const email = document.getElementById("email").value;
	const number = document.getElementById("number").value;
	const password = document.getElementById("password").value;
	const confirmPassword = document.getElementById("confirm_password").value;
	const csrfToken = document.getElementById("csrf_token").value;

	// Client-side validation - check BEFORE OTP to give immediate feedback
	if (!fname || !lname || !email || !number || !password || !confirmPassword) {
		Feedback.toast({
			title: "Missing information",
			message:
				"First name, last name, email, phone number, password, and confirm password are required.",
			variant: "error",
		});
		return;
	}

	// Email format validation
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!emailRegex.test(email)) {
		Feedback.toast({
			title: "Invalid email",
			message: "Please enter a valid email address.",
			variant: "error",
		});
		return;
	}

	// Phone number validation
	const phoneRegex = /^[0-9]{10,15}$/;
	if (!phoneRegex.test(number)) {
		Feedback.toast({
			title: "Invalid phone number",
			message: "Please enter a valid phone number (10-15 digits).",
			variant: "error",
		});
		return;
	}

	// Password validation check (using real-time validation state)
	if (!passwordValid) {
		Feedback.toast({
			title: "Password requirements not met",
			message:
				"Please ensure your password has 8 characters, uppercase, lowercase, number, and special character.",
			variant: "error",
		});
		return;
	}

	// Check for weak password
	if (isWeakPassword(password)) {
		Feedback.toast({
			title: "Weak password",
			message: "Please choose a stronger password that is not commonly used.",
			variant: "error",
		});
		return;
	}

	// Password confirmation check - CRITICAL: Check BEFORE OTP to prevent unnecessary OTP sending
	// Update the match state first
	checkPasswordMatch();

	// Explicit password match check - this MUST pass before proceeding
	if (password !== confirmPassword) {
		Feedback.toast({
			title: "Password mismatch",
			message: "Passwords do not match. Please try again.",
			variant: "error",
		});
		return; // Stop form submission
	}

	// Also verify the validation state variable
	if (!passwordsMatch) {
		Feedback.toast({
			title: "Password mismatch",
			message: "Passwords do not match. Please try again.",
			variant: "error",
		});
		return; // Stop form submission
	}

	// All validations passed - now proceed with OTP
	// First, send OTP automatically
	const otpSent = await sendOTPAutomatically(number);
	if (!otpSent) {
		return; // Error already shown in sendOTPAutomatically
	}

	// Show OTP input popup
	const otpVerified = await showOTPPopup();
	if (!otpVerified) {
		return; // User cancelled or OTP verification failed
	}

	const closeLoader = Feedback.loader("Creating account...");

	const formdata = new FormData(createForm);

	try {
		const res = await fetch("../php/supabase/create_account.php", {
			method: "POST",
			body: formdata,
		});

		const text = await res.text();
		let data;
		try {
			data = JSON.parse(text);
		} catch (e) {
			closeLoader();
			console.log("Server error:\n" + text);
			Feedback.toast({
				title: "Server error",
				message: "An unexpected error occurred. Please try again.",
				variant: "error",
			});
			return;
		}

		closeLoader();

		if (data.status === "success") {
			await Feedback.modal({
				title: "Account created",
				message: data.message,
				icon: "success",
				confirmText: "Continue to Login",
				showCancel: false,
			});
			window.location.href = "../views/auth/login.php";
		} else {
			if (data.debug) {
				console.log("Create account debug:", data.debug);
			}
			console.log("Create account response:", data);
			Feedback.toast({
				title: "Registration failed",
				message: data.message,
				variant: "error",
			});
		}
	} catch (error) {
		closeLoader();
		console.error("Network error:", error);
		Feedback.toast({
			title: "Network error",
			message: "Please check your internet connection and try again.",
			variant: "error",
		});
	}
}

// Cascading Place Dropdown Functions
async function loadProvinces() {
	try {
		console.log("Loading provinces...");
		const response = await fetch(
			"../php/supabase/admin/get_places.php?type=provinces"
		);
		console.log("Response status:", response.status);

		if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`);
		}

		const provinces = await response.json();
		console.log("Provinces received:", provinces);

		const provinceSelect = document.getElementById("province");
		if (!provinceSelect) {
			console.error("Province select element not found!");
			return;
		}

		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		if (Array.isArray(provinces) && provinces.length > 0) {
			provinces.forEach((provinceObj) => {
				const option = document.createElement("option");
				option.value = provinceObj.province;
				option.textContent = provinceObj.province;
				provinceSelect.appendChild(option);
			});
			console.log(`Added ${provinces.length} provinces to dropdown`);
		} else {
			console.log("No provinces found or invalid data format");
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

async function loadCities() {
	const province = document.getElementById("province").value;
	const citySelect = document.getElementById("city_municipality");
	const barangaySelect = document.getElementById("barangay");
	const purokSelect = document.getElementById("purok");

	// Reset dependent dropdowns
	citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
	barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province) return;

	try {
		const response = await fetch(
			`../php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		cities.forEach((cityObj) => {
			const option = document.createElement("option");
			option.value = cityObj.city_municipality;
			option.textContent = cityObj.city_municipality;
			citySelect.appendChild(option);
		});
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

async function loadBarangays() {
	const province = document.getElementById("province").value;
	const city = document.getElementById("city_municipality").value;
	const barangaySelect = document.getElementById("barangay");
	const purokSelect = document.getElementById("purok");

	// Reset dependent dropdowns
	barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province || !city) return;

	try {
		const response = await fetch(
			`../php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		barangays.forEach((barangayObj) => {
			const option = document.createElement("option");
			option.value = barangayObj.barangay;
			option.textContent = barangayObj.barangay;
			barangaySelect.appendChild(option);
		});
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

async function loadPuroks() {
	const province = document.getElementById("province").value;
	const city = document.getElementById("city_municipality").value;
	const barangay = document.getElementById("barangay").value;
	const purokSelect = document.getElementById("purok");

	// Reset purok dropdown
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		puroks.forEach((purokObj) => {
			const option = document.createElement("option");
			option.value = purokObj.purok;
			option.textContent = purokObj.purok;
			purokSelect.appendChild(option);
		});
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}
