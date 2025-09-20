// Common Superadmin JavaScript Functions

// Sidebar toggle functionality
function toggleSidebar() {
	const sidebar = document.getElementById("sidebar");
	sidebar.classList.toggle("collapsed");
}

// Logout function
async function logoutSuperAdmin() {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "You will be logged out of the system",
		icon: "question",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, logout",
	});

	if (result.isConfirmed) {
		try {
			const response = await fetch(
				"../../../php/supabase/superadmin/logout.php",
				{
					// const response = await fetch("../../php/mysql/superadmin/logout.php", {
					method: "POST",
				}
			);

			const data = await response.json();

			if (data.status === "success") {
				Swal.fire({
					icon: "success",
					title: "Logged Out",
					text: "You have been successfully logged out",
					showConfirmButton: false,
					timer: 1500,
				}).then(() => {
					window.location.href = "../landing_page.php";
				});
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Logout error:", error);
			// Even if there's an error, redirect to landing page
			window.location.href = "../landing_page.php";
		}
	}
}

// Generic search function
function filterTable(searchTerm, tableBodyId) {
	const tbody = document.getElementById(tableBodyId);
	const rows = tbody.getElementsByTagName("tr");

	for (let row of rows) {
		const cells = row.getElementsByTagName("td");
		let found = false;

		for (let cell of cells) {
			if (cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
				found = true;
				break;
			}
		}

		row.style.display = found ? "" : "none";
	}
}

// Clear search function
function clearSearch(searchInputId, tableBodyId) {
	document.getElementById(searchInputId).value = "";
	filterTable("", tableBodyId);
}

// Setup search listeners
function setupSearchListeners() {
	// Admin search
	const adminSearch = document.getElementById("searchAdmins");
	if (adminSearch) {
		adminSearch.addEventListener("input", function () {
			filterTable(this.value, "adminsTableBody");
		});
	}

	// User search
	const userSearch = document.getElementById("searchUsers");
	if (userSearch) {
		userSearch.addEventListener("input", function () {
			filterTable(this.value, "usersTableBody");
		});
	}

	// BHW search
	const bhwSearch = document.getElementById("searchBhw");
	if (bhwSearch) {
		bhwSearch.addEventListener("input", function () {
			filterTable(this.value, "bhwTableBody");
		});
	}

	// Midwives search
	const midwivesSearch = document.getElementById("searchMidwives");
	if (midwivesSearch) {
		midwivesSearch.addEventListener("input", function () {
			filterTable(this.value, "midwivesTableBody");
		});
	}

	// Locations search
	const locationsSearch = document.getElementById("searchLocations");
	if (locationsSearch) {
		locationsSearch.addEventListener("input", function () {
			filterTable(this.value, "locationsTableBody");
		});
	}

	// Activity logs search
	const logsSearch = document.getElementById("searchActivityLogs");
	if (logsSearch) {
		logsSearch.addEventListener("input", function () {
			filterTable(this.value, "activityLogsTableBody");
		});
	}
}

// Initialize common functionality
document.addEventListener("DOMContentLoaded", function () {
	setupSearchListeners();

	// Auto-hide sidebar on mobile
	if (window.innerWidth <= 768) {
		const sidebar = document.getElementById("sidebar");
		if (sidebar) {
			sidebar.classList.add("collapsed");
		}
	}
});

// Handle window resize
window.addEventListener("resize", function () {
	if (window.innerWidth <= 768) {
		const sidebar = document.getElementById("sidebar");
		if (sidebar && !sidebar.classList.contains("collapsed")) {
			sidebar.classList.add("collapsed");
		}
	}
});
