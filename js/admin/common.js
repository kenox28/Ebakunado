// Admin Dashboard Common Functions

// Toggle sidebar
function toggleSidebar() {
	const sidebar = document.getElementById("sidebar");
	const mainContent = document.querySelector(".main-content");

	sidebar.classList.toggle("collapsed");
	mainContent.classList.toggle("sidebar-collapsed");
}

// Logout admin
function logoutAdmin() {
	// Swal.fire({
	// 	title: "Are you sure?",
	// 	text: "You will be logged out of the admin panel",
	// 	icon: "warning",
	// 	showCancelButton: true,
	// 	confirmButtonColor: "#dc3545",
	// 	cancelButtonColor: "#6c757d",
	// 	confirmButtonText: "Yes, logout",
	// }).then((result) => {
	// 	if (result.isConfirmed) {
	// 		// Show loading
	// 		Swal.fire({
	// 			title: "Logging out...",
	// 			allowOutsideClick: false,
	// 			didOpen: () => {
	// 				Swal.showLoading();
	// 			},
	// 		});

	// Perform logout
	fetch("../../php/admin/logout.php", {
		method: "POST",
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.status === "success") {
				window.location.href = "../../views/landing_page.php";
			} else {
				Swal.fire("Error!", "Logout failed", "error");
			}
		})
		.catch((error) => {
			console.error("Logout error:", error);
			// Even if there's an error, redirect to landing page
			window.location.href = "../../views/landing_page.php";
		});
	// }
	// });
}

// Filter table function
function filterTable(searchInputId, tableId) {
	const searchInput = document.getElementById(searchInputId);
	const table = document.getElementById(tableId);
	const tbody = table.querySelector("tbody");
	const rows = tbody.querySelectorAll("tr");

	const searchTerm = searchInput.value.toLowerCase();

	rows.forEach((row) => {
		// Skip loading/empty state rows
		if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
			return;
		}

		const text = row.textContent.toLowerCase();
		row.style.display = text.includes(searchTerm) ? "" : "none";
	});
}

// Clear search
function clearSearch(searchInputId, tableId) {
	document.getElementById(searchInputId).value = "";
	filterTable(searchInputId, tableId);
}

// Setup search listeners
function setupSearchListeners() {
	// User search
	const userSearch = document.getElementById("userSearch");
	if (userSearch) {
		userSearch.addEventListener("input", () =>
			filterTable("userSearch", "usersTable")
		);
	}

	// BHW search
	const bhwSearch = document.getElementById("bhwSearch");
	if (bhwSearch) {
		bhwSearch.addEventListener("input", () =>
			filterTable("bhwSearch", "bhwTable")
		);
	}

	// Midwife search
	const midwifeSearch = document.getElementById("midwifeSearch");
	if (midwifeSearch) {
		midwifeSearch.addEventListener("input", () =>
			filterTable("midwifeSearch", "midwivesTable")
		);
	}

	// Location search
	const locationSearch = document.getElementById("locationSearch");
	if (locationSearch) {
		locationSearch.addEventListener("input", () =>
			filterTable("locationSearch", "locationsTable")
		);
	}

	// Activity search
	const activitySearch = document.getElementById("activitySearch");
	if (activitySearch) {
		activitySearch.addEventListener("input", () =>
			filterTable("activitySearch", "activityLogsTable")
		);
	}
}

// Format date for display
function formatDate(dateString) {
	if (!dateString) return "N/A";

	const date = new Date(dateString);
	return date.toLocaleDateString("en-US", {
		year: "numeric",
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
}

// Show loading in table
function showTableLoading(tableBodyId, colspan = 8) {
	const tbody = document.getElementById(tableBodyId);
	tbody.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading...</p>
                </div>
            </td>
        </tr>
    `;
}

// Show empty state in table
function showTableEmpty(
	tableBodyId,
	message = "No data available",
	colspan = 8
) {
	const tbody = document.getElementById(tableBodyId);
	tbody.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>${message}</p>
                </div>
            </td>
        </tr>
    `;
}

// Show error in table
function showTableError(
	tableBodyId,
	error = "Failed to load data",
	colspan = 8
) {
	const tbody = document.getElementById(tableBodyId);
	tbody.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center">
                <div class="empty-state text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${error}</p>
                </div>
            </td>
        </tr>
    `;
}

// Handle responsive sidebar on mobile
function handleResponsiveSidebar() {
	const sidebar = document.getElementById("sidebar");
	const sidebarToggle = document.querySelector(".sidebar-toggle");

	// Close sidebar when clicking outside on mobile
	document.addEventListener("click", function (event) {
		if (window.innerWidth <= 768) {
			if (
				!sidebar.contains(event.target) &&
				!sidebarToggle.contains(event.target)
			) {
				sidebar.classList.remove("show");
			}
		}
	});

	// Handle window resize
	window.addEventListener("resize", function () {
		if (window.innerWidth > 768) {
			sidebar.classList.remove("show");
		}
	});
}

// Initialize common functionality
document.addEventListener("DOMContentLoaded", function () {
	handleResponsiveSidebar();

	// Override sidebar toggle for mobile
	const originalToggle = window.toggleSidebar;
	window.toggleSidebar = function () {
		if (window.innerWidth <= 768) {
			const sidebar = document.getElementById("sidebar");
			sidebar.classList.toggle("show");
		} else {
			originalToggle();
		}
	};
});
