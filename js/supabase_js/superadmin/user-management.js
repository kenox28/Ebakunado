// User Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getUsers();
	loadAddUserProvinces();
});

// Fetch and display users (reusing from home.js)
async function getUsers() {
	try {
		// MySQL: const response = await fetch("php/mysql/admin/show_users.php");
		const response = await fetch("php/supabase/admin/show_users.php");
		const data = await response.json();

		const tbody = document.querySelector("#usersTableBody");
		tbody.innerHTML = "";

		for (const user of data) {
			const formattedDate = formatDateShort(user.created_at);
			tbody.innerHTML += `<tr>
				<td class="checkbox-cell"><input type="checkbox" class="user-checkbox" value="${user.user_id}"></td>
				<td>${user.user_id}</td>
				<td>${user.fname}</td>
				<td>${user.lname}</td>
				<td>${user.email}</td>
				<td>${user.phone_number || ''}</td>
				<td>${user.gender || ''}</td>
				<td>${user.place || ''}</td>
				<td>${user.role}</td>
				<td>${formattedDate}</td>
				<td class="actions-cell">
					<button onclick="editUser('${user.user_id}')" class="action-icon-btn" aria-label="Edit user ${user.user_id}"><span class="material-symbols-rounded">edit</span></button>
					<button onclick="deleteUser('${user.user_id}')" class="action-icon-btn" aria-label="Delete user ${user.user_id}"><span class="material-symbols-rounded">delete</span></button>
				</td>
			</tr>`;
		}
	} catch (error) {
		console.error("Error fetching users:", error);
	}
}

// Format date to short month name, numeric day, full year (e.g., Nov 23, 2025)
function formatDateShort(dateStr) {
	if (!dateStr) return '';
	const date = new Date(dateStr);
	if (isNaN(date.getTime())) return dateStr; // fallback if invalid
	const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]; 
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
    openModal('addUserModal');
}

// Cancel add user
function cancelAddUser() {
    // Clear fields
    const fields = [
        'add_user_fname','add_user_lname','add_user_email','add_user_phone',
        'add_user_gender','add_user_password','add_user_confirm_password',
        'add_user_province','add_user_city_municipality','add_user_barangay','add_user_purok'
    ];
    fields.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    // Reset dependent selects
    document.getElementById('add_user_city_municipality').innerHTML = '<option value="">Select City/Municipality</option>';
    document.getElementById('add_user_barangay').innerHTML = '<option value="">Select Barangay</option>';
    document.getElementById('add_user_purok').innerHTML = '<option value="">Select Purok</option>';
    closeModal('addUserModal');
}

