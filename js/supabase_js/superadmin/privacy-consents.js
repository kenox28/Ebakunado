let consentData = [];
let filteredConsents = [];

document.addEventListener("DOMContentLoaded", () => {
	loadPrivacyConsents();
	bindConsentFilters();
});

function bindConsentFilters() {
	const searchInput = document.getElementById("consentSearchInput");
	const startDateInput = document.getElementById("consentStartDate");
	const endDateInput = document.getElementById("consentEndDate");
	const clearBtn = document.getElementById("clearConsentFiltersBtn");

	if (searchInput) searchInput.addEventListener("input", applyConsentFilters);
	if (startDateInput) startDateInput.addEventListener("change", applyConsentFilters);
	if (endDateInput) endDateInput.addEventListener("change", applyConsentFilters);
	if (clearBtn)
		clearBtn.addEventListener("click", () => {
			if (searchInput) searchInput.value = "";
			if (startDateInput) startDateInput.value = "";
			if (endDateInput) endDateInput.value = "";
			applyConsentFilters();
		});
}

async function loadPrivacyConsents() {
	const tbody = document.getElementById("consentsTableBody");
	try {
		const response = await fetch(
			"../../php/supabase/superadmin/get_privacy_consents.php"
		);
		const result = await response.json();

		if (result.status !== "success") {
			throw new Error(result.message || "Failed to load privacy consents.");
		}

		consentData = Array.isArray(result.data) ? result.data : [];
		filteredConsents = [...consentData];
		renderConsentTable(filteredConsents);
	} catch (error) {
		console.error("Error loading privacy consents:", error);
		if (tbody) {
			tbody.innerHTML =
				'<tr><td colspan="6" class="empty-state">Failed to load privacy consents.</td></tr>';
		}
	}
}

function applyConsentFilters() {
	const searchValue = document
		.getElementById("consentSearchInput")
		?.value.trim()
		.toLowerCase();
	const startDateValue = document.getElementById("consentStartDate")?.value;
	const endDateValue = document.getElementById("consentEndDate")?.value;

	filteredConsents = consentData.filter((consent) => {
		const fullName = (consent.full_name || "").toLowerCase();
		const email = (consent.email || "").toLowerCase();
		const phone = (consent.phone_number || "").toLowerCase();
		const matchesSearch =
			!searchValue ||
			fullName.includes(searchValue) ||
			email.includes(searchValue) ||
			phone.includes(searchValue);

		const consentDate = consent.agreed_date
			? new Date(consent.agreed_date)
			: null;
		const matchesStart =
			!startDateValue ||
			(consentDate && consentDate >= new Date(startDateValue));
		const matchesEnd =
			!endDateValue ||
			(consentDate &&
				consentDate <= new Date(endDateValue + "T23:59:59"));

		return matchesSearch && matchesStart && matchesEnd;
	});

	renderConsentTable(filteredConsents);
}

function renderConsentTable(rows) {
	const tbody = document.getElementById("consentsTableBody");
	if (!tbody) return;

	if (!rows.length) {
		tbody.innerHTML =
			'<tr><td colspan="6" class="empty-state">No privacy consents match your filters.</td></tr>';
		return;
	}

	tbody.innerHTML = rows
		.map((consent) => {
			const agreed = isConsentAgreed(consent);
			return `<tr>
				<td>${consent.full_name || "—"}</td>
				<td>${consent.email || "—"}</td>
				<td>${consent.phone_number || "—"}</td>
				<td><span class="status-pill ${agreed ? "agreed" : "declined"}">${
				agreed ? "Yes" : "No"
			}</span></td>
				<td>${formatConsentDate(consent.agreed_date)}</td>
				<td>${consent.ip_address || "—"}</td>
			</tr>`;
		})
		.join("");
}

function isConsentAgreed(consent) {
	const value = consent.agreed_privacy;
	return value === true || value === "true" || value === "t" || value === 1 || value === "1";
}

function formatConsentDate(dateInput) {
	if (!dateInput) {
		return "—";
	}
	const date = new Date(dateInput);
	if (Number.isNaN(date.getTime())) {
		return dateInput;
	}
	return new Intl.DateTimeFormat("en-PH", {
		year: "numeric",
		month: "short",
		day: "2-digit",
		hour: "2-digit",
		minute: "2-digit",
	}).format(date);
}

function exportConsentsToCsv() {
	if (!filteredConsents.length) {
		Swal.fire("Nothing to export", "No privacy consent data to export.", "info");
		return;
	}

	const header = [
		"Full Name",
		"Email",
		"Phone Number",
		"Agreed",
		"Agreed Date",
		"IP Address",
	];

	const rows = filteredConsents.map((consent) => [
		consent.full_name || "",
		consent.email || "",
		consent.phone_number || "",
		isConsentAgreed(consent) ? "Yes" : "No",
		consent.agreed_date || "",
		consent.ip_address || "",
	]);

	const csvContent = [header, ...rows]
		.map((row) => row.map((val) => `"${String(val).replace(/"/g, '""')}"`).join(","))
		.join("\n");

	const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
	const url = URL.createObjectURL(blob);
	const link = document.createElement("a");
	link.href = url;
	link.download = `privacy-consents-${new Date()
		.toISOString()
		.split("T")[0]}.csv`;
	link.click();
	URL.revokeObjectURL(url);
}

