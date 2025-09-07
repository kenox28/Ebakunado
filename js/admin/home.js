async function getActivityLogs() {
	const response = await fetch("../../php/admin/show_activitylog.php");
	const data = await response.json();
	console.log(data);
	const tbody = document.querySelector("#activityLogs");
	for (const log of data) {
		tbody.innerHTML += `<tr>
            <td><input type="checkbox" class="log-checkbox" value="${log.log_id}"></td>
            <td>${log.log_id}</td>
            <td>${log.user_id}</td>
            <td>${log.user_type}</td>
            <td>${log.action_type}</td>
            <td>${log.description}</td>
            <td>${log.ip_address}</td>
            <td>${log.created_at}</td>
        </tr>`;
	}
}
getActivityLogs();

function toggleAllLogs() {
	const selectAll = document.getElementById("selectAll");
	const checkboxes = document.querySelectorAll(".log-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

async function deleteSelectedLogs() {
	const selectedBoxes = document.querySelectorAll(".log-checkbox:checked");

	if (selectedBoxes.length === 0) {
		alert("Please select at least one log to delete");
		return;
	}

	if (
		!confirm(
			`Are you sure you want to delete ${selectedBoxes.length} selected log(s)?`
		)
	) {
		return;
	}

	try {
		// Delete each selected log
		for (const checkbox of selectedBoxes) {
			const formData = new FormData();
			formData.append("log_id", checkbox.value);
			await fetch("../../php/admin/delete_log.php", {
				method: "POST",
				body: formData,
			});
		}

		alert(`${selectedBoxes.length} log(s) deleted successfully`);
		location.reload();
	} catch (error) {
		console.error("Error deleting logs:", error);
		alert("Failed to delete logs. Please try again.");
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

// Delete selected users

async function getUsers() {
	const response = await fetch("../../php/admin/show_users.php");
	const data = await response.json();
	console.log(data);
	const tbody = document.querySelector("#users");
	for (const user of data) {
		tbody.innerHTML += `<tr>
            <td><input type="checkbox" class="user-checkbox" value="${user.user_id}"></td>
            <td><input type="hidden" id="user_id" name="user_id" value="${user.user_id}"></td>
            <td>${user.fname}</td>
            <td>${user.lname}</td>
            <td>${user.email}</td>
            <td>${user.phone_number}</td>
            <td>${user.profileImg}</td>
            <td>${user.failed_attempts}</td>
            <td>${user.lockout_time}</td>
            <td>${user.gender}</td>
            <td>${user.bdate}</td>
            <td>${user.created_at}</td>
            <td>${user.updated}</td>
            <td>${user.role}</td>
            <td><button onclick="editUser('${user.user_id}')" class="btn btn-primary">Edit</button></td>
            <td><button onclick="deleteUser('${user.user_id}')" class="btn btn-danger">Delete</button></td>
        </tr>`;
	}
}
getUsers();

async function editUser(user_id) {
	try {
		const formData = new FormData();
		formData.append("user_id", user_id);
		const response = await fetch("../../php/admin/edit_user.php", {
			method: "POST",
			body: formData,
		});

		if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`);
		}

		const data = await response.json();
		console.log(data);

		if (data.status && data.status === "error") {
			alert("Error: " + data.message);
			return;
		}

		const form = document.querySelector("#editUserForm");
		form.innerHTML = `
            <h3>Edit User</h3>
            <input type="hidden" id="user_id" name="user_id" value="${
							data.user_id || ""
						}">
            
            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" placeholder="First Name" value="${
									data.fname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" placeholder="Last Name" value="${
									data.lname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="${
									data.email || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Phone Number" value="${
									data.phone_number || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="role_user" name="role" value="user" ${
													data.role === "user" || !data.role ? "checked" : ""
												}>
                        <label for="role_user">User</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="role_bhw" name="role" value="bhw" ${
													data.role === "bhw" ? "checked" : ""
												}>
                        <label for="role_bhw">BHW (Barangay Health Worker)</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="role_midwife" name="role" value="midwife" ${
													data.role === "midwife" ? "checked" : ""
												}>
                        <label for="role_midwife">Midwife</label>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button type="button" onclick="saveUser()" class="btn btn-primary">Save</button>
                <button type="button" onclick="cancelEdit()" class="btn">Cancel</button>
            </div>
        `;
		form.style.display = "block";
	} catch (error) {
		console.error("Error fetching user data:", error);
		alert("Failed to load user data. Please try again.");
	}
}

async function saveUser() {
	try {
		const formData = new FormData();
		formData.append("user_id", document.getElementById("user_id").value);
		formData.append("fname", document.getElementById("fname").value);
		formData.append("lname", document.getElementById("lname").value);
		formData.append("email", document.getElementById("email").value);
		formData.append(
			"phone_number",
			document.getElementById("phone_number").value
		);

		// Get selected radio button value
		const selectedRole = document.querySelector(
			'input[name="role"]:checked'
		).value;
		formData.append("role", selectedRole);

		const response = await fetch("../../php/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("User updated successfully");
			cancelEdit();
			location.reload(); // Refresh the page to update the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error saving user:", error);
		alert("Failed to save user. Please try again.");
	}
}

function cancelEdit() {
	const form = document.querySelector("#editUserForm");
	form.style.display = "none";
}

async function deleteUser(user_id) {
	if (!confirm("Are you sure you want to delete this user?")) {
		return;
	}

	try {
		const formData = new FormData();
		formData.append("user_id", user_id);

		const response = await fetch("../../php/admin/delete_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("User deleted successfully");
			location.reload(); // Refresh the page to update the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error deleting user:", error);
		alert("Failed to delete user. Please try again.");
	}
}
async function deleteSelectedUsers() {
	const selectedBoxes = document.querySelectorAll(".user-checkbox:checked");

	if (selectedBoxes.length === 0) {
		alert("Please select at least one user to delete");
		return;
	}

	if (
		!confirm(
			`Are you sure you want to delete ${selectedBoxes.length} selected user(s)?`
		)
	) {
		return;
	}

	try {
		// Delete each selected user
		for (const checkbox of selectedBoxes) {
			const formData = new FormData();
			formData.append("user_id", checkbox.value);
			await fetch("../../php/admin/delete_user.php", {
				method: "POST",
				body: formData,
			});
		}

		alert(`${selectedBoxes.length} user(s) deleted successfully`);
		location.reload();
	} catch (error) {
		console.error("Error deleting users:", error);
		alert("Failed to delete users. Please try again.");
	}
}

// BHW Functions
async function getBhw() {
	const response = await fetch("../../php/admin/show_bhw.php");
	const data = await response.json();
	console.log(data);
	const tbody = document.querySelector("#bhwTable");
	tbody.innerHTML = ""; // Clear existing content
	for (const bhw of data) {
		tbody.innerHTML += `<tr>
            <td><input type="checkbox" class="bhw-checkbox" value="${
							bhw.bhw_id
						}"></td>
            <td>${bhw.bhw_id}</td>
            <td>${bhw.fname}</td>
            <td>${bhw.lname}</td>
            <td>${bhw.email}</td>
            <td>${bhw.phone_number}</td>
            <td>${bhw.profileImg || ""}</td>
            <td>${bhw.gender || ""}</td>
            <td>${bhw.bdate || ""}</td>
            <td>${bhw.permissions}</td>
            <td>${bhw.last_active || ""}</td>
            <td>${bhw.created_at}</td>
            <td>${bhw.updated}</td>
            <td>${bhw.role}</td>
            <td><button onclick="editBhw('${
							bhw.bhw_id
						}')" class="btn btn-primary">Edit</button></td>
            <td><button onclick="deleteBhw('${
							bhw.bhw_id
						}')" class="btn btn-danger">Delete</button></td>
        </tr>`;
	}
}

async function getMidwives() {
	const response = await fetch("../../php/admin/show_midwives.php");
	const data = await response.json();
	console.log(data);
	const tbody = document.querySelector("#midwivesTable");
	tbody.innerHTML = ""; // Clear existing content
	for (const midwife of data) {
		tbody.innerHTML += `<tr>
            <td><input type="checkbox" class="midwife-checkbox" value="${
							midwife.midwife_id
						}"></td>
            <td>${midwife.midwife_id}</td>
            <td>${midwife.fname}</td>
            <td>${midwife.lname}</td>
            <td>${midwife.email}</td>
            <td>${midwife.phone_number}</td>
            <td>${midwife.profileImg || ""}</td>
            <td>${midwife.gender || ""}</td>
            <td>${midwife.bdate || ""}</td>
            <td>${midwife.permissions}</td>
            <td>${midwife.Approve ? "Yes" : "No"}</td>
            <td>${midwife.last_active || ""}</td>
            <td>${midwife.created_at}</td>
            <td>${midwife.updated}</td>
            <td>${midwife.role}</td>
            <td><button onclick="editMidwife('${
							midwife.midwife_id
						}')" class="btn btn-primary">Edit</button></td>
            <td><button onclick="deleteMidwife('${
							midwife.midwife_id
						}')" class="btn btn-danger">Delete</button></td>
        </tr>`;
	}
}

// Toggle functions
function toggleAllBhw() {
	const selectAll = document.getElementById("selectAllBhw");
	const checkboxes = document.querySelectorAll(".bhw-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

function toggleAllMidwives() {
	const selectAll = document.getElementById("selectAllMidwives");
	const checkboxes = document.querySelectorAll(".midwife-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Delete functions
async function deleteBhw(bhw_id) {
	if (!confirm("Are you sure you want to delete this BHW?")) {
		return;
	}

	try {
		const formData = new FormData();
		formData.append("bhw_id", bhw_id);

		const response = await fetch("../../php/admin/delete_bhw.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("BHW deleted successfully");
			getBhw(); // Refresh the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error deleting BHW:", error);
		alert("Failed to delete BHW. Please try again.");
	}
}

async function deleteMidwife(midwife_id) {
	if (!confirm("Are you sure you want to delete this Midwife?")) {
		return;
	}

	try {
		const formData = new FormData();
		formData.append("midwife_id", midwife_id);

		const response = await fetch("../../php/admin/delete_midwife.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("Midwife deleted successfully");
			getMidwives(); // Refresh the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error deleting Midwife:", error);
		alert("Failed to delete Midwife. Please try again.");
	}
}

async function deleteSelectedBhw() {
	const selectedBoxes = document.querySelectorAll(".bhw-checkbox:checked");

	if (selectedBoxes.length === 0) {
		alert("Please select at least one BHW to delete");
		return;
	}

	if (
		!confirm(
			`Are you sure you want to delete ${selectedBoxes.length} selected BHW(s)?`
		)
	) {
		return;
	}

	try {
		for (const checkbox of selectedBoxes) {
			const formData = new FormData();
			formData.append("bhw_id", checkbox.value);
			await fetch("../../php/admin/delete_bhw.php", {
				method: "POST",
				body: formData,
			});
		}

		alert(`${selectedBoxes.length} BHW(s) deleted successfully`);
		getBhw();
	} catch (error) {
		console.error("Error deleting BHWs:", error);
		alert("Failed to delete BHWs. Please try again.");
	}
}

async function deleteSelectedMidwives() {
	const selectedBoxes = document.querySelectorAll(".midwife-checkbox:checked");

	if (selectedBoxes.length === 0) {
		alert("Please select at least one Midwife to delete");
		return;
	}

	if (
		!confirm(
			`Are you sure you want to delete ${selectedBoxes.length} selected Midwife(s)?`
		)
	) {
		return;
	}

	try {
		for (const checkbox of selectedBoxes) {
			const formData = new FormData();
			formData.append("midwife_id", checkbox.value);
			await fetch("../../php/admin/delete_midwife.php", {
				method: "POST",
				body: formData,
			});
		}

		alert(`${selectedBoxes.length} Midwife(s) deleted successfully`);
		getMidwives();
	} catch (error) {
		console.error("Error deleting Midwives:", error);
		alert("Failed to delete Midwives. Please try again.");
	}
}

// Edit BHW function
async function editBhw(bhw_id) {
	try {
		const formData = new FormData();
		formData.append("bhw_id", bhw_id);
		const response = await fetch("../../php/admin/edit_bhw.php", {
			method: "POST",
			body: formData,
		});

		if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`);
		}

		const data = await response.json();
		console.log(data);

		if (data.status && data.status === "error") {
			alert("Error: " + data.message);
			return;
		}

		const form = document.querySelector("#editBhwForm");
		form.innerHTML = `
            <h3>Edit BHW</h3>
            <input type="hidden" id="bhw_id" name="bhw_id" value="${
							data.bhw_id || ""
						}">
            
            <div class="form-group">
                <label for="bhw_fname">First Name</label>
                <input type="text" id="bhw_fname" name="fname" placeholder="First Name" value="${
									data.fname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="bhw_lname">Last Name</label>
                <input type="text" id="bhw_lname" name="lname" placeholder="Last Name" value="${
									data.lname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="bhw_email">Email</label>
                <input type="email" id="bhw_email" name="email" placeholder="Email" value="${
									data.email || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="bhw_phone_number">Phone Number</label>
                <input type="text" id="bhw_phone_number" name="phone_number" placeholder="Phone Number" value="${
									data.phone_number || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="bhw_gender">Gender</label>
                <select id="bhw_gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" ${
											data.gender === "male" ? "selected" : ""
										}>Male</option>
                    <option value="female" ${
											data.gender === "female" ? "selected" : ""
										}>Female</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="bhw_bdate">Birth Date</label>
                <input type="date" id="bhw_bdate" name="bdate" value="${
									data.bdate || ""
								}">
            </div>
            
            <div class="form-group">
                <label>Permissions</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="bhw_perm_view" name="permissions" value="view" ${
													data.permissions === "view" || !data.permissions
														? "checked"
														: ""
												}>
                        <label for="bhw_perm_view">View Only</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="bhw_perm_edit" name="permissions" value="edit" ${
													data.permissions === "edit" ? "checked" : ""
												}>
                        <label for="bhw_perm_edit">Edit</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="bhw_perm_admin" name="permissions" value="admin" ${
													data.permissions === "admin" ? "checked" : ""
												}>
                        <label for="bhw_perm_admin">Admin</label>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button type="button" onclick="saveBhw()" class="btn btn-primary">Save</button>
                <button type="button" onclick="cancelEditBhw()" class="btn">Cancel</button>
            </div>
        `;
		form.style.display = "block";
	} catch (error) {
		console.error("Error fetching BHW data:", error);
		alert("Failed to load BHW data. Please try again.");
	}
}

