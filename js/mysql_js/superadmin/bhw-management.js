// BHW Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getBhw();
});

// Fetch and display BHW (reusing from home.js)
async function getBhw() {
	try {
		const response = await fetch("../../php/mysql/admin/show_bhw.php");
		const data = await response.json();

		const tbody = document.querySelector("#bhwTableBody");
		tbody.innerHTML = "";

		for (const bhw of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="bhw-checkbox" value="${
									bhw.bhw_id
								}"></td>
                <td>${bhw.bhw_id}</td>
                <td>${bhw.fname}</td>
                <td>${bhw.lname}</td>
                <td>${bhw.email}</td>
                <td>${bhw.phone_number}</td>
                <td>${bhw.gender || ""}</td>
                <td>${bhw.place || ""}</td>
                <td>${bhw.permissions}</td>
                <td>${bhw.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editBhw('${
											bhw.bhw_id
										}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteBhw('${
											bhw.bhw_id
										}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching BHW:", error);
	}
}

// Toggle all BHW checkboxes
function toggleAllBhw() {
	const selectAll = document.getElementById("selectAllBhw");
	const checkboxes = document.querySelectorAll(".bhw-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Edit BHW function with place editing
async function editBhw(bhw_id) {
	try {
		const response = await fetch(
			`../../php/mysql/admin/edit_bhw.php?bhw_id=${bhw_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const bhw = data.data;
			const form = document.getElementById("editBhwForm");

			form.innerHTML = `
                <h3>Edit BHW</h3>
                <input type="hidden" id="edit_bhw_id" value="${bhw.bhw_id}">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_bhw_fname">First Name</label>
                        <input type="text" id="edit_bhw_fname" name="fname" value="${
													bhw.fname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_lname">Last Name</label>
                        <input type="text" id="edit_bhw_lname" name="lname" value="${
													bhw.lname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_email">Email</label>
                        <input type="email" id="edit_bhw_email" name="email" value="${
													bhw.email
												}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_bhw_phone">Phone Number</label>
                        <input type="text" id="edit_bhw_phone" name="phone_number" value="${
													bhw.phone_number
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_gender">Gender</label>
                        <select id="edit_bhw_gender" name="gender" required>
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
                        <select id="edit_bhw_permissions" name="permissions" required>
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
                        <select id="edit_bhw_role" name="role" required>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_bhw_province">Province</label>
                        <select id="edit_bhw_province" name="province" onchange="loadEditBhwCities()" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_city_municipality">City/Municipality</label>
                        <select id="edit_bhw_city_municipality" name="city_municipality" onchange="loadEditBhwBarangays()" required>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_barangay">Barangay</label>
                        <select id="edit_bhw_barangay" name="barangay" onchange="loadEditBhwPuroks()" required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_purok">Purok</label>
                        <select id="edit_bhw_purok" name="purok" required>
                            <option value="">Select Purok</option>
                        </select>
                    </div>
                </div>
                <div class="action-buttons">
                    <button type="button" onclick="updateBhw()" class="btn btn-primary">Update BHW</button>
                    <button type="button" onclick="cancelEditBhw()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			form.scrollIntoView({ behavior: "smooth" });
			await loadEditBhwProvinces(bhw.place || "");
		} else {
			Swal.fire("Error!", "Failed to load BHW data", "error");
		}
	} catch (error) {
		console.error("Error editing BHW:", error);
		Swal.fire("Error!", "Failed to load BHW data", "error");
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
		const response = await fetch("../../php/mysql/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "BHW updated successfully", "success");
			cancelEditBhw();
			getBhw();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error updating BHW:", error);
		Swal.fire("Error!", "Failed to update BHW", "error");
	}
}

// Cancel edit BHW
function cancelEditBhw() {
	const form = document.getElementById("editBhwForm");
	form.style.display = "none";
	form.innerHTML = "";
}

// Delete BHW
async function deleteBhw(bhw_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the BHW account!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("bhw_id", bhw_id);

			const response = await fetch("../../php/mysql/admin/delete_bhw.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "BHW has been deleted.", "success");
				getBhw();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting BHW:", error);
			Swal.fire("Error!", "Failed to delete BHW", "error");
		}
	}
}

// Delete selected BHW
async function deleteSelectedBhw() {
	const selectedBoxes = document.querySelectorAll(".bhw-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one BHW to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected BHW(s)!`,
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
				formData.append("bhw_id", checkbox.value);
				await fetch("../../php/mysql/admin/delete_bhw.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} BHW(s) deleted successfully`,
				"success"
			);
			getBhw();
		} catch (error) {
			console.error("Error deleting BHW:", error);
			Swal.fire("Error!", "Failed to delete BHW", "error");
		}
	}
}

// Place editing functions for BHW
async function loadEditBhwProvinces(currentPlace = "") {
	try {
		// Store current place globally for cascading
		window.currentBhwPlace = currentPlace;

		const response = await fetch(
			"../../php/mysql/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_bhw_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		if (currentProvince) {
			await loadEditBhwCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

async function loadEditBhwCities() {
	const province = document.getElementById("edit_bhw_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/mysql/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const data = await response.json();

		const citySelect = document.getElementById("edit_bhw_city_municipality");
		citySelect.innerHTML = '<option value="">Select City/Municipality</option>';

		for (const item of data) {
			citySelect.innerHTML += `<option value="${item.city_municipality}">${item.city_municipality}</option>`;
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

async function loadEditBhwBarangays() {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/mysql/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const data = await response.json();

		const barangaySelect = document.getElementById("edit_bhw_barangay");
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		for (const item of data) {
			barangaySelect.innerHTML += `<option value="${item.barangay}">${item.barangay}</option>`;
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

async function loadEditBhwPuroks() {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	const barangay = document.getElementById("edit_bhw_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/mysql/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const data = await response.json();

		const purokSelect = document.getElementById("edit_bhw_purok");
		purokSelect.innerHTML = '<option value="">Select Purok</option>';

		for (const item of data) {
			purokSelect.innerHTML += `<option value="${item.purok}">${item.purok}</option>`;
		}
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}
