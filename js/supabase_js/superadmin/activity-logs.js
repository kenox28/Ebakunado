// Activity Logs Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getActivityLogs();
});

// Fetch and display activity logs (reusing from home.js)
async function getActivityLogs() {
	try {
		console.log("Loading activity logs...");
		const response = await fetch(
			"../../php/supabase/admin/show_activitylog.php"
		);
		// const response = await fetch("../../php/mysql/admin/show_activitylog.php");
		console.log("Activity logs response status:", response.status);

		const data = await response.json();
		console.log("Activity logs data:", data);

		const tbody = document.querySelector("#activityLogsTableBody");
		tbody.innerHTML = "";

		if (Array.isArray(data) && data.length > 0) {
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
		} else {
			tbody.innerHTML =
				'<tr><td colspan="9" class="empty-state">No activity logs found</td></tr>';
		}
	} catch (error) {
		console.error("Error fetching activity logs:", error);
		const tbody = document.querySelector("#activityLogsTableBody");
		tbody.innerHTML =
			'<tr><td colspan="9" class="empty-state">Failed to load activity logs</td></tr>';
	}
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

			const response = await fetch("../../php/supabase/admin/delete_log.php", {
				// const response = await fetch("../../php/mysql/admin/delete_log.php", {
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
				await fetch("../../php/supabase/admin/delete_log.php", {
					// await fetch("../../php/mysql/admin/delete_log.php", {
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
