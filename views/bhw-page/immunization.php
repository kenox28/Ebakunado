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
    <title>Immunization</title>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>BHW Dashboard</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
        <link rel="stylesheet" href="../../css/main.css" />
        <link rel="stylesheet" href="../../css/header.css" />
        <link rel="stylesheet" href="../../css/sidebar.css" />
        <link rel="stylesheet" href="../../css/bhw/immunization-style.css">
    </head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="section-title">
                <span class="material-symbols-rounded">syringe</span>
                Immunization Records
            </h2>
        </section>
        <section class="immunization-section">
            <div class="filters-header">
                <span class="material-symbols-rounded" aria-hidden="true">tune</span>
                <span>Filters:</span>
            </div>
            <div class="filters">
                <div class="select-with-icon">
                    <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
                    <input id="filterDate" type="date" />
                </div>

                <div class="select-with-icon">
                    <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                    <select id="filterStatus">
                        <option value="" disabled selected>Status</option>
                        <option value="all">All</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="missed">Missed</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="select-with-icon">
                    <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                    <select id="filterVaccine">
                        <option value="" disabled selected>Vaccines</option>
                        <option value="all">All</option>
                    </select>
                </div>

                <div class="select-with-icon">
                    <span class="material-symbols-rounded" aria-hidden="true">location_on</span>
                    <input id="filterPurok" type="text" placeholder="e.g. Purok 1" />
                </div>

                <button class="btn btn-primary" id="applyFiltersBtn">Apply</button>
                <button class="btn btn-secondary" id="clearFiltersBtn">Clear</button>
                <button class="btn btn-outline-primary" id="openScannerBtn" onclick="openScanner()">
                    <span class="material-symbols-rounded" aria-hidden="true">qr_code_scanner</span>
                    Scan QR
                </button>
            </div>

            <div class="table-container">
                <table class="table table-hover" id="childhealthrecord">
                    <thead>
                        <tr>
                            <th>Fullname</th>
                            <th>Address</th>
                            <th>Vaccine</th>
                            <th>Schedule Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="childhealthrecordBody">
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading records...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

            <!-- Immunization Record Overlay -->
            <div class="immunization-record" id="immunizationOverlay">
                <div class="immunization-modal">
                    <div class="immunization-header">
                        <h3 class="immunization-title">
                            <span class="material-symbols-rounded">assignment</span>
                            Record Immunization
                        </h3>
                        <button class="close-btn" onclick="closeImmunizationForm()">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="immunization-form" id="immunizationFormContainer"></div>
                </div>
            </div>

        </section>
    </main>

    <!-- QR Scanner Modal -->
    <div id="qrOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 20px; border-radius: 12px; max-width: 600px; width: 90%; text-align: center;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Scan Baby QR Code for Immunization</h3>
                <button id="closeScannerBtn" style="background: #dc3545; color: white; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; font-size: 20px;">×</button>
            </div>
            <select id="cameraSelect" style="margin-bottom: 15px; padding: 8px; width: 100%; border-radius: 5px; display: none;"></select>
            <div id="qrReader" style="width: 100%; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px;"></div>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">Point the camera at the QR code</p>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        // spinner CSS (scoped)
        const style = document.createElement('style');
        style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}`;
        document.head.appendChild(style);
        let chrRecords = [];
        let currentPage = 1;
        const pageSize = 10; // fixed to 10 per request

        async function fetchChildHealthRecord(page = 1, opts = {}) {
            const body = document.querySelector('#childhealthrecordBody');
            const keepRows = opts.keep === true;
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const btnWrap = document.getElementById('pageButtons');

            // Save previous markup before updating
            let prevMarkup = btnWrap ? btnWrap.innerHTML : '';

            // Always show pager spinner while fetching (like pending-approval)
            if (btnWrap) btnWrap.innerHTML = `<span class="pager-spinner" aria-label="Loading" role="status"></span>`;
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;

            if (!keepRows) {
                body.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
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
                if (dateSel) params.set('date', dateSel);
                if (statusSel) params.set('status', statusSel);
                if (vaccineSel) params.set('vaccine', vaccineSel);
                if (purokQ) params.set('purok', purokQ);

                const res = await fetch(`../../php/supabase/bhw/get_immunization_view.php?${params.toString()}`);
                const data = await res.json();
                if (data.status !== 'success') {
                    body.innerHTML = '<tr><td colspan="6">Failed to load records</td></tr>';
                    updatePagination(0, 0, 0);
                    return;
                }
                chrRecords = data.data || [];
                renderTable(chrRecords);
                populateVaccineDropdown();
                updatePagination(data.total, data.page || 1, data.limit || pageSize, data.has_more === true);
                currentPage = data.page || 1;
            } catch (e) {
                body.innerHTML = '<tr><td colspan="6">Error loading records</td></tr>';
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

            const feedingStatus = getFeedingStatusForVaccine(vaccineName, scheduleDate);
            const motherTDStatus = getMotherTDStatus();

            const html = `
                        <div class="form-container">
                            <div class="form-group row-1">
                                <label>
                                    Child Name:
                                    <input type="text" id="im_child_name" value="${childName}" readonly disabled />
                                </label>
                                <label>
                                    Vaccine:
                                    <input type="text" id="im_vaccine_name" value="${vaccineName}" readonly disabled />
                                </label>
                            </div>
                            <div class="form-group row-2">
                                <input  type="hidden" id="im_schedule_date" value="${scheduleDate}" readonly />
                                ${catchUpDate ? `
                                <label>
                                    Catch-up Date:
                                    <input type="date" id="im_catch_up_date" value="${catchUpDate}" readonly disabled />
                                </label>
                            </div>` : ''}
                            <div class="form-group row-3">
                                <input type="hidden" id="im_date_taken" value="${dateToday}" />
                            </div>
                        </div>

                    ${feedingStatus ? `
                    <div class="form-group row-4">
                        <h4>Update Feeding Status for ${vaccineName}</h4>
                        <div>
                            <span>${feedingStatus.text}:</span>
                            ${feedingStatus.type === 'exclusive_breastfeeding' ? `
                                <label>
                                    <input type="checkbox" id="update_feeding_status" ${feedingStatus.status === '✓' ? 'checked' : ''}>
                                    <span>Currently breastfeeding</span>
                                </label>
                            ` : `
                                <input type="text" id="update_complementary_feeding" placeholder="Enter food given" 
                                    value="${feedingStatus.status !== 'Not recorded' ? feedingStatus.status : ''}">
                            `}
                        </div>
                    </div>` : ''}

                    <div class="form-group row-5">
                        <h4>Mother's TD (Tetanus-Diphtheria) Status</h4>
                        <div>
                            <span>Completed Doses: ${motherTDStatus.completed}/5</span>
                            ${motherTDStatus.lastDose ? `<span>Last dose: ${motherTDStatus.lastDose.date}</span>` : ''}
                        </div>
                        ${motherTDStatus.nextDose ? `
                        <div>
                            <span>TD ${motherTDStatus.nextDose.dose} dose date:</span>
                            <input type="date" id="update_td_dose" value="${motherTDStatus.nextDose.date}">
                        </div>
                        ` : `
                        <div>✓ All TD doses completed</div>
                        `}
                    </div>
                        <div class="form-group row-6">
                            <label>
                                Temperature (°C)
                                <input type="number" step="0.1" id="im_temperature" placeholder="e.g. 36.8" />
                            </label>
                            <label>
                                Height (cm)
                                <input type="number" step="0.1" id="im_height" placeholder="e.g. 60" />
                            </label>
                            <label>
                                Weight (kg)
                                <input type="number" step="0.01" id="im_weight" placeholder="e.g. 6.5" />
                            </label>
                        </div>

                    <!-- Dose and Lot fields removed: dose is auto-determined from record, lot/site not in schema -->
                        <div class="form-group row-7">
                            <label>
                                Administered By
                                <input type="text" id="im_administered_by" placeholder="Name" />
                            </label>
                            <label>
                                Remarks
                                <textarea id="im_remarks" rows="3"></textarea>
                            </label>
                            <label for="im_mark_completed">
                                <input type="checkbox" id="im_mark_completed" />
                                Mark as Taken
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn cancel-btn" onclick="closeImmunizationForm()">Cancel</button>
                        <button class="btn save-btn" onclick="submitImmunizationForm()">Save</button>
                    </div>

                    <input type="hidden" id="im_record_id" value="${recordId}" />
                    <input type="hidden" id="im_user_id" value="${userId}" />
                    <input type="hidden" id="im_baby_id" value="${babyId}" />
                `;

            document.getElementById('immunizationFormContainer').innerHTML = html;
            document.getElementById('immunizationOverlay').style.display = 'flex';
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
            // dose_number, lot_number, site removed - dose inferred from existing record
            formData.append('administered_by', document.getElementById('im_administered_by').value || '');
            formData.append('remarks', document.getElementById('im_remarks').value || '');
            formData.append('mark_completed', document.getElementById('im_mark_completed').checked ? '1' : '0');
            const cu = document.getElementById('im_catch_up_date');
            if (cu && cu.value) formData.append('catch_up_date', cu.value);

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

            try {
                const res = await fetch('../../php/supabase/bhw/save_immunization.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json().catch(() => ({
                    status: 'error',
                    message: 'Invalid server response'
                }));
                if (data.status === 'success') {
                    closeImmunizationForm();
                    await fetchChildHealthRecord();
                    applyFilters();
                    alert('Immunization saved successfully');
                } else {
                    alert('Save failed: ' + (data.message || 'Unknown error'));
                }
            } catch (err) {
                alert('Network error saving immunization');
                console.error('save_immunization error:', err);
            }
        }

        function renderTable(records) {
            const body = document.querySelector('#childhealthrecordBody');
            if (!records || records.length === 0) {
                body.innerHTML = '<tr><td colspan="6">No records</td></tr>';
                return;
            }
            let rows = '';
            records.forEach(item => {
                rows += `<tr>
                            <td hidden>${item.id || ''}</td>
                            <td hidden>${item.user_id || ''}</td>
                            <td hidden>${item.baby_id || ''}</td>
                            <td>${(item.child_fname || '') + ' ' + (item.child_lname || '')}</td>
                            <td>${item.address || ''}</td>
                            <td>${item.vaccine_name || ''}</td>
                            <td>${item.schedule_date || ''}</td>
                            <td>${statusChip(item.status, item.date_given)}</td>
                            <td>
                                <button class="btn view-btn" onclick="openImmunizationForm(this)"
                                    data-record-id="${item.immunization_id || ''}"
                                    data-user-id="${item.user_id || ''}"
                                    data-baby-id="${item.baby_id || ''}"
                                    data-child-name="${((item.child_fname || '') + ' ' + (item.child_lname || '')).replace(/"/g, '&quot;')}"
                                    data-vaccine-name="${String(item.vaccine_name || '').replace(/"/g, '&quot;')}"
                                    data-schedule-date="${item.schedule_date || ''}"
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
                return `<span class="chip chip--upcoming">Upcoming</span>`;
            }
            if (s === 'completed') {
                return `<span class="chip chip--completed">Completed</span>`;
            }
            return `<span class="chip chip--default">${status || '—'}</span>`;
        }

        async function viewChildInformation(baby_id) {
            formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('../../php/supabase/bhw/child_information.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {

                console.log(data.data);
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
            } else {
                console.log(data.message);
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

            // Always start with the default prompt
            const options = [
                '<option value="" disabled selected>Vaccines</option>',
                '<option value="all">All</option>'
            ];

            // Get unique vaccines from the loaded data
            const vaccines = [...new Set(chrRecords.map(item => item.vaccine_name).filter(v => v))].sort();
            options.push(...vaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));

            sel.innerHTML = options.join('');
            // If the current value exists, set it; otherwise, keep the default
            if (Array.from(sel.options).some(o => o.value === current)) sel.value = current;
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
            document.getElementById('filterVaccine').value = 'all';
            document.getElementById('filterPurok').value = '';
            fetchChildHealthRecord(1); // <-- FIXED
        }

        function updatePagination(total, page, limit, hasMore = null) {
            const info = document.getElementById('pageInfo');
            const btnWrap = document.getElementById('pageButtons');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!info || !btnWrap || !prevBtn || !nextBtn) return;
            const start = (page - 1) * limit + 1;
            const end = start + (chrRecords?.length || 0) - 1;
            const endClamped = Math.min(end, total || end);
            info.textContent = chrRecords && chrRecords.length ? `Showing ${start}-${endClamped} of ${total || 0} entries` : '';
            btnWrap.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;
            prevBtn.disabled = page <= 1;
            const canNext = hasMore === true || (chrRecords && chrRecords.length === limit);
            nextBtn.disabled = !canNext;
            prevBtn.onclick = () => {
                if (page > 1) fetchChildHealthRecord(page - 1, { keep: true });
            };
            nextBtn.onclick = () => {
                if (canNext) fetchChildHealthRecord(page + 1, { keep: true });
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
            const response = await fetch('../../php/supabase/bhw/accept_chr.php', {
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
            const response = await fetch('../../php/mysql/bhw/reject_chr.php', {
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
            // set defaults like before then load page 1
            const dateInput = document.getElementById('filterDate');
            if (dateInput) dateInput.value = '';
            const statusSelEl = document.getElementById('filterStatus');
            if (statusSelEl) statusSelEl.value = 'upcoming';
            fetchChildHealthRecord(1); // <-- FIXED
        });
        window.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyFiltersBtn');
            const clearBtn = document.getElementById('clearFiltersBtn');
            if (applyBtn) applyBtn.addEventListener('click', applyFilters);
            if (clearBtn) clearBtn.addEventListener('click', clearFilters);

            // Make the custom calendar icon open the date picker
            const dateInput = document.getElementById('filterDate');
            const dateIcon = dateInput?.closest('.select-with-icon')?.querySelector('.material-symbols-rounded');
            if (dateInput && dateIcon) {
                dateIcon.style.cursor = 'pointer';
                dateIcon.addEventListener('click', () => {
                    if (typeof dateInput.showPicker === 'function') dateInput.showPicker();
                    dateInput.focus();
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
            } catch (e) {
            }
        }

        async function onScanSuccess(decodedText) {
            console.log('QR Scan success:', decodedText);
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
                const response = await fetch(`../../php/supabase/bhw/get_immunization_records.php?baby_id=${encodeURIComponent(baby_id)}`);
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
                 const childResponse = await fetch('../../php/supabase/bhw/get_child_details.php', {
                     method: 'POST',
                     body: formData
                 });
                 const childData = await childResponse.json();

                 console.log('Child details response:', childData);
                 console.log('Baby ID searched:', baby_id);

                 if (childData.status === 'success' && childData.data && childData.data.length > 0) {
                     const child = childData.data[0];
                     
                     console.log('Child data received:', child);
                     
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
                         child
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
        function openImmunizationFormForScan(recordId, userId, babyId, childName, vaccineName, scheduleDate, catchUpDate, childData) {
            // Create a temporary button element with all the necessary data attributes
            const tempBtn = document.createElement('button');
            tempBtn.setAttribute('data-record-id', recordId);
            tempBtn.setAttribute('data-user-id', userId);
            tempBtn.setAttribute('data-baby-id', babyId);
            tempBtn.setAttribute('data-child-name', childName);
            tempBtn.setAttribute('data-vaccine-name', vaccineName);
            tempBtn.setAttribute('data-schedule-date', scheduleDate);
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
        
    </script>

</body>

</html>