// Generate CSRF token on page load
document.addEventListener("DOMContentLoaded", function () {
	generateCSRFToken();
});

// Fetch CSRF token from server
async function generateCSRFToken() {
	try {
		const response = await fetch("../php/generate_csrf.php");
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

// Client-side password validation
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

	// Client-side validation
	if (!fname || !lname || !email || !number || !password || !confirmPassword) {
		Swal.fire({
			icon: "error",
			title: "Validation Error!",
			text: "First name, last name, email, phone number, password, and confirm password are required.",
		});
		return;
	}

	// Email format validation
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!emailRegex.test(email)) {
		Swal.fire({
			icon: "error",
			title: "Invalid Email!",
			text: "Please enter a valid email address.",
		});
		return;
	}

	// Phone number validation
	const phoneRegex = /^[0-9]{10,15}$/;
	if (!phoneRegex.test(number)) {
		Swal.fire({
			icon: "error",
			title: "Invalid Phone Number!",
			text: "Please enter a valid phone number (10-15 digits).",
		});
		return;
	}

	// Password validation
	const passwordErrors = validatePassword(password);
	if (passwordErrors.length > 0) {
		Swal.fire({
			icon: "error",
			title: "Password Requirements Not Met!",
			html: passwordErrors.join("<br>"),
		});
		return;
	}

	// Check for weak password
	if (isWeakPassword(password)) {
		Swal.fire({
			icon: "error",
			title: "Weak Password!",
			text: "Please choose a stronger password that is not commonly used.",
		});
		return;
	}

	// Password confirmation check
	if (password !== confirmPassword) {
		Swal.fire({
			icon: "error",
			title: "Password Mismatch!",
			text: "Passwords do not match. Please try again.",
		});
		return;
	}

	// Show loading state
	Swal.fire({
		title: "Creating Account...",
		text: "Please wait while we set up your account.",
		allowOutsideClick: false,
		didOpen: () => {
			Swal.showLoading();
		},
	});

	const formdata = new FormData(createForm);

	try {
		const res = await fetch("../php/create_account.php", {
			method: "POST",
			body: formdata,
		});

		const text = await res.text();
		let data;
		try {
			data = JSON.parse(text);
		} catch (e) {
			console.log("Server error:\n" + text);
			Swal.fire({
				icon: "error",
				title: "Server Error!",
				text: "An unexpected error occurred. Please try again.",
			});
			return;
		}

		if (data.status === "success") {
			Swal.fire({
				icon: "success",
				title: "Success!",
				text: data.message,
				confirmButtonText: "Continue to Login",
			}).then(() => {
				window.location.href = "../views/login.php";
			});
		} else {
			Swal.fire({
				icon: "error",
				title: "Error!",
				text: data.message,
			});
		}
	} catch (error) {
		console.error("Network error:", error);
		Swal.fire({
			icon: "error",
			title: "Network Error!",
			text: "Please check your internet connection and try again.",
		});
	}
}
