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
    <!-- <link rel="stylesheet" href="/css/base.css" /> -->
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/variables.css" />
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
            <div class="chr-header">
                <h2>CHILD HEALTH RECORD</h2>
                <p>City Health Department, Ormoc City</p>
            </div>

            <!-- Child Profile -->
            <div id="childProfile" class="chr-section profile-section">
                <div class="chr-header ">
                    <h2>CHILD INFORMATION</h2>
                </div>
                <div class="chr-grid">
                    <div class="childinfo-column">
                        <label>Name of Child: <span id="f_name"></span></label>
                        <label>Gender: <span id="f_gender"></span></label>
                        <label>Date of Birth: <span id="f_birth_date"></span></label>
                        <label>Place of Birth: <span id="f_birth_place"></span></label>
                        <label>Birth Weight: <span id="f_birth_weight"></span></label>
                        <label>Birth Length: <span id="f_birth_height"></span></label>
                        <label>Address: <span id="f_address"></span></label>
                        <label>Allergies: <span id="f_allergies"></span></label>
                        <label>Blood Type: <span id="f_blood_type"></span></label>
                    </div>

                    <div class="childinfo-column">
                        <label>Family Number: <span id="f_family_no"></span></label>
                        <label>PhilHealth No.: <span id="f_philhealth"></span></label>
                        <label>NHTS: <span id="f_nhts"></span></label>
                        <label>Non-NHTS: <span id="f_non_nhts"></span></label>
                        <label>Father's Name: <span id="f_father"></span></label>
                        <label>Mother's Name: <span id="f_mother"></span></label>
                        <label>NB Screening: <span id="f_nb_screen"></span></label>
                        <label>Family Planning: <span id="f_fp"></span></label>
                    </div>

                </div>
            </div>

            <!-- Child History -->
            <div id="childHistory" class="chr-section history-section">
                <div class="chr-header ">
                    <h2>CHILD HISTORY</h2>
                </div>

                <div class="chr-grid">
                    <div>
                        <div>Date of Newborn Screening: <span id="f_nbs_date"></span></div>
                        <div>Type of Delivery: <span id="f_delivery_type"></span></div>
                        <div>Birth Order: <span id="f_birth_order"></span></div>
                    </div>
                    <div>
                        <div>Place of Newborn Screening: <span id="f_nbs_place"></span></div>
                        <div>Attended by: <span id="f_attended_by"></span></div>
                    </div>
                </div>
            </div>

            <!-- Feeding Section -->
            <div id="feedingSection" class="chr-section feeding-section">
                <div class="chr-header">
                    <h2>Exclusive Breastfeeding & Complementary Feeding</h2>
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
                        <button onclick="updateFeedingStatus()">Update Feeding Status</button>
                    </div>
                </div>
            </div>

            <!-- Mother's TD Status -->
            <div id="tdSection" class="chr-section td-section">
                <div class="chr-header">
                    <h2>Mother's TD (Tetanus-Diphtheria) Status</h2>
                </div>
                <div class="td-grid">
                    <label>TD 1st dose: <input type="date" id="td_dose1"></label>
                    <label>TD 2nd dose: <input type="date" id="td_dose2"></label>
                    <label>TD 3rd dose: <input type="date" id="td_dose3"></label>
                    <label>TD 4th dose: <input type="date" id="td_dose4"></label>
                    <label>TD 5th dose: <input type="date" id="td_dose5"></label>
                </div>
                <div class="section-btn">
                    <button onclick="updateTDStatus()">Update TD Status</button>
                </div>
            </div>

            <!-- Immunization Record -->
            <div class="immunization-record">
                <div class="chr-header">
                    <h2>IMMUNIZATION RECORD</h2>
                </div>
                <div class="table-container">
                    <table class="immunization-table">
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody">
                            <tr>
                                <td colspan="11" class="loading-cell">Loading...</td>
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

                // Fill header (read-only)
                document.getElementById('f_name').textContent = child.name || [(child.child_fname || ''), (child.child_lname || '')].filter(Boolean).join(' ');
                document.getElementById('f_gender').textContent = child.child_gender || child.gender || '';
                document.getElementById('f_birth_date').textContent = child.child_birth_date || '';
                document.getElementById('f_birth_place').textContent = child.place_of_birth || '';
                document.getElementById('f_birth_weight').textContent = child.birth_weight || '';
                document.getElementById('f_birth_height').textContent = child.birth_height || '';
                document.getElementById('f_address').textContent = child.address || '';
                document.getElementById('f_allergies').textContent = '';
                document.getElementById('f_blood_type').textContent = '';
                document.getElementById('f_family_no').textContent = child.family_number || '';
                document.getElementById('f_philhealth').textContent = '';
                document.getElementById('f_nhts').textContent = '';
                document.getElementById('f_non_nhts').textContent = '';
                document.getElementById('f_father').textContent = child.father_name || '';
                document.getElementById('f_mother').textContent = child.mother_name || '';
                document.getElementById('f_nb_screen').textContent = '';
                document.getElementById('f_fp').textContent = '';
                document.getElementById('f_nbs_date').textContent = '';
                document.getElementById('f_delivery_type').textContent = child.delivery_type || '';
                document.getElementById('f_birth_order').textContent = child.birth_order || '';
                document.getElementById('f_nbs_place').textContent = '';
                document.getElementById('f_attended_by').textContent = child.birth_attendant || '';

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

                // Build editable ledger
                let ledgerHtml = '';
                if (allRows.length === 0) {
                    ledgerHtml = '<tr><td colspan="11" class="text-center p-10">No immunization records found</td></tr>';
                } else {
                    allRows.forEach(row => {
                        const date = row.date_given || row.schedule_date || '';
                        const ht = row.height || row.height_cm || '';
                        const wt = row.weight || row.weight_kg || '';
                        const status = row.status || 'scheduled';
                        const statusText = status === 'taken' ? 'Taken' : (status === 'completed' ? 'Completed' : 'Scheduled');

                        ledgerHtml += `
                    <tr>
                        <td><input type="date" class="table-input" value="${date}" onchange="updateImmunizationDate(${row.id}, this.value)"></td>
                        <td>${row.vaccine_name || ''}</td>
                        <td><input type="text" class="table-input small" value="${ht}" placeholder="cm" onchange="updateImmunizationHeight(${row.id}, this.value)"></td>
                        <td><input type="text" class="table-input small" value="${wt}" placeholder="kg" onchange="updateImmunizationWeight(${row.id}, this.value)"></td>
                        <td><input type="text" class="table-input small" placeholder="cm"></td>
                        <td>
                            <select class="table-select" onchange="updateImmunizationStatus(${row.id}, this.value)">
                                <option value="scheduled" ${status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                <option value="taken" ${status === 'taken' ? 'selected' : ''}>Taken</option>
                                <option value="completed" ${status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="missed" ${status === 'missed' ? 'selected' : ''}>Missed</option>
                            </select>
                        </td>
                        <td><input type="text" class="table-input" placeholder="Good"></td>
                        <td><input type="text" class="table-input" placeholder="Continue feeding"></td>
                        <td><input type="date" class="table-input" value="${row.catch_up_date || ''}" onchange="updateNextScheduleDate(${row.id}, this.value)"></td>
                        <td><input type="text" class="table-input" placeholder="Notes"></td>
                        <td><button class="btn-delete" onclick="deleteImmunizationRecord(${row.id})">Delete</button></td>
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

        // Immunization update functions
        async function updateImmunizationDate(id, val) {
            await updateField('update_immunization_date.php', 'date_given', id, val);
        }
        async function updateImmunizationHeight(id, val) {
            await updateField('update_immunization_height.php', 'height', id, val);
        }
        async function updateImmunizationWeight(id, val) {
            await updateField('update_immunization_weight.php', 'weight', id, val);
        }
        async function updateImmunizationStatus(id, val) {
            await updateField('update_immunization_status.php', 'status', id, val);
        }
        async function updateNextScheduleDate(id, val) {
            await updateField('update_next_schedule_date.php', 'catch_up_date', id, val);
        }

        async function updateField(api, field, id, val) {
            try {
                const fd = new FormData();
                fd.append('record_id', id);
                fd.append(field, val);
                const res = await fetch(`../../php/supabase/bhw/${api}`, {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.status !== 'success') alert(`Failed to update ${field}: ${data.message}`);
            } catch (e) {
                alert(`Error updating ${field}: ${e.message}`);
            }
        }

        async function deleteImmunizationRecord(recordId) {
            if (!confirm('Are you sure you want to delete this immunization record?')) return;
            try {
                const formData = new FormData();
                formData.append('record_id', recordId);
                const res = await fetch('../../php/supabase/bhw/delete_immunization_record.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alert('Record deleted successfully!');
                    location.reload();
                } else {
                    alert('Failed to delete record: ' + data.message);
                }
            } catch (e) {
                alert('Error deleting record: ' + e.message);
            }
        }
    </script>
</body>

</html>