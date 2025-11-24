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
    <title>Child Health Record Lists</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/bhw/child-health-list.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section>
            <h2 class="section-title">
                <span class="material-symbols-rounded">list_alt</span>
                Child Health Record Lists
            </h2>
        </section>
        <section class="child-health-list-section">
            <div class="filters-bar">
                <div class="filters-header">
                    <span class="material-symbols-rounded" aria-hidden="true">tune</span>
                    <span>Filters:</span>
                </div>
                <div class="filters">
                    <div class="select-with-icon">
                        <span class="material-symbols-rounded" aria-hidden="true">search</span>
                        <input id="chlSearchInput" type="text" placeholder="Search by name, mother, address" />
                    </div>
                    <div class="select-with-icon">
                        <span class="material-symbols-rounded" aria-hidden="true">location_on</span>
                        <input id="chlPurok" type="text" placeholder="e.g. Purok 1" />
                    </div>
                    <button id="chlApplyFiltersBtn" class="btn btn-primary" type="button">Apply</button>
                    <button id="chlClearFiltersBtn" class="btn btn-secondary" type="button">Clear</button>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover" id="childhealthrecord">
                    <thead>
                        <tr>
                            <th>Fullname</th>
                            <th>Birth Date</th>
                            <th>Place of Birth</th>
                            <th>Mother</th>
                            <th>Address</th>
                            <th>Schedule</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="childhealthrecordBody">
                        <tr class="skeleton-row">
                            <td><div class="skeleton skeleton-text skeleton-col-1"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-4"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-5"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-btn skeleton-col-6"></div></td>
                            <td>
                                <div class="skeleton-actions-pair">
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="skeleton-row">
                            <td><div class="skeleton skeleton-text skeleton-col-1"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-4"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-5"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-btn skeleton-col-6"></div></td>
                            <td>
                                <div class="skeleton-actions-pair">
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="skeleton-row">
                            <td><div class="skeleton skeleton-text skeleton-col-1"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-4"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-5"></div></td>
                            <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                            <td><div class="skeleton skeleton-btn skeleton-col-6"></div></td>
                            <td>
                                <div class="skeleton-actions-pair">
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="chlPager" class="pager">
                <div id="chlPageInfo" class="page-info">&nbsp;</div>
                <div class="pager-controls">
                    <button id="chlPrevBtn" type="button" class="pager-btn">
                        <span class="material-symbols-rounded">chevron_backward</span>
                        Prev
                    </button>
                    <span id="chlPageButtons" class="page-buttons"></span>
                    <button id="chlNextBtn" type="button" class="pager-btn">
                        Next
                        <span class="material-symbols-rounded">chevron_forward</span>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
        // Column config for skeleton (7 visible columns)
        function getChildHealthListColsConfig() {
            return [
                { type: 'text', widthClass: 'skeleton-col-1' }, // Fullname
                { type: 'text', widthClass: 'skeleton-col-3' }, // Birth Date
                { type: 'text', widthClass: 'skeleton-col-4' }, // Place of Birth
                { type: 'text', widthClass: 'skeleton-col-5' }, // Mother
                { type: 'text', widthClass: 'skeleton-col-3' }, // Address
                { type: 'btn',  widthClass: 'skeleton-col-6' }, // Schedule
                { type: 'btn',  widthClass: 'skeleton-col-6' }  // Action (paired buttons via post-adjust)
            ];
        }
        // Date formatting helper: Mon D, YYYY
        function formatDate(dateStr){
            if(!dateStr) return '';
            const d = new Date(dateStr);
            if(isNaN(d.getTime())) return dateStr; // fallback if invalid
            return d.toLocaleDateString(undefined,{ month:'short', day:'numeric', year:'numeric'});
        }
        // --- Status chips (use main.css .chip styles, with dynamic fallback like CHR) ---
        (function initChipRegistry(){
            if (!window.__CHIP_STYLE_REG__) window.__CHIP_STYLE_REG__ = new Set();
        })();

        function sanitizeStatus(val){
            return String(val || '').trim().toLowerCase();
        }

        function escapeHtml(str){
            return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
        }

        function ensureChipStyle(variant, colors){
            const key = 'chip--' + variant;
            const reg = window.__CHIP_STYLE_REG__;
            if (reg && reg.has(key)) return;
            const style = document.createElement('style');
            const { bg, fg, bd } = colors || {};
            const css = `.chip--${variant}{background:${bg||'#f3f4f6'};color:${fg||'#374151'};border:1px solid ${bd||'#e5e7eb'};}`;
            style.textContent = css;
            document.head.appendChild(style);
            if (reg) reg.add(key);
        }

        function statusVariantFromText(status){
            const s = sanitizeStatus(status);
            if (!s) return 'default';
            // Map common keywords to existing variants from main.css
            if (s.includes('taken') || s.includes('given') || s === 'done') return 'taken';
            if (s.includes('upcoming') || s.includes('due') || s.includes('scheduled')) return 'upcoming';
            if (s.includes('missed') || s.includes('overdue')) return 'missed';
            if (s.includes('complete')) return 'completed';
            if (s.includes('transfer')) return 'transferred';
            // Additional statuses we want styled similarly to CHR dynamic approach
            if (s === 'pending' || s === 'for approval' || s === 'awaiting') return 'pending';
            if (s === 'accepted') return 'accepted';
            if (s === 'approved' || s === 'active') return 'approved';
            if (s === 'rejected' || s === 'inactive' || s === 'declined') return 'rejected';
            return 'default';
        }

        function renderStatusChip(status){
            const raw = (status == null ? '' : String(status));
            const variant = statusVariantFromText(raw);
            // Inject dynamic styles for new variants not present in main.css
            if (variant === 'pending') {
                ensureChipStyle('pending', { bg: '#fff4e5', fg: '#8a5a00', bd: '#ffd8a8' });
            } else if (variant === 'approved') {
                ensureChipStyle('approved', { bg: '#e6f4ea', fg: '#137333', bd: '#b7dec2' });
            } else if (variant === 'rejected') {
                ensureChipStyle('rejected', { bg: '#fdecea', fg: '#b3261e', bd: '#f5c6c3' });
            }
            // Known variants (taken, upcoming, missed, completed, transferred, default) already exist in main.css
            return `<span class="chip chip--${variant}">${escapeHtml(raw)}</span>`;
        }
        // pager CSS
        (function() {
            const s = document.createElement('style');
            s.textContent = '.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:chlSpin .8s linear infinite}@keyframes chlSpin{to{transform:rotate(360deg)}}';
            document.head.appendChild(s);
        })();

        let chlPage = 1;
        const chlLimit = 10;

        async function getChildHealthRecord(page = 1, opts = {}) {
            const body = document.querySelector('#childhealthrecordBody');
            const keep = opts.keep === true;
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            const pageSpan = document.getElementById('chlPageButtons');
            const pageInfoEl = document.getElementById('chlPageInfo');

            // Always show pager spinner while fetching (like pending-approval)
            if (pageSpan) pageSpan.innerHTML = '<span class="pager-spinner" aria-label="Loading" role="status"></span>';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            // Ensure a neutral page-info is visible during loading (parity with immunization & pending-approval)
            if (pageInfoEl && (!pageInfoEl.textContent || pageInfoEl.textContent === '\u00A0')) {
                pageInfoEl.textContent = 'Showing 0-0 of 0 entries';
            }

            if (!keep) {
                if (typeof applyTableSkeleton === 'function') {
                    applyTableSkeleton(body, getChildHealthListColsConfig(), chlLimit);
                    // Post-adjust: make last column show two skeleton buttons side-by-side
                    try {
                        body.querySelectorAll('tr.skeleton-row').forEach(tr => {
                            const lastTd = tr.querySelector('td:last-child');
                            if (lastTd) {
                                lastTd.innerHTML = '<div class="skeleton-actions-pair"><div class="skeleton skeleton-btn skeleton-col-6"></div><div class="skeleton skeleton-btn skeleton-col-6"></div></div>';
                            }
                        });
                    } catch (_) {}
                }
                // Removed fallback "Loading..." text; rely on static skeleton rows if utility unavailable.
            }
            try {
                const qs = new URLSearchParams({
                    page: String(page),
                    limit: String(chlLimit)
                });
                const searchVal = (document.getElementById('chlSearchInput')?.value || '').trim();
                const purokVal = (document.getElementById('chlPurok')?.value || '').trim();
                if (searchVal) qs.set('search', searchVal);
                if (purokVal) qs.set('purok', purokVal);

                const res = await fetch('../../php/supabase/bhw/get_child_health_record.php?' + qs.toString());
                const data = await res.json();
                if (data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', { colspan: 7, kind: 'error' });
                    } else {
                        body.innerHTML = '<tr class="message-row error"><td colspan="7">Failed to load data. Please try again.</td></tr>';
                    }
                    updateChlPagination(0, page, chlLimit, false);
                    return;
                }
                const rowsData = Array.isArray(data.data) ? data.data : [];
                if (rowsData.length === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'No records found', { colspan: 7 });
                    } else {
                        body.innerHTML = '<tr class="message-row"><td colspan="7">No records found</td></tr>';
                    }
                    updateChlPagination(data.total || 0, data.page || page, data.limit || chlLimit, false);
                    return;
                }

                let rows = '';
                rowsData.forEach(item => {
                    const birthDate = formatDate(item.child_birth_date);
                    rows += `<tr>
							<td>${item.child_fname || ''} ${item.child_lname || ''}</td>
							<td>${birthDate}</td>
							<td>${item.place_of_birth || ''}</td>
							<td>${item.mother_name || ''}</td>
							<td>${item.address || ''}</td>
                            <td>
                                <button class="btn view-schedule-btn"
                                        onclick="viewSchedule('${item.baby_id}', this)"
                                        aria-expanded="false">
                                    <span class="material-symbols-rounded btn-icon">calendar_month</span>
                                    <span class="btn-text">Schedule</span>
                                    <span class="material-symbols-rounded btn-chevron">expand_more</span>
                                </button>
                            </td>
                            <td>
                                <a class="btn viewCHR-btn" href="child-health-record.php?baby_id=${encodeURIComponent(item.baby_id)}">
                                    <span class="material-symbols-rounded btn-icon">visibility</span>
                                    <span class="btn-text">View CHR</span>
                                </a>
                                <a class="btn downloadCHR-btn" href="../../php/supabase/bhw/download_chr.php?baby_id=${encodeURIComponent(item.baby_id)}">
                                    <span class="material-symbols-rounded btn-icon">download</span>
                                    <span class="btn-text">Download CHR</span>
                                </a>
                            </td>
						</tr>`;
                });
                body.innerHTML = rows;

                chlPage = data.page || page;
                const canNext = data.has_more === true || rowsData.length === (data.limit || chlLimit);
                updateChlPagination(data.total || 0, chlPage, data.limit || chlLimit, canNext);
            } catch (e) {
                if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', { colspan: 7, kind: 'error' });
                } else {
                        body.innerHTML = '<tr class="message-row error"><td colspan="7">Failed to load data. Please try again.</td></tr>';
                }
                updateChlPagination(0, page, chlLimit, false);
            }
        }

        function updateChlPagination(total, page, limit, hasMore = null) {
            const info = document.getElementById('chlPageInfo');
            const btnWrap = document.getElementById('chlPageButtons');
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            if (!info || !btnWrap || !prevBtn || !nextBtn) return;

            // Count only actual data rows, excluding skeleton and message rows
            const count = document.querySelectorAll('#childhealthrecordBody tr:not(.message-row):not(.skeleton-row)').length || 0;
            const totalNum = Number.isFinite(Number(total)) ? Number(total) : 0;

            // Always show a page-info string; when zero results, show neutral text
            if (totalNum === 0 || count === 0) {
                info.textContent = 'Showing 0-0 of 0 entries';
            } else {
                const start = (page - 1) * limit + 1;
                const end = start + Math.max(0, count) - 1;
                const endClamped = Math.min(end, totalNum || end);
                info.textContent = `Showing ${start}-${endClamped} of ${totalNum} entries`;
            }
            btnWrap.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;

            prevBtn.disabled = page <= 1;

            // Only enable Next if there is another page
            const canNextByTotal = totalNum > page * limit;
            const canNext = (typeof hasMore === 'boolean' ? hasMore : true) && canNextByTotal;
            nextBtn.disabled = !canNext;

            prevBtn.onclick = () => { if (page > 1) getChildHealthRecord(page - 1, { keep: true }); };
            nextBtn.onclick = () => { if (canNext) getChildHealthRecord(page + 1, { keep: true }); };
        }

        // Immediate schedule skeleton for collapse expansion
        function buildScheduleSkeletonTableHTML() {
            return `
                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Dose No.</th>
                            <th>Due</th>
                            <th>Date Given</th>
                            <th>Status</th>
                            <th>Catch-up Date</th>
                        </tr>
                    </thead>
                    <tbody class="sched-body">
                        <tr class="loading"><td colspan="6"><span class="loading"><i class="material-symbols-rounded">hourglass_top</i> Loading schedule...</span></td></tr>
                    </tbody>
                </table>`;
        }

    window.addEventListener('DOMContentLoaded', () => getChildHealthRecord(1, { keep: false }));
        document.addEventListener('DOMContentLoaded', () => {
            const applyBtn = document.getElementById('chlApplyFiltersBtn');
            const clearBtn = document.getElementById('chlClearFiltersBtn');
            if (applyBtn) applyBtn.addEventListener('click', () => getChildHealthRecord(1));
            if (clearBtn) clearBtn.addEventListener('click', () => {
                const si = document.getElementById('chlSearchInput');
                if (si) si.value = '';
                const pk = document.getElementById('chlPurok');
                if (pk) pk.value = '';
                getChildHealthRecord(1);
            });

            // Make the filter icons focus their inputs (like Immunization)
            document.querySelectorAll('.child-health-list-section .filters .select-with-icon').forEach(wrapper => {
                const icon = wrapper.querySelector('.material-symbols-rounded');
                const input = wrapper.querySelector('input');
                if (icon && input) {
                    icon.style.cursor = 'pointer';
                    icon.addEventListener('click', () => input.focus());
                }
            });
        });


        let html5QrcodeInstance = null;
        async function viewSchedule(baby_id, btn) {
            const tr = btn.closest('tr');
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            const next = tr.nextElementSibling;
            const hasSchedRow = next && next.classList.contains('sched-row');

            if (btn.dataset.loading === '1') return;

            // Collapse if already open
            if (isExpanded && hasSchedRow) {
                next.remove();
                btn.setAttribute('aria-expanded', 'false');
                return;
            }

            // Remove any stray sched row before inserting fresh
            if (hasSchedRow) next.remove();

            // Expand immediately with a skeleton table (no delay)
            btn.dataset.loading = '1';
            btn.setAttribute('aria-expanded', 'true');

            const detailsRow = document.createElement('tr');
            detailsRow.className = 'sched-row';
            const td = document.createElement('td');
            td.colSpan = tr.cells.length || 7;
            td.innerHTML = buildScheduleSkeletonTableHTML();
            detailsRow.appendChild(td);
            tr.parentNode.insertBefore(detailsRow, tr.nextElementSibling);

            try {
                const res = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
                const data = await res.json();

                    const tbody = detailsRow.querySelector('.sched-body');
                if (!data || data.status !== 'success') {
                    if (tbody) tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                    return;
                }
                const rows = Array.isArray(data.data) ? data.data : [];
                if (rows.length === 0) {
                    if (tbody) tbody.innerHTML = '<tr class="message-row"><td colspan="6">No records found</td></tr>';
                    return;
                }

                let html = '';
                rows.forEach(r => {
                    const status = (r.status || '').toString();
                    const showCatch = status.trim().toLowerCase() === 'missed';
                    html += `<tr>
                        <td>${escapeHtml(r.vaccine_name || '')}</td>
                        <td style="text-align:center">${escapeHtml(String(r.dose_number || ''))}</td>
                        <td>${escapeHtml(formatDate(r.schedule_date) || '')}</td>
                        <td>${escapeHtml(formatDate(r.date_given) || '')}</td>
                        <td>${renderStatusChip(status)}</td>
                        <td>${showCatch ? escapeHtml(formatDate(r.catch_up_date) || '') : ''}</td>
                    </tr>`;
                });
                if (tbody) tbody.innerHTML = html;
            } catch (e) {
                console.error('Error loading schedule:', e);
                const tbody = detailsRow.querySelector('.sched-body');
                if (tbody) tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                btn.setAttribute('aria-expanded', 'false');
            } finally {
                delete btn.dataset.loading;
            }
        }

        async function closeScanner() {
            const overlay = document.getElementById('qrOverlay');
            overlay.style.display = 'none';
            try {
                if (html5QrcodeInstance) {
                    await html5QrcodeInstance.stop();
                    await html5QrcodeInstance.clear();
                }
            } catch (_) {
                /* ignore */
            }
        }

        async function switchCamera(e) {
            const deviceId = e && e.target ? e.target.value : null;
            if (!deviceId || !html5QrcodeInstance) {
                return;
            }
            console.log('[QR] Switching camera to', deviceId);
            try {
                await html5QrcodeInstance.stop();
                await html5QrcodeInstance.clear();
                await html5QrcodeInstance.start({
                        deviceId: {
                            exact: deviceId
                        }
                    }, {
                        fps: 8,
                        qrbox: 320,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
                    },
                    onScanSuccess,
                    onScanFailure
                );
            } catch (err) {
                console.error('[QR] Switch camera failed:', err);
            }
        }

        function onScanSuccess(decodedText) {
            console.log('[QR] Scan success:', decodedText);
            closeScanner();



            const match = decodedText.match(/baby_id=([^&\s]+)/i);
            if (match && match[1]) {
                document.getElementById('searchInput').value = decodeURIComponent(match[1]);
                filterTable();
                focusRowByBabyId(decodeURIComponent(match[1]));
                return;
            }

            document.getElementById('searchInput').value = decodedText;
            filterTable();
            focusRowByBabyId(decodedText);
        }

        function onScanFailure(err) {
            console.log('[QR] Scanning...', err ? String(err).slice(0, 80) : '');
        }



        function focusRowByBabyId(babyId) {
            const rows = document.querySelectorAll('#childhealthrecordBody tr');
            for (const tr of rows) {
                const tds = tr.querySelectorAll('td');
                if (!tds || tds.length < 3) continue;
                const val = (tds[2].textContent || '').trim();
                if (val === babyId) {
                    tr.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    const originalBg = tr.style.backgroundColor;
                    tr.style.backgroundColor = '#fff6b3';
                    setTimeout(() => {
                        tr.style.backgroundColor = originalBg || '';
                    }, 1500);
                    break;
                }
            }
        }

        let torchOn = false;
        async function toggleTorch() {
            try {
                const video = document.querySelector('#qrReader video');
                const stream = video && video.srcObject ? video.srcObject : null;
                const track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
                if (!track) {
                    return;
                }
                await track.applyConstraints({
                    advanced: [{
                        torch: !torchOn
                    }]
                });
                torchOn = !torchOn;
                document.getElementById('torchBtn').textContent = torchOn ? 'Torch Off' : 'Torch On';
            } catch (err) {
                console.warn('[QR] Torch not supported:', err);
            }
        }

        async function scanFromImage(event) {
            const file = event.target && event.target.files && event.target.files[0];
            if (!file) {
                return;
            }
            console.log('[QR] Scanning from image:', file.name, file.type, file.size);
            try {
                // Use Html5Qrcode to scan file
                const result = await Html5QrcodeScanner.scanFile(file, true);
                console.log('[QR] Image scan result:', result);
                onScanSuccess(result);
            } catch (err) {
                console.error('[QR] Image scan failed:', err);
                alert('Unable to read QR from image.');
            }
        }
        async function logoutBhw() {
            // const response = await fetch('/ebakunado/php/bhw/logout.php', { method: 'POST' });
            const response = await fetch('../../php/supabase/bhw/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = '../../views/login.php';
            } else {
                alert('Logout failed: ' + data.message);
            }
        }

        async function viewSchedule(baby_id, btn) {
            const tr = btn.closest('tr');
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            const next = tr.nextElementSibling;
            const hasSchedRow = next && next.classList.contains('sched-row');

            // If already loading, ignore repeated clicks
            if (btn.dataset.loading === '1') return;

            // Toggle close if expanded and row exists
            if (isExpanded && hasSchedRow) {
                next.remove();
                btn.setAttribute('aria-expanded', 'false');
                return;
            }

            // Clean any stray sched-row before loading (race-condition guard)
            if (hasSchedRow) next.remove();

            btn.dataset.loading = '1';
            // Immediate visual feedback: rotate chevron before data loads
            btn.setAttribute('aria-expanded', 'true');

            try {
                const res = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
                const data = await res.json();

                // Re-check just before insert in case another insert happened
                const curNext = tr.nextElementSibling;
                if (curNext && curNext.classList.contains('sched-row')) {
                    curNext.remove();
                }

                const colspan = tr.cells.length || 7;
                let html = `<tr class="sched-row"><td colspan="${colspan}">`;

                if (data.status !== 'success' || !data.data || data.data.length === 0) {
                    html += '<div class="small">No schedule</div>';
                } else {
                    html += '<table class="small"><tr><th>Vaccine</th><th>Dose No.</th><th>Due</th><th>Date Given</th><th>Status</th><th>Catch-up Date</th></tr>';
                    data.data.forEach(r => {
                        html += `<tr>
                            <td>${r.vaccine_name}</td>
                            <td>${r.dose_number}</td>
                            <td>${formatDate(r.schedule_date)}</td>
                            <td>${formatDate(r.date_given)}</td>
                            <td>${renderStatusChip(r.status)}</td>
                            <td>${((r.status || '').toLowerCase() === 'missed') ? formatDate(r.catch_up_date) : ''}</td>
                        </tr>`;
                    });
                    html += '</table>';
                }

                html += '</td></tr>';
                tr.insertAdjacentHTML('afterend', html);
                // Expanded already set prior to fetch for early icon rotation
            } catch (e) {
                console.error('Error loading schedule:', e);
                btn.setAttribute('aria-expanded', 'false');
            } finally {
                delete btn.dataset.loading;
            }
        }
    </script>
</body>

</html>