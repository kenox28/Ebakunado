<?php session_start(); ?>
<?php
// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = $_SESSION['fname'] . " " . $_SESSION['lname'];
if ($user_types != 'midwifes') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Added Children</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/bhw/pending-approval-style.css" />
    <link rel="stylesheet" href="../../css/bhw/added-children-style.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="pending-approval section-title">
                <span class="material-symbols-rounded">child_care</span>
                Added Children List
            </h2>
        </section>
        <section class="pending-approval-section">
            <div class="pending-approval-panel">
                <div class="filters-bar">
                    <div class="filters-header">
                        <span class="material-symbols-rounded" aria-hidden="true">tune</span>
                        <span>Filters:</span>
                    </div>
                    <div class="filters">
                        <div class="select-with-icon">
                            <span class="material-symbols-rounded" aria-hidden="true">search</span>
                            <input id="paSearch" type="text" placeholder="Search name" />
                        </div>
                        <div class="select-with-icon">
                            <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                            <select id="paStatus">
                                <option value="pending">Pending</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <button id="paClear" type="button" class="btn btn-secondary">Clear</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table table-hover" id="childhealthrecord">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Gender</th>
                                <th>Birth Date</th>
                                <th>Place of Birth</th>
                                <th>Mother's Name</th>
                                <th>Father's Name</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Family Code</th>
                            </tr>
                        </thead>
                        <tbody id="childhealthrecordBody">
                            <tr class="skeleton-row">
                                <td><div class="skeleton skeleton-text skeleton-col-1"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-2"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-4"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-5"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-2"></div></td>
                                <td><div class="skeleton skeleton-pill skeleton-col-5"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-6"></div></td>
                            </tr>
                            <tr class="skeleton-row">
                                <td><div class="skeleton skeleton-text skeleton-col-1"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-2"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-4"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-5"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-3"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-2"></div></td>
                                <td><div class="skeleton skeleton-pill skeleton-col-5"></div></td>
                                <td><div class="skeleton skeleton-text skeleton-col-6"></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pager" id="paPager">
                <div id="paPageInfo" class="page-info">&nbsp;</div>
                <div class="pager-controls">
                    <button id="paPrevBtn" type="button" class="pager-btn">
                        <span class="material-symbols-rounded">chevron_backward</span>
                        Prev
                    </button>
                    <span id="paPageButtons" class="page-buttons"></span>
                    <button id="paNextBtn" type="button" class="pager-btn">
                        Next
                        <span class="material-symbols-rounded">chevron_forward</span>
                    </button>
                </div>
            </div>

            <div class="childinformation-container">
                <div class="ac-top-actions">
                    <button type="button" class="btn back-btn" onclick="backToList()">
                        <span class="material-symbols-rounded">arrow_back</span>
                        Back
                    </button>
                </div>
                <div class="child-information childinfo-header">
                    <h1 class="section-heading">
                        <span class="material-symbols-rounded">
                            article_person
                        </span>
                        Child Information
                    </h1>
                </div>

                <div class="childinfo-main">
                    <div class="childinfo-details">
                        <h2 class="childinfo-header">
                            <div class="childinfo-title">
                                <span class="material-symbols-rounded">person</span>
                                <span>Child Details</span>
                            </div>
                            <button type="button" class="btn edit-btn" id="editChildInfoBtn" onclick="toggleChildInfoEditing()">
                                <span class="material-symbols-rounded">edit</span>
                                <span class="btn-text">Edit</span>
                            </button>
                        </h2>
                        <div class="childinfo-grid">
                            <div class="childinfo-row">
                                <label>
                                    Name:
                                    <input type="text" id="childName">
                                </label>
                                <label>
                                    Gender:
                                    <select id="childGender">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </label>
                            </div>

                            <div class="childinfo-row">
                                <label>
                                    Birth Date:
                                    <input type="date" id="childBirthDate">
                                </label>
                                <label>
                                    Place of Birth:
                                    <input type="text" id="childPlaceOfBirth">
                                </label>
                            </div>

                            <div class="childinfo-row">
                                <label>
                                    Birth Weight (kg):
                                    <input type="number" id="childWeight" step="0.01">
                                </label>
                                <label>
                                    Birth Height (cm):
                                    <input type="number" id="childHeight" step="0.01">
                                </label>
                            </div>

                            <div class="childinfo-row">
                                <label>
                                    Mother:
                                    <input type="text" id="childMother">
                                </label>
                                <label>
                                    Father:
                                    <input type="text" id="childFather">
                                </label>
                            </div>

                            <div class="childinfo-row">
                                <label>
                                    Birth Attendant:
                                    <select id="childBirthAttendant">
                                        <option value="Doctor">Doctor</option>
                                        <option value="Midwife">Midwife</option>
                                        <option value="Nurse">Nurse</option>
                                        <option value="Hilot">Hilot</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </label>
                                <label>
                                    Delivery Type:
                                    <select id="childDeliveryType">
                                        <option value="Normal">Normal</option>
                                        <option value="Caesarean Section">Caesarean Section</option>
                                    </select>
                                </label>
                            </div>

                            <div class="childinfo-row">
                                <label>
                                    Birth Order:
                                    <select id="childBirthOrder">
                                        <option value="Single">Single</option>
                                        <option value="Twin">Twin</option>
                                    </select>
                                </label>
                                <label>
                                    Address:
                                    <input type="text" id="childAddress">
                                </label>
                            </div>
                        </div>

                        <div class="childinfo-buttons">
                            <button onclick="saveChildInfo()" class="btn save-btn">Save Changes</button>
                            <button onclick="cancelEdit()" class="btn cancel-btn">Cancel</button>
                        </div>
                    </div>

                    <div class="childinfo-image">
                        <h2 class="childinfo-header">
                            <div class="childinfo-title">
                                <span class="material-symbols-rounded">image</span>
                                <span>Baby's Card Image</span>
                            </div>
                        </h2>
                        <img src="" alt="Baby Card" id="childImage">
                    </div>
                </div>

                <div class="vaccination-section">
                    <h2 class="vaccination-header">
                        <span class="material-symbols-rounded">
                            syringe
                        </span>
                        Child's Vaccination Records
                    </h2>
                    <div class="vaccination-record-list" id="vaccinationRecordsContainer">
                        <!-- Content populated dynamically: skeleton table, rows, or message -->
                    </div>
                </div>
            </div>
        </section>
        
    </main>

    <div id="childImageOverlay" class="childimage-overlay" style="display:none;">
        <img id="childImageLarge" alt="Baby Card Full View" src="" />
    </div>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
        // Display date helper: Mon D, YYYY
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        }
        // Column config for Added Children (9 visible columns)
        function getAddedChildrenColsConfig() {
            return [
                { type: 'text', widthClass: 'skeleton-col-1' }, // Child Name
                { type: 'text', widthClass: 'skeleton-col-2' }, // Gender
                { type: 'text', widthClass: 'skeleton-col-3' }, // Birth Date
                { type: 'text', widthClass: 'skeleton-col-4' }, // Place of Birth
                { type: 'text', widthClass: 'skeleton-col-5' }, // Mother's Name
                { type: 'text', widthClass: 'skeleton-col-3' }, // Father's Name
                { type: 'text', widthClass: 'skeleton-col-2' }, // Address
                { type: 'pill', widthClass: 'skeleton-col-5' }, // Status
                { type: 'text', widthClass: 'skeleton-col-6' }  // Family Code
            ];
        }
        // Pager spinner CSS
        (function ensureSpinnerCss(){
            if (document.getElementById('pagerSpinnerCss')) return;
            const style = document.createElement('style');
            style.id = 'pagerSpinnerCss';
            style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:paSpin .7s linear infinite}@keyframes paSpin{to{transform:rotate(360deg)}}`;
            document.head.appendChild(style);
        })();

        const paState = { page: 1, limit: 10, loading: false };

        // Columns for Child's Vaccination Records table (6 columns)
        function getVaccinationColsConfig() {
            return [
                { type: 'text', widthClass: 'skeleton-col-2' }, // Vaccine
                { type: 'text', widthClass: 'skeleton-col-6' }, // Dose
                { type: 'text', widthClass: 'skeleton-col-3' }, // Schedule Date
                { type: 'text', widthClass: 'skeleton-col-3' }, // Catch-up Date
                { type: 'text', widthClass: 'skeleton-col-3' }, // Date Given
                { type: 'pill', widthClass: 'skeleton-col-5' }  // Status
            ];
        }

        function buildVaccinationSkeletonTableHTML() {
            return `
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Schedule Date</th>
                            <th>Catch-up Date</th>
                            <th>Date Given</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="vaccinationRecordsBody"></tbody>
                </table>`;
        }

        async function loadAddedChildren(page = 1, opts = { keep: true }) {
            const body = document.querySelector('#childhealthrecordBody');
            const prevBtn = document.getElementById('paPrevBtn');
            const nextBtn = document.getElementById('paNextBtn');
            const pageButtons = document.getElementById('paPageButtons');
            const pageInfo = document.getElementById('paPageInfo');

            const search = (document.getElementById('paSearch').value || '').trim();
            const status = (document.getElementById('paStatus').value || 'pending');
            const limit = paState.limit;

            paState.page = page;
            paState.loading = true;

            if (pageButtons) pageButtons.innerHTML = '<span class="pager-spinner" aria-label="Loading" role="status"></span>';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            // Keep a neutral page-info visible during loading (parity with other pages)
            if (pageInfo && (!pageInfo.textContent || pageInfo.textContent === '\u00A0')) {
                pageInfo.textContent = 'Showing 0-0 of 0 entries';
            }

            if (!opts || !opts.keep) {
                if (typeof applyTableSkeleton === 'function') {
                    applyTableSkeleton(body, getAddedChildrenColsConfig(), limit);
                }
                // Removed fallback "Loading..." text; rely on static skeleton rows if utility unavailable.
            }

            try {
                const qs = new URLSearchParams({ page: String(page), limit: String(limit), status, search });
                const res = await fetch('../../php/supabase/bhw/get_bhw_added_children.php?' + qs.toString());
                const data = await res.json();

                if (data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', { colspan: 9, kind: 'error' });
                    } else {
                        body.innerHTML = '<tr class="message-row error"><td colspan="9">Failed to load data. Please try again.</td></tr>';
                    }
                    updatePaPager({ page, has_more: false });
                    updatePaInfo(page, limit, 0, 0);
                    return;
                }

                const rowsData = Array.isArray(data.data) ? data.data : [];
                const count = rowsData.length;

                if (count === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'No records found', { colspan: 9 });
                    } else {
                        body.innerHTML = '<tr class="message-row"><td colspan="9">No records found</td></tr>';
                    }
                    updatePaPager({ page: data.page || page, has_more: false });
                    updatePaInfo(data.page || page, data.limit || limit, 0);
                    return;
                }

                let rows = '';
                rowsData.forEach(item => {
                    const fullName = `${item.child_fname || ''} ${item.child_lname || ''}`.trim();
                    const familyCode = item.user_id || '-';
                    rows += `<tr data-id="${item.id || ''}" data-user-id="${item.user_id || ''}" data-baby-id="${item.baby_id || ''}">
                            <td>
                                <div class="name-cell">
                                    <span class="child-name">${fullName || '-'}</span>
                                    <button type="button" class="icon-btn view-child-btn" aria-label="View details" title="View details" onclick="viewChildInformation('${item.baby_id}')">
                                        <span class="material-symbols-rounded">visibility</span>
                                    </button>
                                </div>
                            </td>
                            <td>${item.child_gender || ''}</td>
                            <td>${formatDate(item.child_birth_date) || ''}</td>
                            <td>${item.place_of_birth || ''}</td>
                            <td>${item.mother_name || ''}</td>
                            <td>${item.father_name || ''}</td>
                            <td>${item.address || ''}</td>
                            <td>${item.status || ''}</td>
                            <td class="family-code">${familyCode}</td>
                        </tr>`;
                });
                body.innerHTML = rows;
                

                updatePaPager({ page: data.page || page, has_more: !!data.has_more || count === (data.limit || limit) });
                updatePaInfo(data.page || page, data.limit || limit, count, data.total || 0);
            } catch (e) {
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(body, 'Failed to load data. Please try again.', { colspan: 9, kind: 'error' });
                } else {
                    body.innerHTML = '<tr class="message-row error"><td colspan="9">Failed to load data. Please try again.</td></tr>';
                }
                updatePaPager({ page, has_more: false });
                updatePaInfo(page, limit, 0, 0);
            } finally {
                paState.loading = false;
            }
        }

        function updatePaPager(meta){
            const prevBtn = document.getElementById('paPrevBtn');
            const nextBtn = document.getElementById('paNextBtn');
            const pageButtons = document.getElementById('paPageButtons');
            const page = meta.page || 1;
            const hasMore = !!meta.has_more;

            if (pageButtons) pageButtons.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;
            if (prevBtn) prevBtn.disabled = page <= 1;
            if (nextBtn) nextBtn.disabled = !hasMore;

            if (prevBtn) prevBtn.onclick = () => { if (page > 1) loadAddedChildren(page - 1, { keep: true }); };
            if (nextBtn) nextBtn.onclick = () => { if (hasMore) loadAddedChildren(page + 1, { keep: true }); };
        }

        function updatePaInfo(page, limit, count, total){
            const info = document.getElementById('paPageInfo');
            if (!info) return;
            const totalNum = Number.isFinite(Number(total)) ? Number(total) : 0;
            if (totalNum === 0 || count <= 0) {
                info.textContent = 'Showing 0-0 of 0 entries';
                return;
            }
            const start = (page - 1) * limit + 1;
            const end = start + Math.max(0, count) - 1;
            const endClamped = Math.min(end, totalNum || end);
            info.textContent = `Showing ${start}-${endClamped} of ${totalNum} entries`;
        }

        let originalChildData = {};

        function backToList() {
            const header = document.querySelector('.section-container');
            const listPanel = document.querySelector('.pending-approval-panel');
            const pager = document.getElementById('paPager');
            const details = document.querySelector('.childinformation-container');
            if (header) header.style.display = '';
            if (listPanel) listPanel.style.display = '';
            if (pager) pager.style.display = '';
            if (details) details.style.display = 'none';

            loadAddedChildren((paState && paState.page) ? paState.page : 1, { keep: true });
        }

        async function loadVaccinationRecords(baby_id) {
            const container = document.querySelector('#vaccinationRecordsContainer');
            if (!container) return;

            // Initialize with a skeleton table
            container.innerHTML = buildVaccinationSkeletonTableHTML();
            const tbody = container.querySelector('#vaccinationRecordsBody');
            if (typeof applyTableSkeleton === 'function') {
                // Increased skeleton placeholder rows from 8 to 14 for better perceived loading density
                applyTableSkeleton(tbody, getVaccinationColsConfig(), 14);
            }
            // Removed fallback "Loading..." text for vaccination records; skeleton preferred.

            try {
                const response = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
                const data = await response.json();

                if (!data || data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 6, kind: 'error' });
                    } else if (tbody) {
                        tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }

                const records = Array.isArray(data.data) ? data.data : [];
                if (records.length === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(tbody, 'No records found', { colspan: 6 });
                    } else if (tbody) {
                        tbody.innerHTML = '<tr class="message-row"><td colspan="6">No records found</td></tr>';
                    }
                    return;
                }

                let rows = '';
                records.forEach(record => {
                    const status = String(record.status || '').toLowerCase();
                    const statusClass = status === 'completed' ? 'success' : (status === 'missed' ? 'danger' : 'warning');
                    const statusText = status ? status.charAt(0).toUpperCase() + status.slice(1) : '-';
                    rows += `
                        <tr data-record-id="${record.id || ''}">
                            <td>${record.vaccine_name || ''}</td>
                            <td>${record.dose_number || ''}</td>
                            <td>${formatDate(record.schedule_date) || ''}</td>
                            <td>${formatDate(record.catch_up_date) || ''}</td>
                            <td>${formatDate(record.date_given) || ''}</td>
                            <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                        </tr>`;
                });
                if (tbody) tbody.innerHTML = rows;
            } catch (error) {
                console.error('Error loading vaccination records:', error);
                const tb = container.querySelector('#vaccinationRecordsBody') || tbody;
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(tb, 'Failed to load data. Please try again.', { colspan: 6, kind: 'error' });
                } else if (tb) {
                    tb.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                }
            }
        }

        async function viewChildInformation(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);

            try {
                const response = await fetch('../../php/supabase/bhw/child_information.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status !== 'success' || !Array.isArray(data.data) || data.data.length === 0) {
                    alert('Unable to load child information.');
                    return;
                }

                const row = data.data[0];
                window.originalChildData = row;

                document.querySelector('#childName').value = `${row.child_fname || ''} ${row.child_lname || ''}`.trim();
                document.querySelector('#childGender').value = row.child_gender || '';
                document.querySelector('#childBirthDate').value = row.child_birth_date || '';
                document.querySelector('#childPlaceOfBirth').value = row.place_of_birth || '';
                document.querySelector('#childAddress').value = row.address || '';
                document.querySelector('#childWeight').value = row.birth_weight || '';
                document.querySelector('#childHeight').value = row.birth_height || '';
                document.querySelector('#childMother').value = row.mother_name || '';
                document.querySelector('#childFather').value = row.father_name || '';
                document.querySelector('#childBirthAttendant').value = row.birth_attendant || '';
                document.querySelector('#childDeliveryType').value = row.delivery_type || 'Normal';
                document.querySelector('#childBirthOrder').value = row.birth_order || 'Single';
                document.querySelector('#childImage').src = row.babys_card || '';
                // Make baby card image clickable to open overlay
                const thumb = document.getElementById('childImage');
                if (thumb) {
                    thumb.style.cursor = 'pointer';
                    thumb.onclick = () => openChildImage(thumb.src);
                }

                document.querySelector('.childinformation-container').dataset.babyId = baby_id;

                await loadVaccinationRecords(baby_id);
                setChildInfoEditing(false);
                showDetailsView(true);
            } catch (err) {
                console.error('Error loading child info:', err);
                alert('Error loading child information.');
            }
        }

        function openChildImage(src) {
            if (!src) return;
            const overlay = document.getElementById('childImageOverlay');
            const large = document.getElementById('childImageLarge');
            if (!overlay || !large) return;
            large.src = src;
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeChildImage() {
            const overlay = document.getElementById('childImageOverlay');
            const large = document.getElementById('childImageLarge');
            if (overlay) overlay.style.display = 'none';
            if (large) large.src = '';
            document.body.style.overflow = '';
        }

        async function saveChildInfo() {
            const baby_id = document.querySelector('.childinformation-container').dataset.babyId;
            if (!baby_id) {
                alert('No child record selected');
                return;
            }

            const nameParts = document.querySelector('#childName').value.trim().split(' ');
            const child_fname = nameParts[0] || '';
            const child_lname = nameParts.slice(1).join(' ') || '';

            const updateData = {
                baby_id: baby_id,
                child_fname: child_fname,
                child_lname: child_lname,
                child_gender: document.querySelector('#childGender').value,
                child_birth_date: document.querySelector('#childBirthDate').value,
                place_of_birth: document.querySelector('#childPlaceOfBirth').value,
                mother_name: document.querySelector('#childMother').value,
                father_name: document.querySelector('#childFather').value,
                address: document.querySelector('#childAddress').value,
                birth_weight: document.querySelector('#childWeight').value,
                birth_height: document.querySelector('#childHeight').value,
                birth_attendant: document.querySelector('#childBirthAttendant').value,
                delivery_type: document.querySelector('#childDeliveryType').value,
                birth_order: document.querySelector('#childBirthOrder').value
            };

            try {
                const formData = new FormData();
                Object.keys(updateData).forEach(key => {
                    formData.append(key, updateData[key]);
                });

                const response = await fetch('../../php/supabase/bhw/update_child_info.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    alert('Child information updated successfully!');
                    originalChildData = { ...originalChildData, ...updateData };
                    setChildInfoEditing(false);
                } else {
                    alert('Failed to update child information: ' + data.message);
                }
            } catch (error) {
                console.error('Error updating child info:', error);
                alert('Error updating child information');
            }
        }

        function cancelEdit() {
            const orig = window.originalChildData || originalChildData || null;
            if (!orig || Object.keys(orig).length === 0) {
                setChildInfoEditing(false);
                return;
            }
            document.querySelector('#childName').value = (orig.child_fname || '') + ' ' + (orig.child_lname || '');
            document.querySelector('#childGender').value = orig.child_gender || '';
            document.querySelector('#childBirthDate').value = orig.child_birth_date || '';
            document.querySelector('#childPlaceOfBirth').value = orig.place_of_birth || '';
            document.querySelector('#childAddress').value = orig.address || '';
            document.querySelector('#childWeight').value = orig.birth_weight || '';
            document.querySelector('#childHeight').value = orig.birth_height || '';
            document.querySelector('#childMother').value = orig.mother_name || '';
            document.querySelector('#childFather').value = orig.father_name || '';
            document.querySelector('#childBirthAttendant').value = orig.birth_attendant || '';
            document.querySelector('#childDeliveryType').value = orig.delivery_type || 'Normal';
            document.querySelector('#childBirthOrder').value = orig.birth_order || 'Single';
            setChildInfoEditing(false);
        }

        function setChildInfoEditing(editing) {
            const details = document.querySelector('.childinfo-details');
            if (!details) return;

            details.querySelectorAll('input, select').forEach(el => {
                el.disabled = !editing;
            });

            details.classList.toggle('editing', editing);

            const editBtn = document.getElementById('editChildInfoBtn');
            if (editBtn) {
                editBtn.style.display = editing ? 'none' : '';
            }

            details.querySelectorAll('.childinfo-buttons button').forEach(btn => {
                btn.disabled = !editing;
            });
        }

        function toggleChildInfoEditing() {
            const isEditing = document.querySelector('.childinfo-details')?.classList.contains('editing');
            setChildInfoEditing(!isEditing);
        }

        function showDetailsView(show) {
            const header = document.querySelector('.section-container');
            const listPanel = document.querySelector('.pending-approval-panel');
            const pager = document.getElementById('paPager');
            const details = document.querySelector('.childinformation-container');

            if (show) {
                if (header) header.style.display = 'none';
                if (listPanel) listPanel.style.display = 'none';
                if (pager) pager.style.display = 'none';
                if (details) details.style.display = 'flex';
            } else {
                if (header) header.style.display = '';
                if (listPanel) listPanel.style.display = '';
                if (pager) pager.style.display = '';
                if (details) details.style.display = 'none';
            }
        }

        // Wire filters and pager
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('paPrevBtn').addEventListener('click', () => {
                if (paState.loading) return; 
                const nextPage = Math.max(1, (paState.page||1) - 1);
                loadAddedChildren(nextPage, { keep: true });
            });
            document.getElementById('paNextBtn').addEventListener('click', () => {
                if (paState.loading) return; 
                const nextPage = (paState.page||1) + 1;
                loadAddedChildren(nextPage, { keep: true });
            });
            document.getElementById('paSearch').addEventListener('input', () => loadAddedChildren(1, { keep: true }));
            document.getElementById('paStatus').addEventListener('change', () => loadAddedChildren(1, { keep: true }));
            document.getElementById('paClear').addEventListener('click', () => {
                document.getElementById('paSearch').value = '';
                document.getElementById('paStatus').value = 'pending';
                loadAddedChildren(1, { keep: true });
            });

            document.querySelectorAll('.pending-approval-section .filters .select-with-icon').forEach(w => {
                const icon = w.querySelector('.material-symbols-rounded');
                const ctrl = w.querySelector('input, select');
                if (icon && ctrl) {
                    icon.style.cursor = 'pointer';
                    icon.addEventListener('click', () => ctrl.focus());
                }
            });

            loadAddedChildren(1, { keep: false });

            // Overlay backdrop + ESC key handling
            const overlay = document.getElementById('childImageOverlay');
            if (overlay) {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) closeChildImage();
                });
            }
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeChildImage();
            });
        });
    </script>
</body>

</html>

