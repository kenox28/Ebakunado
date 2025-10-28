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
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th>Place of Birth</th>
                            <th>Mother</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Schedule</th>
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
                        <tr>
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
    <script>
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

            // Always show pager spinner while fetching (like pending-approval)
            if (pageSpan) pageSpan.innerHTML = '<span class="pager-spinner" aria-label="Loading" role="status"></span>';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;

            if (!keep) {
                body.innerHTML = '<tr><td colspan="21">Loading...</td></tr>';
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
                    body.innerHTML = '<tr><td colspan="21">Failed to load records</td></tr>';
                    updateChlPagination(0, page, chlLimit, false);
                    return;
                }
                const rowsData = Array.isArray(data.data) ? data.data : [];
                if (rowsData.length === 0) {
                    body.innerHTML = '<tr><td colspan="21">No records found</td></tr>';
                    updateChlPagination(data.total || 0, data.page || page, data.limit || chlLimit, false);
                    return;
                }

                let rows = '';
                rowsData.forEach(item => {
                    rows += `<tr>
                                <td>${item.child_fname || ''} ${item.child_lname || ''}</td>
                                <td>${item.child_gender || ''}</td>
                                <td>${item.child_birth_date || ''}</td>
                                <td>${item.place_of_birth || ''}</td>
                                <td>${item.mother_name || ''}</td>
                                <td>${item.address || ''}</td>
                                <td>${item.status || ''}</td>
                                <td>
                                    <button class="btn view-schedule-btn"
                                            onclick="viewSchedule('${item.baby_id}', this)"
                                            aria-expanded="false">
                                        <span class="material-symbols-rounded btn-icon">calendar_month</span>
                                        <span class="btn-text">Schedule</span>
                                        <span class="material-symbols-rounded btn-chevron">expand_more</span>
                                    </button>
                                    <a class="btn viewCHR-btn"
                                       href="child-health-record.php?baby_id=${encodeURIComponent(item.baby_id)}">
                                        <span class="material-symbols-rounded btn-icon">visibility</span>
                                        <span class="btn-text">CHR</span>
                                    </a>
                                </td>
                            </tr>`;
                });
                body.innerHTML = rows;

                chlPage = data.page || page;
                const canNext = data.has_more === true || rowsData.length === (data.limit || chlLimit);
                updateChlPagination(data.total || 0, chlPage, data.limit || chlLimit, canNext);
            } catch (e) {
                body.innerHTML = '<tr><td colspan="21">Error loading records</td></tr>';
                updateChlPagination(0, page, chlLimit, false);
            }
        }

        function updateChlPagination(total, page, limit, hasMore = null) {
            const info = document.getElementById('chlPageInfo');
            const btnWrap = document.getElementById('chlPageButtons');
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            if (!info || !btnWrap || !prevBtn || !nextBtn) return;

            const start = (page - 1) * limit + 1;
            const count = document.querySelectorAll('#childhealthrecordBody tr').length || 0;
            const end = start + Math.max(0, count) - 1;
            const totalNum = Number.isFinite(Number(total)) ? Number(total) : 0;
            const endClamped = Math.min(end, totalNum || end);

            info.textContent = count > 0 ? `Showing ${start}-${endClamped} of ${totalNum} entries` : '';
            btnWrap.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;

            prevBtn.disabled = page <= 1;

            // Only enable Next if there is another page
            const canNextByTotal = totalNum > page * limit;
            const canNext = (typeof hasMore === 'boolean' ? hasMore : true) && canNextByTotal;
            nextBtn.disabled = !canNext;

            prevBtn.onclick = () => { if (page > 1) getChildHealthRecord(page - 1, { keep: true }); };
            nextBtn.onclick = () => { if (canNext) getChildHealthRecord(page + 1, { keep: true }); };
        }

        window.addEventListener('DOMContentLoaded', () => getChildHealthRecord(1));
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
        async function openScanner() {
            const overlay = document.getElementById('qrOverlay');
            overlay.style.display = 'flex';
            console.log('[QR] Opening scanner...');
            try {
                // Check camera permissions/devices first
                const devices = await Html5Qrcode.getCameras().catch(err => {
                    console.log('[QR] getCameras error:', err);
                    return [];
                });
                console.log('[QR] Cameras found:', devices);
                if (!devices || devices.length === 0) {
                    console.warn('[QR] No camera devices found. Use image upload.');
                }
                // Populate camera select
                const camSel = document.getElementById('cameraSelect');
                camSel.innerHTML = '';
                if (devices && devices.length > 0) {
                    devices.forEach((d, idx) => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.label || ('Camera ' + (idx + 1));
                        camSel.appendChild(opt);
                    });
                    camSel.style.display = 'inline-block';
                    // Try enabling torch control if supported (via capabilities check after start)
                } else {
                    camSel.style.display = 'none';
                }
                if (!html5QrcodeInstance) {
                    html5QrcodeInstance = new Html5Qrcode("qrReader");
                }
                await html5QrcodeInstance.start({
                        facingMode: "environment"
                    }, {
                        fps: 12,
                        qrbox: 360,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        disableFlip: true
                    },
                    onScanSuccess,
                    onScanFailure
                );
                console.log('[QR] Scanner started');
                // Show torch button if track supports torch
                try {
                    const stream = await html5QrcodeInstance.getState() ? document.querySelector('#qrReader video')?.srcObject : null;
                    const track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
                    const caps = track && track.getCapabilities ? track.getCapabilities() : {};
                    const torchBtn = document.getElementById('torchBtn');
                    if (caps && caps.torch !== undefined) {
                        torchBtn.style.display = 'inline-block';
                    } else {
                        torchBtn.style.display = 'none';
                    }
                } catch (_) {
                    document.getElementById('torchBtn').style.display = 'none';
                }
            } catch (e) {
                console.error('[QR] Camera error:', e);
                alert('Camera error: ' + e);
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

            try {
                const res = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
                const data = await res.json();

                // Re-check just before insert in case another insert happened
                const curNext = tr.nextElementSibling;
                if (curNext && curNext.classList.contains('sched-row')) {
                    curNext.remove();
                }

                const colspan = tr.cells.length || 21;
                let html = `<tr class="sched-row"><td colspan="${colspan}">`;

                if (data.status !== 'success' || !data.data || data.data.length === 0) {
                    html += '<div class="small">No schedule</div>';
                } else {
                    html += '<table class="small"><tr><th>Vaccine</th><th>Dose #</th><th>Due</th><th>Date Given</th><th>Status</th></tr>';
                    data.data.forEach(r => {
                        html += `<tr>
                            <td>${r.vaccine_name}</td>
                            <td>${r.dose_number}</td>
                            <td>${r.schedule_date || ''}</td>
                            <td>${r.date_given || ''}</td>
                            <td>${r.status}</td>
                        </tr>`;
                    });
                    html += '</table>';
                }

                html += '</td></tr>';
                tr.insertAdjacentHTML('afterend', html);

                // Mark expanded (keeps chevron rotated via CSS)
                btn.setAttribute('aria-expanded', 'true');
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