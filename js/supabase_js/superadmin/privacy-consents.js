const CONSENT_LIMIT = 10;
let currentConsentPage = 1;
let consentFilters = { search: "", start: "", end: "" };
let consentPageData = [];

document.addEventListener("DOMContentLoaded", () => {
	bindConsentFilters();
	initConsentPager();
	loadPrivacyConsents(1);
});

function bindConsentFilters() {
	const searchInput = document.getElementById("consentSearchInput");
	const startDateInput = document.getElementById("consentStartDate");
	const endDateInput = document.getElementById("consentEndDate");
	const clearBtn = document.getElementById("clearConsentFiltersBtn");

	if (searchInput) {
		searchInput.addEventListener("keydown", (e) => {
			if (e.key === "Enter") {
				e.preventDefault();
				applyConsentFilters();
			}
		});
		searchInput.addEventListener("input", () => {
			if (!searchInput.value.trim()) {
				applyConsentFilters();
			}
		});
	}
	if (startDateInput) startDateInput.addEventListener("change", applyConsentFilters);
	if (endDateInput) endDateInput.addEventListener("change", applyConsentFilters);
	if (clearBtn) {
		clearBtn.addEventListener("click", () => {
			if (searchInput) searchInput.value = "";
			if (startDateInput) startDateInput.value = "";
			if (endDateInput) endDateInput.value = "";
			applyConsentFilters();
		});
	}
}

async function loadPrivacyConsents(page = 1) {
	const tbody = document.getElementById("consentsTableBody");
	if (tbody) {
		tbody.innerHTML =
			'<tr class="data-table__message-row loading"><td colspan="6">Loading privacy consents...</td></tr>';
	}
	try {
		const params = new URLSearchParams({
			page,
			limit: CONSENT_LIMIT,
		});
		if (consentFilters.search) params.append("search", consentFilters.search);
		if (consentFilters.start) params.append("start", consentFilters.start);
		if (consentFilters.end) params.append("end", consentFilters.end);

		const response = await fetch(
			`php/supabase/superadmin/get_privacy_consents.php?${params.toString()}`
		);
		const result = await response.json();

		if (result.status !== "success") {
			throw new Error(result.message || "Failed to load privacy consents.");
		}

		const data = Array.isArray(result.data) ? result.data : [];
		const total = result.total || 0;

		if (total > 0 && data.length === 0 && page > 1) {
			loadPrivacyConsents(page - 1);
			return;
		}

		consentPageData = data;
		renderConsentTable(consentPageData);

		currentConsentPage = result.page || page;
		updateConsentPager({
			page: currentConsentPage,
			limit: result.limit || CONSENT_LIMIT,
			total,
			hasMore: result.has_more || false,
		});
	} catch (error) {
		console.error("Error loading privacy consents:", error);
		if (tbody) {
			tbody.innerHTML =
				'<tr><td colspan="6" class="empty-state">Failed to load privacy consents.</td></tr>';
		}
		updateConsentPager({ page: 1, limit: CONSENT_LIMIT, total: 0, hasMore: false });
	}
}

function applyConsentFilters() {
	const searchValue = document.getElementById("consentSearchInput")?.value.trim() || "";
	const startDateValue = document.getElementById("consentStartDate")?.value || "";
	const endDateValue = document.getElementById("consentEndDate")?.value || "";

	consentFilters = {
		search: searchValue,
		start: startDateValue,
		end: endDateValue,
	};
	loadPrivacyConsents(1);
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

async function exportConsentsToCsv() {
	try {
		const params = new URLSearchParams({
			page: 1,
			limit: 1000,
		});
		if (consentFilters.search) params.append("search", consentFilters.search);
		if (consentFilters.start) params.append("start", consentFilters.start);
		if (consentFilters.end) params.append("end", consentFilters.end);

		const response = await fetch(
			`php/supabase/superadmin/get_privacy_consents.php?${params.toString()}`
		);
		const result = await response.json();

		if (result.status !== "success" || !Array.isArray(result.data) || !result.data.length) {
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

		const rows = result.data.map((consent) => [
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
		link.download = `privacy-consents-${new Date().toISOString().split("T")[0]}.csv`;
		link.click();
		URL.revokeObjectURL(url);
	} catch (error) {
		console.error("Failed to export consents:", error);
		Swal.fire("Error", "Failed to export privacy consents.", "error");
	}
}

function initConsentPager() {
	const prevBtn = document.getElementById("consentPrevBtn");
	const nextBtn = document.getElementById("consentNextBtn");
	if (prevBtn) {
		prevBtn.addEventListener("click", () => {
			const page = parseInt(prevBtn.dataset.page || "1", 10);
			if (page > 1) loadPrivacyConsents(page - 1);
		});
	}
	if (nextBtn) {
		nextBtn.addEventListener("click", () => {
			const page = parseInt(nextBtn.dataset.page || "1", 10);
			loadPrivacyConsents(page + 1);
		});
	}
}

function updateConsentPager({ page, limit, total, hasMore }) {
	const prevBtn = document.getElementById("consentPrevBtn");
	const nextBtn = document.getElementById("consentNextBtn");
	const info = document.getElementById("consentPageInfo");
	if (!prevBtn || !nextBtn || !info) return;

	const start = total === 0 ? 0 : (page - 1) * limit + 1;
	const end = total === 0 ? 0 : Math.min(page * limit, total);
	info.textContent = `Showing ${start}-${end} of ${total}`;
	prevBtn.disabled = page <= 1;
	nextBtn.disabled = !hasMore;
	prevBtn.dataset.page = String(page);
	nextBtn.dataset.page = String(page);
}

