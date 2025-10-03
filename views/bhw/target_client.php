<?php include 'Include/header.php'; ?>
<style>
	.vaccine-completed {
		color: #28a745;
		font-weight: bold;
	}
	.vaccine-missed {
		color: #dc3545;
		font-weight: bold;
	}
	.vaccine-scheduled {
		color: #ffc107;
		font-weight: bold;
	}
</style>

<div id="qrOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.7); display:none; align-items:center; justify-content:center; z-index:9999;">
	<div style="background:#fff; padding:10px; max-width:90vw; max-height:90vh; display:flex; flex-direction:column; gap:8px;">
		<select id="cameraSelect" style="margin-bottom:6px; padding:4px 6px; display:none;" onchange="switchCamera(event)"></select>
		<div id="qrReader" style="width: 340px;"></div>
		<div style="display:flex; justify-content:space-between; gap:8px; align-items:center; flex-wrap:wrap;">
			<span style="font-size:12px; color:#444;">Point camera at QR code</span>
			<label style="font-size:12px;">
				<span style="margin-right:6px;">or Upload Image:</span>
				<input type="file" id="qrImageInput" accept="image/*" onchange="scanFromImage(event)" />
			</label>
			<button id="torchBtn" onclick="toggleTorch()" style="display:none;">Torch On</button>
			<button onclick="closeScanner()">Close</button>
		</div>
	</div>
</div>

<div class="filters" style="margin:10px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
	<label style="font-size:12px; color:#333;">Search/QR:
		<input id="searchInput" type="text" placeholder="Search by name, mother, address" oninput="filterTable()" style="padding:4px 6px;">
	</label>
	<label style="font-size:12px; color:#333;">Status:
		<select id="filterStatus" style="padding:4px 6px;">
			<option value="all">All</option>
			<option value="SCHEDULED">Scheduled</option>
			<option value="MISSED">Missed</option>
			<option value="TRANSFERRED">Transferred</option>
		</select>
	</label>
	<label style="font-size:12px; color:#333;">Vaccine:
		<select id="filterVaccine" style="padding:4px 6px; min-width:160px;">
			<option value="all">All</option>
			<option value="BCG">BCG</option>
			<option value="HEPAB1">HEPAB1</option>
			<option value="Pentavalent">Pentavalent</option>
			<option value="OPV">OPV</option>
			<option value="Rota">Rota Virus</option>
			<option value="PCV">PCV</option>
			<option value="MCV">MCV</option>
		</select>
	</label>
	<label style="font-size:12px; color:#333;">Purok:
		<input id="filterPurok" type="text" placeholder="e.g. Purok 1" style="padding:4px 6px;">
	</label>
	<button id="applyFiltersBtn" style="padding:4px 10px;">Apply</button>
	<button id="clearFiltersBtn" style="padding:4px 10px;">Clear</button>
	<button id="openScannerBtn" onclick="openScanner()" style="padding:4px 10px;">Scan QR</button>
	<button onclick="exportToCSV()" style="padding:4px 10px;">Export CSV</button>
</div>

