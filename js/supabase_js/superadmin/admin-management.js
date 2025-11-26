// Superadmin Admin Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getAdmins();
});

// Fetch and display admins
async function getAdmins() {
	try {
		console.log("Loading admins...");
		const response = await fetch(
			"php/supabase/superadmin/show_admins.php"
		);
		// const response = await fetch("php/mysql/superadmin/show_admins.php");
		const data = await response.json();
		console.log("All admins loaded:", data);

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
		console.log("Loading admins...");
	}
}

// Toggle all admin checkboxes
function toggleAllAdmins() {
	const selectAll = document.getElementById("selectAllAdmins");
	const checkboxes = document.querySelectorAll(".admin-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Show add admin form
function showAddAdminForm() {
	const form = document.getElementById("addAdminForm");
	form.style.display = "block";
	form.scrollIntoView({ behavior: "smooth" });
}

// Cancel add admin
function cancelAddAdmin() {
	const form = document.getElementById("addAdminForm");
	form.style.display = "none";

	// Clear form fields
	document.getElementById("add_admin_id").value = "";
	document.getElementById("add_admin_fname").value = "";
	document.getElementById("add_admin_lname").value = "";
	document.getElementById("add_admin_email").value = "";
	document.getElementById("add_admin_password").value = "";
}

// Save admin (create new)
async function saveAdmin() {
	const formData = new FormData();

	formData.append("admin_id", document.getElementById("add_admin_id").value);
	formData.append("fname", document.getElementById("add_admin_fname").value);
	formData.append("lname", document.getElementById("add_admin_lname").value);
	formData.append("email", document.getElementById("add_admin_email").value);
	formData.append(
		"password",
		document.getElementById("add_admin_password").value
	);

	try {
		const response = await fetch(
			"../php/supabase/superadmin/save_admin.php",
			{
				// const response = await fetch("php/mysql/superadmin/save_admin.php", {
				method: "POST",
				body: formData,
			}
		);

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Admin created successfully", "success");
			cancelAddAdmin();
			getAdmins();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error saving admin:", error);
		Swal.fire("Error!", "Failed to save admin", "error");
	}
}

// Edit admin
async function editAdmin(admin_id) {
	try {
		console.log("Editing admin with ID:", admin_id);
		const response = await fetch(
			`../php/supabase/superadmin/edit_admin.php?admin_id=${admin_id}`
			// `php/mysql/superadmin/edit_admin.php?admin_id=${admin_id}`
		);
		console.log("Edit admin response status:", response.status);
		const data = await response.json();
		console.log("Edit admin response data:", data);

		if (data.status === "success") {
			const admin = data.data;
			console.log("Admin data received:", admin);
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
                <div class="action-buttons">
                    <button type="button" onclick="updateAdmin()" class="btn btn-primary">Update Admin</button>
                    <button type="button" onclick="cancelEditAdmin()" class="btn btn-secondary">Cancel</button>
                </div>
            `;

			form.style.display = "block";
			form.scrollIntoView({ behavior: "smooth" });
		} else {
			Swal.fire("Error!", "Failed to load admin data", "error");
		}
	} catch (error) {
		console.error("Error editing admin:", error);
		Swal.fire("Error!", "Failed to load admin data", "error");
	}
}

// Update admin
async function updateAdmin() {
	const formData = new FormData();

	formData.append("admin_id", document.getElementById("edit_admin_id").value);
	formData.append("fname", document.getElementById("edit_admin_fname").value);
	formData.append("lname", document.getElementById("edit_admin_lname").value);
	formData.append("email", document.getElementById("edit_admin_email").value);

	try {
		const response = await fetch(
			"../php/supabase/superadmin/edit_admin.php",
			{
				// const response = await fetch("php/mysql/superadmin/edit_admin.php", {
				method: "POST",
				body: formData,
			}
		);

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Admin updated successfully", "success");
			cancelEditAdmin();
			getAdmins();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error updating admin:", error);
		Swal.fire("Error!", "Failed to update admin", "error");
	}
}

// Cancel edit admin
function cancelEditAdmin() {
	const form = document.getElementById("editAdminForm");
	form.style.display = "none";
	form.innerHTML = "";
}

// Delete admin
async function deleteAdmin(admin_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the admin account!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("admin_id", admin_id);

			const response = await fetch(
				"../php/supabase/superadmin/delete_admin.php",
				// "php/mysql/superadmin/delete_admin.php",
				{
					method: "POST",
					body: formData,
				}
			);

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

// Delete selected admins
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
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("admin_id", checkbox.value);
				await fetch("../php/supabase/superadmin/delete_admin.php", {
					// await fetch("php/mysql/superadmin/delete_admin.php", {
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
