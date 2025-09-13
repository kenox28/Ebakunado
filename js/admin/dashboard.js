// Admin Dashboard Functions

// Load dashboard statistics
async function loadDashboardStats() {
	try {
		const response = await fetch("../../php/admin/dashboard-stats.php");
		const data = await response.json();

		if (data.status === "success") {
			// Update stat cards
			document.getElementById("totalUsers").textContent =
				data.stats.users || "0";
			document.getElementById("totalBhws").textContent = data.stats.bhws || "0";
			document.getElementById("totalMidwives").textContent =
				data.stats.midwives || "0";
			document.getElementById("totalLocations").textContent =
				data.stats.locations || "0";
			document.getElementById("totalActivityLogs").textContent =
				data.stats.activity_logs || "0";
		} else {
			console.error("Failed to load dashboard stats:", data.message);
			// Set default values on error
			document.getElementById("totalUsers").textContent = "0";
			document.getElementById("totalBhws").textContent = "0";
			document.getElementById("totalMidwives").textContent = "0";
			document.getElementById("totalLocations").textContent = "0";
			document.getElementById("totalActivityLogs").textContent = "0";
		}
	} catch (error) {
		console.error("Error loading dashboard stats:", error);
		// Set default values on error
		document.getElementById("totalUsers").textContent = "0";
		document.getElementById("totalBhws").textContent = "0";
		document.getElementById("totalMidwives").textContent = "0";
		document.getElementById("totalLocations").textContent = "0";
		document.getElementById("totalActivityLogs").textContent = "0";
	}
}

// Load recent activity
async function loadRecentActivity() {
	try {
		const response = await fetch(
			"../../php/admin/get_activity_logs.php?limit=5"
		);
		const data = await response.json();

		const activityContainer = document.getElementById("recentActivity");

		if (data.status === "success" && data.logs && data.logs.length > 0) {
			let activityHTML = "";

			data.logs.forEach((log) => {
				const actionBadge = getActionBadge(log.action_type);
				const timeAgo = getTimeAgo(log.created_at);

				activityHTML += `
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${log.user_id}</strong> 
                                <span class="badge ${actionBadge.class} me-2">${
					actionBadge.text
				}</span>
                                <span class="text-muted">${
																	log.description || "No description"
																}</span>
                            </div>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        <div class="activity-meta">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> ${log.user_type} 
                                <i class="fas fa-globe ms-2"></i> ${
																	log.ip_address || "Unknown IP"
																}
                            </small>
                        </div>
                    </div>
                `;
			});

			activityContainer.innerHTML = activityHTML;
		} else {
			activityContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No recent activity</p>
                </div>
            `;
		}
	} catch (error) {
		console.error("Error loading recent activity:", error);
		document.getElementById("recentActivity").innerHTML = `
            <div class="empty-state text-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Failed to load recent activity</p>
            </div>
        `;
	}
}

// Get action badge styling
function getActionBadge(action) {
	switch (action.toUpperCase()) {
		case "CREATE":
			return { class: "bg-success", text: "CREATE" };
		case "UPDATE":
			return { class: "bg-warning", text: "UPDATE" };
		case "DELETE":
			return { class: "bg-danger", text: "DELETE" };
		case "LOGIN":
			return { class: "bg-info", text: "LOGIN" };
		case "LOGOUT":
			return { class: "bg-secondary", text: "LOGOUT" };
		default:
			return { class: "bg-primary", text: action.toUpperCase() };
	}
}

// Get time ago string
function getTimeAgo(dateString) {
	if (!dateString) return "Unknown time";

	const date = new Date(dateString);
	const now = new Date();
	const diffInSeconds = Math.floor((now - date) / 1000);

	if (diffInSeconds < 60) {
		return "Just now";
	} else if (diffInSeconds < 3600) {
		const minutes = Math.floor(diffInSeconds / 60);
		return `${minutes} minute${minutes > 1 ? "s" : ""} ago`;
	} else if (diffInSeconds < 86400) {
		const hours = Math.floor(diffInSeconds / 3600);
		return `${hours} hour${hours > 1 ? "s" : ""} ago`;
	} else if (diffInSeconds < 2592000) {
		const days = Math.floor(diffInSeconds / 86400);
		return `${days} day${days > 1 ? "s" : ""} ago`;
	} else {
		return date.toLocaleDateString("en-US", {
			year: "numeric",
			month: "short",
			day: "numeric",
		});
	}
}
