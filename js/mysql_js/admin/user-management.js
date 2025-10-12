// Admin User Management Functions

// Global variable for current user place
window.currentUserPlace = "";

// Get all users
async function getUsers() {
	showTableLoading("usersTableBody", 9);

	try {
		// const response = await fetch("../../php/supabase/admin/show_users.php");
		const response = await fetch("../../php/mysql/admin/show_users.php");
		const data = await response.json();

		const tbody = document.getElementById("usersTableBody");

		if (data && data.length > 0) {
			let html = "";
			data.forEach((user) => {
				const roleBadge = getRoleBadge(user.role);
				html += `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>${user.fname} ${user.lname}</td>
                        <td>${user.email}</td>
                        <td>${user.phone_number || "N/A"}</td>
                        <td>${user.gender || "N/A"}</td>
                        <td><span class="badge ${roleBadge.class}">${
					roleBadge.text
				}</span></td>
                        <td>${user.place || "N/A"}</td>
                        <td>${formatDate(user.created_at)}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" onclick="editUser('${
																	user.user_id
																}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteUser('${
																	user.user_id
																}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
			});
			tbody.innerHTML = html;
		} else {
			showTableEmpty("usersTableBody", "No users found", 9);
		}
	} catch (error) {
		console.error("Error fetching users:", error);
		showTableError("usersTableBody", "Failed to load users", 9);
	}
}

// Get role badge styling
function getRoleBadge(role) {
	switch (role?.toLowerCase()) {
		case "user":
			return { class: "bg-primary", text: "User" };
		case "bhw":
			return { class: "bg-success", text: "BHW" };
		case "midwife":
			return { class: "bg-info", text: "Midwife" };
		default:
			return { class: "bg-secondary", text: role || "Unknown" };
	}
}

// Edit user
async function editUser(user_id) {
	try {
		const response = await fetch(
			// `../../php/supabase/admin/edit_user.php?user_id=${encodeURIComponent(user_id)}`
			`../../php/mysql/admin/edit_user.php?user_id=${encodeURIComponent(
				user_id
			)}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const user = data.data;

			// Store current place globally for cascading dropdowns
			window.currentUserPlace = user.place || "";

			const editForm = document.getElementById("editUserForm");
			editForm.innerHTML = `
                <form id="userEditForm">
                    <input type="hidden" id="edit_user_id" value="${
											user.user_id
										}">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_user_fname">First Name</label>
                            <input type="text" class="form-control" id="edit_user_fname" value="${
															user.fname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_lname">Last Name</label>
                            <input type="text" class="form-control" id="edit_user_lname" value="${
															user.lname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_email">Email</label>
                            <input type="email" class="form-control" id="edit_user_email" value="${
															user.email
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_phone">Phone Number</label>
                            <input type="text" class="form-control" id="edit_user_phone" value="${
															user.phone_number || ""
														}">
                        </div>
                        <div class="form-group">
                            <label for="edit_user_gender">Gender</label>
                            <select class="form-control" id="edit_user_gender" required>
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
                            <select class="form-control" id="edit_user_role" required>
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
                    
                    <!-- Location Fields -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_user_province">Province</label>
                            <select class="form-control" id="edit_user_province" onchange="loadEditUserCities()" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_city_municipality">City/Municipality</label>
                            <select class="form-control" id="edit_user_city_municipality" onchange="loadEditUserBarangays()" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_barangay">Barangay</label>
                            <select class="form-control" id="edit_user_barangay" onchange="loadEditUserPuroks()" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_user_purok">Purok</label>
                            <select class="form-control" id="edit_user_purok" required>
                                <option value="">Select Purok</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" onclick="cancelEditUser()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="updateUser()">Update User</button>
                    </div>
                </form>
            `;

			// Load provinces and set up cascading dropdowns
			await loadEditUserProvinces();

			// Show modal
			// const modal = new bootstrap.Modal(
			// 	document.getElementById("editUserModal")
			// );
			// modal.show();
			alert("success");
		} else {
			// Swal.fire("Error!", data.message || "Failed to load user data", "error");
		}
	} catch (error) {
		console.error("Error loading user:", error);
		Swal.fire("Error!", "Failed to load user data", "error");
	}
}

// Load provinces for edit form
async function loadEditUserProvinces() {
	try {
		const response = await fetch(
			// "../../php/supabase/admin/get_places.php?type=provinces"
			"../../php/mysql/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_user_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		// Get current place parts to pre-select current province
		const currentPlace = window.currentUserPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		// If current province exists, load cities
		if (currentProvince) {
			await loadEditUserCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

// Load cities for edit form
async function loadEditUserCities() {
	const province = document.getElementById("edit_user_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			// `../../php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
			`../../php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
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

// Load barangays for edit form
async function loadEditUserBarangays() {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			// `../../php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
			`../../php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
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

// Load puroks for edit form
async function loadEditUserPuroks() {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	const barangay = document.getElementById("edit_user_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			// `../../php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
			`../../php/mysql/admin/get_places.php?type=puroks&province=${encodeURIComponent(
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
		// const response = await fetch("../../php/supabase/admin/save_user.php");
		const response = await fetch("../../php/mysql/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			// Swal.fire("Success!", "User updated successfully", "success");
			alert("success");

			cancelEditUser();
			getUsers(); // Reload users table
		} else {
			// Swal.fire("Error!", data.message || "Failed to update user", "error");
			alert("failed");
		}
	} catch (error) {
		console.error("Error updating user:", error);
		// Swal.fire("Error!", "Failed to update user", "error");
	}
}

// Cancel edit user
function cancelEditUser() {
	const modal = bootstrap.Modal.getInstance(
		document.getElementById("editUserModal")
	);
	modal.hide();
	window.currentUserPlace = ""; // Clear global variable
}

// Delete user
async function deleteUser(user_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the user!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		cancelButtonColor: "#6c757d",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("user_id", user_id);

			// const response = await fetch("../../php/supabase/admin/delete_user.php");
			const response = await fetch("../../php/mysql/admin/delete_user.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "User has been deleted.", "success");
				getUsers(); // Reload users table
			} else {
				Swal.fire("Error!", data.message || "Failed to delete user", "error");
			}
		} catch (error) {
			console.error("Error deleting user:", error);
			Swal.fire("Error!", "Failed to delete user", "error");
		}
	}
}
