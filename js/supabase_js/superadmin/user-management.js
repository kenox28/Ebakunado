// User Management JavaScript

const USERS_LIMIT = 10;
let currentUsersPage = 1;

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	initUserPager();
	setupUserSearchHandlers();
	getUsers(1);
});

// Fetch and display users (reusing from home.js)
async function getUsers(page = 1) {
	try {
		const tbody = document.querySelector("#usersTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr class="data-table__message-row loading"><td colspan="11">Loading users...</td></tr>';
		}

		const searchInput = document.getElementById("searchUsers");
		const searchTerm = searchInput ? searchInput.value.trim() : "";
		const params = new URLSearchParams({
			page: page,
			limit: USERS_LIMIT,
		});
		if (searchTerm) {
			params.append("search", searchTerm);
		}

		const response = await fetch(
			`php/supabase/superadmin/list_users.php?${params.toString()}`
		);
		const result = await response.json();

		if (result.status !== "success") {
			throw new Error(result.message || "Failed to load users");
		}

		const data = Array.isArray(result.data) ? result.data : [];
		const total = result.total || 0;

		// Handle case where page is out of range after deletions
		if (total > 0 && data.length === 0 && page > 1) {
			getUsers(page - 1);
			return;
		}

		tbody.innerHTML = "";

		if (!data.length) {
			tbody.innerHTML =
				'<tr class="data-table__message-row"><td colspan="11">No users found.</td></tr>';
		} else {
			for (const user of data) {
				const formattedDate = formatDateShort(user.created_at);
				tbody.innerHTML += `<tr>
				<td class="checkbox-cell"><input type="checkbox" class="user-checkbox" value="${
					user.user_id
				}"></td>
				<td>${user.user_id}</td>
				<td>${user.fname}</td>
				<td>${user.lname}</td>
				<td>${user.email}</td>
				<td>${user.phone_number || ""}</td>
				<td>${user.gender || ""}</td>
				<td>${user.place || ""}</td>
				<td>${user.role}</td>
				<td>${formattedDate}</td>
				<td class="actions-cell">
					<button onclick="editUser('${
						user.user_id
					}')" class="action-icon-btn" aria-label="Edit user ${
					user.user_id
				}"><span class="material-symbols-rounded">edit</span></button>
					<button onclick="deleteUser('${
						user.user_id
					}')" class="action-icon-btn" aria-label="Delete user ${
					user.user_id
				}"><span class="material-symbols-rounded">delete</span></button>
				</td>
			</tr>`;
			}
		}

		currentUsersPage = result.page || page;
		updateUserPager({
			page: currentUsersPage,
			limit: result.limit || USERS_LIMIT,
			total,
			hasMore: result.has_more || false,
		});
	} catch (error) {
		console.error("Error fetching users:", error);
		const tbody = document.querySelector("#usersTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr class="data-table__message-row error"><td colspan="11">Failed to load users.</td></tr>';
		}
		updateUserPager({ page: 1, limit: USERS_LIMIT, total: 0, hasMore: false });
	}
}

function initUserPager() {
	const prevBtn = document.getElementById("usersPrevBtn");
	const nextBtn = document.getElementById("usersNextBtn");

	if (prevBtn) {
		prevBtn.addEventListener("click", () => {
			const page = parseInt(prevBtn.dataset.page || "1", 10);
			if (page > 1) {
				getUsers(page - 1);
			}
		});
	}
	if (nextBtn) {
		nextBtn.addEventListener("click", () => {
			const page = parseInt(nextBtn.dataset.page || "1", 10);
			getUsers(page + 1);
		});
	}
}

function updateUserPager({ page, limit, total, hasMore }) {
	const prevBtn = document.getElementById("usersPrevBtn");
	const nextBtn = document.getElementById("usersNextBtn");
	const info = document.getElementById("usersPageInfo");

	if (!prevBtn || !nextBtn || !info) return;

	const start = total === 0 ? 0 : (page - 1) * limit + 1;
	const end = total === 0 ? 0 : Math.min(page * limit, total);

	info.textContent = `Showing ${start}-${end} of ${total}`;
	prevBtn.disabled = page <= 1;
	nextBtn.disabled = !hasMore;

	prevBtn.dataset.page = String(page);
	nextBtn.dataset.page = String(page);
}

function setupUserSearchHandlers() {
	const input = document.getElementById("searchUsers");
	if (!input) return;

	input.addEventListener("keydown", (event) => {
		if (event.key === "Enter") {
			event.preventDefault();
			getUsers(1);
		}
	});
	input.addEventListener("input", () => {
		if (!input.value.trim()) {
			getUsers(1);
		}
	});
}

