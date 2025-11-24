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
			const s = data.stats || {};
			const setText = (id, value) => {
				const el = document.getElementById(id);
				if (el) el.textContent = value;
			};
			setText("totalUsers", s.users || 0);
			setText("totalAdmins", s.admins || 0);
			setText("totalBhw", s.bhw || 0);
			setText("totalMidwives", s.midwives || 0);
			setText("totalLocations", s.locations || 0);
			setText("totalLogs", s.logs || 0);

			setText("usersTrend", "Active");
			setText("adminsTrend", "Active");
			setText("bhwTrend", "Active");
			setText("midwivesTrend", "Active");
			setText("locationsTrend", "Updated");
			setText("logsTrend", "Recent");
		} else {
			console.error("Dashboard stats error:", data.message);
			["totalUsers","totalAdmins","totalBhw","totalMidwives","totalLocations","totalLogs"].forEach(id => {
				const el = document.getElementById(id);
				if (el) el.textContent = "Error";
			});
		}
	} catch (error) {
		console.error("Error loading dashboard stats:", error);
		["totalUsers","totalAdmins","totalBhw","totalMidwives","totalLocations","totalLogs"].forEach(id => {
			const el = document.getElementById(id);
			if (el) el.textContent = "0";
		});
	}
}

// Load recent activity
async function loadRecentActivity() {
	try {
		const limit = 10;
		const response = await fetch(
			`../../php/supabase/admin/show_activitylog.php?limit=${limit}`
			// `../../php/mysql/admin/show_activitylog.php?limit=${limit}`
		);
		const data = await response.json();

			const tableBody = document.getElementById("recentActivityTable");
		if (tableBody) tableBody.innerHTML = "";

		if (Array.isArray(data) && data.length > 0) {
			const slice = data.slice(0, limit);
			for (const log of slice) {
				const row = `
				<tr>
					<td>${log.user_id} (${log.user_type})</td>
					<td>${log.action_type}</td>
					<td>${log.description}</td>
					<td>${formatDateTime(log.created_at)}</td>
				</tr>`;
				if (tableBody) tableBody.innerHTML += row;
			}
		} else {
			if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">No recent activity found</td></tr>';
		}
	} catch (error) {
		console.error("Error loading recent activity:", error);
		const tableBody = document.getElementById("recentActivityTable");
		if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">Failed to load recent activity</td></tr>';
	}
}

// Format date time for display
function formatDateTime(dateString) {
	try {
		const date = new Date(dateString);
		if (isNaN(date)) return dateString;
		const datePart = date.toLocaleDateString(undefined, {
			month: 'short',
			day: 'numeric',
			year: 'numeric'
		});
		// Keep time concise (HH:MM) for readability
		const timePart = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		return `${datePart} ${timePart}`;
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
