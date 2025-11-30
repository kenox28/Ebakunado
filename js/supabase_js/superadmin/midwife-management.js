const MIDWIFE_LIMIT = 10;
let currentMidwifePage = 1;

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	initMidwifePager();
	setupMidwifeSearchHandlers();
	getMidwives(1);
});

// Fetch and display Midwives (reusing from home.js)
async function getMidwives(page = 1) {
	try {
		const tbody = document.querySelector("#midwivesTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr class="data-table__message-row loading"><td colspan="10">Loading midwives...</td></tr>';
		}

		const searchInput = document.getElementById("searchMidwives");
		const searchTerm = searchInput ? searchInput.value.trim() : "";
		const params = new URLSearchParams({
			page,
			limit: MIDWIFE_LIMIT,
		});
		if (searchTerm) params.append("search", searchTerm);

		const response = await fetch(
			`php/supabase/superadmin/list_midwives.php?${params.toString()}`
		);
		const result = await response.json();

		if (result.status !== "success") {
			throw new Error(result.message || "Failed to load midwives");
		}

		const data = Array.isArray(result.data) ? result.data : [];
		const total = result.total || 0;

		if (total > 0 && data.length === 0 && page > 1) {
			getMidwives(page - 1);
			return;
		}

		tbody.innerHTML = "";

		if (!data.length) {
			tbody.innerHTML =
				'<tr class="data-table__message-row"><td colspan="10">No midwives found.</td></tr>';
		} else {
			for (const midwife of data) {
				tbody.innerHTML += `<tr>
				<td class="checkbox-cell"><input type="checkbox" class="midwife-checkbox" value="${
					midwife.midwife_id
				}"></td>
				<td>${midwife.midwife_id}</td>
				<td>${midwife.fname}</td>
				<td>${midwife.lname}</td>
				<td>${midwife.email}</td>
				<td>${midwife.phone_number || ""}</td>
				<td>${midwife.gender || ""}</td>
				<td>${midwife.place || ""}</td>
				<td>${formatDateShort(midwife.created_at)}</td>
				<td class="actions-cell">
					<button onclick="editMidwife('${
						midwife.midwife_id
					}')" class="action-icon-btn" title="Edit" aria-label="Edit user ${
					midwife.midwife_id
				}">
						<span class="material-symbols-rounded">edit</span>
					</button>
					<button onclick="deleteMidwife('${
						midwife.midwife_id
					}')" class="action-icon-btn" title="Delete" aria-label="Delete user ${
					midwife.midwife_id
				}">
						<span class="material-symbols-rounded">delete</span>
					</button>
				</td>
			</tr>`;
			}
		}

		currentMidwifePage = result.page || page;
		updateMidwifePager({
			page: currentMidwifePage,
			limit: result.limit || MIDWIFE_LIMIT,
			total,
			hasMore: result.has_more || false,
		});
	} catch (error) {
		console.error("Error fetching midwives:", error);
		const tbody = document.querySelector("#midwivesTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr class="data-table__message-row error"><td colspan="10">Failed to load midwives.</td></tr>';
		}
		updateMidwifePager({
			page: 1,
			limit: MIDWIFE_LIMIT,
			total: 0,
			hasMore: false,
		});
	}
}

function initMidwifePager() {
	const prevBtn = document.getElementById("midwivesPrevBtn");
	const nextBtn = document.getElementById("midwivesNextBtn");
	if (prevBtn) {
		prevBtn.addEventListener("click", () => {
			const page = parseInt(prevBtn.dataset.page || "1", 10);
			if (page > 1) getMidwives(page - 1);
		});
	}
	if (nextBtn) {
		nextBtn.addEventListener("click", () => {
			const page = parseInt(nextBtn.dataset.page || "1", 10);
			getMidwives(page + 1);
		});
	}
}

function updateMidwifePager({ page, limit, total, hasMore }) {
	const prevBtn = document.getElementById("midwivesPrevBtn");
	const nextBtn = document.getElementById("midwivesNextBtn");
	const info = document.getElementById("midwivesPageInfo");
	if (!prevBtn || !nextBtn || !info) return;

	const start = total === 0 ? 0 : (page - 1) * limit + 1;
	const end = total === 0 ? 0 : Math.min(page * limit, total);
	info.textContent = `Showing ${start}-${end} of ${total}`;
	prevBtn.disabled = page <= 1;
	nextBtn.disabled = !hasMore;
	prevBtn.dataset.page = String(page);
	nextBtn.dataset.page = String(page);
}

