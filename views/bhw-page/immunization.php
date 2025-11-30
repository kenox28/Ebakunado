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
    <title>Immunization Records</title>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>BHW Dashboard</title>
        <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
        <link rel="stylesheet" href="css/main.css?v=1.0.2" />
        <link rel="stylesheet" href="css/header.css?v=1.0.1" />
        <link rel="stylesheet" href="css/sidebar.css?v=1.0.1" />

        <link rel="stylesheet" href="css/notification-style.css?v=1.0.1" />
        <link rel="stylesheet" href="css/skeleton-loading.css?v=1.0.1" />
        <link rel="stylesheet" href="css/modals.css?v=1.0.5" />
        <link rel="stylesheet" href="css/bhw/immunization-style.css?v=1.0.2">
        <link rel="stylesheet" href="css/bhw/growth-assessment.css?v=1.0.1">
        <link rel="stylesheet" href="css/bhw/table-style.css?v=1.0.3">
    </head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <!-- QR Scanner Modal -->
    <div id="qrOverlay" class="qr-overlay">
        <div class="qr-modal">
            <div class="qr-header">
                <h3 class="qr-title">
                    <span class="material-symbols-rounded" aria-hidden="true">qr_code_scanner</span>
                    Scan Baby QR Code for Immunization
                </h3>
                <button id="closeScannerBtn" class="close-btn action-icon-btn" aria-label="Close scanner">
                    <span class="material-symbols-rounded" aria-hidden="true">close</span>
                </button>
            </div>
            <div class="qr-body">
                <div id="cameraField" class="qr-field">
                    <label class="qr-label" for="cameraSelect">Select camera:</label>
                    <select id="cameraSelect" class="qr-select" aria-label="Available cameras"></select>
                </div>
                <div id="qrReader" class="qr-reader" aria-describedby="qrHelp"></div>
                <p id="qrHelp" class="qr-help">Point the camera at the QR code. Tips: allow camera permission, ensure good lighting, and hold steady about 10–15 cm away.</p>
            </div>
        </div>
    </div>

    <main>
        <section class="immunization-section">
            <div class="page-header">
                <h1 class="page-title">Immunization Records</h1>
                <p class="page-subtitle">Manage and review child vaccination information.</p>
            </div>

            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__top">
                        <div class="data-table-toolbar__titles">
                            <h2 class="data-table-title">Child Immunization List</h2>
                        </div>
                        <div class="data-table-toolbar__controls">
                            <button class="btn clear-btn btn-icon" id="clearFiltersBtn">Clear</button>
                            <button class="btn apply-btn btn-icon" id="applyFiltersBtn">Apply</button>
                            <button class="btn qr-btn btn-icon" id="openScannerBtn" onclick="openScanner()" type="button">
                                <span class="material-symbols-rounded" aria-hidden="true">qr_code_scanner</span>
                                Scan QR
                            </button>
                        </div>
                    </div>

                    <!-- Filters separated into their own row inside the toolbar -->
                    <div class="data-table-actions">
                        <div class="filters">
                            <div class="filter-item filter-search">
                                <label class="filter-label" for="filterName">Search Child</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">search</span>
                                    <input id="filterName" type="text" placeholder="Enter child name" />
                                </div>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label" for="filterDate">Date</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
                                    <input id="filterDate" type="date" />
                                </div>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label" for="filterStatus">Status</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                                    <select id="filterStatus">
                                        <option value="" disabled>Status</option>
                                        <option value="upcoming" selected>Scheduled</option>
                                        <option value="missed">Missed</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label" for="filterVaccine">Vaccine</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">vaccines</span>
                                    <select id="filterVaccine">
                                        <option value="" disabled selected>Vaccines</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label" for="filterPurok">Purok</label>
                                <div class="input-field">
                                    <span class="material-symbols-rounded" aria-hidden="true">location_on</span>
                                    <input id="filterPurok" type="text" placeholder="e.g. Purok 1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table" id="childhealthrecord">
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Address</th>
                                <th>Vaccine</th>
                                <th id="scheduleDateHeader">Schedule Date</th>
                                <th>Batch Schedule</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="childhealthrecordBody">
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
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
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
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
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
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
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
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
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
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="pager" id="pager">
                        <div id="pageInfo" class="page-info">&nbsp;</div>
                        <div class="pager-controls">
                            <button id="prevBtn" type="button" class="pager-btn">
                                <span class="material-symbols-rounded">chevron_backward</span>
                                Prev
                            </button>
                            <span id="pageButtons" class="page-buttons"></span>
                            <button id="nextBtn" type="button" class="pager-btn">
                                Next
                                <span class="material-symbols-rounded">chevron_forward</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Immunization Record Overlay -->
            <div class="immunization-record" id="immunizationOverlay">
                <div class="immunization-modal">
                    <div class="immunization-header">
                        <h3 class="immunization-title">
                            <span class="material-symbols-rounded">assignment</span>
                            Record Immunization
                        </h3>
                        <button class="close-btn action-icon-btn" onclick="closeImmunizationForm()">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="immunization-form" id="immunizationFormContainer"></div>
                </div>
            </div>

        </section>
    </main>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="js/growth-standards/who-growth-calculator.js?v=1.0.1" defer></script>
    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/utils/ui-feedback.js?v=1.0.2" defer></script>

    
    <script>
        // spinner CSS (scoped)
        const style = document.createElement('style');
        style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}`;
        document.head.appendChild(style);
        let chrRecords = [];
        let currentPage = 1;
        const pageSize = 10; // fixed to 10 per request

        // Column config used by skeleton generator: 7 columns layout
        function getImmunizationColsConfig() {
            return [{
                    type: 'text',
                    widthClass: 'skeleton-col-1'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-4'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-5'
                },
                {
                    type: 'pill',
                    widthClass: 'skeleton-col-6'
                },
                {
                    type: 'btn',
                    widthClass: 'skeleton-col-7'
                }
            ];
        }

        async function fetchChildHealthRecord(page = 1, opts = {}) {
            const body = document.querySelector('#childhealthrecordBody');
            const keepRows = opts.keep === true;
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const btnWrap = document.getElementById('pageButtons');
            const pageInfoEl = document.getElementById('pageInfo');

            // Save previous markup before updating
            let prevMarkup = btnWrap ? btnWrap.innerHTML : '';

            // Always show pager spinner while fetching (like pending-approval)
            if (btnWrap) btnWrap.innerHTML = `<span class="pager-spinner" aria-label="Loading" role="status"></span>`;
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            // Ensure a neutral page-info is visible during initial / loading state (parity with pending-approval)
            if (pageInfoEl && (!pageInfoEl.textContent || pageInfoEl.textContent === '\u00A0')) {
                pageInfoEl.textContent = 'Showing 0-0 of 0 entries';
            }

            if (!keepRows) {
                applyTableSkeleton(body, getImmunizationColsConfig(), pageSize);
            }
            try {
                const params = new URLSearchParams();
                params.set('page', page);
                params.set('limit', pageSize);
                // include current filter values for server-side filtering
                const dateSel = (document.getElementById('filterDate').value || '').trim();
                const statusSel = document.getElementById('filterStatus').value;
                const vaccineSel = document.getElementById('filterVaccine').value;
                const purokQ = (document.getElementById('filterPurok').value || '').trim();
                const nameQuery = (document.getElementById('filterName')?.value || '').trim();
                if (dateSel) params.set('date', dateSel);
                if (statusSel) params.set('status', statusSel);
                // Only send vaccine filter if a specific vaccine is selected (not empty)
                if (vaccineSel && vaccineSel !== '') params.set('vaccine', vaccineSel);
                if (purokQ) params.set('purok', purokQ);
                if (nameQuery) params.set('name', nameQuery);

                const res = await fetch(`php/supabase/bhw/get_immunization_view.php?${params.toString()}`);
                const data = await res.json();
                if (data.status !== 'success') {
                    body.innerHTML = '<tr class="message-row error"><td colspan="7">Failed to load data. Please try again.</td></tr>';
                    updatePagination(0, 0, pageSize);
                    return;
                }
                chrRecords = data.data || [];
                renderTable(chrRecords);
                populateVaccineDropdown();
                updatePagination(data.total, data.page || 1, data.limit || pageSize, data.has_more === true);
                currentPage = data.page || 1;
            } catch (e) {
                body.innerHTML = '<tr class="message-row error"><td colspan="7">Failed to load data. Please try again.</td></tr>';
                updatePagination(0, 0, 0);
            } finally {
                // Remove this line - updatePagination handles the button display
                // if (btnWrap && prevMarkup && !keepRows) btnWrap.innerHTML = prevMarkup;
            }
        }

        function openImmunizationForm(btn) {
            const recordId = btn.getAttribute('data-record-id') || '';
            const userId = btn.getAttribute('data-user-id') || '';
            const babyId = btn.getAttribute('data-baby-id') || '';
            const childName = btn.getAttribute('data-child-name') || '';
            const vaccineName = btn.getAttribute('data-vaccine-name') || '';
            const scheduleDate = btn.getAttribute('data-schedule-date') || '';
            const batchScheduleDate = btn.getAttribute('data-batch-date') || '';
            const catchUpDate = btn.getAttribute('data-catch-up-date') || '';

            // Get feeding status data
            const feedingData = {
                eb1mo: btn.getAttribute('data-eb-1mo') === 'true',
                eb2mo: btn.getAttribute('data-eb-2mo') === 'true',
                eb3mo: btn.getAttribute('data-eb-3mo') === 'true',
                eb4mo: btn.getAttribute('data-eb-4mo') === 'true',
                eb5mo: btn.getAttribute('data-eb-5mo') === 'true',
                eb6mo: btn.getAttribute('data-eb-6mo') === 'true',
                cf6mo: btn.getAttribute('data-cf-6mo') || '',
                cf7mo: btn.getAttribute('data-cf-7mo') || '',
                cf8mo: btn.getAttribute('data-cf-8mo') || '',
                tdDose1: btn.getAttribute('data-td-dose1') || '',
                tdDose2: btn.getAttribute('data-td-dose2') || '',
                tdDose3: btn.getAttribute('data-td-dose3') || '',
                tdDose4: btn.getAttribute('data-td-dose4') || '',
                tdDose5: btn.getAttribute('data-td-dose5') || ''
            };

            const dateToday = normalizeDateStr(new Date());

            // Function to determine feeding status based on vaccination month
            function getFeedingStatusForVaccine(vaccineName, scheduleDate) {
                const vaccineMonths = {
                    'BCG': 0,
                    'HEPAB1': 0,
                    'Pentavalent': [1, 2, 3],
                    'OPV': [1, 2, 3],
                    'PCV': [1, 2, 3],
                    'MCV1': 8,
                    'MCV2': 14,
                    'MMR': 11
                };

                let relevantMonth = null;
                if (vaccineName.includes('BCG') || vaccineName.includes('HEPAB1')) {
                    relevantMonth = 1;
                } else if (vaccineName.includes('Pentavalent') || vaccineName.includes('OPV') || vaccineName.includes('PCV')) {
                    if (vaccineName.includes('1st')) relevantMonth = 2;
                    else if (vaccineName.includes('2nd')) relevantMonth = 3;
                    else if (vaccineName.includes('3rd')) relevantMonth = 4;
                    else relevantMonth = 2;
                } else if (vaccineName.includes('MCV1')) {
                    relevantMonth = 6;
                } else if (vaccineName.includes('MCV2') || vaccineName.includes('MMR')) {
                    relevantMonth = 8;
                }

                if (!relevantMonth) return null;

                if (relevantMonth <= 6) {
                    const feedingKey = `eb${relevantMonth}mo`;
                    return {
                        type: 'exclusive_breastfeeding',
                        month: relevantMonth,
                        status: feedingData[feedingKey] ? '✓' : '✗',
                        text: `${relevantMonth}st month exclusive breastfeeding`
                    };
                } else if (relevantMonth >= 6 && relevantMonth <= 8) {
                    const feedingKey = `cf${relevantMonth}mo`;
                    const food = feedingData[feedingKey];
                    return {
                        type: 'complementary_feeding',
                        month: relevantMonth,
                        status: food ? food : 'Not recorded',
                        text: `${relevantMonth}th month complementary feeding`
                    };
                }

                return null;
            }

            function getMotherTDStatus() {
                const tdDoses = [{
                        dose: 1,
                        date: feedingData.tdDose1
                    },
                    {
                        dose: 2,
                        date: feedingData.tdDose2
                    },
                    {
                        dose: 3,
                        date: feedingData.tdDose3
                    },
                    {
                        dose: 4,
                        date: feedingData.tdDose4
                    },
                    {
                        dose: 5,
                        date: feedingData.tdDose5
                    }
                ];

                const completedDoses = tdDoses.filter(d => d.date && d.date !== '');
                const lastCompletedDose = completedDoses.length > 0 ? completedDoses[completedDoses.length - 1] : null;
                const nextDose = completedDoses.length < 5 ? tdDoses[completedDoses.length] : null;

                return {
                    completed: completedDoses.length,
                    lastDose: lastCompletedDose,
                    nextDose: nextDose,
                    allCompleted: completedDoses.length === 5
                };
            }

            // Helper to get correct ordinal suffix
            function ordinal(n) {
                const s = ["th", "st", "nd", "rd"],
                    v = n % 100;
                return s[(v - 20) % 10] || s[v] || s[0];
            }
            const feedingStatusRaw = getFeedingStatusForVaccine(vaccineName, scheduleDate);
            const feedingStatus = feedingStatusRaw ? {
                ...feedingStatusRaw,
                text: feedingStatusRaw.type === 'exclusive_breastfeeding' ?
                    `${feedingStatusRaw.month}${ordinal(feedingStatusRaw.month)} month exclusive breastfeeding` : `${feedingStatusRaw.month}${ordinal(feedingStatusRaw.month)} month complementary feeding`
            } : null;
            const motherTDStatus = getMotherTDStatus();

            const html = `
                        <div class="form-container">
                            <div class="form-group row-1">
                                <h4 class="im-group-title">Child Info. &amp; Vaccine Details</h4>
                                <label>
                                    Child Name:
                                    <input type="text" id="im_child_name" value="${childName}" readonly disabled />
                                </label>
                                <label>
                                    Vaccine:
                                    <input type="text" id="im_vaccine_name" value="${vaccineName}" readonly disabled />
                                </label>
                            </div>
                            <div class="form-group row-1 schedule-summary">
                                <div class="schedule-chip">
                                    <span class="chip-label">Guideline:</span>
                                    <strong>${scheduleDate || 'N/A'}</strong>
                                </div>
                                ${batchScheduleDate ? `
                                <div class="schedule-chip">
                                    <span class="chip-label">Batch:</span>
                                    <strong>${batchScheduleDate}</strong>
                                </div>` : ''}
                                ${catchUpDate ? `
                                <div class="schedule-chip">
                                    <span class="chip-label">Catch-up:</span>
                                    <strong>${catchUpDate}</strong>
                                </div>` : ''}
                                <input type="hidden" id="im_schedule_date" value="${scheduleDate}" />
                                <input type="hidden" id="im_catch_up_date" value="${catchUpDate || ''}" />
                            </div>
                            <div class="form-group row-2">
                                <input type="hidden" id="im_date_taken" value="${dateToday}" />
                            </div>
                        </div>

                            <div class="form-group row-3">
                                ${feedingStatus ? `
                                <div class="im-panel im-panel-feeding">
                                    <h4 class="im-panel-title">Update Feeding Status for ${vaccineName}</h4>
                                    <div class="im-panel-body">
                                        <div class="im-field-row">
                                            <span class="field-label">${feedingStatus.text}:</span>
                                            ${feedingStatus.type === 'exclusive_breastfeeding' ? `
                                                <label class="im-inline-checkbox">
                                                    <input type="checkbox" id="update_feeding_status" ${feedingStatus.status === '✓' ? 'checked' : ''}>
                                                    <span>Currently breastfeeding</span>
                                                </label>
                                            ` : `
                                                <input type="text" id="update_complementary_feeding" class="text-input" placeholder="Enter food given" 
                                                    value="${feedingStatus.status !== 'Not recorded' ? feedingStatus.status : ''}">
                                            `}
                                        </div>
                                    </div>
                                </div>` : ''}
                                <div class="im-panel im-panel-td">
                                    <h4 class="im-panel-title">Mother's TD (Tetanus-Diphtheria) Status</h4>
                                    <div class="im-panel-body">
                                        <div class="im-field-row">
                                            <span class="field-label">Completed Doses:</span>
                                            <span>${motherTDStatus.completed}/5</span>
                                        </div>
                                        ${motherTDStatus.lastDose ? `
                                        <div class="im-field-row">
                                            <span class="field-label">Last dose:</span>
                                            <span>${motherTDStatus.lastDose.date}</span>
                                        </div>` : ''}
                                        ${motherTDStatus.nextDose ? `
                                        <div class="im-field-row">
                                            <label for="update_td_dose" class="field-label">TD ${motherTDStatus.nextDose.dose} dose date:</label>
                                            <input type="date" id="update_td_dose" value="${motherTDStatus.nextDose.date}">
                                        </div>
                                        ` : `
                                        <div class="im-field-row">
                                            <span class="field-label">Status:</span>
                                            <span>✓ All TD doses completed</span>
                                        </div>
                                        `}
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" id="im_schedule_date" value="${scheduleDate}" />
                            <input type="hidden" id="im_date_taken" value="${dateToday}" />

                            <div class="form-group row-1">
                                <h4 class="im-group-title">Measurements</h4>
                                <label>
                                    Temperature (°C):
                                    <input type="number" step="0.1" id="im_temperature" placeholder="e.g. 36.8" />
                                </label>
                                <label>
                                    Height (cm):
                                    <input type="number" step="0.1" id="im_height" placeholder="e.g. 60" />
                                </label>
                                <label>
                                    Weight (kg):
                                    <input type="number" step="0.01" id="im_weight" placeholder="e.g. 6.5" />
                                </label>
                            </div>

                        <!-- Growth Assessment Section -->
                        <div class="form-group row-growth-assessment" id="growthAssessmentSection" style="display: none;">
                            <div class="growth-assessment-container">
                                <h4 class="growth-assessment-title">
                                    <span class="material-symbols-rounded">monitor_heart</span>
                                    Growth Assessment
                                </h4>
                                <div class="growth-assessment-content" id="growthAssessmentContent">
                                    <p class="growth-assessment-placeholder">Enter height and weight to see growth assessment</p>
                                </div>
                            </div>
                        </div>

                    <!-- Dose and Lot fields removed: dose is auto-determined from record, lot/site not in schema -->
                        <div class="form-group row-6">
                            <label>
                                Remarks (Optional)
                                <textarea id="im_remarks" rows="3" placeholder="Optional remarks"></textarea>
                            </label>
                        </div>
                    </div>

                            <div class="form-actions">
                                <button class="btn cancel-btn" onclick="closeImmunizationForm()">Cancel</button>
                                <button class="btn save-btn" onclick="submitImmunizationForm()">Confirm</button>
                            </div>
                        </div>

                        <input type="hidden" id="im_record_id" value="${recordId}" />
                        <input type="hidden" id="im_user_id" value="${userId}" />
                        <input type="hidden" id="im_baby_id" value="${babyId}" />
                    <input type="hidden" id="im_child_birth_date" value="" />
                    <input type="hidden" id="im_child_gender" value="" />
                `;

            document.getElementById('immunizationFormContainer').innerHTML = html;
            document.getElementById('immunizationOverlay').style.display = 'flex';

            // Fetch child details for growth assessment
            fetchChildDetailsForGrowth(babyId);

            // Add event listeners for height and weight inputs
            setupGrowthAssessmentListeners();
        }

        function closeImmunizationForm() {
            document.getElementById('immunizationOverlay').style.display = 'none';
            document.getElementById('immunizationFormContainer').innerHTML = '';
        }

        async function submitImmunizationForm() {
            const formData = new FormData();

            formData.append('record_id', document.getElementById('im_record_id').value || '');
            formData.append('user_id', document.getElementById('im_user_id').value || '');
            formData.append('baby_id', document.getElementById('im_baby_id').value || '');
            formData.append('vaccine_name', document.getElementById('im_vaccine_name').value || '');
            formData.append('schedule_date', document.getElementById('im_schedule_date').value || '');
            formData.append('date_taken', document.getElementById('im_date_taken').value || '');
            formData.append('temperature', document.getElementById('im_temperature').value || '');
            formData.append('height_cm', document.getElementById('im_height').value || '');
            formData.append('weight_kg', document.getElementById('im_weight').value || '');
            formData.append('remarks', document.getElementById('im_remarks').value || '');
            // Mark as completed automatically when saved
            formData.append('mark_completed', '1');

            const catchUpDate = document.getElementById('im_catch_up_date')?.value || '';
            if (catchUpDate) formData.append('catch_up_date', catchUpDate);

            // Add growth assessment data
            const growthWfa = document.getElementById('im_growth_wfa');
            const growthLfa = document.getElementById('im_growth_lfa');
            const growthWfl = document.getElementById('im_growth_wfl');
            const growthAgeMonths = document.getElementById('im_growth_age_months');

            if (growthWfa) formData.append('growth_wfa', growthWfa.value || '');
            if (growthLfa) formData.append('growth_lfa', growthLfa.value || '');
            if (growthWfl) formData.append('growth_wfl', growthWfl.value || '');
            if (growthAgeMonths) formData.append('growth_age_months', growthAgeMonths.value || '');

            // Add feeding status updates if available
            const feedingCheckbox = document.getElementById('update_feeding_status');
            const feedingInput = document.getElementById('update_complementary_feeding');
            if (feedingCheckbox) {
                formData.append('update_feeding_status', feedingCheckbox.checked ? '1' : '0');
            }
            if (feedingInput) {
                formData.append('update_complementary_feeding', feedingInput.value || '');
            }

            // Add Mother's TD Status update if available
            const tdDoseInput = document.getElementById('update_td_dose');
            if (tdDoseInput && tdDoseInput.value) {
                formData.append('update_td_dose_date', tdDoseInput.value);
            }

            // Show loading indicator
            const confirmBtn = document.querySelector('.save-btn');
            const originalBtnText = confirmBtn ? confirmBtn.textContent : '';
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Saving...';
            }

            try {
                const res = await fetch('php/supabase/bhw/save_immunization.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await res.text();
                console.log('Server response:', responseText); // Debug log
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseErr) {
                    console.error('JSON parse error:', parseErr, 'Response:', responseText); // Debug log
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = originalBtnText;
                    }
                    // Show error toast for invalid response
                    if (window.UIFeedback && window.UIFeedback.showToast) {
                        window.UIFeedback.showToast({
                            title: 'Error',
                            message: 'Invalid server response. Please try again or contact support.',
                            variant: 'error',
                            duration: 5000
                        });
                    } else {
                        alert('Invalid server response. Please check console for details.');
                    }
                    console.error('Full response:', responseText);
                    return;
                }

                if (data.status === 'success') {
                    closeImmunizationForm();
                    await fetchChildHealthRecord();
                    applyFilters();
                    
                    // Show success toast
                    if (window.UIFeedback && window.UIFeedback.showToast) {
                        window.UIFeedback.showToast({
                            title: 'Success',
                            message: 'Immunization recorded successfully',
                            variant: 'success',
                            duration: 3000
                        });
                    } else {
                        alert('Immunization saved successfully');
                    }
                } else {
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = originalBtnText;
                    }
                    
                    // Show error modal for validation errors
                    if (window.UIFeedback && window.UIFeedback.showModal) {
                        const errorMessage = data.message || 'Unknown error';
                        const isPrerequisiteError = errorMessage.includes('Missing prerequisite') || 
                                                   errorMessage.includes('Missed vaccines') ||
                                                   errorMessage.includes('Cannot record');
                        
                        if (isPrerequisiteError) {
                            // Extract missing vaccines from message - handle both formats
                            let missingVaccinesList = [];
                            
                            // Try to extract from "Missing or incomplete prerequisites: ..."
                            const missingMatch = errorMessage.match(/Missing or incomplete prerequisites[^:]+:\s*([^.]+)/i);
                            if (missingMatch) {
                                const vaccinesStr = missingMatch[1].trim();
                                // Split by comma and clean up each vaccine name
                                missingVaccinesList = vaccinesStr.split(',').map(v => {
                                    // Remove "(Status: ...)" part if present
                                    const cleaned = v.trim().replace(/\s*\(Status:\s*[^)]+\)/gi, '').trim();
                                    return cleaned;
                                }).filter(v => v.length > 0);
                            }
                            
                            // Try to extract from "Missed vaccines: ..."
                            const missedMatch = errorMessage.match(/Missed vaccines[^:]+:\s*([^.]+)/i);
                            if (missedMatch && missingVaccinesList.length === 0) {
                                const vaccinesStr = missedMatch[1].trim();
                                missingVaccinesList = vaccinesStr.split(',').map(v => v.trim()).filter(v => v.length > 0);
                            }
                            
                            // Try to extract from "Missing prerequisite vaccines: ..."
                            const prereqMatch = errorMessage.match(/Missing prerequisite vaccines[^:]+:\s*([^.]+)/i);
                            if (prereqMatch && missingVaccinesList.length === 0) {
                                const vaccinesStr = prereqMatch[1].trim();
                                missingVaccinesList = vaccinesStr.split(',').map(v => v.trim()).filter(v => v.length > 0);
                            }
                            
                            // Temporarily hide immunization form overlay so error modal appears on top
                            const immunizationOverlay = document.getElementById('immunizationOverlay');
                            const wasVisible = immunizationOverlay && (immunizationOverlay.style.display === 'flex' || window.getComputedStyle(immunizationOverlay).display === 'flex');
                            
                            // Hide the form overlay temporarily so error modal appears clearly
                            if (immunizationOverlay && wasVisible) {
                                immunizationOverlay.style.display = 'none';
                            }
                            
                            // Show detailed error modal
                            window.UIFeedback.showModal({
                                title: 'Cannot Record Vaccine',
                                message: 'The following prerequisite vaccines must be completed first:',
                                html: missingVaccinesList.length > 0 ? `<div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-top: 16px; border-left: 4px solid #ffc107;">
                                    <div style="display: flex; flex-direction: column; gap: 0;">
                                        ${missingVaccinesList.map((v, index) => `
                                            <div style="display: flex; align-items: center; gap: 12px; padding: 14px 12px; background: rgba(255,255,255,0.7); border-bottom: ${index < missingVaccinesList.length - 1 ? '1px solid rgba(212, 175, 55, 0.3)' : 'none'};">
                                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #ffc107; color: #856404; border-radius: 50%; font-weight: 700; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">${index + 1}</span>
                                                <span style="color: #856404; font-weight: 500; flex: 1; line-height: 1.5; font-size: 1.3rem;">${v}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <p style="margin-top: 18px; color: #6c757d; font-size: 0.95em; text-align: center;">
                                    Please record all previous vaccines in order before proceeding.
                                </p>` : `<p style="color: #856404; padding: 12px; background: #fff3cd; border-radius: 6px;">${errorMessage}</p>`,
                                icon: 'error',
                                confirmText: 'OK',
                                showCancel: false,
                                showConfirm: true,
                                onClose: () => {
                                    // Show the form overlay again when error modal closes
                                    if (immunizationOverlay && wasVisible) {
                                        immunizationOverlay.style.display = 'flex';
                                    }
                                }
                            });
                        } else {
                            // Show simple error toast
                            window.UIFeedback.showToast({
                                title: 'Error',
                                message: errorMessage,
                                variant: 'error',
                                duration: 5000
                            });
                        }
                    } else {
                        alert('Save failed: ' + (data.message || 'Unknown error'));
                    }
                }
            } catch (err) {
                console.error('Network error:', err); // Debug log
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = originalBtnText;
                }
                
                // Show network error toast
                if (window.UIFeedback && window.UIFeedback.showToast) {
                    window.UIFeedback.showToast({
                        title: 'Network Error',
                        message: 'Failed to save immunization. Please check your connection and try again.',
                        variant: 'error',
                        duration: 5000
                    });
                } else {
                    alert('Network error saving immunization: ' + err.message);
                }
            }
        }

        function renderTable(records) {
            const body = document.querySelector('#childhealthrecordBody');
            if (!records || records.length === 0) {
                body.innerHTML = '<tr class="message-row"><td colspan="7">No records found</td></tr>';
                return;
            }

            // Get current filter status to determine header and display logic
            const filterStatus = document.getElementById('filterStatus').value || '';
            const isMissedFilter = filterStatus.toLowerCase() === 'missed';

            // Update header dynamically
            const scheduleHeader = document.getElementById('scheduleDateHeader');
            if (scheduleHeader) {
                scheduleHeader.textContent = isMissedFilter ? 'Catch Up Date' : 'Schedule Date';
            }

            let rows = '';
            const formatDate = (dateStr) => {
                if (!dateStr) return '-';
                const date = new Date(dateStr);
                if (Number.isNaN(date.getTime())) return dateStr;
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            };

            records.forEach(item => {
                // Determine which date to show in Schedule Date column based on filter
                const scheduleDateToShow = isMissedFilter ?
                    (item.catch_up_date || '-') :
                    (item.schedule_date || '-');
                const formattedScheduleDate = scheduleDateToShow !== '-' ? formatDate(scheduleDateToShow) : '-';
                const formattedBatchDate = item.batch_schedule_date ? formatDate(item.batch_schedule_date) : '-';

                rows += `<tr>
                            <td hidden>${item.id || ''}</td>
                            <td hidden>${item.user_id || ''}</td>
                            <td hidden>${item.baby_id || ''}</td>
                            <td>${(item.child_fname || '') + ' ' + (item.child_lname || '')}</td>
                            <td>${item.address || ''}</td>
                            <td>${item.vaccine_name || ''}</td>
                            <td>${formattedScheduleDate}</td>
                            <td>${formattedBatchDate}</td>
                            <td>${statusChip(item.status, item.date_given)}</td>
                            <td>
                                <button class="btn view-btn" onclick="openImmunizationForm(this)"
                                    data-record-id="${item.immunization_id || ''}"
                                    data-user-id="${item.user_id || ''}"
                                    data-baby-id="${item.baby_id || ''}"
                                    data-child-name="${((item.child_fname || '') + ' ' + (item.child_lname || '')).replace(/"/g, '&quot;')}"
                                    data-vaccine-name="${String(item.vaccine_name || '').replace(/"/g, '&quot;')}"
                                    data-schedule-date="${item.schedule_date || ''}"
                                    data-batch-date="${item.batch_schedule_date || ''}"
                                    data-catch-up-date="${item.catch_up_date || ''}"
                                    data-eb-1mo="${item.exclusive_breastfeeding_1mo || false}"
                                    data-eb-2mo="${item.exclusive_breastfeeding_2mo || false}"
                                    data-eb-3mo="${item.exclusive_breastfeeding_3mo || false}"
                                    data-eb-4mo="${item.exclusive_breastfeeding_4mo || false}"
                                    data-eb-5mo="${item.exclusive_breastfeeding_5mo || false}"
                                    data-eb-6mo="${item.exclusive_breastfeeding_6mo || false}"
                                    data-cf-6mo="${(item.complementary_feeding_6mo || '').replace(/"/g, '&quot;')}"
                                    data-cf-7mo="${(item.complementary_feeding_7mo || '').replace(/"/g, '&quot;')}"
                                    data-cf-8mo="${(item.complementary_feeding_8mo || '').replace(/"/g, '&quot;')}"
                                    data-td-dose1="${item.mother_td_dose1_date || ''}"
                                    data-td-dose2="${item.mother_td_dose2_date || ''}"
                                    data-td-dose3="${item.mother_td_dose3_date || ''}"
                                    data-td-dose4="${item.mother_td_dose4_date || ''}"
                                    data-td-dose5="${item.mother_td_dose5_date || ''}">
                                    <span class="material-symbols-rounded">visibility</span>
                                    Record
                                </button>
                            </td>
                        </tr>`;
            });
            body.innerHTML = rows;
        }

        function statusChip(status, dateGiven) {
            const s = String(status || '').toLowerCase();
            if (s === 'taken') {
                return `<span class="chip chip--taken">${dateGiven ? `Taken (${dateGiven})` : 'Taken'}</span>`;
            }
            if (s === 'missed') {
                return `<span class="chip chip--missed">Missed</span>`;
            }
            if (s === 'upcoming' || s === 'scheduled') {
                return `<span class="chip chip--upcoming">Scheduled</span>`;
            }
            if (s === 'completed') {
                return `<span class="chip chip--completed">Completed</span>`;
            }
            return `<span class="chip chip--default">${status || '—'}</span>`;
        }

        async function viewChildInformation(baby_id) {
            formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('php/supabase/bhw/child_information.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.querySelector('#childName').textContent = data.data[0].child_fname + ' ' + data.data[0].child_lname;
                document.querySelector('#childGender').textContent = data.data[0].child_gender;
                document.querySelector('#childBirthDate').textContent = data.data[0].child_birth_date;
                document.querySelector('#childPlaceOfBirth').textContent = data.data[0].place_of_birth;
                document.querySelector('#childAddress').textContent = data.data[0].address;
                document.querySelector('#childWeight').textContent = data.data[0].birth_weight;
                document.querySelector('#childHeight').textContent = data.data[0].birth_height;
                document.querySelector('#childMother').textContent = data.data[0].mother_name;
                document.querySelector('#childFather').textContent = data.data[0].father_name;
                document.querySelector('#childBirthAttendant').textContent = data.data[0].birth_attendant;
                document.querySelector('#childImage').src = data.data[0].babys_card;
                document.querySelector('#acceptButton').addEventListener('click', () => {
                    acceptRecord(baby_id);
                });
                document.querySelector('.childinformation-container').style.display = 'flex';
                document.querySelector('.table-container').style.display = 'none';
            }


        }

        function closeChildInformation() {
            document.querySelector('.childinformation-container').style.display = 'none';
            document.querySelector('.table-container').style.display = 'block';
        }

        // Removed text search; filtering is driven by the filter controls only


        function populateVaccineDropdown() {
            const sel = document.getElementById('filterVaccine');
            if (!sel) return;
            const current = sel.value;

            // Fixed list of 8 vaccines
            const fixedVaccines = [
                'BCG',
                'Hepatitis B',
                'Pentavalent (DPT-HepB-Hib) - 1st',
                'OPV - 1st',
                'PCV - 1st',
                'MCV1 (AMV)',
                'MCV2 (MMR)',
                'IPV'
            ];

            // Build options with default prompt
            const options = [
                '<option value="" disabled selected>Vaccines</option>'
            ];

            // Add fixed vaccine list
            options.push(...fixedVaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));

            sel.innerHTML = options.join('');
            // If the current value exists in the fixed list, set it; otherwise, keep the default
            if (Array.from(sel.options).some(o => o.value === current)) {
                sel.value = current;
            } else {
                sel.value = '';
            }
        }

        function normalizeDateStr(d) {
            const pad = n => (n < 10 ? '0' : '') + n;
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        }

        function applyFilters() {
            fetchChildHealthRecord(1); // <-- FIXED
        }

        function clearFilters() {
            document.getElementById('filterDate').value = '';
            document.getElementById('filterStatus').value = 'upcoming';
            document.getElementById('filterVaccine').value = '';
            document.getElementById('filterPurok').value = '';
            const nameInput = document.getElementById('filterName');
            if (nameInput) nameInput.value = '';
            fetchChildHealthRecord(1); // <-- FIXED
        }

        function updatePagination(total, page, limit, hasMore = null) {
            const info = document.getElementById('pageInfo');
            const btnWrap = document.getElementById('pageButtons');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!info || !btnWrap || !prevBtn || !nextBtn) return;
            const count = chrRecords ? chrRecords.length : 0;
            // Always show a page-info string, even when zero (parity with pending-approval page)
            if (!total || total === 0 || count === 0) {
                info.textContent = 'Showing 0-0 of 0 entries';
            } else {
                const start = (page - 1) * limit + 1;
                const end = start + count - 1;
                const endClamped = Math.min(end, total);
                info.textContent = `Showing ${start}-${endClamped} of ${total} entries`;
            }
            btnWrap.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;
            prevBtn.disabled = page <= 1;
            const canNext = hasMore === true || (chrRecords && chrRecords.length === limit);
            nextBtn.disabled = !canNext;
            prevBtn.onclick = () => {
                if (page > 1) fetchChildHealthRecord(page - 1, {
                    keep: true
                });
            };
            nextBtn.onclick = () => {
                if (canNext) fetchChildHealthRecord(page + 1, {
                    keep: true
                });
            };
        }

        function viewChrImage(urlEnc) {
            const url = decodeURIComponent(urlEnc);
            document.querySelector('#overlayImage').src = url;
            document.querySelector('#openInNewTab').href = url;
            document.querySelector('#imageOverlay').style.display = 'flex';
        }

        function hideOverlay() {
            document.querySelector('#imageOverlay').style.display = 'none';
            document.querySelector('#overlayImage').src = '';
        }

        function closeOverlay(e) {
            if (e.target && e.target.id === 'imageOverlay') {
                hideOverlay();
            }
        }

        async function acceptRecord(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            // const response = await fetch('../../php/bhw/accept_chr.php', { method: 'POST', body: formData });
            const response = await fetch('php/supabase/bhw/accept_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                fetchChildHealthRecord();
            } else {
                alert('Record not accepted: ' + data.message);
            }
            closeChildInformation();
        }

        async function rejectRecord(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('php/mysql/bhw/reject_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                fetchChildHealthRecord();
            } else {
                alert('Record not rejected: ' + data.message);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            // Read URL query parameters and set filters
            const urlParams = new URLSearchParams(window.location.search);
            const dateParam = urlParams.get('date');
            const statusParam = urlParams.get('status');
            
            const dateInput = document.getElementById('filterDate');
            if (dateInput) {
                dateInput.value = dateParam || '';
            }
            
            const statusSelEl = document.getElementById('filterStatus');
            if (statusSelEl) {
                statusSelEl.value = statusParam || 'upcoming';
            }
            
            // Load page 1 with filters applied
            fetchChildHealthRecord(1);
        });
        window.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyFiltersBtn');
            const clearBtn = document.getElementById('clearFiltersBtn');
            if (applyBtn) applyBtn.addEventListener('click', applyFilters);
            if (clearBtn) clearBtn.addEventListener('click', clearFilters);
            const nameInput = document.getElementById('filterName');
            if (nameInput) {
                nameInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applyFilters();
                    }
                });
            }

            // Make the custom calendar icon open the date picker (supports both .select-with-icon and .input-field wrappers)
            const dateInput = document.getElementById('filterDate');
            let dateIcon = null;
            if (dateInput) {
                const wrapper = dateInput.closest('.select-with-icon, .input-field');
                dateIcon = wrapper ? wrapper.querySelector('.material-symbols-rounded') : null;
            }
            if (dateInput && dateIcon) {
                dateIcon.style.cursor = 'pointer';
                dateIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof dateInput.showPicker === 'function') {
                        try {
                            dateInput.showPicker();
                        } catch (err) {
                            dateInput.focus();
                        }
                    } else {
                        dateInput.focus();
                        try {
                            dateInput.click();
                        } catch (err) {}
                    }
                });
            }

            // Add event listener for close scanner button
            const closeScannerBtn = document.getElementById('closeScannerBtn');
            if (closeScannerBtn) {
                closeScannerBtn.addEventListener('click', closeScanner);
            }
        });

        // QR Scanner functions
        let html5QrcodeInstance = null;

        async function openScanner() {
            const overlay = document.getElementById('qrOverlay');
            if (overlay) overlay.style.display = 'flex';

            try {
                const devices = await Html5Qrcode.getCameras();
                const camSel = document.getElementById('cameraSelect');
                if (camSel) camSel.innerHTML = '';

                if (devices && devices.length > 0) {
                    const field = document.getElementById('cameraField');
                    if (field) field.style.display = 'block';
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

        async function onScanSuccess(decodedText) {
            closeScanner();

            // Extract baby_id from QR code
            const match = decodedText.match(/baby_id=([^&\s]+)/i);
            const baby_id = match ? decodeURIComponent(match[1]) : decodedText;

            if (!baby_id) {
                alert('Invalid QR code format');
                return;
            }

            try {
                // Fetch immunization records for this baby
                const response = await fetch(`php/supabase/bhw/get_immunization_records.php?baby_id=${encodeURIComponent(baby_id)}`);
                const data = await response.json();

                if (data.status !== 'success' || !data.data || data.data.length === 0) {
                    alert('No immunization records found for this baby');
                    return;
                }

                // Filter for upcoming/scheduled records
                const today = new Date().toISOString().split('T')[0];
                const upcomingRecords = data.data.filter(record => {
                    const status = record.status?.toLowerCase();
                    const scheduleDate = record.schedule_date || record.catch_up_date;
                    return (status === 'scheduled' || status === 'upcoming') && scheduleDate >= today;
                });

                if (upcomingRecords.length === 0) {
                    alert('No upcoming immunizations found for this baby');
                    return;
                }

                // Find the nearest upcoming record (closest future date)
                let nearestRecord = null;
                let nearestDate = null;

                upcomingRecords.forEach(record => {
                    const scheduleDate = record.schedule_date || record.catch_up_date;
                    if (!nearestDate || scheduleDate < nearestDate) {
                        nearestDate = scheduleDate;
                        nearestRecord = record;
                    }
                });

                if (!nearestRecord) {
                    alert('Could not find nearest upcoming immunization');
                    return;
                }

                // Fetch child details to get full information for the form
                const formData = new FormData();
                formData.append('baby_id', baby_id);
                const childResponse = await fetch('php/supabase/bhw/get_child_details.php', {
                    method: 'POST',
                    body: formData
                });
                const childData = await childResponse.json();

                if (childData.status === 'success' && childData.data && childData.data.length > 0) {
                    const child = childData.data[0];

                    // Use the user_id from child record, fallback to empty string if not available
                    const userId = child.user_id || '';

                    // Call openImmunizationForm with the nearest record data
                    openImmunizationFormForScan(
                        nearestRecord.id,
                        userId,
                        nearestRecord.baby_id,
                        `${child.child_fname || ''} ${child.child_lname || ''}`.trim(),
                        nearestRecord.vaccine_name,
                        nearestRecord.schedule_date || '',
                        nearestRecord.catch_up_date || '',
                        nearestRecord.batch_schedule_date || ''
                    );
                } else {
                    console.error('Child details fetch failed:', childData);
                    alert('Could not fetch child details: ' + (childData.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error processing QR scan:', error);
                alert('Error processing QR code: ' + error.message);
            }
        }

        function onScanFailure(err) {
            // Silent failure, scanner will keep trying
        }

        // Modified function to handle QR scan auto-open
        function openImmunizationFormForScan(recordId, userId, babyId, childName, vaccineName, scheduleDate, catchUpDate, childData, batchScheduleDate = '') {
            // Create a temporary button element with all the necessary data attributes
            const tempBtn = document.createElement('button');
            tempBtn.setAttribute('data-record-id', recordId);
            tempBtn.setAttribute('data-user-id', userId);
            tempBtn.setAttribute('data-baby-id', babyId);
            tempBtn.setAttribute('data-child-name', childName);
            tempBtn.setAttribute('data-vaccine-name', vaccineName);
            tempBtn.setAttribute('data-schedule-date', scheduleDate);
            tempBtn.setAttribute('data-batch-date', batchScheduleDate);
            tempBtn.setAttribute('data-catch-up-date', catchUpDate);

            // Add feeding data attributes
            tempBtn.setAttribute('data-eb-1mo', childData.exclusive_breastfeeding_1mo || 'false');
            tempBtn.setAttribute('data-eb-2mo', childData.exclusive_breastfeeding_2mo || 'false');
            tempBtn.setAttribute('data-eb-3mo', childData.exclusive_breastfeeding_3mo || 'false');
            tempBtn.setAttribute('data-eb-4mo', childData.exclusive_breastfeeding_4mo || 'false');
            tempBtn.setAttribute('data-eb-5mo', childData.exclusive_breastfeeding_5mo || 'false');
            tempBtn.setAttribute('data-eb-6mo', childData.exclusive_breastfeeding_6mo || 'false');
            tempBtn.setAttribute('data-cf-6mo', childData.complementary_feeding_6mo || '');
            tempBtn.setAttribute('data-cf-7mo', childData.complementary_feeding_7mo || '');
            tempBtn.setAttribute('data-cf-8mo', childData.complementary_feeding_8mo || '');
            tempBtn.setAttribute('data-td-dose1', childData.mother_td_dose1_date || '');
            tempBtn.setAttribute('data-td-dose2', childData.mother_td_dose2_date || '');
            tempBtn.setAttribute('data-td-dose3', childData.mother_td_dose3_date || '');
            tempBtn.setAttribute('data-td-dose4', childData.mother_td_dose4_date || '');
            tempBtn.setAttribute('data-td-dose5', childData.mother_td_dose5_date || '');

            // Call the existing openImmunizationForm function
            openImmunizationForm(tempBtn);
        }

        /**
         * Fetch child details for growth assessment
         */
        async function fetchChildDetailsForGrowth(babyId) {
            if (!babyId) return;

            try {
                const formData = new FormData();
                formData.append('baby_id', babyId);
                const response = await fetch('php/supabase/bhw/get_child_details.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success' && data.data && data.data.length > 0) {
                    const child = data.data[0];
                    const birthDateInput = document.getElementById('im_child_birth_date');
                    const genderInput = document.getElementById('im_child_gender');

                    if (birthDateInput) birthDateInput.value = child.child_birth_date || '';
                    if (genderInput) genderInput.value = child.child_gender || '';
                }
            } catch (error) {
                console.error('Error fetching child details for growth assessment:', error);
            }
        }

        /**
         * Setup event listeners for growth assessment
         */
        function setupGrowthAssessmentListeners() {
            const heightInput = document.getElementById('im_height');
            const weightInput = document.getElementById('im_weight');

            if (heightInput && weightInput) {
                // Debounce function to avoid too many calculations
                let assessmentTimeout;
                const calculateGrowth = () => {
                    clearTimeout(assessmentTimeout);
                    assessmentTimeout = setTimeout(() => {
                        performGrowthAssessment();
                    }, 500); // Wait 500ms after user stops typing
                };

                heightInput.addEventListener('input', calculateGrowth);
                weightInput.addEventListener('input', calculateGrowth);
            }
        }

        /**
         * Perform growth assessment
         */
        async function performGrowthAssessment() {
            const birthDate = document.getElementById('im_child_birth_date')?.value;
            const gender = document.getElementById('im_child_gender')?.value;
            const weight = parseFloat(document.getElementById('im_weight')?.value);
            const height = parseFloat(document.getElementById('im_height')?.value);
            const assessmentSection = document.getElementById('growthAssessmentSection');
            const assessmentContent = document.getElementById('growthAssessmentContent');

            if (!birthDate || !gender) {
                // Child details not loaded yet
                if (assessmentSection) assessmentSection.style.display = 'none';
                return;
            }

            if (!weight && !height) {
                if (assessmentSection) assessmentSection.style.display = 'none';
                return;
            }

            try {
                await growthCalculator.loadStandards();
                const assessment = await growthCalculator.assessGrowth(birthDate, gender, weight, height);

                if (assessment.error) {
                    if (assessmentContent) {
                        assessmentContent.innerHTML = `<p class="growth-assessment-error">${assessment.error}</p>`;
                    }
                    if (assessmentSection) assessmentSection.style.display = 'block';
                    return;
                }

                // Display assessment results
                let html = '<div class="growth-indicators">';

                if (assessment.ageMonths !== null && assessment.ageMonths !== undefined) {
                    html += `<div class="growth-info"><strong>Age:</strong> ${assessment.ageMonths} months</div>`;
                }

                // Weight-for-Age
                if (assessment.weightForAge) {
                    const wfa = assessment.weightForAge;
                    html += `
                        <div class="growth-indicator">
                            <div class="indicator-label">Weight-for-Age:</div>
                            <div class="indicator-value ${wfa.color}">
                                <span class="indicator-icon">${wfa.icon}</span>
                                <span class="indicator-text">${wfa.label}</span>
                            </div>
                        </div>
                    `;
                }

                // Length-for-Age (only for 0-11 months)
                if (assessment.lengthForAge) {
                    const lfa = assessment.lengthForAge;
                    html += `
                        <div class="growth-indicator">
                            <div class="indicator-label">Length-for-Age:</div>
                            <div class="indicator-value ${lfa.color}">
                                <span class="indicator-icon">${lfa.icon}</span>
                                <span class="indicator-text">${lfa.label}</span>
                            </div>
                        </div>
                    `;
                }

                // Weight-for-Length
                if (assessment.weightForLength) {
                    const wfl = assessment.weightForLength;
                    html += `
                        <div class="growth-indicator">
                            <div class="indicator-label">Weight-for-Length:</div>
                            <div class="indicator-value ${wfl.color}">
                                <span class="indicator-icon">${wfl.icon}</span>
                                <span class="indicator-text">${wfl.label}</span>
                            </div>
                        </div>
                    `;
                }

                html += '</div>';

                if (assessmentContent) {
                    assessmentContent.innerHTML = html;
                }
                if (assessmentSection) {
                    assessmentSection.style.display = 'block';
                }

                // Store assessment data in hidden fields for saving
                storeGrowthAssessmentData(assessment);

            } catch (error) {
                console.error('Error performing growth assessment:', error);
                if (assessmentContent) {
                    assessmentContent.innerHTML = '<p class="growth-assessment-error">Error calculating growth assessment</p>';
                }
                if (assessmentSection) assessmentSection.style.display = 'block';
            }
        }

        /**
         * Store growth assessment data in hidden fields
         */
        function storeGrowthAssessmentData(assessment) {
            // Create or update hidden fields for growth assessment
            let container = document.getElementById('immunizationFormContainer');
            if (!container) return;

            // Remove existing growth assessment hidden fields
            const existingFields = container.querySelectorAll('[id^="im_growth_"]');
            existingFields.forEach(field => field.remove());

            // Add new hidden fields
            if (assessment.weightForAge) {
                const wfaField = document.createElement('input');
                wfaField.type = 'hidden';
                wfaField.id = 'im_growth_wfa';
                wfaField.value = assessment.weightForAge.status;
                container.appendChild(wfaField);
            }

            if (assessment.lengthForAge) {
                const lfaField = document.createElement('input');
                lfaField.type = 'hidden';
                lfaField.id = 'im_growth_lfa';
                lfaField.value = assessment.lengthForAge.status;
                container.appendChild(lfaField);
            }

            if (assessment.weightForLength) {
                const wflField = document.createElement('input');
                wflField.type = 'hidden';
                wflField.id = 'im_growth_wfl';
                wflField.value = assessment.weightForLength.status;
                container.appendChild(wflField);
            }

            // Store age in months
            if (assessment.ageMonths !== null && assessment.ageMonths !== undefined) {
                const ageField = document.createElement('input');
                ageField.type = 'hidden';
                ageField.id = 'im_growth_age_months';
                ageField.value = assessment.ageMonths;
                container.appendChild(ageField);
            }
        }
    </script>

</body>

</html>