<?php session_start(); ?>
<?php 
// Handle both BHW and Midwife sessions (but BHW should only see BHW features)
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type']; // Default to bhw for BHW pages
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = $_SESSION['fname'] ." ". $_SESSION['lname'];
if($user_types != 'midwifes') {   
    $user_type = 'Barangay Health Worker';
}else{
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
        <link rel="stylesheet" href="../../css/main.css?v=1.0.1" />
        <link rel="stylesheet" href="../../css/header.css?v=1.0.1" />
        <link rel="stylesheet" href="../../css/sidebar.css?v=1.0.1" />
        <link rel="stylesheet" href="../../css/bhw/immunization-style.css?v=1.0.1">
        <link rel="stylesheet" href="../../css/bhw/queries.css?v=1.0.1">
    </head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="immunization-section">
            <div class="filters">
                <label>Date:
                    <input id="filterDate" type="date">
                </label>
                <label>Status:
                    <select id="filterStatus">
                        <option value="all">All</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="missed">Missed</option>
                        <option value="completed">Completed</option>
                    </select>
                </label>
                <label>Vaccine:
                    <select id="filterVaccine">
                        <option value="all">All</option>
                    </select>
                </label>
                <label>Purok:
                    <input id="filterPurok" type="text" placeholder="e.g. Purok 1">
                </label>
                <button id="applyFiltersBtn">Apply</button>
                <button id="clearFiltersBtn">Clear</button>
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
                            <td colspan="21" class="text-center">
                                <div class="loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading records...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            

            <!-- Immunization Record Overlay -->
            <div class="immunization-record" id="immunizationOverlay">
                <div class="immunization-modal">
                    <div class="immunization-header">
                        <h3 class="immunization-title">Record Immunization</h3>
                        <button onclick="closeImmunizationForm()">Close</button>
                    </div>
                    <div id="immunizationFormContainer"></div>
                </div>
            </div>

        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        let chrRecords = [];

        async function getChildHealthRecord() {
            const body = document.querySelector('#childhealthrecordBody');
            body.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
            try {
                const res = await fetch('../../php/supabase/bhw/get_immunization_view.php');
                const data = await res.json();
                if (data.status !== 'success') {
                    body.innerHTML = '<tr><td colspan="4">Failed to load records</td></tr>';
                    return;
                }
                if (!data.data || data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="4">No records found</td></tr>';
                    chrRecords = [];
                    return;
                }
                chrRecords = data.data;
                renderTable(chrRecords);
                populateVaccineDropdown();
                // Default date to today and status to upcoming; apply filters on first load
                const dateInput = document.getElementById('filterDate');
                if (dateInput && !dateInput.value) {
                    dateInput.value = normalizeDateStr(new Date());
                }
                const statusSelEl = document.getElementById('filterStatus');
                if (statusSelEl) {
                    statusSelEl.value = 'upcoming';
                }
                applyFilters();
            } catch (e) {
                body.innerHTML = '<tr><td colspan="4">Error loading records</td></tr>';
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
                const tdDoses = [
                    { dose: 1, date: feedingData.tdDose1 },
                    { dose: 2, date: feedingData.tdDose2 },
                    { dose: 3, date: feedingData.tdDose3 },
                    { dose: 4, date: feedingData.tdDose4 },
                    { dose: 5, date: feedingData.tdDose5 }
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
                    <div>
                        <div>
                            <label>Child Name</label>
                            <input type="text" id="im_child_name" value="${childName}" readonly />
                        </div>
                        <div>
                            <label>Vaccine</label>
                            <input type="text" id="im_vaccine_name" value="${vaccineName}" readonly />
                        </div>
                        <div>
                            <label>Scheduled Date</label>
                            <input type="date" id="im_schedule_date" value="${scheduleDate}" readonly />
                        </div>
                        ${catchUpDate ? `
                        <div>
                            <label>Catch-up Date</label>
                            <input type=\"date\" id=\"im_catch_up_date\" value=\"${catchUpDate}\" readonly />
                        </div>` : ''}
                        <div>
                            <label>Date Taken</label>
                            <input type="date" id="im_date_taken" value="${dateToday}" />
                        </div>
                    </div>

                    ${feedingStatus ? `
                    <div>
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

                    <div>
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

                    <div>
                        <div>
                            <label>Temperature (°C)</label>
                            <input type="number" step="0.1" id="im_temperature" placeholder="e.g. 36.8" />
                        </div>
                        <div>
                            <label>Height (cm)</label>
                            <input type="number" step="0.1" id="im_height" placeholder="e.g. 60" />
                        </div>
                        <div>
                            <label>Weight (kg)</label>
                            <input type="number" step="0.01" id="im_weight" placeholder="e.g. 6.5" />
                        </div>
                    </div>

                    <!-- Dose and Lot fields removed: dose is auto-determined from record, lot/site not in schema -->

                    <div>
                        <div>
                            <label>Administered By</label>
                            <input type="text" id="im_administered_by" placeholder="Name" />
                        </div>
                        <div>
                            <input type="checkbox" id="im_mark_completed" />
                            <label for="im_mark_completed">Mark as Taken</label>
                        </div>
                    </div>

                    <div>
                        <label>Remarks</label>
                        <textarea id="im_remarks" rows="3"></textarea>
                    </div>

                    <div>
                        <button onclick="closeImmunizationForm()">Cancel</button>
                        <button onclick="submitImmunizationForm()">Save</button>
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
                    await getChildHealthRecord();
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
                        <td>${item.status === 'taken' && item.date_given ? ('taken (' + item.date_given + ')') : (item.status || '')}</td>
                            <td>
                                <button onclick="openImmunizationForm(this)"
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
                                    Record
                                </button>
                            </td>
                        </tr>`;
            });
            body.innerHTML = rows;
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

            // Get unique vaccines from the loaded data
            const vaccines = [...new Set(chrRecords.map(item => item.vaccine_name).filter(v => v))].sort();

            const options = ['<option value="all">All</option>'].concat(vaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));
            sel.innerHTML = options.join('');
            if (Array.from(sel.options).some(o => o.value === current)) sel.value = current;
        }

        function normalizeDateStr(d) {
            const pad = n => (n < 10 ? '0' : '') + n;
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        }

        function applyFilters() {
            if (!chrRecords || chrRecords.length === 0) {
                renderTable([]);
                return;
            }
            const dateSel = (document.getElementById('filterDate').value || '').trim();
            const statusSel = document.getElementById('filterStatus').value;
            const vaccineSel = document.getElementById('filterVaccine').value;
            const purokQ = (document.getElementById('filterPurok').value || '').trim().toLowerCase();

            const today = new Date();
            const todayStr = normalizeDateStr(today);

            const filtered = chrRecords.filter(item => {
                // Purok filter
                if (purokQ) {
                    const addr = String(item.address || '').toLowerCase();
                    if (!addr.includes(purokQ)) return false;
                }

                // Vaccine filter
                if (vaccineSel !== 'all') {
                    const vaccine = String(item.vaccine_name || '');
                    if (vaccine !== vaccineSel) return false;
                }

                // Date filter
                if (dateSel !== '') {
                    const scheduleDate = item.schedule_date || '';
                    const catchUpDate = item.catch_up_date || '';
                    if (scheduleDate !== dateSel && catchUpDate !== dateSel) return false;
                }

                // Status filter
                if (statusSel !== 'all') {
                    const status = String(item.status || '');
                    if (statusSel === 'upcoming') {
                        const dueDate = item.catch_up_date || item.schedule_date || '';
                        if (dueDate === '' || dueDate < todayStr || status === 'taken') return false;
                    } else if (statusSel === 'missed') {
                        const dueDate = item.catch_up_date || item.schedule_date || '';
                        if (dueDate === '' || dueDate >= todayStr || status === 'taken') return false;
                    } else if (statusSel === 'completed') {
                        if (status !== 'taken') return false;
                    }
                }

                return true;
            });

            renderTable(filtered);
        }

        function clearFilters() {
            document.getElementById('filterDate').value = normalizeDateStr(new Date());
            document.getElementById('filterStatus').value = 'upcoming';
            document.getElementById('filterVaccine').value = 'all';
            document.getElementById('filterPurok').value = '';
            renderTable(chrRecords);
            applyFilters();
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
                getChildHealthRecord();
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
                getChildHealthRecord();
            } else {
                alert('Record not rejected: ' + data.message);
            }
        }




        window.addEventListener('DOMContentLoaded', getChildHealthRecord);
        window.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyFiltersBtn');
            const clearBtn = document.getElementById('clearFiltersBtn');
            if (applyBtn) applyBtn.addEventListener('click', applyFilters);
            if (clearBtn) clearBtn.addEventListener('click', clearFilters);
        });

        // Removed QR scanner functionality and dependencies
        async function logoutBhw() {
            // const response = await fetch('../../php/bhw/logout.php', { method: 'POST' });
            const response = await fetch('../../php/supabase/bhw/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = '../../views/auth/login.php';
            } else {
                alert('Logout failed: ' + data.message);
            }
        }
    </script>

</body>

</html>