// Edit Midwife function
async function editMidwife(midwife_id) {
	try {
		const formData = new FormData();
		formData.append("midwife_id", midwife_id);
		const response = await fetch("../../php/admin/edit_midwife.php", {
			method: "POST",
			body: formData,
		});

		if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`);
		}

		const data = await response.json();
		console.log(data);

		if (data.status && data.status === "error") {
			alert("Error: " + data.message);
			return;
		}

		const form = document.querySelector("#editMidwifeForm");
		form.innerHTML = `
            <h3>Edit Midwife</h3>
            <input type="hidden" id="midwife_id" name="midwife_id" value="${
							data.midwife_id || ""
						}">
            
            <div class="form-group">
                <label for="midwife_fname">First Name</label>
                <input type="text" id="midwife_fname" name="fname" placeholder="First Name" value="${
									data.fname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="midwife_lname">Last Name</label>
                <input type="text" id="midwife_lname" name="lname" placeholder="Last Name" value="${
									data.lname || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="midwife_email">Email</label>
                <input type="email" id="midwife_email" name="email" placeholder="Email" value="${
									data.email || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="midwife_phone_number">Phone Number</label>
                <input type="text" id="midwife_phone_number" name="phone_number" placeholder="Phone Number" value="${
									data.phone_number || ""
								}" required>
            </div>
            
            <div class="form-group">
                <label for="midwife_gender">Gender</label>
                <select id="midwife_gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" ${
											data.gender === "male" ? "selected" : ""
										}>Male</option>
                    <option value="female" ${
											data.gender === "female" ? "selected" : ""
										}>Female</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="midwife_bdate">Birth Date</label>
                <input type="date" id="midwife_bdate" name="bdate" value="${
									data.bdate || ""
								}">
            </div>
            
            <div class="form-group">
                <label>Permissions</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="midwife_perm_view" name="permissions" value="view" ${
													data.permissions === "view" || !data.permissions
														? "checked"
														: ""
												}>
                        <label for="midwife_perm_view">View Only</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="midwife_perm_edit" name="permissions" value="edit" ${
													data.permissions === "edit" ? "checked" : ""
												}>
                        <label for="midwife_perm_edit">Edit</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="midwife_perm_admin" name="permissions" value="admin" ${
													data.permissions === "admin" ? "checked" : ""
												}>
                        <label for="midwife_perm_admin">Admin</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Approval Status</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="midwife_approve_yes" name="approve" value="1" ${
													data.Approve == 1 ? "checked" : ""
												}>
                        <label for="midwife_approve_yes">Approved</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="midwife_approve_no" name="approve" value="0" ${
													data.Approve == 0 ? "checked" : ""
												}>
                        <label for="midwife_approve_no">Not Approved</label>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button type="button" onclick="saveMidwife()" class="btn btn-primary">Save</button>
                <button type="button" onclick="cancelEditMidwife()" class="btn">Cancel</button>
            </div>
        `;
		form.style.display = "block";
	} catch (error) {
		console.error("Error fetching Midwife data:", error);
		alert("Failed to load Midwife data. Please try again.");
	}
}

// Save BHW function
async function saveBhw() {
	try {
		const formData = new FormData();
		formData.append("bhw_id", document.getElementById("bhw_id").value);
		formData.append("fname", document.getElementById("bhw_fname").value);
		formData.append("lname", document.getElementById("bhw_lname").value);
		formData.append("email", document.getElementById("bhw_email").value);
		formData.append(
			"phone_number",
			document.getElementById("bhw_phone_number").value
		);
		formData.append("gender", document.getElementById("bhw_gender").value);
		formData.append("bdate", document.getElementById("bhw_bdate").value);

		// Get selected permission radio button value
		const selectedPermission = document.querySelector(
			'input[name="permissions"]:checked'
		).value;
		formData.append("permissions", selectedPermission);

		const response = await fetch("../../php/admin/save_bhw.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("BHW updated successfully");
			cancelEditBhw();
			getBhw(); // Refresh the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error saving BHW:", error);
		alert("Failed to save BHW. Please try again.");
	}
}

async function saveMidwife() {
	try {
		const formData = new FormData();
		formData.append("midwife_id", document.getElementById("midwife_id").value);
		formData.append("fname", document.getElementById("midwife_fname").value);
		formData.append("lname", document.getElementById("midwife_lname").value);
		formData.append("email", document.getElementById("midwife_email").value);
		formData.append(
			"phone_number",
			document.getElementById("midwife_phone_number").value
		);
		formData.append("gender", document.getElementById("midwife_gender").value);
		formData.append("bdate", document.getElementById("midwife_bdate").value);

		// Get selected permission radio button value
		const selectedPermission = document.querySelector(
			'input[name="permissions"]:checked'
		).value;
		formData.append("permissions", selectedPermission);

		// Get selected approve radio button value
		const selectedApprove = document.querySelector(
			'input[name="approve"]:checked'
		).value;
		formData.append("approve", selectedApprove);

		const response = await fetch("../../php/admin/save_midwife.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();

		if (data.status === "success") {
			alert("Midwife updated successfully");
			cancelEditMidwife();
			getMidwives(); // Refresh the table
		} else {
			alert("Error: " + data.message);
		}
	} catch (error) {
		console.error("Error saving Midwife:", error);
		alert("Failed to save Midwife. Please try again.");
	}
}

// Cancel edit functions
function cancelEditBhw() {
	const form = document.querySelector("#editBhwForm");
	form.style.display = "none";
}

function cancelEditMidwife() {
	const form = document.querySelector("#editMidwifeForm");
	form.style.display = "none";
}

// Initialize all tables
getBhw();
getMidwives();

// Search functionality
function setupSearchListeners() {
	// Activity Logs search
	document.getElementById("searchLogs").addEventListener("input", function () {
		filterTable("searchLogs", "activityLogs");
	});

	// Users search
	document.getElementById("searchUsers").addEventListener("input", function () {
		filterTable("searchUsers", "users");
	});

	// BHW search
	document.getElementById("searchBhw").addEventListener("input", function () {
		filterTable("searchBhw", "bhwTable");
	});

	// Midwives search
	document
		.getElementById("searchMidwives")
		.addEventListener("input", function () {
			filterTable("searchMidwives", "midwivesTable");
		});
}

function filterTable(searchInputId, tableBodyId) {
	const searchInput = document.getElementById(searchInputId);
	const tableBody = document.getElementById(tableBodyId);
	const searchTerm = searchInput.value.toLowerCase();
	const rows = tableBody.getElementsByTagName("tr");

	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		const cells = row.getElementsByTagName("td");
		let found = false;

		// Skip the first cell (checkbox) and last two cells (action buttons)
		for (let j = 1; j < cells.length - 2; j++) {
			const cellText = cells[j].textContent || cells[j].innerText;
			if (cellText.toLowerCase().indexOf(searchTerm) > -1) {
				found = true;
				break;
			}
		}

		if (found || searchTerm === "") {
			row.style.display = "";
		} else {
			row.style.display = "none";
		}
	}
}

function clearSearch(searchInputId, tableBodyId) {
	document.getElementById(searchInputId).value = "";
	filterTable(searchInputId, tableBodyId);
}

// Initialize search listeners when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
	setupSearchListeners();
});
