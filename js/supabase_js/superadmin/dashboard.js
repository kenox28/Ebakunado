// Super Admin Dashboard JavaScript

// Initialize dashboard on page load
document.addEventListener("DOMContentLoaded", function () {
	loadDashboardStats();
	loadRecentActivity();
});

// Load dashboard statistics
async function loadDashboardStats() {
	try {
		const response = await fetch(
			"../../php/supabase/superadmin/dashboard-stats.php"
			// "../../php/mysql/superadmin/dashboard-stats.php"
		);
		const data = await response.json();

		if (data.status === "success") {
			// Update stat cards
			document.getElementById("totalUsers").textContent = data.stats.users || 0;
			document.getElementById("totalAdmins").textContent =
				data.stats.admins || 0;
			document.getElementById("totalBhw").textContent = data.stats.bhw || 0;
			document.getElementById("totalMidwives").textContent =
				data.stats.midwives || 0;
			document.getElementById("totalLocations").textContent =
				data.stats.locations || 0;
			document.getElementById("totalLogs").textContent = data.stats.logs || 0;

			// Update trends (you can implement trend calculation based on your needs)
			document.getElementById("usersTrend").textContent = "Active";
			document.getElementById("adminsTrend").textContent = "Active";
			document.getElementById("bhwTrend").textContent = "Active";
			document.getElementById("midwivesTrend").textContent = "Active";
			document.getElementById("locationsTrend").textContent = "Updated";
			document.getElementById("logsTrend").textContent = "Recent";
		} else {
			console.error("Dashboard stats error:", data.message);
			// Set error message
			document.getElementById("totalUsers").textContent = "Error";
			document.getElementById("totalAdmins").textContent = "Error";
			document.getElementById("totalBhw").textContent = "Error";
			document.getElementById("totalMidwives").textContent = "Error";
			document.getElementById("totalLocations").textContent = "Error";
			document.getElementById("totalLogs").textContent = "Error";
		}
	} catch (error) {
		console.error("Error loading dashboard stats:", error);
		// Set default values on error
		document.getElementById("totalUsers").textContent = "0";
		document.getElementById("totalAdmins").textContent = "0";
		document.getElementById("totalBhw").textContent = "0";
		document.getElementById("totalMidwives").textContent = "0";
		document.getElementById("totalLocations").textContent = "0";
		document.getElementById("totalLogs").textContent = "0";
	}
}

// Load recent activity
async function loadRecentActivity() {
	try {
		const response = await fetch(
			"../../php/supabase/admin/show_activitylog.php?limit=10"
			// "../../php/mysql/admin/show_activitylog.php?limit=10"
		);
		const data = await response.json();

		const tableBody = document.getElementById("recentActivityTable");
		tableBody.innerHTML = "";

		if (Array.isArray(data) && data.length > 0) {
			for (const log of data) {
				const row = `
                    <tr>
                        <td>${log.user_id} (${log.user_type})</td>
                        <td>${log.action_type}</td>
                        <td>${log.description}</td>
                        <td>${formatDateTime(log.created_at)}</td>
                    </tr>
                `;
				tableBody.innerHTML += row;
			}
		} else {
			tableBody.innerHTML =
				'<tr><td colspan="4" class="empty-state">No recent activity found</td></tr>';
		}
	} catch (error) {
		console.error("Error loading recent activity:", error);
		document.getElementById("recentActivityTable").innerHTML =
			'<tr><td colspan="4" class="empty-state">Failed to load recent activity</td></tr>';
	}
}

// Format date time for display
function formatDateTime(dateString) {
	try {
		const date = new Date(dateString);
		return date.toLocaleDateString() + " " + date.toLocaleTimeString();
	} catch (error) {
		return dateString;
	}
}

// Refresh dashboard data
function refreshDashboard() {
	loadDashboardStats();
	loadRecentActivity();
}

// Auto-refresh dashboard every 5 minutes
setInterval(refreshDashboard, 300000);