// Save user (create new) - No OTP verification
async function saveUser() {
	const fname = document.getElementById("add_user_fname").value.trim();
	const lname = document.getElementById("add_user_lname").value.trim();
	const email = document.getElementById("add_user_email").value.trim();
	const phone = document.getElementById("add_user_phone").value.trim();
	const gender = document.getElementById("add_user_gender").value;
	const password = document.getElementById("add_user_password").value;
	const confirmPassword = document.getElementById("add_user_confirm_password").value;
	const province = document.getElementById("add_user_province").value;
	const city = document.getElementById("add_user_city_municipality").value;
	const barangay = document.getElementById("add_user_barangay").value;
	const purok = document.getElementById("add_user_purok").value;

	// Validation
	if (!fname || !lname || !email || !phone || !gender || !password || !confirmPassword || !province || !city || !barangay || !purok) {
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
			Swal.fire("Error!", "Server returned invalid response. Please check the console for details.", "error");
			return;
		}

		if (data.status === "success") {
			console.log("User created successfully!");
			Swal.fire("Success!", "User created successfully", "success");
			cancelAddUser();
			getUsers();
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
                        <select id="edit_user_province" name="province" onchange="loadEditUserCities()" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_city_municipality">City/Municipality</label>
                        <select id="edit_user_city_municipality" name="city_municipality" onchange="loadEditUserBarangays()" required>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_barangay">Barangay</label>
                        <select id="edit_user_barangay" name="barangay" onchange="loadEditUserPuroks()" required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_purok">Purok</label>
                        <select id="edit_user_purok" name="purok" required>
                            <option value="">Select Purok</option>
                        </select>
                    </div>
                </div>
            `;

			await loadEditUserProvinces(user.place || "");
			openModal('editUserModal');
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
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	const barangay = document.getElementById("edit_user_barangay").value;
	const purok = document.getElementById("edit_user_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
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
			getUsers();
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
    const form = document.getElementById('editUserForm');
    form.innerHTML = '';
    closeModal('editUserModal');
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
				getUsers();
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
			getUsers();
		} catch (error) {
			console.error("Error deleting users:", error);
			Swal.fire("Error!", "Failed to delete users", "error");
		}
	}
}

// Place editing functions (reusing from home.js)
async function loadEditUserProvinces(currentPlace = "") {
	try {
		// Store current place globally for cascading
		window.currentUserPlace = currentPlace;

		const response = await fetch(
			// "php/mysql/admin/get_places.php?type=provinces"
			"php/supabase/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_user_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		if (currentProvince) {
			await loadEditUserCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

async function loadEditUserCities() {
	const province = document.getElementById("edit_user_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			// `php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
			`php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const data = await response.json();

		const citySelect = document.getElementById("edit_user_city_municipality");
		citySelect.innerHTML = '<option value="">Select City/Municipality</option>';

		// Get current place parts to pre-select current city
		const currentPlace = window.currentUserPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentCity = placeParts[1] || "";

		for (const item of data) {
			const selected = item.city_municipality === currentCity ? "selected" : "";
			citySelect.innerHTML += `<option value="${item.city_municipality}" ${selected}>${item.city_municipality}</option>`;
		}

		// If current city exists, load barangays
		if (currentCity) {
			await loadEditUserBarangays();
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

async function loadEditUserBarangays() {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			// `php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
			`php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const data = await response.json();

		const barangaySelect = document.getElementById("edit_user_barangay");
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		// Get current place parts to pre-select current barangay
		const currentPlace = window.currentUserPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentBarangay = placeParts[2] || "";

		for (const item of data) {
			const selected = item.barangay === currentBarangay ? "selected" : "";
			barangaySelect.innerHTML += `<option value="${item.barangay}" ${selected}>${item.barangay}</option>`;
		}

		// If current barangay exists, load puroks
		if (currentBarangay) {
			await loadEditUserPuroks();
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

async function loadEditUserPuroks() {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	const barangay = document.getElementById("edit_user_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			// `php/mysql/admin/get_places.php?type=puroks&province=${encodeURIComponent(
			`php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const data = await response.json();

		const purokSelect = document.getElementById("edit_user_purok");
		purokSelect.innerHTML = '<option value="">Select Purok</option>';

		// Get current place parts to pre-select current purok
		const currentPlace = window.currentUserPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentPurok = placeParts[3] || "";

		for (const item of data) {
			const selected = item.purok === currentPurok ? "selected" : "";
			purokSelect.innerHTML += `<option value="${item.purok}" ${selected}>${item.purok}</option>`;
		}
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}

// Place loading functions for Add User Form
async function loadAddUserProvinces() {
	try {
		const response = await fetch(
			"php/supabase/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("add_user_province");
		if (!provinceSelect) return;
		
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		for (const item of data) {
			provinceSelect.innerHTML += `<option value="${item.province}">${item.province}</option>`;
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

async function loadAddUserCities() {
	const province = document.getElementById("add_user_province").value;
	const citySelect = document.getElementById("add_user_city_municipality");
	const barangaySelect = document.getElementById("add_user_barangay");
	const purokSelect = document.getElementById("add_user_purok");

	// Reset dependent dropdowns
	citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
	barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province) return;

	try {
		const response = await fetch(
			`php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const data = await response.json();

		for (const item of data) {
			citySelect.innerHTML += `<option value="${item.city_municipality}">${item.city_municipality}</option>`;
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

async function loadAddUserBarangays() {
	const province = document.getElementById("add_user_province").value;
	const city = document.getElementById("add_user_city_municipality").value;
	const barangaySelect = document.getElementById("add_user_barangay");
	const purokSelect = document.getElementById("add_user_purok");

	// Reset dependent dropdowns
	barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province || !city) return;

	try {
		const response = await fetch(
			`php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const data = await response.json();

		for (const item of data) {
			barangaySelect.innerHTML += `<option value="${item.barangay}">${item.barangay}</option>`;
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

async function loadAddUserPuroks() {
	const province = document.getElementById("add_user_province").value;
	const city = document.getElementById("add_user_city_municipality").value;
	const barangay = document.getElementById("add_user_barangay").value;
	const purokSelect = document.getElementById("add_user_purok");

	// Reset purok dropdown
	purokSelect.innerHTML = '<option value="">Select Purok</option>';

	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const data = await response.json();

		for (const item of data) {
			purokSelect.innerHTML += `<option value="${item.purok}">${item.purok}</option>`;
		}
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}