// Format date to short month name, numeric day, full year (e.g., Nov 23, 2025)
function formatDateShort(dateStr) {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	if (isNaN(date.getTime())) return dateStr; // fallback if invalid
	const monthNames = [
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"May",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Oct",
		"Nov",
		"Dec",
	];
	const month = monthNames[date.getMonth()];
	const day = date.getDate();
	const year = date.getFullYear();
	return `${month} ${day}, ${year}`;
}

// Toggle all user checkboxes
function toggleAllUsers() {
	const selectAll = document.getElementById("selectAllUsers");
	const checkboxes = document.querySelectorAll(".user-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Show add user form
function showAddUserForm() {
	openModal("addUserModal");
}

// Cancel add user
function cancelAddUser() {
	// Clear fields
	const fields = [
		"add_user_fname",
		"add_user_lname",
		"add_user_email",
		"add_user_phone",
		"add_user_gender",
		"add_user_password",
		"add_user_confirm_password",
		"add_user_province",
		"add_user_city_municipality",
		"add_user_barangay",
		"add_user_purok",
	];
	fields.forEach((id) => {
		const el = document.getElementById(id);
		if (el) el.value = "";
	});
	// Clear input fields (no longer using dropdowns)
	closeModal("addUserModal");
}

// Save user (create new) - No OTP verification
async function saveUser() {
	const fname = document.getElementById("add_user_fname").value.trim();
	const lname = document.getElementById("add_user_lname").value.trim();
	const email = document.getElementById("add_user_email").value.trim();
	const phone = document.getElementById("add_user_phone").value.trim();
	const gender = document.getElementById("add_user_gender").value;
	const password = document.getElementById("add_user_password").value;
	const confirmPassword = document.getElementById(
		"add_user_confirm_password"
	).value;
	const province = document.getElementById("add_user_province").value.trim();
	const city = document
		.getElementById("add_user_city_municipality")
		.value.trim();
	const barangay = document.getElementById("add_user_barangay").value.trim();
	const purok = document.getElementById("add_user_purok").value.trim();

	// Validation
	if (
		!fname ||
		!lname ||
		!email ||
		!phone ||
		!gender ||
		!password ||
		!confirmPassword ||
		!province ||
		!city ||
		!barangay ||
		!purok
	) {
		Swal.fire("Error!", "Please fill in all fields", "error");
		return;
	}

	if (password !== confirmPassword) {
		Swal.fire("Error!", "Passwords do not match", "error");
		return;
	}

	if (password.length < 8) {
		Swal.fire("Error!", "Password must be at least 8 characters long", "error");
		return;
	}

	const formData = new FormData();
	formData.append("fname", fname);
	formData.append("lname", lname);
	formData.append("email", email);
	formData.append("number", phone);
	formData.append("password", password);
	formData.append("confirm_password", confirmPassword);
	formData.append("gender", gender);
	formData.append("province", province);
	formData.append("city_municipality", city);
	formData.append("barangay", barangay);
	formData.append("purok", purok);

	try {
		console.log("Sending user creation request...");
		const response = await fetch("php/supabase/superadmin/create_user.php", {
			method: "POST",
			body: formData,
		});

		console.log("Response status:", response.status);
		console.log("Response statusText:", response.statusText);
		console.log("Response headers:", response.headers);

		const text = await response.text();
		console.log("Raw response text:", text);

		let data;
		try {
			data = JSON.parse(text);
			console.log("Parsed JSON data:", data);
		} catch (e) {
			console.error("JSON Parse Error:", e);
			console.error("Failed to parse response as JSON. Raw text:", text);
			Swal.fire(
				"Error!",
				"Server returned invalid response. Please check the console for details.",
				"error"
			);
			return;
		}

		if (data.status === "success") {
			console.log("User created successfully!");
			Swal.fire("Success!", "User created successfully", "success");
			cancelAddUser();
			getUsers(currentUsersPage);
		} else {
			console.error("Create user failed. Response data:", data);
			if (data.debug) {
				console.error("Debug information:", data.debug);
			}
			Swal.fire("Error!", data.message || "Failed to create user", "error");
		}
	} catch (error) {
		console.error("Network/Fetch Error:", error);
		console.error("Error name:", error.name);
		console.error("Error message:", error.message);
		console.error("Error stack:", error.stack);
		Swal.fire("Error!", "Failed to save user: " + error.message, "error");
	}
}

// Edit user function (reusing from home.js with place editing)
async function editUser(user_id) {
	try {
		const response = await fetch(
			// `php/mysql/admin/edit_user.php?user_id=${user_id}`
			`php/supabase/admin/edit_user.php?user_id=${user_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const user = data.data;
			const form = document.getElementById("editUserForm");

			form.innerHTML = `
				<input type="hidden" id="edit_user_id" value="${user.user_id}">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_fname">First Name</label>
                        <input type="text" id="edit_user_fname" name="fname" value="${
													user.fname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_lname">Last Name</label>
                        <input type="text" id="edit_user_lname" name="lname" value="${
													user.lname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_email">Email</label>
                        <input type="email" id="edit_user_email" name="email" value="${
													user.email
												}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_phone">Phone Number</label>
                        <input type="text" id="edit_user_phone" name="phone_number" value="${
													user.phone_number
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_gender">Gender</label>
                        <select id="edit_user_gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" ${
															user.gender === "Male" ? "selected" : ""
														}>Male</option>
                            <option value="Female" ${
															user.gender === "Female" ? "selected" : ""
														}>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_role">Role</label>
                        <select id="edit_user_role" name="role" required>
                            <option value="user" ${
															user.role === "user" ? "selected" : ""
														}>User</option>
                            <option value="bhw" ${
															user.role === "bhw" ? "selected" : ""
														}>BHW</option>
                            <option value="midwife" ${
															user.role === "midwife" ? "selected" : ""
														}>Midwife</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_province">Province</label>
                        <input type="text" id="edit_user_province" name="province" placeholder="Enter province" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_city_municipality">City/Municipality</label>
                        <input type="text" id="edit_user_city_municipality" name="city_municipality" placeholder="Enter city/municipality" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_barangay">Barangay</label>
                        <input type="text" id="edit_user_barangay" name="barangay" placeholder="Enter barangay" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_purok">Purok</label>
                        <input type="text" id="edit_user_purok" name="purok" placeholder="Enter purok" required>
                    </div>
                </div>
            `;

			// Parse existing place value (comma-separated: province, city, barangay, purok)
			const placeParts = (user.place || "").split(", ").map((p) => p.trim());
			document.getElementById("edit_user_province").value = placeParts[0] || "";
			document.getElementById("edit_user_city_municipality").value =
				placeParts[1] || "";
			document.getElementById("edit_user_barangay").value = placeParts[2] || "";
			document.getElementById("edit_user_purok").value = placeParts[3] || "";

			openModal("editUserModal");
		} else {
			Swal.fire("Error!", "Failed to load user data", "error");
		}
	} catch (error) {
		console.error("Error editing user:", error);
		Swal.fire("Error!", "Failed to load user data", "error");
	}
}

// Update user
async function updateUser() {
	const formData = new FormData();

	formData.append("user_id", document.getElementById("edit_user_id").value);
	formData.append("fname", document.getElementById("edit_user_fname").value);
	formData.append("lname", document.getElementById("edit_user_lname").value);
	formData.append("email", document.getElementById("edit_user_email").value);
	formData.append(
		"phone_number",
		document.getElementById("edit_user_phone").value
	);
	formData.append("gender", document.getElementById("edit_user_gender").value);
	formData.append("role", document.getElementById("edit_user_role").value);

	// Combine place data
	const province = document.getElementById("edit_user_province").value.trim();
	const city = document
		.getElementById("edit_user_city_municipality")
		.value.trim();
	const barangay = document.getElementById("edit_user_barangay").value.trim();
	const purok = document.getElementById("edit_user_purok").value.trim();
	const place = [province, city, barangay, purok].filter((p) => p).join(", ");
	formData.append("place", place);

	try {
		// const response = await fetch("php/mysql/admin/save_user.php", {
		const response = await fetch("php/supabase/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		console.log("Update user response:", data);
		if (data.status === "success") {
			Swal.fire("Success!", "User updated successfully", "success");
			cancelEditUser();
			getUsers(currentUsersPage);
		} else {
			console.error("Update user error:", data);
			Swal.fire(
				"Error!",
				data.message +
					(data.debug ? "\nDebug: " + JSON.stringify(data.debug) : ""),
				"error"
			);
		}
	} catch (error) {
		console.error("Error updating user:", error);
		Swal.fire("Error!", "Failed to update user", "error");
	}
}

// Cancel edit user
function cancelEditUser() {
	const form = document.getElementById("editUserForm");
	form.innerHTML = "";
	closeModal("editUserModal");
}

// Delete user
async function deleteUser(user_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the user account!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("user_id", user_id);

			// const response = await fetch("php/mysql/admin/delete_user.php", {
			const response = await fetch("php/supabase/admin/delete_user.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "User has been deleted.", "success");
				getUsers(currentUsersPage);
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting user:", error);
			Swal.fire("Error!", "Failed to delete user", "error");
		}
	}
}

// Delete selected users
async function deleteSelectedUsers() {
	const selectedBoxes = document.querySelectorAll(".user-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one user to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected user(s)!`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("user_id", checkbox.value);
				// await fetch("php/mysql/admin/delete_user.php", {
				await fetch("php/supabase/admin/delete_user.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} user(s) deleted successfully`,
				"success"
			);
			getUsers(currentUsersPage);
		} catch (error) {
			console.error("Error deleting users:", error);
			Swal.fire("Error!", "Failed to delete users", "error");
		}
	}
}

// Place editing functions removed - now using input fields instead of dropdowns
