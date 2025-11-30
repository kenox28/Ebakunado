<?php
session_start();

// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login");
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
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css?v=1.0.3" />
    <link rel="stylesheet" href="css/header.css?v=1.0.2" />
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.5" />

    <link rel="stylesheet" href="css/notification-style.css?v=1.0.1" />
    <link rel="stylesheet" href="css/skeleton-loading.css?v=1.0.1" />
    <link rel="stylesheet" href="css/user/dashboard.css?v=1.0.2" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="dashboard-overview">
            <div class="page-header">
                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Welcome, <?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?>! Parent Dashboard – Track and monitor your child's immunization records with ease.</p>
            </div>

            <div class="card-wrapper">
                <div class="card card-1">
                    <div class="card-top">
                        <div class="card-info">
                            <p class="card-title">Total Children</p>
                            <p class="card-number" id="totalChildren">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">child_care</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <a class="card-link" href="#">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-2">
                    <div class="card-top">
                        <div class="card-info">
                            <p class="card-title">Approved CHR Requests</p>
                            <p class="card-number" id="approvedChr">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">hourglass_top</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <a class="card-link" href="#">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-3">
                    <div class="card-top">
                        <div class="card-info">
                            <p class="card-title">Missed/Delayed Immunizations</p>
                            <p class="card-number" id="missedCount">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">warning</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <a class="card-link" href="#">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
                    </div>
                </div>

                <div class="card card-4">
                    <div class="card-top">
                        <div class="card-info">
                            <p class="card-title">Upcoming Schedule for Today</p>
                            <p class="card-number" id="todaySchedule">0</p>
                        </div>
                        <div class="card-icon">
                            <span class="material-symbols-rounded">vaccines</span>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <a class="card-link" href="#">
                            <span class="material-symbols-rounded">visibility</span>
                            View Details
                        </a>
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

            <!-- QR Modal Popup -->
            <div id="qrModal" class="qr-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
                <div class="qr-modal-content" role="document">
                    <div class="qr-modal-header">
                        <h3 id="qrModalChildName" class="qr-modal-title">QR Code</h3>
                        <button id="closeQrModal" type="button" aria-label="Close" class="qr-modal-close">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <div class="qr-modal-body">
                        <img id="qrModalImage" class="qr-modal-img" src="" alt="QR Code" />
                        <div class="qr-modal-note">
                            <span>Present this QR code at Vaccination appointments</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>


    </main>

    <script src="js/header-handler/profile-menu.js?v=1.0.4" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js?v1.0.2" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script>
        let currentFilter = 'upcoming';
        const qrModalEl = document.getElementById('qrModal');
        const qrModalImageEl = document.getElementById('qrModalImage');
        // Header title element now uses id 'qrModalChildName'
        const qrModalTitleEl = document.getElementById('qrModalChildName');
        // No separate in-body child name element anymore
        const qrModalNameEl = null;
        let qrModalHideTimer = null;
        const QR_MODAL_TRANSITION_MS = 200;

        function isQrModalVisible() {
            return qrModalEl && qrModalEl.classList.contains('is-visible');
        }

        function toggleBodyScrollForModal(disable) {
            document.body.classList.toggle('qr-modal-open', disable);
        }

        async function fetchSummary(filter = null) {
            const url = filter ? `php/supabase/users/get_children_summary.php?filter=${encodeURIComponent(filter)}` :
                `php/supabase/users/get_children_summary.php`;
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
            // Apply skeleton cards instead of spinner
            if (typeof applyChildrenListSkeleton === 'function') {
                applyChildrenListSkeleton(list, 6, label);
            } else {
                list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"loading\"><div class=\"spinner\"></div><p>Loading...</p></div>`;
            }
            try {
                const resp = await fetchSummary(filter);
                if (resp && resp.status === 'success') {
                    renderFilteredList(resp.data.items || []);
                } else {
                    list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"no-data\"><p>Something went wrong</p><small>Please try again.</small></div>`;
                }
            } catch (e) {
                list.innerHTML = `<div class=\"children-list-label\">${label}</div><div class=\"no-data\"><p>Something went wrong</p><small>Please try again.</small></div>`;
            }
            // also refresh counts in background
            refreshCounts();
        }

        function renderFilteredList(items) {
            const list = document.getElementById('childrenList');
            const label = currentFilter === 'missed' ? 'Missed Immunizations' : 'Upcoming Immunizations';
            if (!items || items.length === 0) {
                list.innerHTML = `<div class="children-list-label">${label}</div><div class="no-data"><p>No children registered yet</p></div>`;
                return;
            }
            let html = '';
            html += `<div class="children-list-label">${label}</div>`;
            items.forEach(it => {
                const name = it.name || 'Unknown Child';
                const safeName = String(name).replace(/'/g, "\\'");
                const actualDate = it.upcoming_date ? formatDate(it.upcoming_date) : (currentFilter === 'upcoming' ? 'No date' : '');
                const guidelineDate = it.upcoming_guideline ? formatDate(it.upcoming_guideline) : null;
                const batchDate = it.upcoming_batch ? formatDate(it.upcoming_batch) : null;
                const vaccine = it.upcoming_vaccine || '';
                const qrCodeUrl = it.qr_code || '';
                const safeQrCode = String(qrCodeUrl).replace(/'/g, "\\'");

                // Build missed details HTML if showing missed immunizations (show only closest missed)
                let missedDetailsHtml = '';
                if (currentFilter === 'missed' && it.closest_missed) {
                    const detail = it.closest_missed;
                    const scheduleDate = detail.schedule_date ? formatDate(detail.schedule_date) : 'Not scheduled';
                    const batchMissed = detail.batch_schedule_date ? formatDate(detail.batch_schedule_date) : null;
                    const catchUpDate = detail.catch_up_date ? formatDate(detail.catch_up_date) : '-';
                    missedDetailsHtml = `
                     <div class="missed-detail">
                         <div class="missed-meta">
                             <strong>${detail.vaccine_name} (Dose ${detail.dose_number})</strong><br>
                             <span class="text-muted">Guideline: ${scheduleDate}</span><br>
                             ${batchMissed ? `<span class="text-muted">Batch: ${batchMissed}</span><br>` : ''}
                             <span class="text-danger">Catch Up: ${catchUpDate}</span>
                         </div>
                         ${it.missed_count > 1 ? `<div class="more-missed">...and ${it.missed_count - 1} more missed vaccination(s)</div>` : ''}
                     </div>
                 `;
                }

                const badge = currentFilter === 'missed' ? `<span class="child-vaccine missed-badge">Missed: ${it.missed_count||0}</span>` : (vaccine ? `<span class="child-vaccine">${vaccine}</span>` : '');
                const qrButton = qrCodeUrl ?
                    `<button class="qr-btn" onclick="showQrModal('${safeQrCode}', '${safeName}')"><img class="qr-img" src="${qrCodeUrl}" alt="QR Code"></button>` :
                    `<div class="qr-placeholder"><span class="material-symbols-rounded">qr_code_2</span></div>`;
                const qrBlock = `<div class="qr-area">${qrButton}</div>`;
                const variantClass = currentFilter === 'missed' ? 'is-missed' : 'is-upcoming';
                html += `
                <div class="child-list-item ${variantClass}">
                    <div class="child-item-body">
                        <div class="child-details">
                        <h3 class="child-name">${name}</h3>
                        <div class="child-name-border"></div>
                        ${currentFilter==='upcoming' ? `
                            <p class="child-schedule"><strong>Next:</strong> ${actualDate}${batchDate ? ' <span class="batch-badge">(Batch)</span>' : ''}</p>
                            ${batchDate ? `<p class="child-schedule child-schedule--batch"><strong>Batch Date:</strong> ${batchDate}</p>` : ''}
                            ${guidelineDate ? `<p class="child-schedule child-schedule--guideline">Guideline: ${guidelineDate}</p>` : ''}
                        ` : ''}
                        ${badge}
                        ${missedDetailsHtml}
                        </div>
                        ${qrBlock}
                    </div>
                    <div class="child-actions">
                        <button class="child-view-btn" onclick="viewChildRecord('${it.baby_id||''}')" ${(it.baby_id?'':'disabled')}>
                            <span class="material-symbols-rounded">visibility</span>
                            <span>View</span>
                        </button>
                        <button class="child-schedule-btn" onclick="viewSchedule('${it.baby_id||''}')" ${(it.baby_id?'':'disabled')}>
                            <span class="material-symbols-rounded">schedule</span>
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
                // Card numbers show skeleton shimmer (set on page load); fetch and replace with values

                // OPTIMIZATION: Load both API calls in parallel instead of sequentially (faster!)
                const [childrenResponse, summaryResponse] = await Promise.all([
                    fetch('php/supabase/users/get_accepted_child.php'),
                    fetch('php/supabase/users/get_dashboard_summary.php')
                ]);

                const [childrenData, summaryData] = await Promise.all([
                    childrenResponse.json(),
                    summaryResponse.json()
                ]);

                if (childrenData.status === 'success') {
                    // Calculate statistics from children data
                    calculateStatsFromChildren(childrenData.data || [], summaryData);
                } else {
                    // Set default values when request failed
                    if (typeof setDashboardCardNumbers === 'function') {
                        setDashboardCardNumbers({
                            totalChildren: 0,
                            approvedChr: 0,
                            missedCount: 0,
                            todaySchedule: 0,
                        });
                    } else {
                        document.getElementById('totalChildren').textContent = '0';
                        document.getElementById('approvedChr').textContent = '0';
                        document.getElementById('missedCount').textContent = '0';
                        document.getElementById('todaySchedule').textContent = '0';
                    }
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);

                // Set default stats
                if (typeof setDashboardCardNumbers === 'function') {
                    setDashboardCardNumbers({
                        totalChildren: 0,
                        approvedChr: 0,
                        missedCount: 0,
                        todaySchedule: 0,
                    });
                } else {
                    document.getElementById('totalChildren').textContent = '0';
                    document.getElementById('approvedChr').textContent = '0';
                    document.getElementById('missedCount').textContent = '0';
                    document.getElementById('todaySchedule').textContent = '0';
                }
            }
        }

        function calculateStatsFromChildren(children, summaryData) {
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

            // Get approved CHR count from summary data (already loaded in parallel)
            const approvedChr = (summaryData && summaryData.status === 'success') ?
                summaryData.data.approved_chr_documents || 0 :
                0;

            // Use today's schedule count from summary if available (more accurate)
            const todaySchedule = (summaryData && summaryData.status === 'success') ?
                summaryData.data.upcoming_schedule_today || totalToday :
                totalToday;

            // Update the stats display using skeleton helper if available
            if (typeof setDashboardCardNumbers === 'function') {
                setDashboardCardNumbers({
                    totalChildren: totalChildren,
                    approvedChr: approvedChr,
                    missedCount: totalMissed,
                    todaySchedule: todaySchedule,
                });
            } else {
                document.getElementById('totalChildren').textContent = totalChildren;
                document.getElementById('approvedChr').textContent = approvedChr;
                document.getElementById('missedCount').textContent = totalMissed;
                document.getElementById('todaySchedule').textContent = todaySchedule;
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
                const actualSchedule = child.schedule_date ? formatDate(child.schedule_date) : 'No upcoming schedule';
                const guidelineSchedule = child.next_guideline_date ? formatDate(child.next_guideline_date) : null;
                const batchSchedule = child.next_batch_date ? formatDate(child.next_batch_date) : null;
                const vaccineName = child.vaccine || 'No vaccine scheduled';
                const qrCodeUrl = child.qr_code || '';
                const safeQrCode = String(qrCodeUrl).replace(/'/g, "\\'");
                const safeChildName = String(fullName).replace(/'/g, "\\'");
                const qrHtml = qrCodeUrl ?
                    `<button class="qr-btn" onclick="showQrModal('${safeQrCode}', '${safeChildName}')"><img class="qr-img" src="${qrCodeUrl}" alt="QR Code"></button>` :
                    `<div class="qr-placeholder"><span class="material-symbols-rounded">qr_code_2</span></div>`;
                const qrBlock = `<div class=\"qr-area\">${qrHtml}</div>`;

                html += `
                <div class="child-list-item is-upcoming">
                        <div class="child-item-body">
                            <div class="child-details">
                            <h3 class="child-name">${fullName}</h3>
                            <p class="child-schedule"><strong>Next:</strong> ${actualSchedule}${batchSchedule ? ' (Batch)' : ''}</p>
                            ${guidelineSchedule ? `<p class="child-schedule child-schedule--guideline">Guideline: ${guidelineSchedule}</p>` : ''}
                            ${batchSchedule ? `<p class="child-schedule child-schedule--batch">Batch: ${batchSchedule}</p>` : ''}
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
                    <p>No children registered yet</p>
                    <small>Start by adding a child record.</small>
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
            window.location.href = `immunizations?baby_id=${encoded}`;
        }

        function viewSchedule(babyId) {
            if (!babyId) return;
            const encoded = encodeURIComponent(String(babyId));
            window.location.href = `upcoming?baby_id=${encoded}`;
        }

        function addChild() {
            window.location.href = 'approved-requests';
        }

        // QR Modal functions
        function showQrModal(qrCodeUrl, fullName = 'Child Name') {
            if (!qrModalEl || !qrModalImageEl || !qrModalTitleEl) return;
            if (qrModalHideTimer) {
                clearTimeout(qrModalHideTimer);
                qrModalHideTimer = null;
            }
            qrModalImageEl.src = qrCodeUrl || '';
            // Set header title to possessive form using the provided fullName
            try {
                const name = String(fullName || 'Child Name');
                const possessive = name.endsWith('s') ? `${name}' QR Code` : `${name}'s QR Code`;
                qrModalTitleEl.textContent = possessive;
            } catch (e) {
                qrModalTitleEl.textContent = `${fullName || 'Child'}'s QR Code`;
            }
            qrModalEl.removeAttribute('hidden');
            requestAnimationFrame(() => {
                qrModalEl.classList.add('is-visible');
                qrModalEl.setAttribute('aria-hidden', 'false');
            });
            toggleBodyScrollForModal(true);
        }

        function closeQrModal() {
            if (!qrModalEl) return;
            qrModalEl.classList.remove('is-visible');
            qrModalEl.setAttribute('aria-hidden', 'true');
            toggleBodyScrollForModal(false);
            qrModalHideTimer = setTimeout(() => {
                if (!qrModalEl.classList.contains('is-visible')) {
                    qrModalEl.setAttribute('hidden', '');
                    if (qrModalImageEl) {
                        qrModalImageEl.removeAttribute('src');
                    }
                }
            }, QR_MODAL_TRANSITION_MS);
        }

        function handleQrModalEscape(event) {
            if (event.key === 'Escape' && isQrModalVisible()) {
                closeQrModal();
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', async function() {
            await refreshCounts();
            setActiveButton();
            // Show initial skeleton immediately
            const initialLabel = 'Upcoming Immunizations';
            if (typeof applyChildrenListSkeleton === 'function') {
                applyChildrenListSkeleton('#childrenList', 6, initialLabel);
            }
            // Apply skeleton shimmer for dashboard card numbers
            if (typeof applyDashboardCardNumbersSkeleton === 'function') {
                applyDashboardCardNumbersSkeleton();
            } else {
                // Fallback to simple placeholders if skeleton utility isn't available
                document.getElementById('totalChildren').textContent = '...';
                document.getElementById('approvedChr').textContent = '...';
                document.getElementById('missedCount').textContent = '...';
                document.getElementById('todaySchedule').textContent = '...';
            }
            selectFilter('upcoming');
            loadDashboardData();

            // Add event listener for close QR modal button
            const closeQrButton = document.getElementById('closeQrModal');
            if (closeQrButton) {
                closeQrButton.addEventListener('click', closeQrModal);
            }

            // Close modal when clicking outside the content
            if (qrModalEl) {
                qrModalEl.addEventListener('click', function(e) {
                    if (e.target === qrModalEl) {
                        closeQrModal();
                    }
                });
            }

            document.addEventListener('keydown', handleQrModalEscape);
        });
    </script>

</body>

</html>