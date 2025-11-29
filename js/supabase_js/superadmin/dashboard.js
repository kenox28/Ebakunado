// Super Admin Dashboard JavaScript

// Initialize dashboard on page load
document.addEventListener("DOMContentLoaded", function () {
	loadDashboardStats();
	initActivityPager();
	loadRecentActivity(1);
});

// Load dashboard statistics
async function loadDashboardStats() {
	try {
		const response = await fetch(
			"php/supabase/superadmin/dashboard-stats.php"
			// "php/mysql/superadmin/dashboard-stats.php"
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
			setText("totalLogs", s.logs || 0);

			setText("usersTrend", "Active");
			setText("adminsTrend", "Active");
			setText("bhwTrend", "Active");
			setText("midwivesTrend", "Active");
			setText("logsTrend", "Recent");
		} else {
			console.error("Dashboard stats error:", data.message);
			[
				"totalUsers",
				"totalAdmins",
				"totalBhw",
				"totalMidwives",
				"totalLogs",
			].forEach((id) => {
				const el = document.getElementById(id);
				if (el) el.textContent = "Error";
			});
		}
	} catch (error) {
		console.error("Error loading dashboard stats:", error);
		[
			"totalUsers",
			"totalAdmins",
			"totalBhw",
			"totalMidwives",
			"totalLogs",
		].forEach((id) => {
			const el = document.getElementById(id);
			if (el) el.textContent = "0";
		});
	}
}

// Load recent activity
async function loadRecentActivity(page = 1) {
	try {
		const limit = 10;
		const response = await fetch(
			`php/supabase/superadmin/show_activitylog.php?page=${page}&limit=${limit}`
		);
		const data = await response.json();

		const tableBody = document.getElementById("recentActivityTable");
		if (tableBody) tableBody.innerHTML = "";

		if (data && Array.isArray(data.data) && data.data.length > 0) {
			for (const log of data.data) {
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
			if (tableBody)
				tableBody.innerHTML =
					'<tr><td colspan="4" class="empty-state">No recent activity found</td></tr>';
		}

		updateActivityPager({
			page: data.page || 1,
			limit: data.limit || limit,
			total: data.total || 0,
			hasMore: data.has_more || false,
		});
	} catch (error) {
		console.error("Error loading recent activity:", error);
		const tableBody = document.getElementById("recentActivityTable");
		if (tableBody)
			tableBody.innerHTML =
				'<tr><td colspan="4" class="empty-state">Failed to load recent activity</td></tr>';
		updateActivityPager({ page: 1, limit: 10, total: 0, hasMore: false });
	}
}

function initActivityPager() {
	const prevBtn = document.getElementById("activityPrevBtn");
	const nextBtn = document.getElementById("activityNextBtn");

	if (prevBtn) {
		prevBtn.addEventListener("click", () => {
			const current = parseInt(prevBtn.dataset.page || "1", 10);
			if (current > 1) loadRecentActivity(current - 1);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener("click", () => {
			const current = parseInt(nextBtn.dataset.page || "1", 10);
			loadRecentActivity(current + 1);
		});
	}
}

function updateActivityPager({ page, limit, total, hasMore }) {
	const prevBtn = document.getElementById("activityPrevBtn");
	const nextBtn = document.getElementById("activityNextBtn");
	const info = document.getElementById("activityPageInfo");

	if (!prevBtn || !nextBtn || !info) return;

	const start = total === 0 ? 0 : (page - 1) * limit + 1;
	const end = total === 0 ? 0 : Math.min(page * limit, total);

	info.textContent = `Showing ${start}-${end} of ${total}`;
	prevBtn.disabled = page <= 1;
	nextBtn.disabled = !hasMore;

	prevBtn.dataset.page = String(page);
	nextBtn.dataset.page = String(page);
}

// Format date time for display
function formatDateTime(dateString) {
	try {
		const date = new Date(dateString);
		if (isNaN(date)) return dateString;
		const datePart = date.toLocaleDateString(undefined, {
			month: "short",
			day: "numeric",
			year: "numeric",
		});
		// Keep time concise (HH:MM) for readability
		const timePart = date.toLocaleTimeString([], {
			hour: "2-digit",
			minute: "2-digit",
		});
		return `${datePart} ${timePart}`;
	} catch (error) {
		return dateString;
	}
}

// Refresh dashboard data
function refreshDashboard() {
	loadDashboardStats();
	const currentPage = parseInt(
		document.getElementById("activityPrevBtn")?.dataset.page || "1",
		10
	);
	loadRecentActivity(currentPage);
}

// Auto-refresh dashboard every 5 minutes
setInterval(refreshDashboard, 300000);
