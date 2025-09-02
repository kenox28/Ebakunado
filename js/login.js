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

	Swal.fire({
		title: "Logging in...",
		text: "Please wait while we verify your credentials.",
		allowOutsideClick: false,
		didOpen: () => {
			Swal.showLoading();
		},
	});

	const formdata = new FormData(loginForm);

	console.log("Form data being sent:");
	for (let [key, value] of formdata.entries()) {
		console.log(key + ": " + value);
	}

	try {
		const res = await fetch("../php/login.php", {
			method: "POST",
			body: formdata,
		});

		const text = await res.text();
		console.log("Server response:", text);

		let data;
		try {
			data = JSON.parse(text);
			console.log("Parsed data:", data);
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
			if (data.user_type === "super_admin") {
				Swal.fire({
					icon: "success",
					title: "Welcome Super Admin!",
					text: data.message,
					confirmButtonText: "Continue",
				}).then(() => {
					window.location.href = "../views/super_admin/home.php";
				});
			} else if (data.user_type === "admin") {
				Swal.fire({
					icon: "success",
					title: "Welcome Admin!",
					text: data.message,
					confirmButtonText: "Continue",
				}).then(() => {
					window.location.href = "../views/admin/home.php";
				});
			} else {
				Swal.fire({
					icon: "success",
					title: "Welcome back!",
					text: data.message,
					confirmButtonText: "Continue",
				}).then(() => {
					window.location.href = "../views/users/home.php";
				});
			}
		} else if (data.status === "already_logged_in") {
			if (data.user_type === "super_admin") {
				window.location.href = "../views/super_admin/home.php";
			} else if (data.user_type === "admin") {
				window.location.href = "../views/admin/home.php";
			} else {
				window.location.href = "../views/users/home.php";
			}
		} else {
			Swal.fire({
				icon: "error",
				title: "Login Failed!",
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
