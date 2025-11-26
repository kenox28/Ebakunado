// Super Admin Dashboard JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getAdmins();
	getUsers();
	getBhw();
	getMidwives();
	getLocations();
	getActivityLogs();
	setupSearchListeners();
});

// =============== ADMIN MANAGEMENT ===============

async function getAdmins() {
	try {
		const response = await fetch("../../php/superadmin/show_admins.php");
		const data = await response.json();

		const tbody = document.querySelector("#adminsTableBody");
		tbody.innerHTML = "";

		for (const admin of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="admin-checkbox" value="${admin.admin_id}"></td>
                <td>${admin.admin_id}</td>
                <td>${admin.fname}</td>
                <td>${admin.lname}</td>
                <td>${admin.email}</td>
                <td>${admin.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editAdmin('${admin.admin_id}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteAdmin('${admin.admin_id}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching admins:", error);
	}
}

function toggleAllAdmins() {
	const selectAll = document.getElementById("selectAllAdmins");
	const checkboxes = document.querySelectorAll(".admin-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

function showAddAdminForm() {
	document.getElementById("addAdminForm").style.display = "block";
}

function cancelAddAdmin() {
	document.getElementById("addAdminForm").style.display = "none";
	// Clear form
	document.getElementById("addAdminForm").reset();
}

async function saveAdmin() {
	const formData = new FormData();

	// Get form data
	formData.append("admin_id", document.getElementById("add_admin_id").value);
	formData.append("fname", document.getElementById("add_admin_fname").value);
	formData.append("lname", document.getElementById("add_admin_lname").value);
	formData.append("email", document.getElementById("add_admin_email").value);
	formData.append(
		"password",
		document.getElementById("add_admin_password").value
	);

	try {
		const response = await fetch("../../php/superadmin/save_admin.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "Admin created successfully!", "success");
			cancelAddAdmin();
			getAdmins();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error saving admin:", error);
		Swal.fire("Error!", "Failed to save admin", "error");
	}
}

async function editAdmin(admin_id) {
	try {
		const response = await fetch(
			`../../php/superadmin/edit_admin.php?admin_id=${admin_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const admin = data.data;
			const form = document.getElementById("editAdminForm");

			form.innerHTML = `
                 <h3>Edit Admin</h3>
                 <input type="hidden" id="edit_admin_id" value="${admin.admin_id}">
                 <div class="form-row">
                     <div class="form-group">
                         <label for="edit_admin_fname">First Name</label>
                         <input type="text" id="edit_admin_fname" name="fname" value="${admin.fname}" required>
                     </div>
                     <div class="form-group">
                         <label for="edit_admin_lname">Last Name</label>
                         <input type="text" id="edit_admin_lname" name="lname" value="${admin.lname}" required>
                     </div>
                 </div>
                 <div class="form-row">
                     <div class="form-group">
                         <label for="edit_admin_email">Email</label>
                         <input type="email" id="edit_admin_email" name="email" value="${admin.email}" required>
                     </div>
                 </div>
                 <div class="form-group">
                     <button type="button" onclick="updateAdmin()" class="btn btn-primary">Update Admin</button>
                     <button type="button" onclick="cancelEditAdmin()" class="btn btn-secondary">Cancel</button>
                 </div>
             `;

			form.style.display = "block";
		} else {
			Swal.fire("Error!", "Failed to load admin data", "error");
		}
	} catch (error) {
		console.error("Error editing admin:", error);
		Swal.fire("Error!", "Failed to load admin data", "error");
	}
}

function cancelEditAdmin() {
	document.getElementById("editAdminForm").style.display = "none";
}

async function updateAdmin() {
	const formData = new FormData();

	formData.append("admin_id", document.getElementById("edit_admin_id").value);
	formData.append("fname", document.getElementById("edit_admin_fname").value);
	formData.append("lname", document.getElementById("edit_admin_lname").value);
	formData.append("email", document.getElementById("edit_admin_email").value);

	try {
		const response = await fetch("../../php/superadmin/edit_admin.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "Admin updated successfully!", "success");
			cancelEditAdmin();
			getAdmins();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error updating admin:", error);
		Swal.fire("Error!", "Failed to update admin", "error");
	}
}

async function deleteAdmin(admin_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the admin!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("admin_id", admin_id);

			const response = await fetch("../../php/superadmin/delete_admin.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Admin has been deleted.", "success");
				getAdmins();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting admin:", error);
			Swal.fire("Error!", "Failed to delete admin", "error");
		}
	}
}

async function deleteSelectedAdmins() {
	const selectedBoxes = document.querySelectorAll(".admin-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one admin to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected admin(s)!`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("admin_id", checkbox.value);
				await fetch("../../php/superadmin/delete_admin.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} admin(s) deleted successfully`,
				"success"
			);
			getAdmins();
		} catch (error) {
			console.error("Error deleting admins:", error);
			Swal.fire("Error!", "Failed to delete admins", "error");
		}
	}
}

// =============== USERS MANAGEMENT ===============

async function getUsers() {
	try {
		const response = await fetch("../../php/mysql/admin/show_users.php");
		const data = await response.json();

		const tbody = document.querySelector("#usersTableBody");
		tbody.innerHTML = "";

		for (const user of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="user-checkbox" value="${user.user_id}"></td>
                <td>${user.user_id}</td>
                <td>${user.fname}</td>
                <td>${user.lname}</td>
                <td>${user.email}</td>
                <td>${user.phone_number}</td>
                <td>${user.gender}</td>
                <td>${user.place}</td>
                <td>${user.role}</td>
                <td>${user.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editUser('${user.user_id}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteUser('${user.user_id}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching users:", error);
	}
}

function toggleAllUsers() {
	const selectAll = document.getElementById("selectAllUsers");
	const checkboxes = document.querySelectorAll(".user-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

async function editUser(user_id) {
	try {
		const response = await fetch(
			`../../php/mysql/admin/edit_user.php?user_id=${user_id}`
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
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_email">Email</label>
                        <input type="email" id="edit_user_email" name="email" value="${
													user.email
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_phone">Phone</label>
                        <input type="text" id="edit_user_phone" name="phone_number" value="${
													user.phone_number
												}" required>
                    </div>
                </div>
                <div class="form-row">
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
                <div class="form-group">
                    <button type="button" onclick="saveUser()" class="btn btn-primary">Update User</button>
                    <button type="button" onclick="cancelEditUser()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			await loadEditUserProvinces(user.place || "");
		} else {
			Swal.fire("Error!", "Failed to load user data", "error");
		}
	} catch (error) {
		console.error("Error editing user:", error);
		Swal.fire("Error!", "Failed to load user data", "error");
	}
}

function cancelEditUser() {
	document.getElementById("editUserForm").style.display = "none";
}

async function saveUser() {
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

	// Combine place fields
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	const barangay = document.getElementById("edit_user_barangay").value;
	const purok = document.getElementById("edit_user_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
	formData.append("place", place);

	try {
		const response = await fetch("../../php/mysql/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "User updated successfully!", "success");
			cancelEditUser();
			getUsers();
			getBhw();
			getMidwives();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error saving user:", error);
		Swal.fire("Error!", "Failed to save user", "error");
	}
}

async function deleteUser(user_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the user!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("user_id", user_id);

			const response = await fetch("../../php/mysql/admin/delete_user.php", {
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
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("user_id", checkbox.value);
				await fetch("../../php/mysql/admin/delete_user.php", {
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

// =============== BHW MANAGEMENT ===============

async function getBhw() {
	try {
		const response = await fetch("../../php/mysql/admin/show_bhw.php");
		const data = await response.json();

		const tbody = document.querySelector("#bhwTableBody");
		tbody.innerHTML = "";

		for (const bhw of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="bhw-checkbox" value="${bhw.bhw_id}"></td>
                <td>${bhw.bhw_id}</td>
                <td>${bhw.fname}</td>
                <td>${bhw.lname}</td>
                <td>${bhw.email}</td>
                <td>${bhw.phone_number}</td>
                <td>${bhw.gender}</td>
                <td>${bhw.place}</td>
                <td>${bhw.permissions}</td>
                <td>${bhw.created_at}</td>
                <td class="actions-cell">
                    <button onclick="editBhw('${bhw.bhw_id}')" class="btn btn-primary">Edit</button>
                    <button onclick="deleteBhw('${bhw.bhw_id}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching BHW:", error);
	}
}

function toggleAllBhw() {
	const selectAll = document.getElementById("selectAllBhw");
	const checkboxes = document.querySelectorAll(".bhw-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

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
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_bhw_email">Email</label>
                        <input type="email" id="edit_bhw_email" name="email" value="${
													bhw.email
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_bhw_phone">Phone</label>
                        <input type="text" id="edit_bhw_phone" name="phone_number" value="${
													bhw.phone_number
												}" required>
                    </div>
                </div>
                <div class="form-row">
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
                            <option value="view" ${
															bhw.permissions === "view" ? "selected" : ""
														}>View</option>
                            <option value="edit" ${
															bhw.permissions === "edit" ? "selected" : ""
														}>Edit</option>
                            <option value="admin" ${
															bhw.permissions === "admin" ? "selected" : ""
														}>Admin</option>
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
                <div class="form-group">
                    <button type="button" onclick="saveBhw()" class="btn btn-primary">Update BHW</button>
                    <button type="button" onclick="cancelEditBhw()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			await loadEditBhwProvinces(bhw.place || "");
		} else {
			Swal.fire("Error!", "Failed to load BHW data", "error");
		}
	} catch (error) {
		console.error("Error editing BHW:", error);
		Swal.fire("Error!", "Failed to load BHW data", "error");
	}
}

function cancelEditBhw() {
	document.getElementById("editBhwForm").style.display = "none";
}

async function saveBhw() {
	const formData = new FormData();

	formData.append("bhw_id", document.getElementById("edit_bhw_id").value);
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

	// Combine place fields
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	const barangay = document.getElementById("edit_bhw_barangay").value;
	const purok = document.getElementById("edit_bhw_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
	formData.append("place", place);

	try {
		const response = await fetch("../../php/admin/save_bhw.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "BHW updated successfully!", "success");
			cancelEditBhw();
			getBhw();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error saving BHW:", error);
		Swal.fire("Error!", "Failed to save BHW", "error");
	}
}

async function deleteBhw(bhw_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the BHW!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("bhw_id", bhw_id);

			const response = await fetch("../../php/admin/delete_bhw.php", {
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
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("bhw_id", checkbox.value);
				await fetch("../../php/admin/delete_bhw.php", {
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
			console.error("Error deleting BHWs:", error);
			Swal.fire("Error!", "Failed to delete BHWs", "error");
		}
	}
}

// =============== MIDWIVES MANAGEMENT ===============

async function getMidwives() {
	try {
		const response = await fetch("../../php/admin/show_midwives.php");
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
                <td>${midwife.gender}</td>
                <td>${midwife.place}</td>
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

function toggleAllMidwives() {
	const selectAll = document.getElementById("selectAllMidwives");
	const checkboxes = document.querySelectorAll(".midwife-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

async function editMidwife(midwife_id) {
	try {
		const response = await fetch(
			`../../php/admin/edit_midwife.php?midwife_id=${midwife_id}`
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
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_midwife_email">Email</label>
                        <input type="email" id="edit_midwife_email" name="email" value="${
													midwife.email
												}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_phone">Phone</label>
                        <input type="text" id="edit_midwife_phone" name="phone_number" value="${
													midwife.phone_number
												}" required>
                    </div>
                </div>
                <div class="form-row">
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
                            <option value="view" ${
															midwife.permissions === "view" ? "selected" : ""
														}>View</option>
                            <option value="edit" ${
															midwife.permissions === "edit" ? "selected" : ""
														}>Edit</option>
                            <option value="admin" ${
															midwife.permissions === "admin" ? "selected" : ""
														}>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_midwife_approve">Approved</label>
                        <select id="edit_midwife_approve" name="approve" required>
                            <option value="0" ${
															midwife.Approve == 0 ? "selected" : ""
														}>No</option>
                            <option value="1" ${
															midwife.Approve == 1 ? "selected" : ""
														}>Yes</option>
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
                <div class="form-group">
                    <button type="button" onclick="saveMidwife()" class="btn btn-primary">Update Midwife</button>
                    <button type="button" onclick="cancelEditMidwife()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			await loadEditMidwifeProvinces(midwife.place || "");
		} else {
			Swal.fire("Error!", "Failed to load midwife data", "error");
		}
	} catch (error) {
		console.error("Error editing midwife:", error);
		Swal.fire("Error!", "Failed to load midwife data", "error");
	}
}

function cancelEditMidwife() {
	document.getElementById("editMidwifeForm").style.display = "none";
}

async function saveMidwife() {
	const formData = new FormData();

	formData.append(
		"midwife_id",
		document.getElementById("edit_midwife_id").value
	);
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
		"approve",
		document.getElementById("edit_midwife_approve").value
	);

	// Combine place fields
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	const barangay = document.getElementById("edit_midwife_barangay").value;
	const purok = document.getElementById("edit_midwife_purok").value;
	const place = `${province}, ${city}, ${barangay}, ${purok}`;
	formData.append("place", place);

	try {
		const response = await fetch("../../php/admin/save_midwife.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "Midwife updated successfully!", "success");
			cancelEditMidwife();
			getMidwives();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error saving midwife:", error);
		Swal.fire("Error!", "Failed to save midwife", "error");
	}
}

async function deleteMidwife(midwife_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the midwife!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("midwife_id", midwife_id);

			const response = await fetch("../../php/admin/delete_midwife.php", {
				method: "POST",
				body: formData,
			});

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
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("midwife_id", checkbox.value);
				await fetch("../../php/admin/delete_midwife.php", {
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

// =============== ACTIVITY LOGS MANAGEMENT ===============

async function getActivityLogs() {
	try {
		const response = await fetch("../../php/admin/show_activitylog.php");
		const data = await response.json();

		const tbody = document.querySelector("#activityLogsTableBody");
		tbody.innerHTML = "";

		for (const log of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="log-checkbox" value="${log.log_id}"></td>
                <td>${log.log_id}</td>
                <td>${log.user_id}</td>
                <td>${log.user_type}</td>
                <td>${log.action_type}</td>
                <td>${log.description}</td>
                <td>${log.ip_address}</td>
                <td>${log.created_at}</td>
                <td class="actions-cell">
                    <button onclick="deleteActivityLog('${log.log_id}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching activity logs:", error);
	}
}

function toggleAllActivityLogs() {
	const selectAll = document.getElementById("selectAllActivityLogs");
	const checkboxes = document.querySelectorAll(".log-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

async function deleteActivityLog(log_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the activity log!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("log_id", log_id);

			const response = await fetch("../../php/admin/delete_log.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Activity log has been deleted.", "success");
				getActivityLogs();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting activity log:", error);
			Swal.fire("Error!", "Failed to delete activity log", "error");
		}
	}
}

async function deleteSelectedActivityLogs() {
	const selectedBoxes = document.querySelectorAll(".log-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one activity log to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected activity log(s)!`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("log_id", checkbox.value);
				await fetch("../../php/admin/delete_log.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} activity log(s) deleted successfully`,
				"success"
			);
			getActivityLogs();
		} catch (error) {
			console.error("Error deleting activity logs:", error);
			Swal.fire("Error!", "Failed to delete activity logs", "error");
		}
	}
}

// =============== LOCATIONS MANAGEMENT ===============

async function getLocations() {
	try {
		const response = await fetch("../../php/admin/show_locations.php");
		const data = await response.json();

		const tbody = document.querySelector("#locationsTableBody");
		tbody.innerHTML = "";

		for (const location of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="location-checkbox" value="${location.id}"></td>
                <td>${location.province}</td>
                <td>${location.city_municipality}</td>
                <td>${location.barangay}</td>
                <td>${location.purok}</td>
                <td>${location.created_at}</td>
                <td class="actions-cell">
                    <button onclick="deleteLocation('${location.id}')" class="btn btn-danger">Delete</button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching locations:", error);
	}
}

function toggleAllLocations() {
	const selectAll = document.getElementById("selectAllLocations");
	const checkboxes = document.querySelectorAll(".location-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

function showAddLocationForm() {
	document.getElementById("addLocationForm").style.display = "block";
}

function cancelAddLocation() {
	document.getElementById("addLocationForm").style.display = "none";
	document.getElementById("addLocationForm").reset();
}

async function saveLocation() {
	const formData = new FormData();

	formData.append("province", document.getElementById("add_province").value);
	formData.append(
		"city_municipality",
		document.getElementById("add_city_municipality").value
	);
	formData.append("barangay", document.getElementById("add_barangay").value);
	formData.append("purok", document.getElementById("add_purok").value);

	try {
		const response = await fetch("../../php/admin/add_location.php", {
			method: "POST",
			body: formData,
		});

		const result = await response.json();
		if (result.status === "success") {
			Swal.fire("Success!", "Location added successfully!", "success");
			cancelAddLocation();
			getLocations();
		} else {
			Swal.fire("Error!", result.message, "error");
		}
	} catch (error) {
		console.error("Error saving location:", error);
		Swal.fire("Error!", "Failed to save location", "error");
	}
}

async function deleteLocation(location_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the location!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("location_id", location_id);

			const response = await fetch("../../php/admin/delete_location.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Location has been deleted.", "success");
				getLocations();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting location:", error);
			Swal.fire("Error!", "Failed to delete location", "error");
		}
	}
}

async function deleteSelectedLocations() {
	const selectedBoxes = document.querySelectorAll(".location-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one location to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected location(s)!`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("location_id", checkbox.value);
				await fetch("../../php/admin/delete_location.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} location(s) deleted successfully`,
				"success"
			);
			getLocations();
		} catch (error) {
			console.error("Error deleting locations:", error);
			Swal.fire("Error!", "Failed to delete locations", "error");
		}
	}
}

// =============== SEARCH FUNCTIONALITY ===============

function setupSearchListeners() {
	// Admin search
	document
		.getElementById("searchAdmins")
		.addEventListener("input", function () {
			filterTable(this.value, "adminsTableBody");
		});

	// Users search
	document.getElementById("searchUsers").addEventListener("input", function () {
		filterTable(this.value, "usersTableBody");
	});

	// BHW search
	document.getElementById("searchBhw").addEventListener("input", function () {
		filterTable(this.value, "bhwTableBody");
	});

	// Midwives search
	document
		.getElementById("searchMidwives")
		.addEventListener("input", function () {
			filterTable(this.value, "midwivesTableBody");
		});

	// Activity logs search
	document
		.getElementById("searchActivityLogs")
		.addEventListener("input", function () {
			filterTable(this.value, "activityLogsTableBody");
		});

	// Locations search
	document
		.getElementById("searchLocations")
		.addEventListener("input", function () {
			filterTable(this.value, "locationsTableBody");
		});
}

function filterTable(searchTerm, tableBodyId) {
	const tbody = document.getElementById(tableBodyId);
	const rows = tbody.getElementsByTagName("tr");

	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		const cells = row.getElementsByTagName("td");
		let found = false;

		// Skip the first cell (checkbox) and last cell (actions)
		for (let j = 1; j < cells.length - 1; j++) {
			if (
				cells[j].textContent.toLowerCase().includes(searchTerm.toLowerCase())
			) {
				found = true;
				break;
			}
		}

		row.style.display = found ? "" : "none";
	}
}

function clearSearch(searchInputId, tableBodyId) {
	document.getElementById(searchInputId).value = "";
	filterTable("", tableBodyId);
}

// =============== CASCADING DROPDOWNS FOR ADMIN ===============

async function loadAdminProvinces() {
	try {
		const response = await fetch(
			"../../php/admin/get_places.php?type=provinces"
		);
		const provinces = await response.json();

		const select = document.getElementById("add_admin_province");
		select.innerHTML = '<option value="">Select Province</option>';

		provinces.forEach((province) => {
			select.innerHTML += `<option value="${province}">${province}</option>`;
		});
	} catch (error) {
		console.error("Error loading provinces:", error);
	}
}

async function loadAdminCities() {
	const province = document.getElementById("add_admin_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		const select = document.getElementById("add_admin_city_municipality");
		select.innerHTML = '<option value="">Select City/Municipality</option>';

		cities.forEach((city) => {
			select.innerHTML += `<option value="${city}">${city}</option>`;
		});

		// Clear dependent dropdowns
		document.getElementById("add_admin_barangay").innerHTML =
			'<option value="">Select Barangay</option>';
		document.getElementById("add_admin_purok").innerHTML =
			'<option value="">Select Purok</option>';
	} catch (error) {
		console.error("Error loading cities:", error);
	}
}

async function loadAdminBarangays() {
	const province = document.getElementById("add_admin_province").value;
	const city = document.getElementById("add_admin_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		const select = document.getElementById("add_admin_barangay");
		select.innerHTML = '<option value="">Select Barangay</option>';

		barangays.forEach((barangay) => {
			select.innerHTML += `<option value="${barangay}">${barangay}</option>`;
		});

		// Clear dependent dropdown
		document.getElementById("add_admin_purok").innerHTML =
			'<option value="">Select Purok</option>';
	} catch (error) {
		console.error("Error loading barangays:", error);
	}
}

async function loadAdminPuroks() {
	const province = document.getElementById("add_admin_province").value;
	const city = document.getElementById("add_admin_city_municipality").value;
	const barangay = document.getElementById("add_admin_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		const select = document.getElementById("add_admin_purok");
		select.innerHTML = '<option value="">Select Purok</option>';

		puroks.forEach((purok) => {
			select.innerHTML += `<option value="${purok}">${purok}</option>`;
		});
	} catch (error) {
		console.error("Error loading puroks:", error);
	}
}

// =============== CASCADING DROPDOWNS FOR EDIT ADMIN ===============

async function loadEditAdminProvinces(currentPlace = "") {
	try {
		const response = await fetch(
			"../../php/admin/get_places.php?type=provinces"
		);
		const provinces = await response.json();

		const select = document.getElementById("edit_admin_province");
		select.innerHTML = '<option value="">Select Province</option>';

		let selectedProvince = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedProvince = placeParts[0] || "";
		}

		provinces.forEach((province) => {
			const selected = province === selectedProvince ? "selected" : "";
			select.innerHTML += `<option value="${province}" ${selected}>${province}</option>`;
		});

		if (selectedProvince) {
			await loadEditAdminCities(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit admin provinces:", error);
	}
}

async function loadEditAdminCities(currentPlace = "") {
	const province = document.getElementById("edit_admin_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		const select = document.getElementById("edit_admin_city_municipality");
		select.innerHTML = '<option value="">Select City/Municipality</option>';

		let selectedCity = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedCity = placeParts[1] || "";
		}

		cities.forEach((city) => {
			const selected = city === selectedCity ? "selected" : "";
			select.innerHTML += `<option value="${city}" ${selected}>${city}</option>`;
		});

		if (selectedCity) {
			await loadEditAdminBarangays(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit admin cities:", error);
	}
}

async function loadEditAdminBarangays(currentPlace = "") {
	const province = document.getElementById("edit_admin_province").value;
	const city = document.getElementById("edit_admin_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		const select = document.getElementById("edit_admin_barangay");
		select.innerHTML = '<option value="">Select Barangay</option>';

		let selectedBarangay = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedBarangay = placeParts[2] || "";
		}

		barangays.forEach((barangay) => {
			const selected = barangay === selectedBarangay ? "selected" : "";
			select.innerHTML += `<option value="${barangay}" ${selected}>${barangay}</option>`;
		});

		if (selectedBarangay) {
			await loadEditAdminPuroks(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit admin barangays:", error);
	}
}

async function loadEditAdminPuroks(currentPlace = "") {
	const province = document.getElementById("edit_admin_province").value;
	const city = document.getElementById("edit_admin_city_municipality").value;
	const barangay = document.getElementById("edit_admin_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		const select = document.getElementById("edit_admin_purok");
		select.innerHTML = '<option value="">Select Purok</option>';

		let selectedPurok = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedPurok = placeParts[3] || "";
		}

		puroks.forEach((purok) => {
			const selected = purok === selectedPurok ? "selected" : "";
			select.innerHTML += `<option value="${purok}" ${selected}>${purok}</option>`;
		});
	} catch (error) {
		console.error("Error loading edit admin puroks:", error);
	}
}

// =============== CASCADING DROPDOWNS FOR EDIT USER ===============

async function loadEditUserProvinces(currentPlace = "") {
	try {
		const response = await fetch(
			"../../php/admin/get_places.php?type=provinces"
		);
		const provinces = await response.json();

		const select = document.getElementById("edit_user_province");
		select.innerHTML = '<option value="">Select Province</option>';

		let selectedProvince = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedProvince = placeParts[0] || "";
		}

		provinces.forEach((province) => {
			const selected = province === selectedProvince ? "selected" : "";
			select.innerHTML += `<option value="${province}" ${selected}>${province}</option>`;
		});

		if (selectedProvince) {
			await loadEditUserCities(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit user provinces:", error);
	}
}

async function loadEditUserCities(currentPlace = "") {
	const province = document.getElementById("edit_user_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		const select = document.getElementById("edit_user_city_municipality");
		select.innerHTML = '<option value="">Select City/Municipality</option>';

		let selectedCity = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedCity = placeParts[1] || "";
		}

		cities.forEach((city) => {
			const selected = city === selectedCity ? "selected" : "";
			select.innerHTML += `<option value="${city}" ${selected}>${city}</option>`;
		});

		if (selectedCity) {
			await loadEditUserBarangays(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit user cities:", error);
	}
}

async function loadEditUserBarangays(currentPlace = "") {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		const select = document.getElementById("edit_user_barangay");
		select.innerHTML = '<option value="">Select Barangay</option>';

		let selectedBarangay = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedBarangay = placeParts[2] || "";
		}

		barangays.forEach((barangay) => {
			const selected = barangay === selectedBarangay ? "selected" : "";
			select.innerHTML += `<option value="${barangay}" ${selected}>${barangay}</option>`;
		});

		if (selectedBarangay) {
			await loadEditUserPuroks(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit user barangays:", error);
	}
}

async function loadEditUserPuroks(currentPlace = "") {
	const province = document.getElementById("edit_user_province").value;
	const city = document.getElementById("edit_user_city_municipality").value;
	const barangay = document.getElementById("edit_user_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		const select = document.getElementById("edit_user_purok");
		select.innerHTML = '<option value="">Select Purok</option>';

		let selectedPurok = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedPurok = placeParts[3] || "";
		}

		puroks.forEach((purok) => {
			const selected = purok === selectedPurok ? "selected" : "";
			select.innerHTML += `<option value="${purok}" ${selected}>${purok}</option>`;
		});
	} catch (error) {
		console.error("Error loading edit user puroks:", error);
	}
}

// =============== CASCADING DROPDOWNS FOR EDIT BHW ===============

async function loadEditBhwProvinces(currentPlace = "") {
	try {
		const response = await fetch(
			"../../php/admin/get_places.php?type=provinces"
		);
		const provinces = await response.json();

		const select = document.getElementById("edit_bhw_province");
		select.innerHTML = '<option value="">Select Province</option>';

		let selectedProvince = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedProvince = placeParts[0] || "";
		}

		provinces.forEach((province) => {
			const selected = province === selectedProvince ? "selected" : "";
			select.innerHTML += `<option value="${province}" ${selected}>${province}</option>`;
		});

		if (selectedProvince) {
			await loadEditBhwCities(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit BHW provinces:", error);
	}
}

async function loadEditBhwCities(currentPlace = "") {
	const province = document.getElementById("edit_bhw_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		const select = document.getElementById("edit_bhw_city_municipality");
		select.innerHTML = '<option value="">Select City/Municipality</option>';

		let selectedCity = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedCity = placeParts[1] || "";
		}

		cities.forEach((city) => {
			const selected = city === selectedCity ? "selected" : "";
			select.innerHTML += `<option value="${city}" ${selected}>${city}</option>`;
		});

		if (selectedCity) {
			await loadEditBhwBarangays(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit BHW cities:", error);
	}
}

async function loadEditBhwBarangays(currentPlace = "") {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		const select = document.getElementById("edit_bhw_barangay");
		select.innerHTML = '<option value="">Select Barangay</option>';

		let selectedBarangay = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedBarangay = placeParts[2] || "";
		}

		barangays.forEach((barangay) => {
			const selected = barangay === selectedBarangay ? "selected" : "";
			select.innerHTML += `<option value="${barangay}" ${selected}>${barangay}</option>`;
		});

		if (selectedBarangay) {
			await loadEditBhwPuroks(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit BHW barangays:", error);
	}
}

async function loadEditBhwPuroks(currentPlace = "") {
	const province = document.getElementById("edit_bhw_province").value;
	const city = document.getElementById("edit_bhw_city_municipality").value;
	const barangay = document.getElementById("edit_bhw_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		const select = document.getElementById("edit_bhw_purok");
		select.innerHTML = '<option value="">Select Purok</option>';

		let selectedPurok = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedPurok = placeParts[3] || "";
		}

		puroks.forEach((purok) => {
			const selected = purok === selectedPurok ? "selected" : "";
			select.innerHTML += `<option value="${purok}" ${selected}>${purok}</option>`;
		});
	} catch (error) {
		console.error("Error loading edit BHW puroks:", error);
	}
}

// =============== CASCADING DROPDOWNS FOR EDIT MIDWIFE ===============

async function loadEditMidwifeProvinces(currentPlace = "") {
	try {
		const response = await fetch(
			"../../php/admin/get_places.php?type=provinces"
		);
		const provinces = await response.json();

		const select = document.getElementById("edit_midwife_province");
		select.innerHTML = '<option value="">Select Province</option>';

		let selectedProvince = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedProvince = placeParts[0] || "";
		}

		provinces.forEach((province) => {
			const selected = province === selectedProvince ? "selected" : "";
			select.innerHTML += `<option value="${province}" ${selected}>${province}</option>`;
		});

		if (selectedProvince) {
			await loadEditMidwifeCities(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit midwife provinces:", error);
	}
}

async function loadEditMidwifeCities(currentPlace = "") {
	const province = document.getElementById("edit_midwife_province").value;
	if (!province) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=cities&province=${encodeURIComponent(
				province
			)}`
		);
		const cities = await response.json();

		const select = document.getElementById("edit_midwife_city_municipality");
		select.innerHTML = '<option value="">Select City/Municipality</option>';

		let selectedCity = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedCity = placeParts[1] || "";
		}

		cities.forEach((city) => {
			const selected = city === selectedCity ? "selected" : "";
			select.innerHTML += `<option value="${city}" ${selected}>${city}</option>`;
		});

		if (selectedCity) {
			await loadEditMidwifeBarangays(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit midwife cities:", error);
	}
}

async function loadEditMidwifeBarangays(currentPlace = "") {
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	if (!province || !city) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=barangays&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(city)}`
		);
		const barangays = await response.json();

		const select = document.getElementById("edit_midwife_barangay");
		select.innerHTML = '<option value="">Select Barangay</option>';

		let selectedBarangay = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedBarangay = placeParts[2] || "";
		}

		barangays.forEach((barangay) => {
			const selected = barangay === selectedBarangay ? "selected" : "";
			select.innerHTML += `<option value="${barangay}" ${selected}>${barangay}</option>`;
		});

		if (selectedBarangay) {
			await loadEditMidwifePuroks(currentPlace);
		}
	} catch (error) {
		console.error("Error loading edit midwife barangays:", error);
	}
}

async function loadEditMidwifePuroks(currentPlace = "") {
	const province = document.getElementById("edit_midwife_province").value;
	const city = document.getElementById("edit_midwife_city_municipality").value;
	const barangay = document.getElementById("edit_midwife_barangay").value;
	if (!province || !city || !barangay) return;

	try {
		const response = await fetch(
			`../../php/admin/get_places.php?type=puroks&province=${encodeURIComponent(
				province
			)}&city_municipality=${encodeURIComponent(
				city
			)}&barangay=${encodeURIComponent(barangay)}`
		);
		const puroks = await response.json();

		const select = document.getElementById("edit_midwife_purok");
		select.innerHTML = '<option value="">Select Purok</option>';

		let selectedPurok = "";
		if (currentPlace) {
			const placeParts = currentPlace.split(", ");
			selectedPurok = placeParts[3] || "";
		}

		puroks.forEach((purok) => {
			const selected = purok === selectedPurok ? "selected" : "";
			select.innerHTML += `<option value="${purok}" ${selected}>${purok}</option>`;
		});
	} catch (error) {
		console.error("Error loading edit midwife puroks:", error);
	}
}

// =============== LOGOUT FUNCTIONALITY ===============

async function logoutSuperAdmin() {
	try {
		const response = await fetch("../../php/superadmin/logout.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
		});

		const data = await response.json();

		if (data.status === "success") {
			window.location.href = "../auth/login.php";
		} else {
			Swal.fire("Error!", "Logout failed", "error");
		}
	} catch (error) {
		console.error("Error during logout:", error);
		Swal.fire("Error!", "Logout failed", "error");
	}
}
