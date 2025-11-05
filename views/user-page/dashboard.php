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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/user/dashboard.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="dashboard section-title">
                <span class="material-symbols-rounded">dashboard</span>
                Dashboard Overview
            </h2>
        </section>
        <section class="dashboard-section">
            <div class="dashboard-overview">
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">child_care</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="totalChildren">0</p>
                            <p class="card-title">Total Children</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-2">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">hourglass_top</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="approvedChr">0</p>
                            <p class="card-title">Approved CHR Requests</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-3">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">warning</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="missedCount">0</p>
                            <p class="card-title">Missed/Delayed Immunizations</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-4">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">vaccines</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="todaySchedule">0</p>
                            <p class="card-title">Upcoming Schedule for Today</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="dashboard-section">
            <div class="children-panel">
                <div class="filter-buttons">
                    <h2 class="section-title">My Children</h2>
                    <div class="segmented-control" role="tablist" aria-label="Immunization filters">
                        <button id="btnUpcoming" class="segmented-btn is-active" role="tab" aria-selected="true" onclick="selectFilter('upcoming')">
                            <!-- <span class="material-symbols-rounded" aria-hidden="true">event_upcoming</span> -->
                            <span>Upcoming</span>
                            <span id="upcomingCount" class="seg-badge">0</span>
                        </button>
                        <button id="btnMissed" class="segmented-btn" role="tab" aria-selected="false" onclick="selectFilter('missed')">
                            <!-- <span class="material-symbols-rounded" aria-hidden="true">warning</span> -->
                            <span>Missed</span>
                            <span id="missedCountBtn" class="seg-badge">0</span>
                        </button>
                    </div>
                </div>
                <div id="childrenList" class="children-list">
                    <!-- Children list will be rendered here -->
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        let currentFilter = 'upcoming';

        async function fetchSummary(filter = null) {
            const url = filter ? `../../php/supabase/users/get_children_summary.php?filter=${encodeURIComponent(filter)}` :
                `../../php/supabase/users/get_children_summary.php`;
            const res = await fetch(url);
            return await res.json();
        }

        async function refreshCounts() {
            try {
                const data = await fetchSummary();
                if (data && data.status === 'success' && data.data) {
                    document.getElementById('upcomingCount').textContent = data.data.upcoming_count || 0;
                    document.getElementById('missedCountBtn').textContent = data.data.missed_count || 0;
                }
            } catch (e) {
                /* silent */
            }
        }

        function setActiveButton() {
            const up = document.getElementById('btnUpcoming');
            const mi = document.getElementById('btnMissed');
            const isUpcoming = currentFilter === 'upcoming';
            up.classList.toggle('is-active', isUpcoming);
            mi.classList.toggle('is-active', !isUpcoming);
            up.setAttribute('aria-selected', String(isUpcoming));
            mi.setAttribute('aria-selected', String(!isUpcoming));
        }

        async function selectFilter(filter) {
            currentFilter = filter;
            setActiveButton();
            const list = document.getElementById('childrenList');
            const label = filter === 'missed' ? 'Missed Immunizations' : 'Upcoming Immunizations';
            list.innerHTML = `<div class="children-list-label">${label}</div><div class=\"loading\"><div class=\"spinner\"></div><p>Loading...</p></div>`;
            try {
                const resp = await fetchSummary(filter);
                if (resp && resp.status === 'success') {
                    renderFilteredList(resp.data.items || []);
                } else {
                    list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"no-data\"><span class=\"material-symbols-rounded icon error\">error</span><p>Failed to load list</p></div>`;
                }
            } catch (e) {
                list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"no-data\"><span class=\"material-symbols-rounded icon error\">error</span><p>Network error</p></div>`;
            }
            // also refresh counts in background
            refreshCounts();
        }

        function renderFilteredList(items) {
            const list = document.getElementById('childrenList');
            const label = currentFilter === 'missed' ? 'Missed Immunizations' : 'Upcoming Immunizations';
            if (!items || items.length === 0) {
                list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"no-data\"><span class=\"material-symbols-rounded icon\">child_care</span><p>No records</p></div>`;
                return;
            }
            let html = '';
            html += `<div class=\"children-list-label\">${label}</div>`;
            items.forEach(it => {
                const name = it.name || 'Unknown Child';
                const upcoming = it.upcoming_date ? formatDate(it.upcoming_date) : (currentFilter === 'upcoming' ? 'No date' : '');
                const vaccine = it.upcoming_vaccine || '';

                // Build missed details HTML if showing missed immunizations (show only closest missed)
                let missedDetailsHtml = '';
                if (currentFilter === 'missed' && it.closest_missed) {
                    const detail = it.closest_missed;
                    const scheduleDate = detail.schedule_date ? formatDate(detail.schedule_date) : 'Not scheduled';
                    const catchUpDate = detail.catch_up_date ? formatDate(detail.catch_up_date) : '-';
                    missedDetailsHtml = `
                     <div class="missed-detail">
                         <div class="missed-meta">
                             <strong>${detail.vaccine_name} (Dose ${detail.dose_number})</strong><br>
                             <span class="text-muted">Scheduled: ${scheduleDate}</span><br>
                             <span class="text-danger">Catch Up: ${catchUpDate}</span>
                         </div>
                         ${it.missed_count > 1 ? `<div class="more-missed">...and ${it.missed_count - 1} more missed vaccination(s)</div>` : ''}
                     </div>
                 `;
                }

                const badge = currentFilter === 'missed' ? `<span class="child-vaccine missed-badge">Missed: ${it.missed_count||0}</span>` : (vaccine ? `<span class="child-vaccine">${vaccine}</span>` : '');
                const qrButton = it.qr_code ?
                    `<button class=\"qr-btn\" onclick=\"showQrModal('${it.qr_code.replace(/'/g, "\\'")}')\"><img class=\"qr-img\" src=\"${it.qr_code}\" alt=\"QR Code\"></button>` :
                    `<div class=\"qr-placeholder\"><span class=\"material-symbols-rounded\">qr_code_2</span></div>`;
                const qrBlock = `<div class=\"qr-area\">${qrButton}</div>`;
                const variantClass = currentFilter === 'missed' ? 'is-missed' : 'is-upcoming';
                html += `
                <div class="child-list-item ${variantClass}">
                    <div class="child-item-body">
                        <div class="child-details">
                        <h3 class="child-name">${name}</h3>
                        ${currentFilter==='upcoming' ? `<p class="child-schedule"><strong>Next:</strong> ${upcoming}</p>` : ''}
                        ${badge}
                        ${missedDetailsHtml}
                        </div>
                        ${qrBlock}
                    </div>
                    <div class="child-actions">
                        <button class="child-view-btn" onclick=\"viewChildRecord('${it.baby_id||''}')\" ${(it.baby_id?'':'disabled')}>
                            <span class=\"material-symbols-rounded\">visibility</span>
                            <span>View</span>
                        </button>
                        <button class="child-schedule-btn" onclick=\"viewSchedule('${it.baby_id||''}')\" ${(it.baby_id?'':'disabled')}>
                            <span class=\"material-symbols-rounded\">schedule</span>
                            <span>View Schedule</span>
                        </button>
                    </div>
                </div>
            `;
            });
            list.innerHTML = html;
        }
        async function loadDashboardData() {
            try {
                // Show loading state
                document.getElementById('totalChildren').textContent = '...';
                document.getElementById('approvedChr').textContent = '...';
                document.getElementById('missedCount').textContent = '...';
                document.getElementById('todaySchedule').textContent = '...';

                // Load children data first (this will give us all the stats we need)
                const childrenResponse = await fetch('../../php/supabase/users/get_accepted_child.php');
                const childrenData = await childrenResponse.json();

                if (childrenData.status === 'success') {
                    // Calculate statistics from children data
                    calculateStatsFromChildren(childrenData.data || []);
                } else {
                    // Set default values when request failed
                    document.getElementById('totalChildren').textContent = '0';
                    document.getElementById('approvedChr').textContent = '0';
                    document.getElementById('missedCount').textContent = '0';
                    document.getElementById('todaySchedule').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);

                // Set default stats
                document.getElementById('totalChildren').textContent = '0';
                document.getElementById('approvedChr').textContent = '0';
                document.getElementById('missedCount').textContent = '0';
                document.getElementById('todaySchedule').textContent = '0';
            }
        }

        function calculateStatsFromChildren(children) {
            let totalChildren = children.length;
            let totalMissed = 0;
            let totalToday = 0;

            // Count missed immunizations and today's schedules
            children.forEach(child => {
                totalMissed += child.missed_count || 0;

                // Check if child has schedule for today
                if (child.schedule_date) {
                    const today = new Date().toISOString().split('T')[0];
                    if (child.schedule_date === today) {
                        totalToday++;
                    }
                }
            });

            // Update the stats display
            document.getElementById('totalChildren').textContent = totalChildren;
            document.getElementById('missedCount').textContent = totalMissed;
            document.getElementById('todaySchedule').textContent = totalToday;

            // Get approved CHR count (we'll load this separately)
            loadApprovedChrCount();
        }

        async function loadApprovedChrCount() {
            try {
                const response = await fetch('../../php/supabase/users/get_dashboard_summary.php');
                const data = await response.json();

                if (data.status === 'success') {
                    document.getElementById('approvedChr').textContent = data.data.approved_chr_documents;
                } else {
                    document.getElementById('approvedChr').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading approved CHR count:', error);
                document.getElementById('approvedChr').textContent = '0';
            }
        }

        function renderChildrenList(children) {
            const list = document.getElementById('childrenList');
            let html = '';

            // Filter to show only accepted children
            const acceptedChildren = children.filter(child => child.status === 'accepted');

            acceptedChildren.forEach(child => {
                const fullName = child.name || 'Unknown Child';
                const babyId = child.baby_id || '';
                const upcomingSchedule = child.schedule_date ? formatDate(child.schedule_date) : 'No upcoming schedule';
                const vaccineName = child.vaccine || 'No vaccine scheduled';
                const qrHtml = child.qr_code ?
                    `<button class=\"qr-btn\" onclick=\"showQrModal('${String(child.qr_code || '').replace(/'/g, "\\'")}')\"><img class=\"qr-img\" src=\"${child.qr_code}\" alt=\"QR Code\"></button>` :
                    `<div class=\"qr-placeholder\"><span class=\"material-symbols-rounded\">qr_code_2</span></div>`;
                const qrBlock = `<div class=\"qr-area\">${qrHtml}</div>`;

                html += `
                <div class="child-list-item is-upcoming">
                        <div class="child-item-body">
                            <div class="child-details">
                            <h3 class="child-name">${fullName}</h3>
                            <p class="child-schedule"><strong>Next:</strong> ${upcomingSchedule}</p>
                            <p class="child-vaccine">${vaccineName}</p>
                        </div>
                            ${qrBlock}
                        </div>
					<div class="child-actions">
                        <button class="child-view-btn" onclick=\"viewChildRecord('${babyId}')\" ${babyId ? '' : 'disabled'}>
                            <span class=\"material-symbols-rounded\">visibility</span>
                            <span>View</span>
                        </button>
                        <button class="child-schedule-btn" onclick=\"viewSchedule('${babyId}')\" ${babyId ? '' : 'disabled'}>
                            <span class=\"material-symbols-rounded\">schedule</span>
                            <span>View Schedule</span>
                        </button>
					</div>
				</div>
			`;
            });

            // Show message if no accepted children
            if (acceptedChildren.length === 0) {
                html = `
                <div class="no-data">
                    <span class="material-symbols-rounded icon">child_care</span>
                    <p>No approved children found</p>
                    <small>Children need to be approved by BHW first</small>
                </div>
			`;
            }

            list.innerHTML = html;
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

        function viewChildRecord(babyId) {
            if (!babyId) return;
            const encoded = encodeURIComponent(String(babyId));
            window.location.href = `child-health-record.php?baby_id=${encoded}`;
        }

        function viewSchedule(babyId) {
            if (!babyId) return;
            const encoded = encodeURIComponent(String(babyId));
            window.location.href = `upcoming_schedule.php?baby_id=${encoded}`;
        }

        function addChild() {
            window.location.href = 'approved-requests.php';
        }

        // QR Modal functions
        function showQrModal(qrCodeUrl) {
            const modal = document.getElementById('qrModal');
            const qrImage = document.getElementById('qrModalImage');
            qrImage.src = qrCodeUrl;
            modal.style.display = 'flex';
        }

        function closeQrModal() {
            const modal = document.getElementById('qrModal');
            modal.style.display = 'none';
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', async function() {
            await refreshCounts();
            setActiveButton();
            selectFilter('upcoming');
            loadDashboardData();

            // Add event listener for close QR modal button
            document.getElementById('closeQrModal').addEventListener('click', closeQrModal);

            // Close modal when clicking outside
            document.getElementById('qrModal').addEventListener('click', function(e) {
                if (e.target.id === 'qrModal') {
                    closeQrModal();
                }
            });
        });
    </script>
</body>

</html>