<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../views/auth/login.php");
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
    <title>Child Immunization Schedule</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css?v=1.0.2" />
    <link rel="stylesheet" href="../../css/sidebar.css" />

    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/user/child-upcoming-schedule.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main class="schedule-page">
        <div class="page-header">
            <h1 id="page-title">Child Immunization Schedule</h1>
            <p class="page-subtitle">View upcoming and taken immunizations for the selected child.</p>
        </div>

        <div class="content-grid">
            <aside class="child-card">
                <div class="child-card-header">
                    <div class="child-avatar">
                        <span class="material-symbols-rounded">child_care</span>
                    </div>
                    <div class="child-meta">
                        <div class="meta-top">
                            <h2 id="childName" class="child-name">Unknown Child</h2>
                            <a id="switchChildBtn" class="btn-switch" title="Switch Child" href="dashboard.php">
                                <span class="material-symbols-rounded">switch_account</span>
                                <span>Switch</span>
                            </a>
                        </div>
                        <div class="child-sub">
                            <div class="child-row">Age: <span id="childAge">-</span></div>
                            <div class="child-row">Gender: <span id="childGender">-</span></div>
                        </div>
                    </div>
                </div>
                <div class="child-qr">
                    <div class="qr-thumb" aria-hidden="false">
                        <img id="childQrCode" src="" alt="QR Code" />
                    </div>
                </div>
            </aside>

            <section class="schedule-panel">
                <div class="schedule-panel-header">
                    <h2 class="schedule-title">Vaccine Schedule</h2>
                    <div class="segmented-control" role="tablist" aria-label="Schedule tabs">
                        <button id="upcomingTab" class="segmented-btn is-active" role="tab" aria-selected="true">
                            <span class="material-symbols-rounded">calendar_month</span>
                            <span>Upcoming</span>
                        </button>
                        <button id="takenTab" class="segmented-btn" role="tab" aria-selected="false">
                            <span class="material-symbols-rounded">check_circle</span>
                            <span>Taken</span>
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="schedule-table" aria-describedby="scheduleDescription">
                        <caption id="scheduleDescription" class="sr-only">List of scheduled immunizations</caption>
                        <thead>
                            <tr>
                                <th>Vaccine</th>
                                <th>Dose</th>
                                <th>Guideline</th>
                                <th>Batch</th>
                                <th>Catch Up</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleBody">
                            <tr>
                                <td colspan="6" class="empty">Loading schedule...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script src="../../js/header-handler/profile-menu.js?v=1.0.4" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
        let scheduleData = [];
        let currentTab = 'upcoming';
        let currentChild = null;
        const urlParams = new URLSearchParams(window.location.search);
        const selectedBabyId = urlParams.get('baby_id') || '';

        async function loadImmunizationSchedule() {
            try {
                const endpoint = '/ebakunado/php/supabase/users/get_immunization_schedule.php' + (selectedBabyId ? ('?baby_id=' + encodeURIComponent(selectedBabyId)) : '');
                const response = await fetch(endpoint);
                const data = await response.json();

                if (data.status === 'success') {
                    scheduleData = Array.isArray(data.data) ? data.data : [];
                    // If API returns all, filter by selectedBabyId
                    if (selectedBabyId) {
                        scheduleData = scheduleData.filter(r => String(r.baby_id || '') === String(selectedBabyId));
                    }
                    loadChildData();
                    renderVaccineCards();
                }
            } catch (error) {
                console.error('Error loading immunization schedule:', error);
                document.getElementById('scheduleBody').innerHTML = `
                <tr><td colspan="5" style="text-align: center; padding: 20px; color: #dc3545;">Error loading schedule</td></tr>
            `;
            }
        }

        function loadChildData() {
            if (scheduleData.length === 0) {
                document.getElementById('scheduleBody').innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px;">No immunization records found</td></tr>';
                document.getElementById('childName').textContent = 'Unknown Child';
                document.getElementById('childAge').textContent = 'Unknown age';
                document.getElementById('childGender').textContent = 'Unknown';
                return;
            }
            const firstRecord = scheduleData[0];
            currentChild = firstRecord;
            const childName = firstRecord.child_name || 'Unknown Child';
            document.getElementById('childName').textContent = childName;
            // Update document title to selected child's name only
            try {
                document.title = childName;
            } catch (e) {
                // ignore if document.title cannot be set in some contexts
            }
            document.getElementById('childAge').textContent = 'Loading age...';
            fetchChildAge(firstRecord.baby_id);
        }

        async function fetchChildAge(baby_id) {
            try {
                const formData = new FormData();
                formData.append('baby_id', baby_id);
                const response = await fetch('/ebakunado/php/supabase/users/get_child_details.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success' && data.data.length > 0) {
                    const child = data.data[0];
                    const ageText = child.age == 0 ? child.weeks_old + ' weeks old' : child.age + ' years old';
                    document.getElementById('childAge').textContent = ageText;
                    // Normalize gender from API (uses child_gender key)
                    const rawGender = child.child_gender || child.gender || '';
                    let prettyGender = 'Unknown';
                    if (rawGender) {
                        const g = String(rawGender).toLowerCase();
                        if (g.startsWith('m')) prettyGender = 'Male';
                        else if (g.startsWith('f')) prettyGender = 'Female';
                        else prettyGender = rawGender; // keep whatever custom value
                    }
                    document.getElementById('childGender').textContent = prettyGender;

                    // Set QR code image
                    if (child.qr_code) {
                        document.getElementById('childQrCode').src = child.qr_code;
                    }
                }
            } catch (error) {
                console.error('Error fetching child age:', error);
                document.getElementById('childAge').textContent = 'Unknown age';
                document.getElementById('childGender').textContent = 'Unknown';
            }
        }

        function renderVaccineCards() {
            const tbody = document.getElementById('scheduleBody');

            if (scheduleData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No immunization records found</td></tr>';
                return;
            }

            // Filter data based on computed status (more reliable than raw record.status)
            let filteredData = scheduleData.filter(record => {
                const status = getVaccineStatus(record);
                if (currentTab === 'upcoming') {
                    // show any non-completed items in Upcoming (upcoming/overdue/missing)
                    return status !== 'completed';
                } else if (currentTab === 'taken') {
                    // only show completed items in Taken
                    return status === 'completed';
                }
                return true;
            });

            if (filteredData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px;">No ${currentTab} vaccines found</td></tr>`;
                return;
            }

            // Group by vaccine name and dose
            const groupedVaccines = {};
            filteredData.forEach(record => {
                const key = `${record.vaccine_name}_${record.dose_number}`;
                if (!groupedVaccines[key]) {
                    groupedVaccines[key] = record;
                }
            });

            let rowsHTML = '';
            Object.values(groupedVaccines).forEach(record => {
                const status = getVaccineStatus(record);
                const statusText = getStatusText(record, status);

                // Format dates - prioritize batch date
                const guidelineDate = record.schedule_date ? formatDate(record.schedule_date) : 'Not scheduled';
                const batchDate = record.batch_schedule_date ? formatDate(record.batch_schedule_date) : '-';
                const catchUpDate = record.catch_up_date ? formatDate(record.catch_up_date) : '-';

                rowsHTML += `
                <tr>
                    <td>${record.vaccine_name}</td>
                    <td>${getDoseText(record.dose_number)}</td>
                    <td>${guidelineDate}</td>
                    <td>${batchDate}</td>
                    <td>${catchUpDate}</td>
                    <td style="color: ${getStatusColor(status)}">${statusText}</td>
                </tr>
                    `;
            });

            tbody.innerHTML = rowsHTML;
        }

        function getVaccineStatus(record) {
            const today = new Date().toISOString().split('T')[0];
            // Prioritize batch_schedule_date over schedule_date
            const targetDate = record.batch_schedule_date || record.schedule_date || record.catch_up_date || '';

            if (record.status === 'completed' || record.status === 'taken') {
                return 'completed';
            } else if (record.status === 'missed' || (targetDate && targetDate < today)) {
                return 'overdue';
            } else if (targetDate && targetDate >= today) {
                return 'upcoming';
            } else {
                return 'missing';
            }
        }

        function getStatusClass(status) {
            switch (status) {
                case 'completed':
                    return 'completed';
                case 'overdue':
                    return 'overdue';
                case 'upcoming':
                    return 'upcoming';
                default:
                    return 'missing';
            }
        }

        function getStatusColor(status) {
            switch (status) {
                case 'completed':
                    return '#28a745';
                case 'overdue':
                    return '#dc3545';
                case 'upcoming':
                    return '#ffc107';
                default:
                    return '#6c757d';
            }
        }

        function getStatusIcon(status) {
            switch (status) {
                case 'completed':
                    return '✓';
                case 'overdue':
                    return '!';
                case 'upcoming':
                    return '↑';
                default:
                    return '?';
            }
        }

        function getStatusText(record, status) {
            const guidelineDate = record.schedule_date ? formatDate(record.schedule_date) : null;
            const batchDate = record.batch_schedule_date ? formatDate(record.batch_schedule_date) : null;
            // Prioritize batch date for display
            const targetDisplay = batchDate || guidelineDate || '';
            switch (status) {
                case 'completed':
                    return record.date_given ? `Completed ${formatDate(record.date_given)}` : 'Completed';
                case 'overdue':
                    return targetDisplay ? `Overdue ${targetDisplay}${batchDate ? ' (Batch)' : ''}` : 'Overdue';
                case 'upcoming':
                    return targetDisplay ? `Upcoming ${targetDisplay}${batchDate ? ' (Batch)' : ''}` : 'Upcoming';
                default:
                    return 'Missing Previous Dose';
            }
        }

        function getDoseText(doseNumber) {
            const doseMap = {
                1: '1st Dose',
                2: '2nd Dose',
                3: '3rd Dose',
                4: '4th Dose'
            };
            return doseMap[doseNumber] || `Dose ${doseNumber}`;
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function switchTab(tab) {
            currentTab = tab;

            // Update tab buttons styling
            const upcomingBtn = document.getElementById('upcomingTab');
            const takenBtn = document.getElementById('takenTab');

            // Use segmented control active class and aria attributes
            upcomingBtn.classList.toggle('is-active', tab === 'upcoming');
            takenBtn.classList.toggle('is-active', tab === 'taken');
            upcomingBtn.setAttribute('aria-selected', String(tab === 'upcoming'));
            takenBtn.setAttribute('aria-selected', String(tab === 'taken'));

            // Re-render table
            renderVaccineCards();
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadImmunizationSchedule();

            // Add tab button event listeners
            document.getElementById('upcomingTab').addEventListener('click', function() {
                switchTab('upcoming');
            });

            document.getElementById('takenTab').addEventListener('click', function() {
                switchTab('taken');
            });

            // QR is now a fixed image (non-clickable). The QR src is set in `fetchChildAge()`
        });
    </script>
</body>

</html>