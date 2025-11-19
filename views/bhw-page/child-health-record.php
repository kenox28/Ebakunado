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
    <title>Child Health Record</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css?v=1.0.1" />
    <link rel="stylesheet" href="../../css/sidebar.css?v=1.0.1" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/bhw/child-health-record.css" />
</head>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="child-health-record-section" id="chrRoot">
            <div class="chr-top-actions">
                <a href="child-health-list.php" class="btn back-btn">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Go Back
                </a>
            </div>

            <div class="chr-header">
                <div class="chr-header-content">
                    <div class="chr-header-text">
                        <h2>CHILD HEALTH RECORD</h2>
                        <p>City Health Department, Ormoc City</p>
                    </div>
                </div>
            </div>

            <!-- Child Profile -->
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

            <!-- Feeding Section -->
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
                        <div class="checkbox-grid">
                            <label><input type="checkbox" id="eb_1mo"> 1st mo</label>
                            <label><input type="checkbox" id="eb_2mo"> 2nd mo</label>
                            <label><input type="checkbox" id="eb_3mo"> 3rd mo</label>
                            <label><input type="checkbox" id="eb_4mo"> 4th mo</label>
                            <label><input type="checkbox" id="eb_5mo"> 5th mo</label>
                            <label><input type="checkbox" id="eb_6mo"> 6th mo</label>
                        </div>
                    </div>
                    <div class="complementary-feeding">
                        <h4>Complementary Feeding:</h4>
                        <div class="food-inputs">
                            <label>6th mo food: <input type="text" id="cf_6mo"></label>
                            <label>7th mo food: <input type="text" id="cf_7mo"></label>
                            <label>8th mo food: <input type="text" id="cf_8mo"></label>
                        </div>
                    </div>

                    <div class="section-btn">
                        <button class="btn edit-btn" onclick="updateFeedingStatus()">
                            <span class="material-symbols-rounded">restaurant_menu</span>
                            Update Feeding Status
                        </button>
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
                    <div class="td-grid-item">
                        <label>TD 1st dose: <input type="date" id="td_dose1"></label>
                        <label>TD 2nd dose: <input type="date" id="td_dose2"></label>
                        <label>TD 3rd dose: <input type="date" id="td_dose3"></label>
                        <label>TD 4th dose: <input type="date" id="td_dose4"></label>
                        <label>TD 5th dose: <input type="date" id="td_dose5"></label>
                    </div>
                    <div class="section-btn">
                        <button class="btn edit-btn" onclick="updateTDStatus()">
                            <span class="material-symbols-rounded">vaccines</span>
                            Update TD Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Immunization Record -->
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
                                <th>Status</th>
                                <th>Condition of Baby</th>
                                <th>Advice Given</th>
                                <th>Next Sched Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
        // Format date as: short month name, numeric day, full year (e.g., Jan 5, 2025)
        function formatDateLong(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            if (Number.isNaN(d.getTime())) return String(dateString);
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

            // Apply skeleton loading ONLY to Child Information & Child History spans
            (function applyChrInfoHistorySkeleton(){
                if (window.CHR_SKELETON && typeof applyFieldsSkeleton === 'function') {
                    const mapping = {
                        ...window.CHR_SKELETON.CHILD_INFO_FIELDS,
                        ...window.CHR_SKELETON.CHILD_HISTORY_FIELDS
                    };
                    applyFieldsSkeleton(mapping);
                } else if (typeof applyFieldSkeleton === 'function') {
                    // Fallback minimal widths if group map is unavailable
                    [
                        'f_name','f_gender','f_birth_date','f_birth_place','f_birth_weight','f_birth_height','f_address','f_allergies','f_blood_type',
                        'f_family_no','f_philhealth','f_nhts','f_non_nhts','f_father','f_mother','f_nb_screen','f_fp',
                        'f_nbs_date','f_delivery_type','f_birth_order','f_nbs_place','f_attended_by'
                    ].forEach(id => applyFieldSkeleton(id,'skeleton-field-m'));
                }
            })();

            // Show skeleton immediately
            const ledgerBody = document.getElementById('ledgerBody');
            function getLedgerColsConfig(){
                return [
                    { type: 'text', widthClass: 'skeleton-col-1' }, // Date
                    { type: 'text', widthClass: 'skeleton-col-2' }, // Purpose
                    { type: 'text', widthClass: 'skeleton-col-3' }, // HT
                    { type: 'text', widthClass: 'skeleton-col-4' }, // WT
                    { type: 'text', widthClass: 'skeleton-col-5' }, // ME/AC
                    { type: 'pill', widthClass: 'skeleton-col-5' }, // Status
                    { type: 'text', widthClass: 'skeleton-col-3' }, // Condition
                    { type: 'text', widthClass: 'skeleton-col-4' }, // Advice
                    { type: 'text', widthClass: 'skeleton-col-2' }, // Next Sched Date
                    { type: 'text', widthClass: 'skeleton-col-6' }  // Remarks
                ];
            }
            if (typeof applyTableSkeleton === 'function' && ledgerBody) {
                applyTableSkeleton(ledgerBody, getLedgerColsConfig(), 10);
            } else if (ledgerBody) {
                ledgerBody.innerHTML = '<tr><td colspan="10" class="loading-cell">Loading...</td></tr>';
            }

            try {
                // Fetch child details
                const fd = new FormData();
                fd.append('baby_id', babyId);
                const childRes = await fetch('../../php/supabase/bhw/get_child_details.php', {
                    method: 'POST',
                    body: fd
                });
                const childJson = await childRes.json();
                const child = (childJson && childJson.status === 'success' && childJson.data && childJson.data[0]) ? childJson.data[0] : {};

                // Helper function to display data or hyphen
                function getValue(value) {
                    return value || '-';
                }

                // Prepare values map for Child Information & Child History sections
                const values = {
                    f_name: getValue(child.name || [(child.child_fname || ''), (child.child_lname || '')].filter(Boolean).join(' ')),
                    f_gender: getValue(child.child_gender || child.gender),
                    f_birth_date: getValue(child.child_birth_date),
                    f_birth_place: getValue(child.place_of_birth),
                    f_birth_weight: getValue(child.birth_weight),
                    f_birth_height: getValue(child.birth_height),
                    f_address: getValue(child.address),
                    f_allergies: getValue(''),
                    f_blood_type: getValue(''),
                    f_family_no: getValue(child.family_number),
                    f_philhealth: getValue(''),
                    f_nhts: getValue(''),
                    f_non_nhts: getValue(''),
                    f_father: getValue(child.father_name),
                    f_mother: getValue(child.mother_name),
                    f_nb_screen: getValue(''),
                    f_fp: getValue(''),
                    f_nbs_date: getValue(''),
                    f_delivery_type: getValue(child.delivery_type),
                    f_birth_order: getValue(child.birth_order),
                    f_nbs_place: getValue(''),
                    f_attended_by: getValue(child.birth_attendant)
                };

                if (typeof setFieldsValues === 'function') {
                    setFieldsValues(values);
                } else {
                    // Fallback manual assignment if skeleton API not available
                    Object.keys(values).forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = values[id];
                    });
                }

                // Fill editable feeding data
                document.getElementById('eb_1mo').checked = child.exclusive_breastfeeding_1mo || false;
                document.getElementById('eb_2mo').checked = child.exclusive_breastfeeding_2mo || false;
                document.getElementById('eb_3mo').checked = child.exclusive_breastfeeding_3mo || false;
                document.getElementById('eb_4mo').checked = child.exclusive_breastfeeding_4mo || false;
                document.getElementById('eb_5mo').checked = child.exclusive_breastfeeding_5mo || false;
                document.getElementById('eb_6mo').checked = child.exclusive_breastfeeding_6mo || false;
                document.getElementById('cf_6mo').value = child.complementary_feeding_6mo || '';
                document.getElementById('cf_7mo').value = child.complementary_feeding_7mo || '';
                document.getElementById('cf_8mo').value = child.complementary_feeding_8mo || '';

                // Fill editable TD status data
                document.getElementById('td_dose1').value = child.mother_td_dose1_date || '';
                document.getElementById('td_dose2').value = child.mother_td_dose2_date || '';
                document.getElementById('td_dose3').value = child.mother_td_dose3_date || '';
                document.getElementById('td_dose4').value = child.mother_td_dose4_date || '';
                document.getElementById('td_dose5').value = child.mother_td_dose5_date || '';

                // Fetch immunization schedule for child

                const schedRes = await fetch(`../../php/supabase/bhw/get_immunization_records.php?baby_id=${encodeURIComponent(babyId)}`);
                const schedJson = await schedRes.json();
                if (schedJson.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'Failed to load data. Please try again.', { colspan: 10, kind: 'error' });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row error"><td colspan="10">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }
                const allRows = Array.isArray(schedJson.data) ? schedJson.data : [];

                if (allRows.length === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(ledgerBody, 'No records found', { colspan: 10 });
                    } else {
                        ledgerBody.innerHTML = '<tr class="message-row"><td colspan="10">No records found</td></tr>';
                    }
                    return;
                }

                let ledgerHtml = '';
                allRows.forEach(row => {
                    const rawDate = row.date_given || row.schedule_date || '';
                    const date = formatDateLong(rawDate);
                    const catchUpDate = formatDateLong(row.catch_up_date || '');
                    const ht = row.height || row.height_cm || '';
                    const wt = row.weight || row.weight_kg || '';
                    const muac = row.muac || row.me_ac || '';
                    const status = row.status || 'scheduled';
                    const statusText = status === 'taken' ? 'Taken' : (status === 'completed' ? 'Completed' : (status === 'missed' ? 'Missed' : 'Scheduled'));
                    const chipClass = status === 'scheduled' ? 'upcoming' : status;

                    ledgerHtml += `
                        <tr>
                            <td>${date}</td>
                            <td>${row.vaccine_name || ''}</td>
                            <td>${ht}</td>
                            <td>${wt}</td>
                            <td>${muac}</td>
                            <td><span class="chip chip--${chipClass}">${statusText}</span></td>
                            <td>${row.condition_of_baby || ''}</td>
                            <td>${row.advice_given || ''}</td>
                            <td>${catchUpDate}</td>
                            <td>${row.remarks || ''}</td>
                        </tr>`;
                });
                ledgerBody.innerHTML = ledgerHtml;

            } catch (err) {
                console.error('CHR load error', err);
                alert('Error loading child health record: ' + err.message);
            }
        });

        // Feeding and TD status update functions
        async function updateFeedingStatus() {
            const babyId = new URLSearchParams(window.location.search).get('baby_id') || '';
            try {
                const formData = new FormData();
                formData.append('baby_id', babyId);
                formData.append('exclusive_breastfeeding_1mo', document.getElementById('eb_1mo').checked ? '1' : '0');
                formData.append('exclusive_breastfeeding_2mo', document.getElementById('eb_2mo').checked ? '1' : '0');
                formData.append('exclusive_breastfeeding_3mo', document.getElementById('eb_3mo').checked ? '1' : '0');
                formData.append('exclusive_breastfeeding_4mo', document.getElementById('eb_4mo').checked ? '1' : '0');
                formData.append('exclusive_breastfeeding_5mo', document.getElementById('eb_5mo').checked ? '1' : '0');
                formData.append('exclusive_breastfeeding_6mo', document.getElementById('eb_6mo').checked ? '1' : '0');
                formData.append('complementary_feeding_6mo', document.getElementById('cf_6mo').value);
                formData.append('complementary_feeding_7mo', document.getElementById('cf_7mo').value);
                formData.append('complementary_feeding_8mo', document.getElementById('cf_8mo').value);

                const res = await fetch('../../php/supabase/bhw/update_feeding_status.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                alert(data.status === 'success' ? 'Feeding status updated successfully!' : 'Update failed: ' + data.message);
            } catch (e) {
                alert('Error updating feeding status: ' + e.message);
            }
        }

        async function updateTDStatus() {
            const babyId = new URLSearchParams(window.location.search).get('baby_id') || '';
            try {
                const formData = new FormData();
                formData.append('baby_id', babyId);
                for (let i = 1; i <= 5; i++) {
                    formData.append(`mother_td_dose${i}_date`, document.getElementById(`td_dose${i}`).value);
                }

                const res = await fetch('../../php/supabase/bhw/update_td_status.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                alert(data.status === 'success' ? 'TD status updated successfully!' : 'Update failed: ' + data.message);
            } catch (e) {
                alert('Error updating TD status: ' + e.message);
            }
        }
    </script>
</body>

</html>