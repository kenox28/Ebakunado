// Admin Location Management Functions

// Get all locations
async function getLocations() {
	showTableLoading("locationsTableBody", 7);

	try {
		// const response = await fetch("../../php/supabase/admin/show_places.php");
		const response = await fetch("../../php/mysql/admin/show_places.php");
		const data = await response.json();

		const tbody = document.getElementById("locationsTableBody");

		if (data && data.length > 0) {
			let html = "";
			data.forEach((location) => {
				html += `
                    <tr>
                        <td>${location.location_id}</td>
                        <td>${location.province}</td>
                        <td>${location.city_municipality}</td>
                        <td>${location.barangay}</td>
                        <td>${location.purok}</td>
                        <td>${formatDate(location.created_at)}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" onclick="editLocation(${
																	location.location_id
																})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteLocation(${
																	location.location_id
																})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
			});
			tbody.innerHTML = html;
		} else {
			showTableEmpty("locationsTableBody", "No locations found", 7);
		}
	} catch (error) {
		console.error("Error fetching locations:", error);
		showTableError("locationsTableBody", "Failed to load locations", 7);
	}
}

// Show add location form
function showAddLocationForm() {
	document.getElementById("addLocationForm").style.display = "block";
	document.getElementById("add_province").focus();
}

// Hide add location form
function hideAddLocationForm() {
	document.getElementById("addLocationForm").style.display = "none";
	document.getElementById("locationForm").reset();
}

// Add new location
async function addLocation() {
	const province = document.getElementById("add_province").value.trim();
	const city = document.getElementById("add_city").value.trim();
	const barangay = document.getElementById("add_barangay").value.trim();
	const purok = document.getElementById("add_purok").value.trim();

	if (!province || !city || !barangay || !purok) {
		Swal.fire("Error!", "Please fill in all fields", "error");
		return;
	}

	try {
		const formData = new FormData();
		formData.append("province", province);
		formData.append("city_municipality", city);
		formData.append("barangay", barangay);
		formData.append("purok", purok);

		// const response = await fetch("../../php/supabase/admin/save_place.php");
		const response = await fetch("../../php/mysql/admin/save_place.php", {
			method: "POST",
			body: formData,
		});

		const data = await response.json();
		if (data.status === "success") {
			Swal.fire("Success!", "Location added successfully", "success");
			hideAddLocationForm();
			getLocations(); // Reload locations table
		} else {
			Swal.fire("Error!", data.message || "Failed to add location", "error");
		}
	} catch (error) {
		console.error("Error adding location:", error);
		Swal.fire("Error!", "Failed to add location", "error");
	}
}

// Edit location (placeholder - can be implemented if needed)
function editLocation(location_id) {
	Swal.fire("Info", "Location editing feature coming soon!", "info");
}

// Delete location
async function deleteLocation(location_id) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will permanently delete the location!",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#dc3545",
		cancelButtonColor: "#6c757d",
		confirmButtonText: "Yes, delete it!",
	});

	if (result.isConfirmed) {
		try {
			const formData = new FormData();
			formData.append("location_id", location_id);

			// const response = await fetch("../../php/supabase/admin/delete_place.php");
			const response = await fetch("../../php/mysql/admin/delete_place.php", {
				method: "POST",
				body: formData,
			});

			const data = await response.json();
			if (data.status === "success") {
				Swal.fire("Deleted!", "Location has been deleted.", "success");
				getLocations(); // Reload locations table
			} else {
				Swal.fire(
					"Error!",
					data.message || "Failed to delete location",
					"error"
				);
			}
		} catch (error) {
			console.error("Error deleting location:", error);
			Swal.fire("Error!", "Failed to delete location", "error");
		}
	}
}
