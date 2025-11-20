<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}


// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Health Record</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/user/child-health-record.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="child-health-record-section" id="chrRoot">
            <div class="chr-header">
                <div class="chr-header-content">
                    <div class="chr-header-text">
                        <h2>CHILD HEALTH RECORD</h2>
                        <p>City Health Department, Ormoc City</p>
                    </div>
                </div>
            </div>

            <!-- Child Profile Header -->
            <div id="childProfile" class="chr-section profile-section">
                <div class="chr-subheader">
                    <h2>
                        <span class="material-symbols-rounded" aria-hidden="true">person</span>
                        Child Information
                    </h2>
                </div>
                <div class="chr-grid">
                    <div class="childinfo-column">
                        <label><span>Name of Child</span><span id="f_name">-</span></label>
                        <label><span>Gender</span><span id="f_gender">-</span></label>
                        <label><span>Date of Birth</span><span id="f_birth_date">-</span></label>
                        <label><span>Place of Birth</span><span id="f_birth_place">-</span></label>
                        <label><span>Birth Weight</span><span id="f_birth_weight">-</span></label>
                        <label><span>Birth Length</span><span id="f_birth_height">-</span></label>
                        <label><span>Address</span><span id="f_address">-</span></label>
                        <label><span>Allergies</span><span id="f_allergies">-</span></label>
                        <label><span>Blood Type</span><span id="f_blood_type">-</span></label>
                    </div>

                    <div class="childinfo-column">
                        <label><span>Family Number</span><span id="f_family_no">-</span></label>
                        <label><span>LMP</span><span id="f_lpm">-</span></label>
                        <label><span>PhilHealth No.</span><span id="f_philhealth">-</span></label>
                        <label><span>NHTS</span><span id="f_nhts">-</span></label>
                        <label><span>Non-NHTS</span><span id="f_non_nhts">-</span></label>
                        <label><span>Father's Name</span><span id="f_father">-</span></label>
                        <label><span>Mother's Name</span><span id="f_mother">-</span></label>
                        <label><span>NB Screening</span><span id="f_nb_screen">-</span></label>
                        <label><span>Family Planning</span><span id="f_fp">-</span></label>
                    </div>
                </div>
            </div>

            <!-- Child History -->
            <div id="childHistory" class="chr-section history-section">
                <div class="chr-subheader">
                    <h2>
                        <span class="material-symbols-rounded" aria-hidden="true">history</span>
                        Child History
                    </h2>
                </div>
                <div class="chr-grid">
                    <div class="childinfo-column">
                        <label><span>Date of Newborn Screening</span><span id="f_nbs_date">-</span></label>
                        <label><span>Type of Delivery</span><span id="f_delivery_type">-</span></label>
                        <label><span>Birth Order</span><span id="f_birth_order">-</span></label>
                    </div>
                    <div class="childinfo-column">
                        <label><span>Place of Newborn Screening</span><span id="f_nbs_place">-</span></label>
                        <label><span>Attended by</span><span id="f_attended_by">-</span></label>
                    </div>
                </div>
            </div>

            <!-- Exclusive Breastfeeding & Complementary Feeding -->
            <div id="feedingSection" class="chr-section feeding-section">
                <div class="chr-subheader">
                    <h2>
                        <span class="material-symbols-rounded" aria-hidden="true">restaurant</span>
                        Exclusive Breastfeeding & Complementary Feeding
                    </h2>
                </div>
                <div class="feeding-grid">
                    <div class="exclusive-breastfeeding">
                        <h4>Exclusive Breastfeeding:</h4>
                        <div class="eb-grid">
                            <label><span>1st mo: </span><span id="f_eb_1mo">-</span></label>
                            <label><span>2nd mo: </span><span id="f_eb_2mo">-</span></label>
                            <label><span>3rd mo: </span><span id="f_eb_3mo">-</span></label>
                            <label><span>4th mo: </span><span id="f_eb_4mo">-</span></label>
                            <label><span>5th mo: </span><span id="f_eb_5mo">-</span></label>
                            <label><span>6th mo: </span><span id="f_eb_6mo">-</span></label>
                        </div>
                    </div>
                    <div class="complementary-feeding">
                        <h4>Complementary Feeding:</h4>
                        <div class="cf-grid">
                            <label><span>6th mo food: </span><span id="f_cf_6mo">-</span></label>
                            <label><span>7th mo food: </span><span id="f_cf_7mo">-</span></label>
                            <label><span>8th mo food: </span><span id="f_cf_8mo">-</span></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mother's TD Status -->
            <div id="tdSection" class="chr-section td-section">
                <div class="chr-subheader">
                    <h2>
                        <span class="material-symbols-rounded" aria-hidden="true">vaccines</span>
                        Mother's TD (Tetanus-Diphtheria) Status
                    </h2>
                </div>
                <div class="td-grid">
                    <label><span>TD 1st dose:</span><span id="f_td_dose1">-</span></label>
                    <label><span>TD 2nd dose:</span><span id="f_td_dose2">-</span></label>
                    <label><span>TD 3rd dose:</span><span id="f_td_dose3">-</span></label>
                    <label><span>TD 4th dose:</span><span id="f_td_dose4">-</span></label>
                    <label><span>TD 5th dose:</span><span id="f_td_dose5">-</span></label>
                </div>
            </div>

            <!-- Compact Ledger: One row per taken vaccine -->
            <div class="immunization-record">
                <div class="chr-subheader">
                    <h2>
                        <span class="material-symbols-rounded" aria-hidden="true">syringe</span>
                        Immunization Record
                    </h2>
                </div>
                <div class="table-container">
                    <table class="table immunization-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Purpose</th>
                                <th>HT</th>
                                <th>WT</th>
                                <th>ME/AC</th>
                                <th>STATUS</th>
                                <th>Condition of Baby</th>
                                <th>Advice Given</th>
                                <th>Next Sched Date</th>
                                <th>Batch Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="chr-actions">
                <button id="reqTransferBtn" class="btn">Request Transfer Copy</button>
                <button id="reqSchoolBtn" class="btn">Request School Copy</button>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
        // Using shared group maps from skeleton-loading.js (CHR_SKELETON)

        function formatDate(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            if (Number.isNaN(d.getTime())) return String(dateString);
            // Example wanted: Nov 7, 2025 (short month, numeric day, full year)
            return d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function normalizeDateStr(d) {
            const pad = n => (n < 10 ? '0' : '') + n;
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        }

        // Return a hyphen when value is empty/undefined/null
        function getValue(value) {
            if (value === null || value === undefined) return '-';
            if (typeof value === 'string' && value.trim() === '') return '-';
            return value;
        }

        function getDoseText(n) {
            const map = {
                1: '1st dose',
                2: '2nd dose',
                3: '3rd dose',
                4: '4th dose'
            };
            return map[n] || `Dose ${n||''}`;
        }

        document.addEventListener('DOMContentLoaded', async function() {
            const params = new URLSearchParams(window.location.search);
            const babyId = params.get('baby_id') || '';
            if (!babyId) {
                alert('Missing baby_id');
                return;
            }
            // No back/print/download elements now

            // Turn on skeletons for all detail fields right away using group helper
            if (window.CHR_SKELETON && typeof window.CHR_SKELETON.apply === 'function') {
                window.CHR_SKELETON.apply();
            } else if (typeof applyFieldsSkeleton === 'function') {
                // Fallback: apply combined map manually if helper not available
                const combined = Object.assign({},
                  window.CHR_SKELETON?.CHILD_INFO_FIELDS || {},
                  window.CHR_SKELETON?.CHILD_HISTORY_FIELDS || {},
                  window.CHR_SKELETON?.FEEDING_FIELDS || {},
                  window.CHR_SKELETON?.TD_STATUS_FIELDS || {}
                );
                applyFieldsSkeleton(combined);
            }

            // Show ledger skeleton immediately (before any network calls)
            const ledgerBody = document.getElementById('ledgerBody');
            function getLedgerColsConfig(){
                return [
                    { type: 'text', widthClass: 'skeleton-col-1' }, // Date
                    { type: 'text', widthClass: 'skeleton-col-2' }, // Purpose
                    { type: 'text', widthClass: 'skeleton-col-3' }, // HT
                    { type: 'text', widthClass: 'skeleton-col-4' }, // WT
                    { type: 'text', widthClass: 'skeleton-col-5' }, // ME/AC
                    { type: 'pill', widthClass: 'skeleton-col-5' }, // STATUS
                    { type: 'text', widthClass: 'skeleton-col-3' }, // Condition of Baby
                    { type: 'text', widthClass: 'skeleton-col-4' }, // Advice Given
                    { type: 'text', widthClass: 'skeleton-col-2' }, // Next Sched Date
                    { type: 'text', widthClass: 'skeleton-col-6' }  // Remarks
                ];
            }
            if (typeof applyTableSkeleton === 'function' && ledgerBody) {
                applyTableSkeleton(ledgerBody, getLedgerColsConfig(), 10);
            } else if (ledgerBody) {
                ledgerBody.innerHTML = '<tr><td colspan="11" class="loading-cell">Loading...</td></tr>';
            }

            try {
                // Fetch child details
                const fd = new FormData();
                fd.append('baby_id', babyId);
                const childRes = await fetch('/ebakunado/php/supabase/users/get_child_details.php', {
                    method: 'POST',
                    body: fd
                });
                const childJson = await childRes.json();
                const child = (childJson && childJson.status === 'success' && childJson.data && childJson.data[0]) ? childJson.data[0] : {};

                // Fill header (blanks when missing) and stop skeletons
                // Prepare values for batch population
                const values = {
                    f_name: child.name || [(child.child_fname || ''), (child.child_lname || '')].filter(Boolean).join(' '),
                    f_gender: child.child_gender || child.gender,
                    f_birth_date: child.child_birth_date,
                    f_birth_place: child.place_of_birth,
                    f_birth_weight: child.birth_weight,
                    f_birth_height: child.birth_height,
                    f_address: child.address,
                    f_allergies: child.allergies,
                    f_lpm: child.lpm,
                    f_blood_type: child.blood_type,
                    f_family_no: child.family_number,
                    f_philhealth: child.philhealth_no,
                    f_nhts: child.nhts,
                    f_non_nhts: '',
                    f_father: child.father_name,
                    f_mother: child.mother_name,
                    f_nb_screen: '',
                    f_fp: child.family_planning,
                    f_nbs_date: '',
                    f_delivery_type: child.delivery_type,
                    f_birth_order: child.birth_order,
                    f_nbs_place: '',
                    f_attended_by: child.birth_attendant,
                    f_eb_1mo: child.exclusive_breastfeeding_1mo ? '✓' : '-',
                    f_eb_2mo: child.exclusive_breastfeeding_2mo ? '✓' : '-',
                    f_eb_3mo: child.exclusive_breastfeeding_3mo ? '✓' : '-',
                    f_eb_4mo: child.exclusive_breastfeeding_4mo ? '✓' : '-',
                    f_eb_5mo: child.exclusive_breastfeeding_5mo ? '✓' : '-',
                    f_eb_6mo: child.exclusive_breastfeeding_6mo ? '✓' : '-',
                    f_cf_6mo: child.complementary_feeding_6mo || '-',
                    f_cf_7mo: child.complementary_feeding_7mo || '-',
                    f_cf_8mo: child.complementary_feeding_8mo || '-',
                    f_td_dose1: formatDate(child.mother_td_dose1_date) || '-',
                    f_td_dose2: formatDate(child.mother_td_dose2_date) || '-',
                    f_td_dose3: formatDate(child.mother_td_dose3_date) || '-',
                    f_td_dose4: formatDate(child.mother_td_dose4_date) || '-',
                    f_td_dose5: formatDate(child.mother_td_dose5_date) || '-'
                };
                if (typeof setFieldsValues === 'function') {
                    setFieldsValues(values);
                } else {
                    // Fallback if utility not loaded for some reason
                    Object.keys(values).forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = values[id];
                    });
                }

                // Wire two request buttons (transfer, school)
                const reqTransferBtn = document.getElementById('reqTransferBtn');
                const reqSchoolBtn = document.getElementById('reqSchoolBtn');

                async function sendRequest(type) {
                    try {
                        const fd2 = new FormData();
                        fd2.append('baby_id', babyId);
                        fd2.append('request_type', type);
                        const res2 = await fetch('/ebakunado/php/supabase/users/request_chr_doc.php', {
                            method: 'POST',
                            body: fd2
                        });
                        const j = await res2.json();
                        if (j.status === 'success') {
                            if (type === 'transfer') {
                                reqTransferBtn.textContent = 'Transfer: Requested (pendingCHR)';
                            }
                            if (type === 'school') {
                                reqSchoolBtn.textContent = 'School: Requested (pendingCHR)';
                            }
                        } else {
                            alert('Request failed: ' + (j.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Network error requesting CHR');
                    }
                }

                if (reqTransferBtn) reqTransferBtn.onclick = () => sendRequest('transfer');
                if (reqSchoolBtn) reqSchoolBtn.onclick = () => sendRequest('school');

                // Poll statuses independently
                await refreshChrDocStatus('transfer');
                await refreshChrDocStatus('school');
                setInterval(() => refreshChrDocStatus('transfer'), 5000);
                setInterval(() => refreshChrDocStatus('school'), 5000);

                async function refreshChrDocStatus(type) {
                    try {
                        const res = await fetch(`/ebakunado/php/supabase/users/get_chr_doc_status.php?baby_id=${encodeURIComponent(babyId)}&request_type=${encodeURIComponent(type)}`);
                        const j = await res.json();
                        if (j.status === 'success' && j.data) {
                            const st = j.data.status || '';
                            const url = j.data.doc_url || '';
                            const hasNewerRecords = j.data.has_newer_records || false;

                            if (type === 'transfer') {
                                if (st === 'approved' && url && !hasNewerRecords) {
                                    reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: Approved — see Approved Requests');
                                } else if (st === 'approved' && url && hasNewerRecords) {
                                    reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: New Records Available - Request Updated Copy');
                                } else if (st === 'pendingCHR') {
                                    reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: Requested (pendingCHR)');
                                } else {
                                    reqTransferBtn && (reqTransferBtn.textContent = 'Request Transfer Copy');
                                }
                            }
                            if (type === 'school') {
                                if (st === 'approved' && url && !hasNewerRecords) {
                                    reqSchoolBtn && (reqSchoolBtn.textContent = 'School: Approved — see Approved Requests');
                                } else if (st === 'approved' && url && hasNewerRecords) {
                                    reqSchoolBtn && (reqSchoolBtn.textContent = 'School: New Records Available - Request Updated Copy');
                                } else if (st === 'pendingCHR') {
                                    reqSchoolBtn && (reqSchoolBtn.textContent = 'School: Requested (pendingCHR)');
                                } else {
                                    reqSchoolBtn && (reqSchoolBtn.textContent = 'Request School Copy');
                                }
                            }
                        }
                    } catch (e) {
                        /* silent */
                    }
                }

                // Fetch immunization schedule for child to build compact ledger
                const schedRes = await fetch(`/ebakunado/php/supabase/users/get_immunization_schedule.php?baby_id=${encodeURIComponent(babyId)}`);
                if (!schedRes.ok) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'Failed to load data. Please try again.', { colspan: 11, kind: 'error' });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row error"><td colspan="11">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }
                const schedJson = await schedRes.json();
                if (!schedJson || schedJson.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'Failed to load data. Please try again.', { colspan: 11, kind: 'error' });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row error"><td colspan="11">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }
                const allRows = Array.isArray(schedJson.data) ? schedJson.data : [];

                // Filter only taken records
                const takenRows = allRows.filter(r => r.status === 'taken' || r.status === 'completed');

                // Determine next upcoming schedule per taken record (earliest future, not taken)
                function nextScheduleAfter(dateStr) {
                    if (!dateStr) return '';
                    const future = allRows
                        .filter(r => (r.status !== 'taken' && r.status !== 'completed'))
                        .filter(r => {
                            const due = r.catch_up_date || r.batch_schedule_date || r.schedule_date || '';
                            return due && String(due) > String(dateStr);
                        })
                        .sort((a, b) => {
                            const aDate = a.catch_up_date || a.batch_schedule_date || a.schedule_date || '';
                            const bDate = b.catch_up_date || b.batch_schedule_date || b.schedule_date || '';
                            return String(aDate).localeCompare(String(bDate));
                        });
                    if (!future.length) return '';
                    const next = future[0];
                    return next.catch_up_date || next.batch_schedule_date || next.schedule_date || '';
                }

                // Canonical vaccine order to avoid duplicates and ensure clear display
                const canonical = [
                    'BCG',
                    'HEPAB1 (w/in 24 hrs)',
                    'HEPAB1 (More than 24hrs)',
                    'Pentavalent (DPT-HepB-Hib) - 1st',
                    'OPV - 1st',
                    'PCV - 1st',
                    'Rota Virus Vaccine - 1st',
                    'Pentavalent (DPT-HepB-Hib) - 2nd',
                    'OPV - 2nd',
                    'PCV - 2nd',
                    'Rota Virus Vaccine - 2nd',
                    'Pentavalent (DPT-HepB-Hib) - 3rd',
                    'OPV - 3rd',
                    'PCV - 3rd',
                    'MCV1 (AMV)',
                    'MCV2 (MMR)'
                ];

                // Pick best taken record per vaccine (earliest date_given)
                const bestByName = {};
                takenRows.forEach(r => {
                    const name = String(r.vaccine_name || '');
                    if (!(name in bestByName)) {
                        bestByName[name] = r;
                        return;
                    }
                    const cur = bestByName[name];
                    const dNew = String(r.date_given || '');
                    const dCur = String(cur.date_given || '');
                    if (dNew && (!dCur || dNew < dCur)) bestByName[name] = r;
                });

                let ledgerHtml = '';
                canonical.forEach(name => {
                    const rec = bestByName[name];
                    if (!rec) return; // show only taken vaccines
                    const date = rec.date_given || rec.batch_schedule_date || rec.schedule_date || '';
                    const ht = rec.height || rec.height_cm || '';
                    const wt = rec.weight || rec.weight_kg || '';
                    const muac = rec.muac || '-';
                    const next = nextScheduleAfter(date);
                    // Get next schedule details including batch date
                    const nextRecord = allRows
                        .filter(r => (r.status !== 'taken' && r.status !== 'completed'))
                        .filter(r => {
                            const due = r.catch_up_date || r.batch_schedule_date || r.schedule_date || '';
                            return due && String(due) > String(date);
                        })
                        .sort((a, b) => {
                            const aDate = a.catch_up_date || a.batch_schedule_date || a.schedule_date || '';
                            const bDate = b.catch_up_date || b.batch_schedule_date || b.schedule_date || '';
                            return String(aDate).localeCompare(String(bDate));
                        })[0];
                    const nextBatchDate = nextRecord?.batch_schedule_date ? formatDate(nextRecord.batch_schedule_date) : '-';
                    ledgerHtml += `
                <tr>
                    <td>${getValue(formatDate(date))}</td>
                    <td>${getValue(name)}</td>
                    <td>${getValue(ht)}</td>
                    <td>${getValue(wt)}</td>
                    <td>${getValue(muac)}</td>
                    <td>Taken</td>
                    <td>-</td>
                    <td>-</td>
                    <td>${getValue(formatDate(next))}</td>
                    <td>${getValue(nextBatchDate)}</td>
                    <td>-</td>
                </tr>`;
                });

                if (!ledgerHtml) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'No records found', { colspan: 11 });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row"><td colspan="11">No records found</td></tr>';
                    }
                } else {
                    ledgerBody.innerHTML = ledgerHtml;
                }

            } catch (err) {
                console.error('CHR load error', err);
                const ledgerBody = document.getElementById('ledgerBody');
                if (ledgerBody) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'Failed to load data. Please try again.', { colspan: 11, kind: 'error' });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row error"><td colspan="11">Failed to load data. Please try again.</td></tr>';
                    }
                }
            }
        });
    </script>
</body>

</html>