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
    <title>Child Health Lists</title>
    <!-- <link rel="stylesheet" href="/css/base.css" /> -->
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/variables.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/bhw/child-health-list.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="child-health-list-section">
            <div class="filters" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
                <label>Search:
                    <input id="chlSearchInput" type="text" placeholder="Search by name, mother, address">
                </label>
                <label>Purok:
                    <input id="chlPurok" type="text" placeholder="e.g. Purok 1">
                </label>
                <button id="chlApplyFiltersBtn" class="btn" type="button">Apply</button>
                <button id="chlClearFiltersBtn" class="btn" type="button">Clear</button>
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
                <div id="chlPager" style="display:flex; align-items:center; justify-content: space-between; gap: 8px; margin-top: 8px;">
                    <div id="chlPageInfo" style="font-size: 12px; color: #555;">&nbsp;</div>
                    <div style="display:flex; gap:4px; align-items:center;">
                        <button id="chlPrevBtn" type="button">Prev</button>
                        <span id="chlPageButtons" style="display:inline-flex; align-items:center; gap:4px;"></span>
                        <button id="chlNextBtn" type="button">Next</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        // pager CSS
        (function(){
            const s=document.createElement('style');
            s.textContent = '.chl-spinner{width:16px;height:16px;border:2px solid #ccc;border-top-color:#1976d2;border-radius:50%;display:inline-block;animation:spin .8s linear infinite}'+
                            '.chl-page-num{background:#1976d2;color:#fff;border:none;border-radius:8px;padding:6px 10px;min-width:28px;text-align:center;font-weight:600}'+
                            '#chlPrevBtn,#chlNextBtn{background:#1976d2;color:#fff;border:none;border-radius:8px;padding:6px 10px}'+
                            '#chlPrevBtn:disabled,#chlNextBtn:disabled{opacity:.6;cursor:not-allowed}'+
                            '@keyframes spin{to{transform:rotate(360deg)}}';
            document.head.appendChild(s);
        })();

        let chlPage = 1; const chlLimit = 10;

        async function getChildHealthRecord(page=1, opts={}) {
            const body = document.querySelector('#childhealthrecordBody');
            const keep = opts.keep === true;
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            const pageSpan = document.getElementById('chlPageButtons');
            if (!keep) {
                body.innerHTML = '<tr><td colspan="21">Loading...</td></tr>';
            } else {
                if (pageSpan) pageSpan.innerHTML = '<span class="chl-spinner" aria-label="Loading" role="status"></span>';
                if (prevBtn) prevBtn.disabled = true; if (nextBtn) nextBtn.disabled = true;
            }
            try {
                const qs = new URLSearchParams({ page: String(page), limit: String(chlLimit) });
                const searchVal = (document.getElementById('chlSearchInput')?.value||'').trim();
                const purokVal = (document.getElementById('chlPurok')?.value||'').trim();
                if (searchVal) qs.set('search', searchVal);
                if (purokVal) qs.set('purok', purokVal);
                const res = await fetch('../../php/supabase/bhw/get_child_health_record.php?'+qs.toString());
                const data = await res.json();
                if (data.status !== 'success') { body.innerHTML = '<tr><td colspan="21">Failed to load records</td></tr>'; updateChlPager(1,false); updateChlInfo(1,chlLimit,0); return; }
                const rowsData = Array.isArray(data.data)?data.data:[];
                if (rowsData.length === 0) { body.innerHTML = '<tr><td colspan="21">No records found</td></tr>'; updateChlPager(page,false); updateChlInfo(page, chlLimit, 0); return; }
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
                                    <button onclick="viewSchedule('${item.baby_id}', this)">View Schedule</button>
                                    <a class="viewCHR-btn" href="child-health-record.php?baby_id=${encodeURIComponent(item.baby_id)}">View CHR</a>
                                </td>
                            </tr>`;
                });
                body.innerHTML = rows;
                chlPage = data.page || page;
                updateChlPager(chlPage, !!data.has_more);
                updateChlInfo(chlPage, data.limit || chlLimit, rowsData.length);
            } catch (e) {
                body.innerHTML = '<tr><td colspan="21">Error loading records</td></tr>';
                updateChlPager(page,false); updateChlInfo(page, chlLimit, 0);
            } finally {
                if (prevBtn) prevBtn.disabled = false; if (nextBtn) nextBtn.disabled = false;
            }
        }

        function updateChlPager(page, hasMore){
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            const pageSpan = document.getElementById('chlPageButtons');
            if (!prevBtn || !nextBtn || !pageSpan) return;
            prevBtn.disabled = page <= 1; nextBtn.disabled = !hasMore;
            pageSpan.innerHTML = `<button type="button" data-page="${page}" disabled class="chl-page-num">${page}</button>`;
        }

        function updateChlInfo(page, limit, count){
            const info = document.getElementById('chlPageInfo');
            if (!info) return; const start = (page-1)*limit + 1; const end = start + Math.max(0, count) - 1;
            info.textContent = count>0 ? `Showing ${start}-${end}` : '';
        }

        function filterTable() {
            const q = (document.getElementById('searchInput').value || '').trim().toLowerCase();
            const rows = document.querySelectorAll('#childhealthrecordBody tr');
            rows.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (!tds || tds.length === 0) return;
                const id = (tds[0].textContent || '').toLowerCase();
                const userId = (tds[1].textContent || '').toLowerCase();
                const babyId = (tds[2].textContent || '').toLowerCase();
                const fname = (tds[3].textContent || '').toLowerCase();
                const lname = (tds[4].textContent || '').toLowerCase();
                const childName = (tds[5].textContent || '').toLowerCase();
                const text = [id, userId, babyId, fname, lname, childName].join(' ');
                tr.style.display = text.includes(q) ? '' : 'none';
            });
        }

        function viewChrImage(urlEnc) {
            const url = decodeURIComponent(urlEnc);
            document.querySelector('#overlayImage').src = url;
            document.querySelector('#openInNewTab').href = url;
            document.querySelector('#imageOverlay').style.display = 'flex';
        }

        function hideOverlay() {
            document.querySelector('#imageOverlay').style.display = 'none';
            document.querySelector('#overlayImage').src = '';
        }

        function closeOverlay(e) {
            if (e.target && e.target.id === 'imageOverlay') {
                hideOverlay();
            }
        }

        async function acceptRecord(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            // const response = await fetch('/ebakunado/php/bhw/accept_chr.php', { method: 'POST', body: formData });
            const response = await fetch('../../php/supabase/bhw/accept_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                getChildHealthRecord();
            } else {
                alert('Record not accepted: ' + data.message);
            }
        }

        async function rejectRecord(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('../../php/mysql/bhw/reject_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                getChildHealthRecord();
            } else {
                alert('Record not rejected: ' + data.message);
            }
        }

        async function viewSchedule(baby_id, btn) {
            const tr = btn.closest('tr');
            const next = tr.nextElementSibling;
            if (next && next.classList.contains('sched-row')) {
                next.remove();
                return;
            }
            const res = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
            const data = await res.json();
            let html = '<tr class="sched-row"><td colspan="21">';
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
        }


        window.addEventListener('DOMContentLoaded', () => getChildHealthRecord(1));
        document.addEventListener('DOMContentLoaded', () => {
            const prevBtn = document.getElementById('chlPrevBtn');
            const nextBtn = document.getElementById('chlNextBtn');
            if (prevBtn) prevBtn.addEventListener('click', () => { if (chlPage>1) getChildHealthRecord(chlPage-1, { keep: true }); });
            if (nextBtn) nextBtn.addEventListener('click', () => { getChildHealthRecord(chlPage+1, { keep: true }); });
            const applyBtn = document.getElementById('chlApplyFiltersBtn');
            const clearBtn = document.getElementById('chlClearFiltersBtn');
            if (applyBtn) applyBtn.addEventListener('click', () => getChildHealthRecord(1, { keep: true }));
            if (clearBtn) clearBtn.addEventListener('click', () => { 
                const si=document.getElementById('chlSearchInput'); if (si) si.value='';
                const pk=document.getElementById('chlPurok'); if (pk) pk.value='';
                getChildHealthRecord(1, { keep: true });
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
    </script>
</body>

</html>