<?php session_start(); ?>
<?php
// Handle both BHW and Midwife sessions (but BHW should only see BHW features)
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type']; // Default to bhw for BHW pages
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = $_SESSION['fname'] . " " . $_SESSION['lname'];
if ($user_types != 'midwifes') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}
// Debug session
if ($user_id) {
    echo "<!-- Session Active: " . $user_type . " - " . $user_id . " -->";
} else {
    echo "<!-- Session: NOT FOUND - Available sessions: " . implode(', ', array_keys($_SESSION)) . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Client List</title>
    <!-- <link rel="stylesheet" href="/css/base.css" /> -->
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/variables.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/bhw/target-client-list.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="target-client-list-section">
            <div class="filters">
                <label>Search/QR:
                    <input id="searchInput" type="text" placeholder="Search by name, mother, address" oninput="filterTable()">
                </label>
                <label>Status:
                    <select id="filterStatus">
                        <option value="all">All</option>
                        <option value="SCHEDULED">Scheduled</option>
                        <option value="MISSED">Missed</option>
                        <option value="TRANSFERRED">Transferred</option>
                    </select>
                </label>
                <label>Vaccine:
                    <select id="filterVaccine" class="filter-vaccine">
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
                <label>Purok:
                    <input id="filterPurok" type="text" placeholder="e.g. Purok 1">
                </label>
                <button id="applyFiltersBtn" class="btn">Apply</button>
                <button id="clearFiltersBtn" class="btn">Clear</button>
                <button id="openScannerBtn" class="btn" onclick="openScanner()">Scan QR</button>
                <button class="btn" onclick="exportToCSV()">Export CSV</button>
            </div>

            <div class="table-container">
                <table class="table" id="tclTable">
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
        <td colspan="26" class="text-center">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading TCL data...</p>
            </div>
        </td>
    </tr>
</tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        let tclRecords = [];

        async function loadTCLData() {
            const body = document.querySelector('#tclBody');
            body.innerHTML = '<tr><td colspan="26">Loading...</td></tr>';

            try {
                const res = await fetch('../../php/supabase/bhw/get_tcl_data.php');
                const data = await res.json();

                if (data.status !== 'success') {
                    body.innerHTML = '<tr><td colspan="26">Failed to load TCL data</td></tr>';
                    return;
                }

                if (!data.data || data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="26">No TCL data found</td></tr>';
                    tclRecords = [];
                    return;
                }

                tclRecords = data.data;
                renderTCLTable(tclRecords);

            } catch (e) {
                console.error('Error loading TCL data:', e);
                body.innerHTML = '<tr><td colspan="26">Error loading TCL data</td></tr>';
            }
        }

        function renderTCLTable(records) {
            const body = document.querySelector('#tclBody');
            if (!records || records.length === 0) {
                body.innerHTML = '<tr><td colspan="26">No records found</td></tr>';
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

            // Remove check and cross symbols from the displayed text only
            const cleanStatus = String(status).replace(/✓|✗/g, '').trim();

            return `<span class="${className}">${cleanStatus}</span>`;
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

            const blob = new Blob([csvContent], {
                type: 'text/csv'
            });
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

                await html5QrcodeInstance.start({
                        facingMode: "environment"
                    }, {
                        fps: 12,
                        qrbox: 360,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        disableFlip: true
                    },
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
</body>

</html>