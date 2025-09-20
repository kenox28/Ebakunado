// User Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getUsers();
});

// Fetch and display users (reusing from home.js)
async function getUsers() {
	try {
		// MySQL: const response = await fetch("../../php/mysql/admin/show_users.php");
		const response = await fetch("../../../php/supabase/admin/show_users.php");
		const data = await response.json();

		const tbody = document.querySelector("#usersTableBody");
		tbody.innerHTML = "";

		for (const user of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="user-checkbox" value="${
									user.user_id
								}"></td>
                <td>${user.user_id}</td>
                <td>${user.fname}</td>
                <td>${user.lname}</td>
                <td>${user.email}</td>
                <td>${user.phone_number}</td>
                <td>${user.gender || ""}</td>
                <td>${user.place || ""}</td>
                <td>${user.role}</td>
                <td>${user.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editUser('${
											user.user_id
										}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteUser('${
											user.user_id
										}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching users:", error);
	}
}

// Toggle all user checkboxes
function toggleAllUsers() {
	const selectAll = document.getElementById("selectAllUsers");
	const checkboxes = document.querySelectorAll(".user-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Edit user function (reusing from home.js with place editing)
async function editUser(user_id) {
	try {
		const response = await fetch(
			// `../../php/mysql/admin/edit_user.php?user_id=${user_id}`
			`../../../php/supabase/admin/edit_user.php?user_id=${user_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const user = data.data;
			const form = document.getElementById("editUserForm");

			form.innerHTML = `
                <h3>Edit User</h3>
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
                <div class="action-buttons">
                    <button type="button" onclick="updateUser()" class="btn btn-primary">Update User</button>
                    <button type="button" onclick="cancelEditUser()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			form.scrollIntoView({ behavior: "smooth" });
			await loadEditUserProvinces(user.place || "");
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
		// const response = await fetch("../../php/mysql/admin/save_user.php", {
		const response = await fetch("../../../php/supabase/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "User updated successfully", "success");
			cancelEditUser();
			getUsers();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error updating user:", error);
		Swal.fire("Error!", "Failed to update user", "error");
	}
}

// Cancel edit user
function cancelEditUser() {
	const form = document.getElementById("editUserForm");
	form.style.display = "none";
	form.innerHTML = "";
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

			// const response = await fetch("../../php/mysql/admin/delete_user.php", {
			const response = await fetch(
				"../../../php/supabase/admin/delete_user.php",
				{
					method: "POST",
					body: formData,
				}
			);

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
				// await fetch("../../php/mysql/admin/delete_user.php", {
				await fetch("../../../php/supabase/admin/delete_user.php", {
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
			// "../../php/mysql/admin/get_places.php?type=provinces"
			"../../../php/supabase/admin/get_places.php?type=provinces"
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
			// `../../php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
			`../../../php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
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
			// `../../php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
			`../../../php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
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
			// `../../php/mysql/admin/get_places.php?type=puroks&province=${encodeURIComponent(
			`../../../php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
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
