// Location Management JavaScript

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	getLocations();
});

// Fetch and display locations (reusing from home.js)
async function getLocations() {
	try {
		const response = await fetch("php/supabase/admin/show_locations.php");
		const data = await response.json();

		const tbody = document.querySelector("#locationsTableBody");
		tbody.innerHTML = "";

		for (const location of data) {
			tbody.innerHTML += `<tr>
                <td class="checkbox-cell"><input type="checkbox" class="location-checkbox" value="${location.id}"></td>
                <td>${location.id}</td>
                <td>${location.province}</td>
                <td>${location.city_municipality}</td>
                <td>${location.barangay}</td>
                <td>${location.purok}</td>
                <td>${location.created_at}</td>
                <td class="actions-cell">
                    <button onclick="deleteLocation('${location.id}')" class="action-icon-btn" aria-label="Delete location ${location.id}"><span class="material-symbols-rounded">delete</span></button>
                </td>
            </tr>`;
		}
	} catch (error) {
		console.error("Error fetching locations:", error);
	}
}

// Toggle all location checkboxes
function toggleAllLocations() {
	const selectAll = document.getElementById("selectAllLocations");
	const checkboxes = document.querySelectorAll(".location-checkbox");
	checkboxes.forEach((checkbox) => {
		checkbox.checked = selectAll.checked;
	});
}

// Show add location form
function showAddLocationForm() {
	openModal('addLocationModal');
}

// Cancel add location
function cancelAddLocation() {
	// Clear form fields
	const fields = [
		"add_province",
		"add_city_municipality",
		"add_barangay",
		"add_purok"
	];
	fields.forEach(id => {
		const el = document.getElementById(id);
		if (el) el.value = "";
	});
	closeModal('addLocationModal');
}

// Save location
async function saveLocation() {
	const formData = new FormData();

	formData.append("province", document.getElementById("add_province").value);
	formData.append(
		"city_municipality",
		document.getElementById("add_city_municipality").value
	);
	formData.append("barangay", document.getElementById("add_barangay").value);
	formData.append("purok", document.getElementById("add_purok").value);

	try {
		const response = await fetch("php/supabase/admin/add_location.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Location added successfully", "success");
			cancelAddLocation();
			getLocations();
		} else {
			Swal.fire("Error!", data.message, "error");
		}
	} catch (error) {
		console.error("Error saving location:", error);
		Swal.fire("Error!", "Failed to save location", "error");
	}
}

// Delete location
async function deleteLocation(location_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the location!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#e74c3c",
		cancelButtonColor: "#95a5a6",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("location_id", location_id);

			const response = await fetch(
				"php/supabase/admin/delete_location.php",
				{
					method: "POST",
					body: formData,
				}
			);

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Location has been deleted.", "success");
				getLocations();
			} else {
				Swal.fire("Error!", data.message, "error");
			}
		} catch (error) {
			console.error("Error deleting location:", error);
			Swal.fire("Error!", "Failed to delete location", "error");
		}
	}
}

// Delete selected locations
async function deleteSelectedLocations() {
	const selectedBoxes = document.querySelectorAll(".location-checkbox:checked");

	if (selectedBoxes.length === 0) {
		Swal.fire(
			"Warning!",
			"Please select at least one location to delete",
			"warning"
		);
		return;
	}

	const result = await Swal.fire({
		title: "Are you sure?",
		text: `This will delete ${selectedBoxes.length} selected location(s)!`,
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
				formData.append("location_id", checkbox.value);
				await fetch("php/supabase/admin/delete_location.php", {
					method: "POST",
					body: formData,
				});
			}

			Swal.fire(
				"Deleted!",
				`${selectedBoxes.length} location(s) deleted successfully`,
				"success"
			);
			getLocations();
		} catch (error) {
			console.error("Error deleting locations:", error);
			Swal.fire("Error!", "Failed to delete locations", "error");
		}
	}
}
