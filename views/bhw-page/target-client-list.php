<?php session_start(); ?>
<?php
// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

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
    <title>Target Client List (TCL)</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css?v=1.0.3" />
    <link rel="stylesheet" href="css/header.css?v=1.0.3" />
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.2" />
    <link rel="stylesheet" href="css/notification-style.css?v=1.0.2" />
    <link rel="stylesheet" href="css/skeleton-loading.css?v=1.0.2" />
    <link rel="stylesheet" href="css/bhw/target-client-list.css?v=1.0.2" />
    <link rel="stylesheet" href="css/bhw/table-style.css?v=1.0.3" />

</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="target-client-list-section">
            <div class="page-header">
                <h1 class="page-title">Target Client List (TCL)</h1>
                <p class="page-subtitle">View and track each child's completed and upcoming vaccinations.</p>
            </div>

            <h2 class="section-title">
                <div class="title-actions">

                </div>
            </h2>
        </section>

        <section class="target-client-list-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__top">
                        <div class="data-table-toolbar__titles">
                            <h2 class="data-table-title">Target Client List</h2>
                        </div>

                        <div class="data-table-toolbar__controls">
                            <div class="data-table-search">
                                <span class="material-symbols-rounded data-table-search__icon" aria-hidden="true">search</span>
                                <input id="searchInput" class="data-table-search__input" type="text" placeholder="Search by name, mother, address" oninput="filterTable()" />
                            </div>
                                                        <button id="clearFiltersBtn" class="btn clear-btn btn-icon">Clear</button>
                            <button id="applyFiltersBtn" class="btn apply-btn btn-icon" type="button">Apply</button>

                            <button class="btn export-btn btn-icon" onclick="exportToCSV()">
                                <span class="material-symbols-rounded" aria-hidden="true">file_download</span>
                                Export CSV
                            </button>

                        </div>
                    </div>

                    <div class="data-table-actions">
                        <div class="filters">
                            <div class="filter-item">
                                <label class="filter-label" for="paStatus">Status</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                                    <select id="filterStatus">
                                        <option value="" disabled selected>Status</option>
                                        <option value="all">All</option>
                                        <option value="SCHEDULED">Scheduled</option>
                                        <option value="MISSED">Missed</option>
                                        <option value="TRANSFERRED">Transferred</option>
                                    </select>
                                </div>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label" for="paPurok">Purok</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">location_on</span>
                                    <input id="filterPurok" type="text" placeholder="e.g. Purok 1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table" id="tclTable">
                        <thead>
                            <tr>
                                <th>Name of Child</th>
                                <th>Sex</th>
                                <th>Date of Birth</th>
                                <th>Mother's Name</th>
                                <th>Address</th>
                                <th>Weight (kg)</th>
                                <th>Height (cm)</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="tclBody">
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                            </tr>
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="pager" id="pager">
                        <div id="pageInfo" class="page-info">&nbsp;</div>
                        <div class="pager-controls">
                            <button id="tclPrevBtn" type="button" class="pager-btn">
                                <span class="material-symbols-rounded">chevron_backward</span>
                                Prev
                            </button>
                            <span id="tclPageButtons" class="page-buttons"></span>
                            <button id="tclNextBtn" type="button" class="pager-btn">
                                Next
                                <span class="material-symbols-rounded">chevron_forward</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- QR Scanner Modal -->
    <div id="qrOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 20px; border-radius: 12px; max-width: 600px; width: 90%; text-align: center;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Scan Baby QR Code</h3>
                <button id="closeScannerBtn" style="background: #dc3545; color: white; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; font-size: 20px;">×</button>
            </div>
            <select id="cameraSelect" style="margin-bottom: 15px; padding: 8px; width: 100%; border-radius: 5px; display: none;"></select>
            <div id="qrReader" style="width: 100%; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px;"></div>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">Point the camera at the QR code</p>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script>
        // Column config for TCL collapsed view (9 columns)
        const TCL_TOTAL_COLS = 8;
        function getTclColsConfig() {
            return [
                { type: 'text', widthClass: 'skeleton-col-1' }, // Name
                { type: 'text', widthClass: 'skeleton-col-2' }, // Sex
                { type: 'text', widthClass: 'skeleton-col-3' }, // DOB
                { type: 'text', widthClass: 'skeleton-col-4' }, // Mother
                { type: 'text', widthClass: 'skeleton-col-5' }, // Address
                { type: 'text', widthClass: 'skeleton-col-2' }, // Weight
                { type: 'text', widthClass: 'skeleton-col-3' }, // Height
                { type: 'text', widthClass: 'skeleton-col-5' }  // Remarks
            ];
        }
        // Date formatting helper
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
                // Escape HTML to avoid injection in inserted table cells
                function escapeHtml(unsafe) {
                    if (unsafe === null || unsafe === undefined) return '';
                    return String(unsafe)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                // Display a value or a hyphen when empty; always escape HTML
                function displayCell(val) {
                    if (val === null || val === undefined) return '-';
                    const s = String(val).trim();
                    return s === '' ? '-' : escapeHtml(s);
                }
        let tclRecords = [];
        let tclPage = 1;
        const tclLimit = 10;

        async function loadTCLData(page = 1, opts = {}) {
            const body = document.querySelector('#tclBody');
            const prevBtn = document.getElementById('tclPrevBtn');
            const nextBtn = document.getElementById('tclNextBtn');
            const pagerSpan = document.getElementById('tclPageButtons');
            const pageInfoEl = document.getElementById('pageInfo');

            if (pagerSpan) pagerSpan.innerHTML = `<span class="pager-spinner" aria-label="Loading" role="status"></span>`;
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            // Show neutral page-info during loading (parity with other pages)
            if (pageInfoEl && (!pageInfoEl.textContent || pageInfoEl.textContent === '\u00A0')) {
                pageInfoEl.textContent = 'Showing 0-0 of 0 entries';
            }

            if (!opts || opts.keep !== true) {
                if (typeof applyTableSkeleton === 'function') {
                    applyTableSkeleton(body, getTclColsConfig(), tclLimit);
                }
                // If skeleton utility is unavailable, keep existing static skeleton rows in markup.
            }

            try {
                const params = new URLSearchParams();
                params.set('page', page);
                params.set('limit', tclLimit);
                const search = document.getElementById('searchInput').value.trim();
                const status = document.getElementById('filterStatus').value;
                const purok = document.getElementById('filterPurok').value.trim();
                if (search) params.set('search', search);
                if (status) params.set('status', status);
                if (purok) params.set('purok', purok);

                const res = await fetch(`php/supabase/bhw/get_target_client_list.php?${params.toString()}`);
                const data = await res.json();
                if (data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', {
                            colspan: TCL_TOTAL_COLS,
                            kind: 'error'
                        });
                    } else {
                        body.innerHTML = `<tr class="message-row error"><td colspan="${TCL_TOTAL_COLS}">Failed to load data. Please try again.</td></tr>`;
                    }
                    updateTclPager(1, false);
                    updateTclInfo(0, 0, 0, 0);
                    return;
                }
                tclRecords = data.data || [];
                renderTCLTable(tclRecords);
                tclPage = data.page || page;
                updateTclPager(tclPage, data.has_more === true);
                updateTclInfo(tclPage, tclLimit, tclRecords.length, data.total || tclRecords.length);
            } catch (e) {
                console.error('Error loading TCL data:', e);
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(body, 'Failed to load data. Please try again.', {
                        colspan: TCL_TOTAL_COLS,
                        kind: 'error'
                    });
                } else {
                    body.innerHTML = `<tr class="message-row error"><td colspan="${TCL_TOTAL_COLS}">Failed to load data. Please try again.</td></tr>`;
                }
                updateTclPager(1, false);
                updateTclInfo(0, 0, 0, 0);
            }
        }

        function renderTCLTable(records) {
            const body = document.querySelector('#tclBody');
            if (!records || records.length === 0) {
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(body, 'No records found', {
                        colspan: TCL_TOTAL_COLS
                    });
                } else {
                    body.innerHTML = `<tr class="message-row"><td colspan="${TCL_TOTAL_COLS}">No records found</td></tr>`;
                }
                return;
            }

            let rows = '';
            records.forEach(item => {
                const vaccines = {
                    BCG: item.BCG,
                    'Hepatitis B': item['Hepatitis B'],
                    'Penta 1': item['Penta 1'],
                    'Penta 2': item['Penta 2'],
                    'Penta 3': item['Penta 3'],
                    'OPV 1': item['OPV 1'],
                    'OPV 2': item['OPV 2'],
                    'OPV 3': item['OPV 3'],
                    'PCV 1': item['PCV 1'],
                    'PCV 2': item['PCV 2'],
                    'PCV 3': item['PCV 3'],
                    'MCV1 (AMV)': item['MCV1_AMV'],
                    'MCV2 (MMR)': item['MCV2_MMR']
                };
                const vaccinesJson = encodeURIComponent(JSON.stringify(vaccines));
                rows += `
                <tr class="tcl-row" data-vaccines='${vaccinesJson}'>
                    <td class="tcl-name-cell"><button type="button" class="vaccine-toggle" aria-label="Toggle vaccines" title="View vaccine details"><span class="material-symbols-rounded">expand_more</span></button>${displayCell(item.child_name)}</td>
                    <td>${displayCell(item.sex)}</td>
                    <td>${displayCell(formatDate(item.date_of_birth))}</td>
                    <td>${displayCell(item.mother_name)}</td>
                    <td>${displayCell(item.address)}</td>
                    <td>${displayCell(item.weight)}</td>
                    <td>${displayCell(item.height)}</td>
                    <td>${displayCell(item.remarks)}</td>
                </tr>`;
            });
            body.innerHTML = rows;
        }

        function getVaccineCell(status) {
            if (!status) return '<span class="vaccine-empty">-</span>';

            let className = '';
            if (status.includes('✓')) {
                className = 'vaccine-completed';
            } else if (status.includes('✗')) {
                className = 'vaccine-missed';
            } else if (status === 'SCHEDULED') {
                className = 'vaccine-scheduled';
            }

            const cleanStatus = String(status).replace(/✓|✗/g, '').trim();

            return `<span class="${className}">${escapeHtml(cleanStatus || '-')}</span>`;
        }

        function formatVaccineExportValue(value) {
            if (!value) return '';
            const trimmed = String(value).trim();
            if (trimmed.startsWith('✓')) {
                return trimmed.replace('✓', '').trim();
            }
            return '';
        }

        function filterTable() {
            loadTCLData(1);
        }

        function applyFilters() {
            filterTable();
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterPurok').value = '';
            loadTCLData(1);
        }

        function updateTclPager(page, hasMore) {
            const prevBtn = document.getElementById('tclPrevBtn');
            const nextBtn = document.getElementById('tclNextBtn');
            const pageSpan = document.getElementById('tclPageButtons');
            if (!prevBtn || !nextBtn || !pageSpan) return;
            prevBtn.disabled = page <= 1;
            nextBtn.disabled = !hasMore;
            pageSpan.innerHTML = `<button type="button" data-page="${page}" disabled class="tcl-page-num">${page}</button>`;
            console.log('Updating pager:', page, hasMore);
        }

        function updateTclInfo(page, limit, count, total) {
            const info = document.getElementById('pageInfo');
            if (!info) return;
            const totalNum = Number.isFinite(Number(total)) ? Number(total) : 0;
            if (totalNum === 0 || count === 0) {
                info.textContent = 'Showing 0-0 of 0 entries';
                return;
            }
            const start = (page - 1) * limit + 1;
            const end = start + Math.max(0, count) - 1;
            const endClamped = Math.min(end, totalNum || end);
            info.textContent = `Showing ${start}-${endClamped} of ${totalNum} entries`;
        }

        document.getElementById('tclPrevBtn').addEventListener('click', (e) => {
            console.log('TCL Prev clicked', {
                currentPage: tclPage
            });
            if (tclPage > 1) loadTCLData(tclPage - 1, {
                keep: true
            });
        });
        document.getElementById('tclNextBtn').addEventListener('click', (e) => {
            console.log('TCL Next clicked', {
                currentPage: tclPage
            });
            loadTCLData(tclPage + 1, {
                keep: true
            });
        });

        // Build vaccine detail HTML
        function buildVaccineDetails(vaccinesObj) {
            const entries = Object.entries(vaccinesObj);
            let inner = '<table class="vaccine-details-table"><thead><tr><th>Vaccine</th><th>Date</th></tr></thead><tbody>';
            entries.forEach(([name, status]) => {
                inner += `<tr><td>${name}</td><td>${getVaccineCell(status)}</td></tr>`;
            });
            inner += '</tbody></table>';
            return inner;
        }

        // Delegated click for expanding/collapsing vaccine details
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.vaccine-toggle');
            if (!btn) return;
            const row = btn.closest('tr.tcl-row');
            if (!row) return;
            const next = row.nextElementSibling;
            const icon = btn.querySelector('.material-symbols-rounded');
            const vaccinesEncoded = row.getAttribute('data-vaccines');
            if (!vaccinesEncoded) return;
            if (next && next.classList.contains('vaccines-row')) {
                // collapse
                next.remove();
                if (icon) icon.textContent = 'expand_more';
            } else {
                let vaccinesObj = {};
                try {
                    vaccinesObj = JSON.parse(decodeURIComponent(vaccinesEncoded));
                } catch (_) {}
                const detailsHtml = buildVaccineDetails(vaccinesObj);
                const detailsRow = document.createElement('tr');
                detailsRow.className = 'vaccines-row';
                const td = document.createElement('td');
                td.colSpan = TCL_TOTAL_COLS;
                td.innerHTML = detailsHtml;
                detailsRow.appendChild(td);
                row.parentNode.insertBefore(detailsRow, row.nextElementSibling);
                if (icon) icon.textContent = 'expand_less';
            }
        });

        function exportToCSV() {
            if (!tclRecords || tclRecords.length === 0) {
                alert('No data to export');
                return;
            }

            const headers = [
                'Child Name', 'Sex', 'Date of Birth', 'Mother\'s Name', 'Address',
                'BCG', 'Hepatitis B', 'Penta 1', 'Penta 2', 'Penta 3',
                'OPV 1', 'OPV 2', 'OPV 3', 'PCV 1', 'PCV 2', 'PCV 3',
                'MCV1 (AMV)', 'MCV2 (MMR)', 'Weight (kg)', 'Height (cm)', 'Remarks'
            ];

            const csvContent = [
                headers.join(','),
                ...tclRecords.map(item => [
                    `"${item.child_name}"`,
                    item.sex,
                    item.date_of_birth,
                    `"${item.mother_name}"`,
                    `"${item.address}"`,
                    `"${formatVaccineExportValue(item.BCG)}"`,
                    `"${formatVaccineExportValue(item['Hepatitis B'])}"`,
                    `"${formatVaccineExportValue(item['Penta 1'])}"`,
                    `"${formatVaccineExportValue(item['Penta 2'])}"`,
                    `"${formatVaccineExportValue(item['Penta 3'])}"`,
                    `"${formatVaccineExportValue(item['OPV 1'])}"`,
                    `"${formatVaccineExportValue(item['OPV 2'])}"`,
                    `"${formatVaccineExportValue(item['OPV 3'])}"`,
                    `"${formatVaccineExportValue(item['PCV 1'])}"`,
                    `"${formatVaccineExportValue(item['PCV 2'])}"`,
                    `"${formatVaccineExportValue(item['PCV 3'])}"`,
                    `"${formatVaccineExportValue(item['MCV1_AMV'])}"`,
                    `"${formatVaccineExportValue(item['MCV2_MMR'])}"`,
                    item.weight,
                    item.height,
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

        let html5QrcodeInstance = null;

        async function openScanner() {
            const overlay = document.getElementById('qrOverlay');
            if (overlay) overlay.style.display = 'flex';

            try {
                const devices = await Html5Qrcode.getCameras();
                const camSel = document.getElementById('cameraSelect');
                if (camSel) camSel.innerHTML = '';

                if (devices && devices.length > 0) {
                    if (camSel) {
                        devices.forEach((d, idx) => {
                            const opt = document.createElement('option');
                            opt.value = d.id;
                            opt.textContent = d.label || ('Camera ' + (idx + 1));
                            camSel.appendChild(opt);
                        });
                        camSel.style.display = 'inline-block';
                    }
                } else if (camSel) {
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
                closeScanner();
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    alert('Camera permission denied. Please allow camera access in your browser settings and try again.');
                } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
                    alert('No camera found. Please connect a camera and try again.');
                } else {
                    alert('Camera error: ' + (e.message || e.toString()));
                }
            }
        }


        function closeScanner() {
            const overlay = document.getElementById('qrOverlay');
            if (overlay) overlay.style.display = 'none';

            try {
                if (html5QrcodeInstance) {
                    html5QrcodeInstance.stop();
                    html5QrcodeInstance.clear();
                }
            } catch (e) {}
        }

        function onScanSuccess(decodedText) {
            console.log('QR Scan success:', decodedText);
            closeScanner();

            const match = decodedText.match(/baby_id=([^&\s]+)/i);
            if (match && match[1]) {
                document.getElementById('searchInput').value = decodeURIComponent(match[1]);
                filterTable();
                return;
            }

            document.getElementById('searchInput').value = decodedText;
            filterTable();
        }

        function onScanFailure(err) {}

        document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
        document.getElementById('clearFiltersBtn').addEventListener('click', clearFilters);
        document.getElementById('closeScannerBtn').addEventListener('click', closeScanner);

        window.addEventListener('DOMContentLoaded', () => loadTCLData(1));
    </script>
</body>

</html>