function setupMidwifeSearchHandlers() {
	const input = document.getElementById("searchMidwives");
	if (!input) return;
	input.addEventListener("keydown", (e) => {
		if (e.key === "Enter") {
			e.preventDefault();
			getMidwives(1);
		}
	});
	input.addEventListener("input", () => {
		if (!input.value.trim()) {
			getMidwives(1);
		}
	});
}

function formatDateShort(dateStr) {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	if (isNaN(date.getTime())) return dateStr;
	return date.toLocaleDateString("en-PH", {
		month: "short",
		day: "numeric",
		year: "numeric",
	});
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
			// `php/mysql/admin/edit_midwife.php?midwife_id=${midwife_id}`
			`php/supabase/admin/edit_midwife.php?midwife_id=${midwife_id}`
		);
		const data = await response.json();

		if (data.status === "success") {
			const midwife = data.data;
			const form = document.getElementById("editMidwifeForm");
			// Populate form fields (example, adjust as needed)
			form.innerHTML = `
        <input type="hidden" id="edit_midwife_id" name="user_id" value="${
					midwife.midwife_id
				}">
        <div class="form-row">
          <div class="form-group">
            <label for="edit_midwife_fname">First Name</label>
            <input type="text" id="edit_midwife_fname" name="fname" value="${
							midwife.fname || ""
						}" required>
          </div>
          <div class="form-group">
            <label for="edit_midwife_lname">Last Name</label>
            <input type="text" id="edit_midwife_lname" name="lname" value="${
							midwife.lname || ""
						}" required>
          </div>
          <div class="form-group">
            <label for="edit_midwife_email">Email</label>
            <input type="email" id="edit_midwife_email" name="email" value="${
							midwife.email || ""
						}" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="edit_midwife_phone">Phone Number</label>
            <input type="text" id="edit_midwife_phone" name="phone_number" value="${
							midwife.phone_number || ""
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
            <input type="text" id="edit_midwife_province" name="province" placeholder="Enter province" required>
          </div>
          <div class="form-group">
            <label for="edit_midwife_city_municipality">City/Municipality</label>
            <input type="text" id="edit_midwife_city_municipality" name="city_municipality" placeholder="Enter city/municipality" required>
          </div>
          <div class="form-group">
            <label for="edit_midwife_barangay">Barangay</label>
            <input type="text" id="edit_midwife_barangay" name="barangay" placeholder="Enter barangay" required>
          </div>
          <div class="form-group">
            <label for="edit_midwife_purok">Purok</label>
            <input type="text" id="edit_midwife_purok" name="purok" placeholder="Enter purok" required>
          </div>
        </div>
      `;
			// Show modal overlay
			form.style.display = "block";
			form.scrollIntoView({ behavior: "smooth" });

			// Parse existing place value (comma-separated: province, city, barangay, purok)
			const placeParts = (midwife.place || "").split(", ").map((p) => p.trim());
			document.getElementById("edit_midwife_province").value =
				placeParts[0] || "";
			document.getElementById("edit_midwife_city_municipality").value =
				placeParts[1] || "";
			document.getElementById("edit_midwife_barangay").value =
				placeParts[2] || "";
			document.getElementById("edit_midwife_purok").value = placeParts[3] || "";

			openModal("editMidwifeModal");
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
	formData.append("role", document.getElementById("edit_midwife_role").value);

	// Combine place data
	const province = document
		.getElementById("edit_midwife_province")
		.value.trim();
	const city = document
		.getElementById("edit_midwife_city_municipality")
		.value.trim();
	const barangay = document
		.getElementById("edit_midwife_barangay")
		.value.trim();
	const purok = document.getElementById("edit_midwife_purok").value.trim();
	const place = [province, city, barangay, purok].filter((p) => p).join(", ");
	formData.append("place", place);

	try {
		// const response = await fetch("php/mysql/admin/save_user.php", {
		const response = await fetch("php/supabase/admin/save_user.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Midwife updated successfully", "success");
			cancelEditMidwife();
			getMidwives(currentMidwifePage);
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
	closeModal("editMidwifeModal");
	const form = document.getElementById("editMidwifeForm");
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

			// const response = await fetch("php/mysql/admin/delete_midwife.php", {
			const response = await fetch("php/supabase/admin/delete_midwife.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Midwife has been deleted.", "success");
				getMidwives(currentMidwifePage);
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
				// await fetch("php/mysql/admin/delete_midwife.php", {
				await fetch("php/supabase/admin/delete_midwife.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} midwife(s) deleted successfully`,
				"success"
			);
			getMidwives(currentMidwifePage);
		} catch (error) {
			console.error("Error deleting midwives:", error);
			Swal.fire("Error!", "Failed to delete midwives", "error");
		}
	}
}

// Place editing functions removed - now using input fields instead of dropdowns
