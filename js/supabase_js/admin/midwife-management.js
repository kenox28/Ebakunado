// Admin Midwife Management Functions

// Global variable for current Midwife place
window.currentMidwifePlace = "";

// Get all Midwives
async function getMidwives() {
	showTableLoading("midwivesTableBody", 10);

	try {
		// const response = await fetch("../../php/mysql/admin/show_midwife.php");
		const response = await fetch(
			"../../../php/supabase/admin/show_midwife.php"
		);
		const data = await response.json();

		const tbody = document.getElementById("midwivesTableBody");

		if (data && data.length > 0) {
			let html = "";
			data.forEach((midwife) => {
				const permissionBadge = getPermissionBadge(midwife.permissions);
				const approvedBadge =
					midwife.Approve == 1
						? '<span class="badge bg-success">Yes</span>'
						: '<span class="badge bg-danger">No</span>';

				html += `
                    <tr>
                        <td>${midwife.midwife_id}</td>
                        <td>${midwife.fname} ${midwife.lname}</td>
                        <td>${midwife.email}</td>
                        <td>${midwife.phone_number || "N/A"}</td>
                        <td>${midwife.gender || "N/A"}</td>
                        <td><span class="badge ${permissionBadge.class}">${
					permissionBadge.text
				}</span></td>
                        <td>${approvedBadge}</td>
                        <td>${midwife.place || "N/A"}</td>
                        <td>${formatDate(midwife.created_at)}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" onclick="editMidwife('${
																	midwife.midwife_id
																}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteMidwife('${
																	midwife.midwife_id
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
			showTableEmpty("midwivesTableBody", "No midwives found", 10);
		}
	} catch (error) {
		console.error("Error fetching midwives:", error);
		showTableError("midwivesTableBody", "Failed to load midwives", 10);
	}
}

// Get permission badge styling (reuse from BHW)
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

// Edit Midwife
async function editMidwife(midwife_id) {
	try {
		const response = await fetch(
			// `../../php/mysql/admin/edit_midwife.php?midwife_id=${encodeURIComponent(
			`../../../php/supabase/admin/edit_midwife.php?midwife_id=${encodeURIComponent(
				midwife_id
			)}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const midwife = data.data;

			// Store current place globally for cascading dropdowns
			window.currentMidwifePlace = midwife.place || "";

			const editForm = document.getElementById("editMidwifeForm");
			editForm.innerHTML = `
                <form id="midwifeEditForm">
                    <input type="hidden" id="edit_midwife_id" value="${
											midwife.midwife_id
										}">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_midwife_fname">First Name</label>
                            <input type="text" class="form-control" id="edit_midwife_fname" value="${
															midwife.fname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_lname">Last Name</label>
                            <input type="text" class="form-control" id="edit_midwife_lname" value="${
															midwife.lname
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_email">Email</label>
                            <input type="email" class="form-control" id="edit_midwife_email" value="${
															midwife.email
														}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_phone">Phone Number</label>
                            <input type="text" class="form-control" id="edit_midwife_phone" value="${
															midwife.phone_number || ""
														}">
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_gender">Gender</label>
                            <select class="form-control" id="edit_midwife_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" ${
																	midwife.gender === "Male" ? "selected" : ""
																}>Male</option>
                                <option value="Female" ${
																	midwife.gender === "Female" ? "selected" : ""
																}>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_permissions">Permissions</label>
                            <select class="form-control" id="edit_midwife_permissions" required>
                                <option value="read" ${
																	midwife.permissions === "read"
																		? "selected"
																		: ""
																}>Read</option>
                                <option value="write" ${
																	midwife.permissions === "write"
																		? "selected"
																		: ""
																}>Write</option>
                                <option value="admin" ${
																	midwife.permissions === "admin"
																		? "selected"
																		: ""
																}>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_approve">Approved</label>
                            <select class="form-control" id="edit_midwife_approve" required>
                                <option value="1" ${
																	midwife.Approve == 1 ? "selected" : ""
																}>Yes</option>
                                <option value="0" ${
																	midwife.Approve == 0 ? "selected" : ""
																}>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_role">Role</label>
                            <select class="form-control" id="edit_midwife_role" required>
                                <option value="midwife" ${
																	midwife.role === "midwife" ? "selected" : ""
																}>Midwife</option>
                                <option value="user" ${
																	midwife.role === "user" ? "selected" : ""
																}>User</option>
                                <option value="bhw" ${
																	midwife.role === "bhw" ? "selected" : ""
																}>BHW</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Location Fields -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_midwife_province">Province</label>
                            <select class="form-control" id="edit_midwife_province" onchange="loadEditMidwifeCities()" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_city_municipality">City/Municipality</label>
                            <select class="form-control" id="edit_midwife_city_municipality" onchange="loadEditMidwifeBarangays()" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_barangay">Barangay</label>
                            <select class="form-control" id="edit_midwife_barangay" onchange="loadEditMidwifePuroks()" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_midwife_purok">Purok</label>
                            <select class="form-control" id="edit_midwife_purok" required>
                                <option value="">Select Purok</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" onclick="cancelEditMidwife()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="updateMidwife()">Update Midwife</button>
                    </div>
                </form>
            `;

			// Load provinces and set up cascading dropdowns
			await loadEditMidwifeProvinces();

			// Show modal
			const modal = new bootstrap.Modal(
				document.getElementById("editMidwifeModal")
			);
			modal.show();
		} else {
			Swal.fire(
				"Error!",
				data.message || "Failed to load midwife data",
				"error"
			);
		}
	} catch (error) {
		console.error("Error loading midwife:", error);
		Swal.fire("Error!", "Failed to load midwife data", "error");
	}
}

// Load provinces for edit form
async function loadEditMidwifeProvinces() {
	try {
		const response = await fetch(
			// "../../php/mysql/admin/get_places.php?type=provinces"
			"../../../php/supabase/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_midwife_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		// Get current place parts to pre-select current province
		const currentPlace = window.currentMidwifePlace || "";
		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		// If current province exists, load cities
		if (currentProvince) {
			await loadEditMidwifeCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

// Load cities for edit form
async function loadEditMidwifeCities() {
	const province = document.getElementById("edit_midwife_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			// `../../php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
			`../../../php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const data = await response.json();

		const citySelect = document.getElementById(
			"edit_midwife_city_municipality"
		);
		citySelect.innerHTML = '<option value="">Select City/Municipality</option>';

		// Get current place parts to pre-select current city
		const currentPlace = window.currentMidwifePlace || "";
		const placeParts = currentPlace.split(", ");
		const currentCity = placeParts[1] || "";

		for (const item of data) {
			const selected = item.city_municipality === currentCity ? "selected" : "";
			citySelect.innerHTML += `<option value="${item.city_municipality}" ${selected}>${item.city_municipality}</option>`;
		}

		// If current city exists, load barangays
		if (currentCity) {
			await loadEditMidwifeBarangays();
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

// Load barangays for edit form
async function loadEditMidwifeBarangays() {
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			// `../../php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
			`../../../php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const data = await response.json();

		const barangaySelect = document.getElementById("edit_midwife_barangay");
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		// Get current place parts to pre-select current barangay
		const currentPlace = window.currentMidwifePlace || "";
		const placeParts = currentPlace.split(", ");
		const currentBarangay = placeParts[2] || "";

		for (const item of data) {
			const selected = item.barangay === currentBarangay ? "selected" : "";
			barangaySelect.innerHTML += `<option value="${item.barangay}" ${selected}>${item.barangay}</option>`;
		}

		// If current barangay exists, load puroks
		if (currentBarangay) {
			await loadEditMidwifePuroks();
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

// Load puroks for edit form
async function loadEditMidwifePuroks() {
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	const barangay = document.getElementById("edit_midwife_barangay").value;
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

		const purokSelect = document.getElementById("edit_midwife_purok");
		purokSelect.innerHTML = '<option value="">Select Purok</option>';

		// Get current place parts to pre-select current purok
		const currentPlace = window.currentMidwifePlace || "";
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

// Update Midwife
async function updateMidwife() {
	const formData = new FormData();

	formData.append("user_id", document.getElementById("edit_midwife_id").value);
	formData.append("fname", document.getElementById("edit_midwife_fname").value);
	formData.append("lname", document.getElementById("edit_midwife_lname").value);
	formData.append("email", document.getElementById("edit_midwife_email").value);
	formData.append(
		"phone_number",
		document.getElementById("edit_midwife_phone").value
	);
	formData.append(
		"gender",
		document.getElementById("edit_midwife_gender").value
	);
	formData.append(
		"permissions",
		document.getElementById("edit_midwife_permissions").value
	);
	formData.append(
		"Approve",
		document.getElementById("edit_midwife_approve").value
	);
	formData.append("role", document.getElementById("edit_midwife_role").value);

	// Combine place data
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	const barangay = document.getElementById("edit_midwife_barangay").value;
	const purok = document.getElementById("edit_midwife_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
	formData.append("place", place);

	try {
		// const response = await fetch("../../php/mysql/admin/save_user.php");
		const response = await fetch("../../../php/supabase/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Midwife updated successfully", "success");
			cancelEditMidwife();
			getMidwives(); // Reload midwives table
		} else {
			Swal.fire("Error!", data.message || "Failed to update midwife", "error");
		}
	} catch (error) {
		console.error("Error updating midwife:", error);
		Swal.fire("Error!", "Failed to update midwife", "error");
	}
}

// Cancel edit Midwife
function cancelEditMidwife() {
	const modal = bootstrap.Modal.getInstance(
		document.getElementById("editMidwifeModal")
	);
	modal.hide();
	window.currentMidwifePlace = ""; // Clear global variable
}

// Delete Midwife
async function deleteMidwife(midwife_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the midwife!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		cancelButtonColor: "#6c757d",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("midwife_id", midwife_id);

			// const response = await fetch("../../php/mysql/admin/delete_midwife.php");
			const response = await fetch(
				"../../../php/supabase/admin/delete_midwife.php",
				{
					// const response = await fetch("../../php/mysql/admin/delete_midwife.php", {
					method: "POST",
					body: formData,
				}
			);

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Midwife has been deleted.", "success");
				getMidwives(); // Reload midwives table
			} else {
				Swal.fire(
					"Error!",
					data.message || "Failed to delete midwife",
					"error"
				);
			}
		} catch (error) {
			console.error("Error deleting midwife:", error);
			Swal.fire("Error!", "Failed to delete midwife", "error");
		}
	}
}
