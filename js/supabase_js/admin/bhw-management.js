// Admin BHW Management Functions

// Global variable for current BHW place
window.currentBhwPlace = "";

// Get all BHWs
async function getBhws() {
	showTableLoading("bhwTableBody", 9);

	try {
		const response = await fetch("../../php/supabase/admin/show_bhw.php");
		// const response = await fetch("../../php/mysql/admin/show_bhw.php");
		const data = await response.json();

		const tbody = document.getElementById("bhwTableBody");

		if (data && data.length > 0) {
			let html = "";
			data.forEach((bhw) => {
				const permissionBadge = getPermissionBadge(bhw.permissions);
				html += `
                    <tr>
                        <td>${bhw.bhw_id}</td>
                        <td>${bhw.fname} ${bhw.lname}</td>
                        <td>${bhw.email}</td>
                        <td>${bhw.phone_number || "N/A"}</td>
                        <td>${bhw.gender || "N/A"}</td>
                        <td><span class="badge ${permissionBadge.class}">${
					permissionBadge.text
				}</span></td>
                        <td>${bhw.place || "N/A"}</td>
                        <td>${formatDate(bhw.created_at)}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" onclick="editBhw('${
																	bhw.bhw_id
																}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteBhw('${
																	bhw.bhw_id
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
			showTableEmpty("bhwTableBody", "No BHWs found", 9);
		}
	} catch (error) {
		console.error("Error fetching BHWs:", error);
		showTableError("bhwTableBody", "Failed to load BHWs", 9);
	}
}

// Get permission badge styling
function getPermissionBadge(permission) {
	switch (permission?.toLowerCase()) {
		case "read":
			return { class: "bg-info", text: "Read" };
		case "write":
			return { class: "bg-warning", text: "Write" };
		case "admin":
			return { class: "bg-danger", text: "Admin" };
		default:
			return { class: "bg-secondary", text: permission || "Unknown" };
	}
}

// Edit BHW
async function editBhw(bhw_id) {
	try {
		const response = await fetch(
			`../../php/supabase/admin/edit_bhw.php?bhw_id=${encodeURIComponent(
				bhw_id
			)}`
			// `../../php/mysql/admin/edit_bhw.php?bhw_id=${encodeURIComponent(bhw_id)}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const bhw = data.data;

			// Store current place globally for cascading dropdowns
			window.currentBhwPlace = bhw.place || "";

			const editForm = document.getElementById("editBhwForm");
			editForm.innerHTML = `
                <form id="bhwEditForm">
                    <input type="hidden" id="edit_bhw_id" value="${bhw.bhw_id}">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_bhw_fname">First Name</label>
                            <input type="text" class="form-control" id="edit_bhw_fname" value="${
															bhw.fname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_lname">Last Name</label>
                            <input type="text" class="form-control" id="edit_bhw_lname" value="${
															bhw.lname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_email">Email</label>
                            <input type="email" class="form-control" id="edit_bhw_email" value="${
															bhw.email
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_phone">Phone Number</label>
                            <input type="text" class="form-control" id="edit_bhw_phone" value="${
															bhw.phone_number || ""
														}">
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_gender">Gender</label>
                            <select class="form-control" id="edit_bhw_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" ${
																	bhw.gender === "Male" ? "selected" : ""
																}>Male</option>
                                <option value="Female" ${
																	bhw.gender === "Female" ? "selected" : ""
																}>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_permissions">Permissions</label>
                            <select class="form-control" id="edit_bhw_permissions" required>
                                <option value="read" ${
																	bhw.permissions === "read" ? "selected" : ""
																}>Read</option>
                                <option value="write" ${
																	bhw.permissions === "write" ? "selected" : ""
																}>Write</option>
                                <option value="admin" ${
																	bhw.permissions === "admin" ? "selected" : ""
																}>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_role">Role</label>
                            <select class="form-control" id="edit_bhw_role" required>
                                <option value="bhw" ${
																	bhw.role === "bhw" ? "selected" : ""
																}>BHW</option>
                                <option value="user" ${
																	bhw.role === "user" ? "selected" : ""
																}>User</option>
                                <option value="midwife" ${
																	bhw.role === "midwife" ? "selected" : ""
																}>Midwife</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Location Fields -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_bhw_province">Province</label>
                            <select class="form-control" id="edit_bhw_province" onchange="loadEditBhwCities()" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_city_municipality">City/Municipality</label>
                            <select class="form-control" id="edit_bhw_city_municipality" onchange="loadEditBhwBarangays()" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_barangay">Barangay</label>
                            <select class="form-control" id="edit_bhw_barangay" onchange="loadEditBhwPuroks()" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_bhw_purok">Purok</label>
                            <select class="form-control" id="edit_bhw_purok" required>
                                <option value="">Select Purok</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" onclick="cancelEditBhw()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="updateBhw()">Update BHW</button>
                    </div>
                </form>
            `;

			// Load provinces and set up cascading dropdowns
			await loadEditBhwProvinces();

			// Show modal
			// const modal = new bootstrap.Modal(
			// 	document.getElementById("editBhwModal")
			// );
			// modal.show();
		} else {
			// Swal.fire("Error!", data.message || "Failed to load BHW data", "error");
		}
	} catch (error) {
		console.error("Error loading BHW:", error);
		// Swal.fire("Error!", "Failed to load BHW data", "error");
	}
}

// Load provinces for edit form
async function loadEditBhwProvinces() {
	try {
		const response = await fetch(
			"../../php/supabase/admin/get_places.php?type=provinces"
			// "../../php/mysql/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_bhw_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		// Get current place parts to pre-select current province
		const currentPlace = window.currentBhwPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		// If current province exists, load cities
		if (currentProvince) {
			await loadEditBhwCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

// Load cities for edit form
async function loadEditBhwCities() {
	const province = document.getElementById("edit_bhw_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
				// `../../php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const data = await response.json();

		const citySelect = document.getElementById("edit_bhw_city_municipality");
		citySelect.innerHTML = '<option value="">Select City/Municipality</option>';

		// Get current place parts to pre-select current city
		const currentPlace = window.currentBhwPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentCity = placeParts[1] || "";

		for (const item of data) {
			const selected = item.city_municipality === currentCity ? "selected" : "";
			citySelect.innerHTML += `<option value="${item.city_municipality}" ${selected}>${item.city_municipality}</option>`;
		}

		// If current city exists, load barangays
		if (currentCity) {
			await loadEditBhwBarangays();
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

// Load barangays for edit form
async function loadEditBhwBarangays() {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				// `../../php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const data = await response.json();

		const barangaySelect = document.getElementById("edit_bhw_barangay");
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		// Get current place parts to pre-select current barangay
		const currentPlace = window.currentBhwPlace || "";
		const placeParts = currentPlace.split(", ");
		const currentBarangay = placeParts[2] || "";

		for (const item of data) {
			const selected = item.barangay === currentBarangay ? "selected" : "";
			barangaySelect.innerHTML += `<option value="${item.barangay}" ${selected}>${item.barangay}</option>`;
		}

		// If current barangay exists, load puroks
		if (currentBarangay) {
			await loadEditBhwPuroks();
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

// Load puroks for edit form
async function loadEditBhwPuroks() {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	const barangay = document.getElementById("edit_bhw_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				// `../../php/mysql/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const data = await response.json();

		const purokSelect = document.getElementById("edit_bhw_purok");
		purokSelect.innerHTML = '<option value="">Select Purok</option>';

		// Get current place parts to pre-select current purok
		const currentPlace = window.currentBhwPlace || "";
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

// Update BHW
async function updateBhw() {
	const formData = new FormData();

	formData.append("user_id", document.getElementById("edit_bhw_id").value);
	formData.append("fname", document.getElementById("edit_bhw_fname").value);
	formData.append("lname", document.getElementById("edit_bhw_lname").value);
	formData.append("email", document.getElementById("edit_bhw_email").value);
	formData.append(
		"phone_number",
		document.getElementById("edit_bhw_phone").value
	);
	formData.append("gender", document.getElementById("edit_bhw_gender").value);
	formData.append(
		"permissions",
		document.getElementById("edit_bhw_permissions").value
	);
	formData.append("role", document.getElementById("edit_bhw_role").value);

	// Combine place data
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	const barangay = document.getElementById("edit_bhw_barangay").value;
	const purok = document.getElementById("edit_bhw_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
	formData.append("place", place);

	try {
		const response = await fetch("../../php/supabase/admin/save_user.php", {
			// const response = await fetch("../../php/mysql/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			// Swal.fire("Success!", "BHW updated successfully", "success");
			alert("Success!", "BHW updated successfully", "success");
			cancelEditBhw();
			getBhws(); // Reload BHWs table
		} else {
			Swal.fire("Error!", data.message || "Failed to update BHW", "error");
		}
	} catch (error) {
		console.error("Error updating BHW:", error);
		// Swal.fire("Error!", "Failed to update BHW", "error");
	}
}

// Cancel edit BHW
function cancelEditBhw() {
	const modal = bootstrap.Modal.getInstance(
		document.getElementById("editBhwModal")
	);
	modal.hide();
	window.currentBhwPlace = ""; // Clear global variable
}

// Delete BHW
async function deleteBhw(bhw_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the BHW!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		cancelButtonColor: "#6c757d",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("bhw_id", bhw_id);

			const response = await fetch("../../php/supabase/admin/delete_bhw.php", {
				// const response = await fetch("../../php/mysql/admin/delete_bhw.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "BHW has been deleted.", "success");
				getBhws(); // Reload BHWs table
			} else {
				Swal.fire("Error!", data.message || "Failed to delete BHW", "error");
			}
		} catch (error) {
			console.error("Error deleting BHW:", error);
			Swal.fire("Error!", "Failed to delete BHW", "error");
		}
	}
}
