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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Immunization Schedule</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css?v=1.0.1" />
    <link rel="stylesheet" href="css/header.css?v=1.0.2" />
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.1" />

    <link rel="stylesheet" href="css/notification-style.css?v=1.0.1" />
    <link rel="stylesheet" href="css/skeleton-loading.css?v=1.0.1" />
    <link rel="stylesheet" href="css/user/child-upcoming-schedule.css?v=1.0.5" />
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
                            <a id="switchChildBtn" class="btn-switch" title="Switch Child" href="dashboard">
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
                            <div id="qrPlaceholder" class="qr-placeholder" aria-hidden="false" title="QR not available">
                                <span class="material-symbols-rounded" aria-hidden="true">qr_code</span>
                            </div>
                            <img id="childQrCode" src="" alt="QR Code" style="display:none;" />
                        </div>
                    <div class="qr-actions">
                        <button id="downloadQrBtn" class="btn-download-qr" type="button" title="Download QR Code">
                            <span class="material-symbols-rounded" aria-hidden="true">download</span>
                            <span>Download QR</span>
                        </button>
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
                    <table class="schedule-table data-table" aria-describedby="scheduleDescription">
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

    <script src="js/header-handler/profile-menu.js?v=1.0.4" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script>
        const DEFAULT_QR_PLACEHOLDER = 'data:image/svg+xml;utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 220 220"%3E%3Crect width="100%25" height="100%25" fill="%23ffffff"/%3E%3Crect x="12" y="12" width="58" height="58" fill="%23e6e6e6"/%3E%3Crect x="150" y="12" width="58" height="58" fill="%23e6e6e6"/%3E%3Crect x="12" y="150" width="58" height="58" fill="%23e6e6e6"/%3E%3Crect x="84" y="84" width="20" height="20" fill="%23bdbdbd"/%3E%3Ctext x="50%25" y="88%25" font-size="12" text-anchor="middle" fill="%236b7280" font-family="Arial, Helvetica, sans-serif"%3ENo QR available%3C/text%3E%3C/svg%3E';

        let scheduleData = [];
        let currentTab = 'upcoming';
        let currentChild = null;
        const urlParams = new URLSearchParams(window.location.search);
        const selectedBabyId = urlParams.get('baby_id') || '';

        async function loadImmunizationSchedule() {
            try {
                const endpoint = 'php/supabase/users/get_immunization_schedule.php' + (selectedBabyId ? ('?baby_id=' + encodeURIComponent(selectedBabyId)) : '');
                const response = await fetch(endpoint);
                const data = await response.json();

                if (data.status === 'success') {
                    scheduleData = Array.isArray(data.data) ? data.data : [];
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

        function getScheduleColsConfig() {
            return [
                { type: 'text', widthClass: 'skeleton-col-2' }, // Vaccine
                { type: 'text', widthClass: 'skeleton-col-3' }, // Dose
                { type: 'text', widthClass: 'skeleton-col-4' }, // Guideline
                { type: 'text', widthClass: 'skeleton-col-4' }, // Batch
                { type: 'text', widthClass: 'skeleton-col-4' }, // Catch Up
                { type: 'pill', widthClass: 'skeleton-col-6' }  // Status
            ];
        }

        function setQrImageSrc(src) {
            const img = document.getElementById('childQrCode');
            const placeholder = document.getElementById('qrPlaceholder');
            if (!img || !placeholder) return;
            if (!src || src === DEFAULT_QR_PLACEHOLDER) {
                img.removeAttribute('src');
                img.style.display = 'none';
                placeholder.style.display = 'flex';
            } else {
                img.src = src;
                img.style.display = 'block';
                placeholder.style.display = 'none';
            }
        }

        function loadChildData() {
            if (scheduleData.length === 0) {
                document.getElementById('scheduleBody').innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">No immunization records found</td></tr>';
                document.getElementById('childName').textContent = 'Unknown Child';
                document.getElementById('childAge').textContent = 'Unknown age';
                document.getElementById('childGender').textContent = 'Unknown';
                const qrElEmpty = document.getElementById('childQrCode');
                if (qrElEmpty) qrElEmpty.src = DEFAULT_QR_PLACEHOLDER;
                return;
            }
            const firstRecord = scheduleData[0];
            currentChild = firstRecord;
            const childName = firstRecord.child_name || 'Unknown Child';
            document.getElementById('childName').textContent = childName;
            try {
                document.title = childName;
            } catch (e) {
            }
            fetchChildAge(firstRecord.baby_id);
            try {
                const qrUrl = firstRecord.qr_code || firstRecord.qr || firstRecord.qr_url || firstRecord.qr_image || firstRecord.qrcode || null;
                setQrImageSrc(qrUrl && qrUrl.length ? qrUrl : null);
            } catch (e) {
            }
        }

        async function fetchChildAge(baby_id) {
            try {
                const formData = new FormData();
                formData.append('baby_id', baby_id);
                const response = await fetch('php/supabase/users/get_child_details.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success' && data.data.length > 0) {
                    const child = data.data[0];
                    const ageText = child.age == 0 ? child.weeks_old + ' weeks old' : child.age + ' years old';
                    document.getElementById('childAge').textContent = ageText;
                    const rawGender = child.child_gender || child.gender || '';
                    let prettyGender = 'Unknown';
                    if (rawGender) {
                        const g = String(rawGender).toLowerCase();
                        if (g.startsWith('m')) prettyGender = 'Male';
                        else if (g.startsWith('f')) prettyGender = 'Female';
                        else prettyGender = rawGender; // keep whatever custom value
                    }
                    document.getElementById('childGender').textContent = prettyGender;

                    if (child.qr_code) {
                        setQrImageSrc(child.qr_code);
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
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No immunization records found</td></tr>';
                return;
            }

            let filteredData = scheduleData.filter(record => {
                const status = getVaccineStatus(record);
                if (currentTab === 'upcoming') {
                    return status !== 'completed';
                } else if (currentTab === 'taken') {
                    return status === 'completed';
                }
                return true;
            });

            if (filteredData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">No ${currentTab} vaccines found</td></tr>`;
                return;
            }

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

                const guidelineDate = record.schedule_date ? formatDate(record.schedule_date) : 'Not scheduled';
                const batchDate = record.batch_schedule_date ? formatDate(record.batch_schedule_date) : '-';
                const catchUpDate = record.catch_up_date ? formatDate(record.catch_up_date) : '-';

                let statusChipClass = 'chip--default';
                switch (status) {
                    case 'completed':
                        statusChipClass = 'chip--completed';
                        break;
                    case 'overdue':
                        statusChipClass = 'chip--missed';
                        break;
                    case 'upcoming':
                        statusChipClass = 'chip--scheduled';
                        break;
                    default:
                        statusChipClass = 'chip--default';
                }

                rowsHTML += `
                <tr>
                    <td>${record.vaccine_name}</td>
                    <td>${getDoseText(record.dose_number)}</td>
                    <td>${guidelineDate}</td>
                    <td>${batchDate}</td>
                    <td>${catchUpDate}</td>
                    <td><span class="chip ${statusChipClass}">${statusText}</span></td>
                </tr>
                    `;
            });

            tbody.innerHTML = rowsHTML;
        }

        function getVaccineStatus(record) {
            const today = new Date().toISOString().split('T')[0];
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

        function getStatusText(record, status) {
            const guidelineDate = record.schedule_date ? formatDate(record.schedule_date) : null;
            const batchDate = record.batch_schedule_date ? formatDate(record.batch_schedule_date) : null;
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

            const upcomingBtn = document.getElementById('upcomingTab');
            const takenBtn = document.getElementById('takenTab');

            upcomingBtn.classList.toggle('is-active', tab === 'upcoming');
            takenBtn.classList.toggle('is-active', tab === 'taken');
            upcomingBtn.setAttribute('aria-selected', String(tab === 'upcoming'));
            takenBtn.setAttribute('aria-selected', String(tab === 'taken'));

            renderVaccineCards();
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof applyTableSkeleton === 'function') {
                try {
                    applyTableSkeleton('#scheduleBody', getScheduleColsConfig(), 8);
                } catch (e) {
                    console.warn('Failed to apply table skeleton:', e);
                }
            }
            loadImmunizationSchedule();

            document.getElementById('upcomingTab').addEventListener('click', function() {
                switchTab('upcoming');
            });

            document.getElementById('takenTab').addEventListener('click', function() {
                switchTab('taken');
            });

            const downloadBtn = document.getElementById('downloadQrBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', async function () {
                    const img = document.getElementById('childQrCode');
                    const src = img && img.src && img.style.display !== 'none' ? img.src : '';
                    if (!src) {
                        alert('QR code not available');
                        return;
                    }
                    downloadBtn.disabled = true;
                    const originalText = downloadBtn.innerHTML;
                    try {
                        const res = await fetch(src, { credentials: 'same-origin' });
                        if (!res.ok) throw new Error('Failed to fetch image');
                        const blob = await res.blob();

                        const qrImg = await new Promise((resolve, reject) => {
                            const i = new Image();
                            i.onload = () => resolve(i);
                            i.onerror = reject;
                            i.src = URL.createObjectURL(blob);
                        });

                        const logoSrc = 'assets/images/ebakunado-logo-without-label.png';
                        let logoImg = null;
                        try {
                            logoImg = await new Promise((resolve, reject) => {
                                const li = new Image();
                                li.onload = () => resolve(li);
                                li.onerror = () => resolve(null); // not fatal
                                li.src = logoSrc;
                            });
                        } catch (_) { logoImg = null; }

                        const qrWidth = qrImg.width;
                        const headerHeight = Math.max(72, Math.round(qrWidth * 0.18));
                        const footerHeight = Math.max(48, Math.round(qrWidth * 0.12));
                        const spacing = 16;

                        const canvas = document.createElement('canvas');
                        canvas.width = qrWidth;
                        canvas.height = headerHeight + qrImg.height + footerHeight;
                        const ctx = canvas.getContext('2d');

                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);

                        const headerY = Math.round(headerHeight / 2);
                        const paddingX = 16;
                        if (logoImg && logoImg.naturalWidth) {
                            const logoH = Math.min(headerHeight - 20, logoImg.naturalHeight);
                            const logoW = Math.round((logoH / logoImg.naturalHeight) * logoImg.naturalWidth);
                            const logoY = Math.round((headerHeight - logoH) / 2);
                            const logoLeftX = paddingX;
                            ctx.drawImage(logoImg, logoLeftX, logoY, logoW, logoH);
                            const logoRightX = Math.max(paddingX, canvas.width - paddingX - logoW);
                            ctx.drawImage(logoImg, logoRightX, logoY, logoW, logoH);
                        }

                        const hc = (doc => doc)();
                        const healthCenterText = 'Linao Health Center';
                        const barangayText = 'Barangay Linao, Ormoc City';
                        const titleText = 'QR Code for Child Immunization Record';

                        const mainFontSize = Math.max(16, Math.round(canvas.width * 0.045));
                        const subFontSize = Math.max(12, Math.round(canvas.width * 0.03));
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const centerX = canvas.width / 2;
                        ctx.fillStyle = '#006c35';
                        ctx.font = `bold ${mainFontSize}px sans-serif`;
                        const healthY = headerY - (subFontSize * 0.9);
                        ctx.fillText(healthCenterText, centerX, healthY);
                        ctx.fillStyle = '#0f172a';
                        ctx.font = `${subFontSize}px sans-serif`;
                        const barangayY = headerY + (subFontSize * 0.9);
                        ctx.fillText(barangayText, centerX, barangayY);
                        ctx.font = `600 ${Math.max(13, Math.round(canvas.width * 0.028))}px sans-serif`;
                        const titleY = headerY + (subFontSize * 2.6);
                        ctx.fillText(titleText, centerX, titleY);

                        const qrX = 0;
                        const qrY = headerHeight;
                        ctx.drawImage(qrImg, qrX, qrY, qrImg.width, qrImg.height);

                        const childNameText = (document.getElementById('childName')?.textContent || 'Child').trim();
                        const footerYCenter = headerHeight + qrImg.height + (footerHeight / 2);
                        ctx.fillStyle = '#111827';
                        ctx.font = `bold ${Math.max(14, Math.round(canvas.width * 0.032))}px sans-serif`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(`Name: ${childNameText}`, centerX, footerYCenter - 8);
                        ctx.font = `${Math.max(12, Math.round(canvas.width * 0.028))}px sans-serif`;
                        const dateText = new Date().toLocaleDateString();
                        ctx.fillText(`Downloaded: ${dateText}`, centerX, footerYCenter + 16);

                        const composedBlob = await new Promise((res) => canvas.toBlob(res, 'image/png'));
                        const url = URL.createObjectURL(composedBlob);
                        const a = document.createElement('a');
                        const childFileName = childNameText.replace(/\s+/g, '_') || 'child';
                        a.href = url;
                        a.download = `QR_${childFileName}.png`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                        try { URL.revokeObjectURL(qrImg.src); } catch (_) {}
                    } catch (err) {
                        console.error('QR download composition failed:', err);
                        window.open(src, '_blank');
                    } finally {
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>
</body>

</html>