<div class="table-container">
	<table class="table table-hover" id="tclTable">
		<thead>
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">Name of Child</th>
				<th rowspan="2">Sex</th>
				<th rowspan="2">Date of Birth</th>
				<th rowspan="2">Mother's Name</th>
				<th rowspan="2">Address</th>
				<th colspan="1">BCG</th>
				<th colspan="2">HEPAB1</th>
				<th colspan="3">PENTAVALENT</th>
				<th colspan="3">OPV</th>
				<th colspan="2">ROTA VIRUS</th>
				<th colspan="3">PCV</th>
				<th colspan="2">MCV</th>
				<th rowspan="2">Weight (kg)</th>
				<th rowspan="2">Height (cm)</th>
				<th rowspan="2">Status</th>
				<th rowspan="2">Remarks</th>
			</tr>
			<tr>
				<th>1st Dose</th>
				<th>w/in 24 hrs</th>
				<th>More than 24hrs</th>
				<th>1</th>
				<th>2</th>
				<th>3</th>
				<th>1</th>
				<th>2</th>
				<th>3</th>
				<th>1</th>
				<th>2</th>
				<th>1</th>
				<th>2</th>
				<th>3</th>
				<th>MCV1 (AMV)</th>
				<th>MCV2 (MMR)</th>
			</tr>
		</thead>
		<tbody id="tclBody">
			<tr>
				<td colspan="19" class="text-center">
					<div class="loading">
						<i class="fas fa-spinner fa-spin"></i>
						<p>Loading TCL data...</p>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
	let tclRecords = [];

	async function loadTCLData() {
		const body = document.querySelector('#tclBody');
		body.innerHTML = '<tr><td colspan="19">Loading...</td></tr>';
		
		try {
			const res = await fetch('../../php/supabase/bhw/get_tcl_data.php');
			const data = await res.json();
			
			if (data.status !== 'success') {
				body.innerHTML = '<tr><td colspan="19">Failed to load TCL data</td></tr>';
				return;
			}
			
			if (!data.data || data.data.length === 0) {
				body.innerHTML = '<tr><td colspan="19">No TCL data found</td></tr>';
				tclRecords = [];
				return;
			}
			
			tclRecords = data.data;
			renderTCLTable(tclRecords);
			
		} catch (e) {
			console.error('Error loading TCL data:', e);
			body.innerHTML = '<tr><td colspan="19">Error loading TCL data</td></tr>';
		}
	}

	function renderTCLTable(records) {
		const body = document.querySelector('#tclBody');
		if (!records || records.length === 0) {
			body.innerHTML = '<tr><td colspan="19">No records found</td></tr>';
			return;
		}

		let rows = '';
		records.forEach(item => {
			rows += `
				<tr>
					<td>${item.id || ''}</td>
					<td>${item.child_name || ''}</td>
					<td>${item.sex || ''}</td>
					<td>${item.date_of_birth || ''}</td>
					<td>${item.mother_name || ''}</td>
					<td>${item.address || ''}</td>
					<td>${getVaccineCell(item.BCG)}</td>
					<td>${getVaccineCell(item['HEPAB1_w_in_24hrs'])}</td>
					<td>${getVaccineCell(item['HEPAB1_more_than_24hrs'])}</td>
					<td>${getVaccineCell(item['Penta 1'])}</td>
					<td>${getVaccineCell(item['Penta 2'])}</td>
					<td>${getVaccineCell(item['Penta 3'])}</td>
					<td>${getVaccineCell(item['OPV 1'])}</td>
					<td>${getVaccineCell(item['OPV 2'])}</td>
					<td>${getVaccineCell(item['OPV 3'])}</td>
					<td>${getVaccineCell(item['Rota 1'])}</td>
					<td>${getVaccineCell(item['Rota 2'])}</td>
					<td>${getVaccineCell(item['PCV 1'])}</td>
					<td>${getVaccineCell(item['PCV 2'])}</td>
					<td>${getVaccineCell(item['PCV 3'])}</td>
					<td>${getVaccineCell(item['MCV1_AMV'])}</td>
					<td>${getVaccineCell(item['MCV2_MMR'])}</td>
					<td>${item.weight || ''}</td>
					<td>${item.height || ''}</td>
					<td>${item.status || ''}</td>
					<td>${item.remarks || ''}</td>
				</tr>
			`;
		});
		body.innerHTML = rows;
	}

	function getVaccineCell(status) {
		if (!status) return '';
		
		let className = '';
		if (status.includes('✓')) {
			className = 'vaccine-completed';
		} else if (status.includes('✗')) {
			className = 'vaccine-missed';
		} else if (status === 'SCHEDULED') {
			className = 'vaccine-scheduled';
		}
		
		return `<span class="${className}">${status}</span>`;
	}

	function filterTable() {
		const searchTerm = document.getElementById('searchInput').value.toLowerCase();
		const statusFilter = document.getElementById('filterStatus').value;
		const vaccineFilter = document.getElementById('filterVaccine').value;
		const purokFilter = document.getElementById('filterPurok').value.toLowerCase();

		if (!tclRecords || tclRecords.length === 0) return;

		const filtered = tclRecords.filter(item => {
			// Search filter
			if (searchTerm) {
				const searchText = [
					item.child_name,
					item.mother_name,
					item.address
				].join(' ').toLowerCase();
				
				if (!searchText.includes(searchTerm)) return false;
			}

			// Status filter
			if (statusFilter !== 'all' && item.status !== statusFilter) return false;

			// Purok filter
			if (purokFilter && !item.address.toLowerCase().includes(purokFilter)) return false;

			// Vaccine filter
			if (vaccineFilter !== 'all') {
				const hasVaccineStatus = [
					item.BCG,
					item['HEPAB1_w_in_24hrs'],
					item['HEPAB1_more_than_24hrs'],
					item['Penta 1'],
					item['Penta 2'],
					item['Penta 3'],
					item['OPV 1'],
					item['OPV 2'],
					item['OPV 3'],
					item['Rota 1'],
					item['Rota 2'],
					item['PCV 1'],
					item['PCV 2'],
					item['PCV 3'],
					item['MCV1_AMV'],
					item['MCV2_MMR']
				].some(status => {
					if (!status) return false;
					// Check if status contains the vaccine filter (handles checkmarks, X marks, and SCHEDULED)
					return status.toLowerCase().includes(vaccineFilter.toLowerCase()) || 
						   status.includes('✓') || status.includes('✗') || status === 'SCHEDULED';
				});
				
				if (!hasVaccineStatus) return false;
			}

			return true;
		});

		renderTCLTable(filtered);
	}

	function applyFilters() {
		filterTable();
	}

	function clearFilters() {
		document.getElementById('searchInput').value = '';
		document.getElementById('filterStatus').value = 'all';
		document.getElementById('filterVaccine').value = 'all';
		document.getElementById('filterPurok').value = '';
		renderTCLTable(tclRecords);
	}

	function exportToCSV() {
		if (!tclRecords || tclRecords.length === 0) {
			alert('No data to export');
			return;
		}

		const headers = [
			'#', 'Child Name', 'Sex', 'Date of Birth', 'Mother\'s Name', 'Address',
			'BCG', 'HEPAB1 (w/in 24hrs)', 'HEPAB1 (More than 24hrs)', 'Penta 1', 'Penta 2', 'Penta 3',
			'OPV 1', 'OPV 2', 'OPV 3', 'Rota 1', 'Rota 2', 'PCV 1', 'PCV 2', 'PCV 3',
			'MCV1 (AMV)', 'MCV2 (MMR)', 'Weight (kg)', 'Height (cm)', 'Status', 'Remarks'
		];

		const csvContent = [
			headers.join(','),
			...tclRecords.map(item => [
				item.id,
				`"${item.child_name}"`,
				item.sex,
				item.date_of_birth,
				`"${item.mother_name}"`,
				`"${item.address}"`,
				`"${item.BCG}"`,
				`"${item['HEPAB1_w_in_24hrs']}"`,
				`"${item['HEPAB1_more_than_24hrs']}"`,
				`"${item['Penta 1']}"`,
				`"${item['Penta 2']}"`,
				`"${item['Penta 3']}"`,
				`"${item['OPV 1']}"`,
				`"${item['OPV 2']}"`,
				`"${item['OPV 3']}"`,
				`"${item['Rota 1']}"`,
				`"${item['Rota 2']}"`,
				`"${item['PCV 1']}"`,
				`"${item['PCV 2']}"`,
				`"${item['PCV 3']}"`,
				`"${item['MCV1_AMV']}"`,
				`"${item['MCV2_MMR']}"`,
				item.weight,
				item.height,
				item.status,
				`"${item.remarks}"`
			].join(','))
		].join('\n');

		const blob = new Blob([csvContent], { type: 'text/csv' });
		const url = window.URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = `TCL_Child_Immunization_${new Date().toISOString().split('T')[0]}.csv`;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		window.URL.revokeObjectURL(url);
	}

	// QR Code Scanner Functions
	let html5QrcodeInstance = null;

	async function openScanner() {
		const overlay = document.getElementById('qrOverlay');
		overlay.style.display = 'flex';
		
		try {
			const devices = await Html5Qrcode.getCameras();
			const camSel = document.getElementById('cameraSelect');
			camSel.innerHTML = '';
			
			if (devices && devices.length > 0) {
				devices.forEach((d, idx) => {
					const opt = document.createElement('option');
					opt.value = d.id;
					opt.textContent = d.label || ('Camera ' + (idx + 1));
					camSel.appendChild(opt);
				});
				camSel.style.display = 'inline-block';
			} else {
				camSel.style.display = 'none';
			}
			
			if (!html5QrcodeInstance) {
				html5QrcodeInstance = new Html5Qrcode("qrReader");
			}
			
			await html5QrcodeInstance.start(
				{ facingMode: "environment" },
				{ fps: 12, qrbox: 360, formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], disableFlip: true },
				onScanSuccess,
				onScanFailure
			);
		} catch (e) {
			console.error('Camera error:', e);
			alert('Camera error: ' + e);
		}
	}

	function closeScanner() {
		const overlay = document.getElementById('qrOverlay');
		overlay.style.display = 'none';
		
		try {
			if (html5QrcodeInstance) {
				html5QrcodeInstance.stop();
				html5QrcodeInstance.clear();
			}
		} catch (e) {
			// Ignore
		}
	}

	function onScanSuccess(decodedText) {
		console.log('QR Scan success:', decodedText);
		closeScanner();
		
		// Try to extract baby_id from QR code
		const match = decodedText.match(/baby_id=([^&\s]+)/i);
		if (match && match[1]) {
			document.getElementById('searchInput').value = decodeURIComponent(match[1]);
			filterTable();
			return;
		}
		
		// Otherwise use the decoded text as search term
		document.getElementById('searchInput').value = decodedText;
		filterTable();
	}

	function onScanFailure(err) {
		// Ignore scan failures
	}

	// Event listeners
	document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
	document.getElementById('clearFiltersBtn').addEventListener('click', clearFilters);

	// Load data on page load
	window.addEventListener('DOMContentLoaded', loadTCLData);
</script>

<?php include 'Include/footer.php'; ?>