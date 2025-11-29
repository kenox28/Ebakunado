const ACTIVITY_LIMIT = 10;
let currentActivityPage = 1;

// Activity Logs Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	initActivityLogsPager();
	setupActivityLogSearch();
	getActivityLogs(1);
});

// Fetch and display activity logs
async function getActivityLogs(page = 1) {
	try {
		const tbody = document.querySelector("#activityLogsTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr class="data-table__message-row loading"><td colspan="9">Loading activity logs...</td></tr>';
		}

		const searchInput = document.getElementById("searchActivityLogs");
		const searchTerm = searchInput ? searchInput.value.trim() : "";
		const params = new URLSearchParams({
			page,
			limit: ACTIVITY_LIMIT,
		});
		if (searchTerm) params.append("search", searchTerm);

		const response = await fetch(
			`php/supabase/superadmin/show_activitylog.php?${params.toString()}`
		);
		const result = await response.json();

		if (result.status !== "success") {
			throw new Error(result.message || "Failed to load activity logs");
		}

		const data = Array.isArray(result.data) ? result.data : [];
		const total = result.total || 0;

		if (total > 0 && data.length === 0 && page > 1) {
			getActivityLogs(page - 1);
			return;
		}

		tbody.innerHTML = "";

		if (!data.length) {
			tbody.innerHTML =
				'<tr><td colspan="9" class="empty-state">No activity logs found</td></tr>';
		} else {
			for (const log of data) {
				tbody.innerHTML += `<tr>
	                <td class="checkbox-cell"><input type="checkbox" class="log-checkbox" value="${log.log_id}"></td>
	                <td>${log.log_id}</td>
	                <td>${log.user_id}</td>
	                <td>${log.user_type}</td>
	                <td>${log.action_type}</td>
	                <td>${log.description}</td>
	                <td>${log.ip_address}</td>
	                <td>${formatDateTime(log.created_at)}</td>
	                <td class="actions-cell">
	                    <button onclick="deleteActivityLog('${log.log_id}')" class="action-icon-btn" aria-label="Delete log ${log.log_id}" title="Delete log"><span class="material-symbols-rounded">delete</span></button>
	                </td>
	            </tr>`;
			}
		}

		currentActivityPage = result.page || page;
		updateActivityLogsPager({
			page: currentActivityPage,
			limit: result.limit || ACTIVITY_LIMIT,
			total,
			hasMore: result.has_more || false,
		});
	} catch (error) {
		console.error("Error fetching activity logs:", error);
		const tbody = document.querySelector("#activityLogsTableBody");
		if (tbody) {
			tbody.innerHTML =
				'<tr><td colspan="9" class="empty-state">Failed to load activity logs</td></tr>';
		}
		updateActivityLogsPager({
			page: 1,
			limit: ACTIVITY_LIMIT,
			total: 0,
			hasMore: false,
		});
	}
}

function initActivityLogsPager() {
	const prevBtn = document.getElementById("activityLogsPrevBtn");
	const nextBtn = document.getElementById("activityLogsNextBtn");
	if (prevBtn) {
		prevBtn.addEventListener("click", () => {
			const page = parseInt(prevBtn.dataset.page || "1", 10);
			if (page > 1) getActivityLogs(page - 1);
		});
	}
	if (nextBtn) {
		nextBtn.addEventListener("click", () => {
			const page = parseInt(nextBtn.dataset.page || "1", 10);
			getActivityLogs(page + 1);
		});
	}
}

function updateActivityLogsPager({ page, limit, total, hasMore }) {
	const prevBtn = document.getElementById("activityLogsPrevBtn");
	const nextBtn = document.getElementById("activityLogsNextBtn");
	const info = document.getElementById("activityLogsPageInfo");
	if (!prevBtn || !nextBtn || !info) return;

	const start = total === 0 ? 0 : (page - 1) * limit + 1;
	const end = total === 0 ? 0 : Math.min(page * limit, total);
	info.textContent = `Showing ${start}-${end} of ${total}`;
	prevBtn.disabled = page <= 1;
	nextBtn.disabled = !hasMore;
	prevBtn.dataset.page = String(page);
	nextBtn.dataset.page = String(page);
}

function setupActivityLogSearch() {
	const input = document.getElementById("searchActivityLogs");
	const clearBtn = document.getElementById("searchActivityLogsClear");
	const wrap = document.getElementById("activityLogsSearchWrap");

	if (input) {
		input.addEventListener("keydown", (event) => {
			if (event.key === "Enter") {
				event.preventDefault();
				getActivityLogs(1);
			}
		});
		input.addEventListener("input", () => {
			const hasValue = input.value.trim().length > 0;
			if (wrap) {
				wrap.classList.toggle("data-table-search--has-value", hasValue);
			}
			// Always refresh page 1 on any change for responsive filtering
			getActivityLogs(1);
		});
	}
	if (clearBtn) {
		clearBtn.addEventListener("click", function () {
			if (input) {
				input.value = "";
				if (wrap) wrap.classList.remove("data-table-search--has-value");
				getActivityLogs(1);
				input.focus();
			}
		});
	}
}

function formatDateTime(dateStr) {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	if (isNaN(date.getTime())) return dateStr;
	return date.toLocaleString("en-PH", {
		month: "short",
		day: "numeric",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
}

// Toggle all activity log checkboxes
function toggleAllActivityLogs() {
	const selectAll = document.getElementById("selectAllActivityLogs");
	const checkboxes = document.querySelectorAll(".log-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Delete activity log
async function deleteActivityLog(log_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the activity log!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("log_id", log_id);

			const response = await fetch("php/supabase/admin/delete_log.php", {
				// const response = await fetch("php/mysql/admin/delete_log.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Activity log has been deleted.", "success");
				getActivityLogs(currentActivityPage);
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting activity log:", error);
			Swal.fire("Error!", "Failed to delete activity log", "error");
		}
	}
}

// Delete selected activity logs
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
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete them!",
	});

	if (result.isConfirmed) {
		try {
			for (const checkbox of selectedBoxes) {
				const formData = new FormData();
				formData.append("log_id", checkbox.value);
				await fetch("php/supabase/admin/delete_log.php", {
					// await fetch("php/mysql/admin/delete_log.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} activity log(s) deleted successfully`,
				"success"
			);
			getActivityLogs(currentActivityPage);
		} catch (error) {
			console.error("Error deleting activity logs:", error);
			Swal.fire("Error!", "Failed to delete activity logs", "error");
		}
	}
}
