// Midwife Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getMidwives();
});

// Fetch and display Midwives (reusing from home.js)
async function getMidwives() {
	try {
		// const response = await fetch("../../php/mysql/admin/show_midwives.php");
		const response = await fetch(
			"../../../php/supabase/admin/show_midwives.php"
		);
		const data = await response.json();

		const tbody = document.querySelector("#midwivesTableBody");
		tbody.innerHTML = "";

		for (const midwife of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="midwife-checkbox" value="${
									midwife.midwife_id
								}"></td>
                <td>${midwife.midwife_id}</td>
                <td>${midwife.fname}</td>
                <td>${midwife.lname}</td>
                <td>${midwife.email}</td>
                <td>${midwife.phone_number}</td>
                <td>${midwife.gender || ""}</td>
                <td>${midwife.place || ""}</td>
                <td>${midwife.permissions}</td>
                <td>${midwife.Approve ? "Yes" : "No"}</td>
                <td>${midwife.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editMidwife('${
											midwife.midwife_id
										}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteMidwife('${
											midwife.midwife_id
										}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching midwives:", error);
	}
}

// Toggle all Midwife checkboxes
function toggleAllMidwives() {
	const selectAll = document.getElementById("selectAllMidwives");
	const checkboxes = document.querySelectorAll(".midwife-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Edit Midwife function with place editing
async function editMidwife(midwife_id) {
	try {
		const response = await fetch(
			// `../../php/mysql/admin/edit_midwife.php?midwife_id=${midwife_id}`
			`../../../php/supabase/admin/edit_midwife.php?midwife_id=${midwife_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const midwife = data.data;
			const form = document.getElementById("editMidwifeForm");

			form.innerHTML = `
                <h3>Edit Midwife</h3>
                <input type="hidden" id="edit_midwife_id" value="${
									midwife.midwife_id
								}">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_midwife_fname">First Name</label>
                        <input type="text" id="edit_midwife_fname" name="fname" value="${
													midwife.fname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_lname">Last Name</label>
                        <input type="text" id="edit_midwife_lname" name="lname" value="${
													midwife.lname
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_email">Email</label>
                        <input type="email" id="edit_midwife_email" name="email" value="${
													midwife.email
												}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_midwife_phone">Phone Number</label>
                        <input type="text" id="edit_midwife_phone" name="phone_number" value="${
													midwife.phone_number
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_gender">Gender</label>
                        <select id="edit_midwife_gender" name="gender" required>
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
                        <select id="edit_midwife_permissions" name="permissions" required>
                            <option value="read" ${
															midwife.permissions === "read" ? "selected" : ""
														}>Read</option>
                            <option value="write" ${
															midwife.permissions === "write" ? "selected" : ""
														}>Write</option>
                            <option value="admin" ${
															midwife.permissions === "admin" ? "selected" : ""
														}>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_midwife_approve">Approved</label>
                        <select id="edit_midwife_approve" name="Approve" required>
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
                        <select id="edit_midwife_role" name="role" required>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_midwife_province">Province</label>
                        <select id="edit_midwife_province" name="province" onchange="loadEditMidwifeCities()" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_city_municipality">City/Municipality</label>
                        <select id="edit_midwife_city_municipality" name="city_municipality" onchange="loadEditMidwifeBarangays()" required>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_barangay">Barangay</label>
                        <select id="edit_midwife_barangay" name="barangay" onchange="loadEditMidwifePuroks()" required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_purok">Purok</label>
                        <select id="edit_midwife_purok" name="purok" required>
                            <option value="">Select Purok</option>
                        </select>
                    </div>
                </div>
                <div class="action-buttons">
                    <button type="button" onclick="updateMidwife()" class="btn btn-primary">Update Midwife</button>
                    <button type="button" onclick="cancelEditMidwife()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			form.scrollIntoView({ behavior: "smooth" });
			await loadEditMidwifeProvinces(midwife.place || "");
		} else {
			Swal.fire("Error!", "Failed to load midwife data", "error");
		}
	} catch (error) {
		console.error("Error editing midwife:", error);
		Swal.fire("Error!", "Failed to load midwife data", "error");
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
		// const response = await fetch("../../php/mysql/admin/save_user.php", {
		const response = await fetch("../../../php/supabase/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Midwife updated successfully", "success");
			cancelEditMidwife();
			getMidwives();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error updating midwife:", error);
		Swal.fire("Error!", "Failed to update midwife", "error");
	}
}

// Cancel edit Midwife
function cancelEditMidwife() {
	const form = document.getElementById("editMidwifeForm");
	form.style.display = "none";
	form.innerHTML = "";
}

// Delete Midwife
async function deleteMidwife(midwife_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the midwife account!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("midwife_id", midwife_id);

			// const response = await fetch("../../php/mysql/admin/delete_midwife.php", {
			const response = await fetch(
				"../../../php/supabase/admin/delete_midwife.php",
				{
					method: "POST",
					body: formData,
				}
			);

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Midwife has been deleted.", "success");
				getMidwives();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting midwife:", error);
			Swal.fire("Error!", "Failed to delete midwife", "error");
		}
	}
}

// Delete selected Midwives
async function deleteSelectedMidwives() {
	const selectedBoxes = document.querySelectorAll(".midwife-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one midwife to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected midwife(s)!`,
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
				formData.append("midwife_id", checkbox.value);
				// await fetch("../../php/mysql/admin/delete_midwife.php", {
				await fetch("../../../php/supabase/admin/delete_midwife.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} midwife(s) deleted successfully`,
				"success"
			);
			getMidwives();
		} catch (error) {
			console.error("Error deleting midwives:", error);
			Swal.fire("Error!", "Failed to delete midwives", "error");
		}
	}
}

// Place editing functions for Midwife
async function loadEditMidwifeProvinces(currentPlace = "") {
	try {
		const response = await fetch(
			// "../../php/mysql/admin/get_places.php?type=provinces"
			"../../../php/supabase/admin/get_places.php?type=provinces"
		);
		const data = await response.json();

		const provinceSelect = document.getElementById("edit_midwife_province");
		provinceSelect.innerHTML = '<option value="">Select Province</option>';

		const placeParts = currentPlace.split(", ");
		const currentProvince = placeParts[0] || "";

		for (const item of data) {
			const selected = item.province === currentProvince ? "selected" : "";
			provinceSelect.innerHTML += `<option value="${item.province}" ${selected}>${item.province}</option>`;
		}

		if (currentProvince) {
			await loadEditMidwifeCities();
		}
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

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

		for (const item of data) {
			citySelect.innerHTML += `<option value="${item.city_municipality}">${item.city_municipality}</option>`;
		}
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

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

		for (const item of data) {
			barangaySelect.innerHTML += `<option value="${item.barangay}">${item.barangay}</option>`;
		}
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

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

		for (const item of data) {
			purokSelect.innerHTML += `<option value="${item.purok}">${item.purok}</option>`;
		}
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}
