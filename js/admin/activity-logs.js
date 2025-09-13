// Admin Activity Logs Functions

// Get all activity logs
async function getActivityLogs() {
	showTableLoading("activityLogsTableBody", 7);

	try {
		const response = await fetch("../../php/admin/get_activity_logs.php");
		const data = await response.json();

		const tbody = document.getElementById("activityLogsTableBody");

		if (data.status === "success" && data.logs && data.logs.length > 0) {
			let html = "";
			data.logs.forEach((log) => {
				const actionBadge = getActionBadge(log.action_type);
				const userTypeBadge = getUserTypeBadge(log.user_type);

				html += `
                    <tr>
                        <td>${log.log_id}</td>
                        <td>${log.user_id}</td>
                        <td><span class="badge ${userTypeBadge.class}">${
					userTypeBadge.text
				}</span></td>
                        <td><span class="badge ${actionBadge.class}">${
					actionBadge.text
				}</span></td>
                        <td>${log.description || "N/A"}</td>
                        <td>${log.ip_address || "Unknown"}</td>
                        <td>${formatDate(log.created_at)}</td>
                    </tr>
                `;
			});
			tbody.innerHTML = html;
		} else {
			showTableEmpty("activityLogsTableBody", "No activity logs found", 7);
		}
	} catch (error) {
		console.error("Error fetching activity logs:", error);
		showTableError("activityLogsTableBody", "Failed to load activity logs", 7);
	}
}

// Get action badge styling
function getActionBadge(action) {
	switch (action?.toUpperCase()) {
		case "CREATE":
			return { class: "bg-success", text: "CREATE" };
		case "UPDATE":
			return { class: "bg-warning text-dark", text: "UPDATE" };
		case "DELETE":
			return { class: "bg-danger", text: "DELETE" };
		case "LOGIN":
			return { class: "bg-info", text: "LOGIN" };
		case "LOGOUT":
			return { class: "bg-secondary", text: "LOGOUT" };
		default:
			return { class: "bg-primary", text: action?.toUpperCase() || "UNKNOWN" };
	}
}

// Get user type badge styling
function getUserTypeBadge(userType) {
	switch (userType?.toLowerCase()) {
		case "admin":
			return { class: "bg-danger", text: "Admin" };
		case "super_admin":
			return { class: "bg-dark", text: "Super Admin" };
		case "user":
			return { class: "bg-primary", text: "User" };
		case "bhw":
			return { class: "bg-success", text: "BHW" };
		case "midwife":
			return { class: "bg-info", text: "Midwife" };
		default:
			return { class: "bg-secondary", text: userType || "Unknown" };
	}
}
