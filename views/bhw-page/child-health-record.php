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
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
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
                        <tbody id="ledgerBody">
                            <tr>
                                <td colspan="10" class="loading-cell">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        function formatDate(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            if (Number.isNaN(d.getTime())) return String(dateString);
            return d.toLocaleDateString('en-US', {
                month: 'numeric',
                day: 'numeric',
                year: '2-digit'
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

                // Fill header (read-only)
                document.getElementById('f_name').textContent = getValue(child.name || [(child.child_fname || ''), (child.child_lname || '')].filter(Boolean).join(' '));
                document.getElementById('f_gender').textContent = getValue(child.child_gender || child.gender);
                document.getElementById('f_birth_date').textContent = getValue(child.child_birth_date);
                document.getElementById('f_birth_place').textContent = getValue(child.place_of_birth);
                document.getElementById('f_birth_weight').textContent = getValue(child.birth_weight);
                document.getElementById('f_birth_height').textContent = getValue(child.birth_height);
                document.getElementById('f_address').textContent = getValue(child.address);
                document.getElementById('f_allergies').textContent = getValue('');
                document.getElementById('f_blood_type').textContent = getValue('');
                document.getElementById('f_family_no').textContent = getValue(child.family_number);
                document.getElementById('f_philhealth').textContent = getValue('');
                document.getElementById('f_nhts').textContent = getValue('');
                document.getElementById('f_non_nhts').textContent = getValue('');
                document.getElementById('f_father').textContent = getValue(child.father_name);
                document.getElementById('f_mother').textContent = getValue(child.mother_name);
                document.getElementById('f_nb_screen').textContent = getValue('');
                document.getElementById('f_fp').textContent = getValue('');
                document.getElementById('f_nbs_date').textContent = getValue('');
                document.getElementById('f_delivery_type').textContent = getValue(child.delivery_type);
                document.getElementById('f_birth_order').textContent = getValue(child.birth_order);
                document.getElementById('f_nbs_place').textContent = getValue('');
                document.getElementById('f_attended_by').textContent = getValue(child.birth_attendant);

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
                const allRows = (schedJson && schedJson.status === 'success' && Array.isArray(schedJson.data)) ? schedJson.data : [];

                // Build display-only ledger
                let ledgerHtml = '';
                if (allRows.length === 0) {
                    ledgerHtml = '<tr><td colspan="10" class="text-center p-10">No immunization records found</td></tr>';
                } else {
                    allRows.forEach(row => {
                        const date = row.date_given || row.schedule_date || '';
                        const ht = row.height || row.height_cm || '';
                        const wt = row.weight || row.weight_kg || '';
                        const status = row.status || 'scheduled';
                        const statusText = status === 'taken' ? 'Taken' : (status === 'completed' ? 'Completed' : (status === 'missed' ? 'Missed' : 'Scheduled'));
                        const chipClass = status === 'scheduled' ? 'upcoming' : status;

                        ledgerHtml += `
                    <tr>
                        <td>${date}</td>
                        <td>${row.vaccine_name || ''}</td>
                        <td>${ht}</td>
                        <td>${wt}</td>
                        <td>${row.me_ac || ''}</td>
                        <td><span class="chip chip--${chipClass}">${statusText}</span></td>
                        <td>${row.condition_of_baby || ''}</td>
                        <td>${row.advice_given || ''}</td>
                        <td>${row.catch_up_date || ''}</td>
                        <td>${row.remarks || ''}</td>
                    </tr>`;
                    });
                }

                document.getElementById('ledgerBody').innerHTML = ledgerHtml;

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