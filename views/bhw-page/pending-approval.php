<?php session_start(); ?>
<?php
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
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
$defaultStatus = (isset($_GET['view']) && $_GET['view'] === 'added') ? 'pendingcode' : 'pending';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <!-- <link rel="stylesheet" href="css/main.css?v=20251106" /> -->
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/sidebar.css" />
    <link rel="stylesheet" href="css/notification-style.css" />
    <link rel="stylesheet" href="css/skeleton-loading.css" />
    <link rel="stylesheet" href="css/bhw/pending-approval-style.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="pending-approval section-title">
                <span class="material-symbols-rounded">hourglass_top</span>
                Pending Immunization List
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
                            <select id="paStatus" data-default-status="<?php echo htmlspecialchars($defaultStatus, ENT_QUOTES); ?>">
                                <option value="pending" <?php echo $defaultStatus === 'pending' ? 'selected' : ''; ?>>Pending Requests</option>
                                <option value="pendingcode" <?php echo $defaultStatus === 'pendingcode' ? 'selected' : ''; ?>>Added Children</option>
                            </select>
                        </div>
                        <button id="paClear" type="button" class="btn btn-secondary">Clear</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table table-hover" id="childhealthrecord">
                        <thead id="childTableHead">
                            <tr>
                                <th>Child Name</th>
                                <th>Birth Date</th>
                                <th>Place of Birth</th>
                                <th>Mother's Name</th>
                                <th>Father's Name</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="childhealthrecordBody">
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </td>
                            </tr>
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </td>
                            </tr>
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

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
            </div>

            <div class="childinformation-container">
                <div class="pa-top-actions">
                    <button class="btn back-btn" onclick="backToList()" id="closeButton">
                        <span class="material-symbols-rounded">arrow_back</span>
                        Back
                    </button>
                </div>
                <div class="child-information childinfo-header">
                    <h1 class="section-heading">
                        <span class="material-symbols-rounded">
                            article_person
                        </span>
                        Child Information Review
                    </h1>
                    <div class="childinfo-actions">
                        <button class="btn reject-btn" id="rejectButton">Reject</button>
                        <button class="btn accept-btn" id="acceptButton">Accept Record</button>
                    </div>
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
                                <span>Baby's Card Image
                            </div>
                        </h2>
                        <img src="" alt="Baby Card" id="childImage">
                    </div>
                </div> <!-- end .childinfo-main (two-column child info area) -->

                <div class="vaccination-section">
                    <h2 class="vaccination-header">
                        <span class="material-symbols-rounded">
                            syringe
                        </span>
                        Child's Vaccination Records
                    </h2>
                    <div class="vaccination-record-list" id="vaccinationRecordsContainer">
                    </div>
                </div>
            </div>
        </section>

    </main>

    <div id="childImageOverlay" class="childimage-overlay" style="display:none;">
        <img id="childImageLarge" alt="Baby Card Full View" src="" />
    </div>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function getPendingColsConfig() {
            return [{
                    type: 'text',
                    widthClass: 'skeleton-col-1'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-4'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-5'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                },
                {
                    type: 'btn',
                    widthClass: 'skeleton-col-6'
                }
            ];
        }

        function getAddedChildColsConfig() {
            return [{
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                }, // Family Code
                {
                    type: 'text',
                    widthClass: 'skeleton-col-1'
                }, // Child Name
                {
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                }, // Gender
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                }, // Birth Date
                {
                    type: 'text',
                    widthClass: 'skeleton-col-4'
                }, // Place of Birth
                {
                    type: 'text',
                    widthClass: 'skeleton-col-5'
                }, // Mother's Name
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                }, // Father's Name
                {
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                } // Address
            ];
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr; // fallback if invalid
            return d.toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
        (function ensureSpinnerCss() {
            if (document.getElementById('pagerSpinnerCss')) return;
            const style = document.createElement('style');
            style.id = 'pagerSpinnerCss';
            style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:paSpin .7s linear infinite}@keyframes paSpin{to{transform:rotate(360deg)}}`;
            document.head.appendChild(style);
        })();

        const defaultStatus = "<?php echo $defaultStatus; ?>";
        const paState = {
            page: 1,
            limit: 10,
            loading: false
        };

        async function loadPending(page = 1, opts = {
            keep: true
        }) {
            const body = document.querySelector('#childhealthrecordBody');
            const prevBtn = document.getElementById('paPrevBtn');
            const nextBtn = document.getElementById('paNextBtn');
            const pageButtons = document.getElementById('paPageButtons');
            const pager = document.getElementById('paPager');
            const pageInfo = document.getElementById('paPageInfo');

            const search = (document.getElementById('paSearch').value || '').trim();
            const statusSelect = document.getElementById('paStatus');
            const status = (statusSelect && statusSelect.value) ? statusSelect.value : defaultStatus;
            const isAddedChildView = status === 'pendingcode';
            const tableHead = document.getElementById('childTableHead');
            const columnCount = isAddedChildView ? 8 : 7;
            const limit = paState.limit;

            paState.page = page;
            paState.loading = true;

            if (pager) pager.style.display = '';
            if (pageInfo && (!pageInfo.textContent || pageInfo.textContent === '\u00A0')) {
                pageInfo.textContent = `Showing 0-0 of 0 entries`;
            }
            if (pageButtons) pageButtons.innerHTML = '<span class="pager-spinner" aria-label="Loading" role="status"></span>';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;

            if (!opts || !opts.keep) {
                if (typeof applyTableSkeleton === 'function') {
                    applyTableSkeleton(body, isAddedChildView ? getAddedChildColsConfig() : getPendingColsConfig(), limit);
                }
            }

            try {
                const qs = new URLSearchParams({
                    page: String(page),
                    limit: String(limit),
                    status,
                    search
                });
                const res = await fetch('php/supabase/bhw/pending_chr.php?' + qs.toString());
                const data = await res.json();

                if (tableHead) {
                    tableHead.innerHTML = isAddedChildView ?
                        `<tr>
                            <th>Family Code</th>
                            <th>Child Name</th>
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th>Place of Birth</th>
                            <th>Mother's Name</th>
                            <th>Father's Name</th>
                            <th>Address</th>
                        </tr>` :
                        `<tr>
                            <th>Child Name</th>
                            <th>Birth Date</th>
                            <th>Place of Birth</th>
                            <th>Mother's Name</th>
                            <th>Father's Name</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>`;
                }

                if (data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', {
                            colspan: columnCount,
                            kind: 'error'
                        });
                    } else {
                        body.innerHTML = `<tr class="message-row error"><td colspan="${columnCount}">Failed to load data. Please try again.</td></tr>`;
                    }
                    updatePaPager({
                        page,
                        has_more: false
                    });
                    updatePaInfo(page, limit, 0, 0);
                    return;
                }

                const rowsData = Array.isArray(data.data) ? data.data : [];
                const count = rowsData.length;

                if (count === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'No records found', {
                            colspan: columnCount
                        });
                    } else {
                        body.innerHTML = `<tr class="message-row"><td colspan="${columnCount}">No records found</td></tr>`;
                    }
                    updatePaPager({
                        page: data.page || page,
                        has_more: false
                    });
                    updatePaInfo(data.page || page, data.limit || limit, 0);
                    return;
                }

                let rows = '';
                rowsData.forEach(item => {
                    const fullName = `${item.child_fname || ''} ${item.child_lname || ''}`.trim();
                    const birthDateFmt = formatDate(item.child_birth_date);
                    const familyCode = item.user_id || '-';
                    if (isAddedChildView) {
                        rows += `<tr>
                            <td hidden>${item.id || ''}</td>
                            <td hidden>${item.user_id || ''}</td>
                            <td hidden>${item.baby_id || ''}</td>
                            <td>${familyCode}</td>
                            <td>${fullName || '-'}</td>
                            <td>${item.child_gender || ''}</td>
                            <td>${birthDateFmt}</td>
                            <td>${item.place_of_birth || ''}</td>
                            <td>${item.mother_name || ''}</td>
                            <td>${item.father_name || ''}</td>
                            <td>${item.address || ''}</td>
                        </tr>`;
                    } else {
                        rows += `<tr>
                            <td hidden>${item.id || ''}</td>
                            <td hidden>${item.user_id || ''}</td>
                            <td hidden>${item.baby_id || ''}</td>
                            <td>${fullName || '-'}</td>
                            <td>${birthDateFmt}</td>
                            <td>${item.place_of_birth || ''}</td>
                            <td>${item.mother_name || ''}</td>
                            <td>${item.father_name || ''}</td>
                            <td>${item.address || ''}</td>
                            <td><button class="btn view-btn" onclick="viewChildInformation('${item.baby_id}')">
                                <span class="material-symbols-rounded">visibility</span>
                                View</button>
                            </td>
                        </tr>`;
                    }
                });
                body.innerHTML = rows;

                updatePaPager({
                    page: data.page || page,
                    has_more: !!data.has_more || count === (data.limit || limit)
                });
                updatePaInfo(data.page || page, data.limit || limit, count, data.total || 0);
            } catch (e) {
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(body, 'Failed to load data. Please try again.', {
                        colspan: columnCount,
                        kind: 'error'
                    });
                } else {
                    body.innerHTML = `<tr class="message-row error"><td colspan="${columnCount}">Failed to load data. Please try again.</td></tr>`;
                }
                updatePaPager({
                    page,
                    has_more: false
                });
                updatePaInfo(page, limit, 0, 0);
            } finally {
                paState.loading = false;
            }
        }

        function updatePaPager(meta) {
            const prevBtn = document.getElementById('paPrevBtn');
            const nextBtn = document.getElementById('paNextBtn');
            const pageButtons = document.getElementById('paPageButtons');
            const page = meta.page || 1;
            const hasMore = !!meta.has_more;

            if (pageButtons) pageButtons.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;
            if (prevBtn) prevBtn.disabled = page <= 1;
            if (nextBtn) nextBtn.disabled = !hasMore;

            if (prevBtn) prevBtn.onclick = () => {
                if (page > 1) loadPending(page - 1, {
                    keep: true
                });
            };
            if (nextBtn) nextBtn.onclick = () => {
                if (hasMore) loadPending(page + 1, {
                    keep: true
                });
            };
        }

        function updatePaInfo(page, limit, count, total) {
            const info = document.getElementById('paPageInfo');
            if (!info) return;
            const totalNum = Number.isFinite(Number(total)) ? Number(total) : 0;
            if (totalNum === 0 || count <= 0) {
                info.textContent = `Showing 0-0 of ${totalNum} entries`;
                return;
            }
            const start = (page - 1) * limit + 1;
            const end = start + Math.max(0, count) - 1;
            const endClamped = Math.min(end, totalNum || end);
            info.textContent = `Showing ${start}-${endClamped} of ${totalNum} entries`;
        }

        async function getChildHealthRecord() {
            const body = document.querySelector('#childhealthrecordBody');
            if (typeof applyTableSkeleton === 'function') {
                applyTableSkeleton(body, getPendingColsConfig(), 10);
            }
            try {
                const res = await fetch('php/supabase/bhw/pending_chr.php');
                const data = await res.json();
                if (data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'Failed to load data. Please try again.', {
                            colspan: 7,
                            kind: 'error'
                        });
                    } else {
                        body.innerHTML = '<tr class="message-row error"><td colspan="7">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }
                if (!data.data || data.data.length === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(body, 'No records found', {
                            colspan: 7
                        });
                    } else {
                        body.innerHTML = '<tr class="message-row"><td colspan="7">No records found</td></tr>';
                    }
                    return;
                }

                let rows = '';
                data.data.forEach((item, index) => {
                    const fullName = `${item.child_fname || ''} ${item.child_lname || ''}`.trim();
                    const birthDateFmt = formatDate(item.child_birth_date);
                    rows += `<tr>
							<td hidden>${item.id || ''}</td>
							<td hidden>${item.user_id || ''}</td>
							<td hidden>${item.baby_id || ''}</td>
							<td>${fullName || '-'}</td>
                            <td>${birthDateFmt}</td>
							<td>${item.place_of_birth || ''}</td>
							<td>${item.mother_name || ''}</td>
							<td>${item.father_name || ''}</td>
							<td>${item.address || ''}</td>
							<td><button class="btn view-btn" onclick=\"viewChildInformation('${item.baby_id}')\">
                            <span class="material-symbols-rounded">visibility</span>
                            View</button>
                            </td>

						</tr>`;
                });
                body.innerHTML = rows;
            } catch (e) {
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(body, 'Failed to load data. Please try again.', {
                        colspan: 9,
                        kind: 'error'
                    });
                } else {
                    body.innerHTML = '<tr class="message-row error"><td colspan="9">Failed to load data. Please try again.</td></tr>';
                }
            }
        }


        let originalChildData = {};

        async function viewChildInformation(baby_id) {
            formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('php/supabase/bhw/child_information.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                console.log(data.data);

                originalChildData = data.data[0];
                const isAddedChild = (String(originalChildData.status || '').toLowerCase() === 'pendingcode');
                const actionButtons = document.querySelector('.childinfo-actions');
                if (actionButtons) {
                    actionButtons.style.display = isAddedChild ? 'none' : '';
                }

                document.querySelector('#childName').value = data.data[0].child_fname + ' ' + data.data[0].child_lname;
                document.querySelector('#childGender').value = data.data[0].child_gender;
                document.querySelector('#childBirthDate').value = data.data[0].child_birth_date;
                document.querySelector('#childPlaceOfBirth').value = data.data[0].place_of_birth;
                document.querySelector('#childAddress').value = data.data[0].address;
                document.querySelector('#childWeight').value = data.data[0].birth_weight;
                document.querySelector('#childHeight').value = data.data[0].birth_height;
                document.querySelector('#childMother').value = data.data[0].mother_name;
                document.querySelector('#childFather').value = data.data[0].father_name;
                document.querySelector('#childBirthAttendant').value = data.data[0].birth_attendant;
                document.querySelector('#childDeliveryType').value = data.data[0].delivery_type || 'Normal';
                document.querySelector('#childBirthOrder').value = data.data[0].birth_order || 'Single';
                document.querySelector('#childImage').src = data.data[0].babys_card;

                document.querySelector('.childinformation-container').dataset.babyId = baby_id;

                document.querySelector('#acceptButton').onclick = () => {
                    acceptRecord(baby_id);
                };

                document.querySelector('#rejectButton').onclick = () => {
                    rejectRecord(baby_id);
                };

                await loadVaccinationRecords(baby_id);

                setChildInfoEditing(false);

                document.querySelector('.childinformation-container').style.display = 'flex';
                document.querySelector('.pending-approval-panel').style.display = 'none';
                const headerSection = document.querySelector('.section-container');
                if (headerSection) headerSection.style.display = 'none';
            } else {
                console.log(data.message);
            }
        }

        function backToList() {
            if (typeof showDetailsView === 'function') {
                showDetailsView(false);
            } else {
                const header = document.querySelector('.section-container');
                const listPanel = document.querySelector('.pending-approval-panel');
                const pager = document.getElementById('paPager');
                const details = document.querySelector('.childinformation-container');
                if (header) header.style.display = '';
                if (listPanel) listPanel.style.display = '';
                if (pager) pager.style.display = ''; // show pager again
                if (details) details.style.display = 'none';
            }

            loadPending((paState && paState.page) ? paState.page : 1, {
                keep: true
            });
        }

        function getVaccinationColsConfig() {
            return [{
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-6'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                },
                {
                    type: 'pill',
                    widthClass: 'skeleton-col-5'
                }
            ];
        }

        function buildVaccinationSkeletonTableHTML() {
            return `<table class="table table-hover" style="width:100%;margin-top:10px;">` +
                `<thead><tr>` +
                `<th>Vaccine</th><th>Dose</th><th>Schedule Date</th><th>Catch-up Date</th><th>Date Given</th><th>Status</th>` +
                `</tr></thead><tbody id="vaccinationRecordsBody"></tbody></table>`;
        }
        async function loadVaccinationRecords(baby_id) {
            const container = document.querySelector('#vaccinationRecordsContainer');
            if (!container) return;
            container.innerHTML = buildVaccinationSkeletonTableHTML();
            const tbody = container.querySelector('#vaccinationRecordsBody');
            if (typeof applyTableSkeleton === 'function') {
                applyTableSkeleton(tbody, getVaccinationColsConfig(), 14);
            }
            try {
                const response = await fetch('php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
                const data = await response.json();
                if (!data || data.status !== 'success') {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(tbody, 'Failed to load data. Please try again.', {
                            colspan: 6,
                            kind: 'error'
                        });
                    } else {
                        tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                    }
                    return;
                }
                const records = Array.isArray(data.data) ? data.data : [];
                if (records.length === 0) {
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(tbody, 'No records found', {
                            colspan: 6
                        });
                    } else {
                        tbody.innerHTML = '<tr class="message-row"><td colspan="6">No records found</td></tr>';
                    }
                    return;
                }
                let rows = '';
                records.forEach(record => {
                    const statusRaw = String(record.status || '').toLowerCase();
                    const statusClass = statusRaw === 'completed' ? 'success' : (statusRaw === 'missed' ? 'danger' : 'warning');
                    const statusText = statusRaw ? statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1) : '-';
                    rows += `<tr data-record-id="${record.id || ''}">` +
                        `<td>${record.vaccine_name || ''}</td>` +
                        `<td>${record.dose_number || ''}</td>` +
                        `<td>${formatDate(record.schedule_date) || ''}</td>` +
                        `<td>${formatDate(record.catch_up_date) || ''}</td>` +
                        `<td>${formatDate(record.date_given) || ''}</td>` +
                        `<td><span class="badge badge-${statusClass}">${statusText}</span></td>` +
                        `</tr>`;
                });
                tbody.innerHTML = rows;
            } catch (error) {
                console.error('Error loading vaccination records:', error);
                if (typeof renderTableMessage === 'function') {
                    renderTableMessage(tbody, 'Failed to load data. Please try again.', {
                        colspan: 6,
                        kind: 'error'
                    });
                } else if (tbody) {
                    tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                }
            }
        }


        function filterTable() {}

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
            const response = await fetch('php/supabase/bhw/accept_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                getChildHealthRecord();
            } else {
                alert('Record not accepted: ' + data.message);
            }
            backToList();
        }

        async function rejectRecord(baby_id) {
            if (!confirm('Are you sure you want to reject and remove this child registration request? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('php/supabase/bhw/reject_chr.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                alert('Child registration request rejected and removed successfully.');
                backToList();
            } else {
                alert('Record not rejected: ' + data.message);
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            const statusSelect = document.getElementById('paStatus');
            if (statusSelect) {
                statusSelect.value = defaultStatus;
            }
            document.getElementById('paPrevBtn').addEventListener('click', () => {
                if (paState.loading) return;
                const nextPage = Math.max(1, (paState.page || 1) - 1);
                loadPending(nextPage, {
                    keep: true
                });
            });
            document.getElementById('paNextBtn').addEventListener('click', () => {
                if (paState.loading) return;
                const nextPage = (paState.page || 1) + 1;
                loadPending(nextPage, {
                    keep: true
                });
            });
            document.getElementById('paSearch').addEventListener('input', () => loadPending(1, {
                keep: true
            }));
            document.getElementById('paStatus').addEventListener('change', () => loadPending(1, {
                keep: true
            }));
            document.getElementById('paClear').addEventListener('click', () => {
                document.getElementById('paSearch').value = '';
                if (statusSelect) {
                    statusSelect.value = defaultStatus;
                }
                loadPending(1, {
                    keep: true
                });
            });

            document.querySelectorAll('.pending-approval-section .filters .select-with-icon').forEach(w => {
                const icon = w.querySelector('.material-symbols-rounded');
                const ctrl = w.querySelector('input, select');
                if (icon && ctrl) {
                    icon.style.cursor = 'pointer';
                    icon.addEventListener('click', () => ctrl.focus());
                }
            });

            loadPending(1, {
                keep: false
            });
        });

        let html5QrcodeInstance = null;
        async function openScanner() {
            const overlay = document.getElementById('qrOverlay');
            overlay.style.display = 'flex';
            console.log('[QR] Opening scanner...');
            try {
                const devices = await Html5Qrcode.getCameras().catch(err => {
                    console.log('[QR] getCameras error:', err);
                    return [];
                });
                console.log('[QR] Cameras found:', devices);
                if (!devices || devices.length === 0) {
                    console.warn('[QR] No camera devices found. Use image upload.');
                }
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
            } catch (_) {}
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
                if (!tds || tds.length < 4) continue;
                const val = (tds[3].textContent || '').trim(); // baby_id is now at index 3
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
                const result = await Html5QrcodeScanner.scanFile(file, true);
                console.log('[QR] Image scan result:', result);
                onScanSuccess(result);
            } catch (err) {
                console.error('[QR] Image scan failed:', err);
                alert('Unable to read QR from image.');
            }
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

                const response = await fetch('php/supabase/bhw/update_child_info.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    alert('Child information updated successfully!');
                    originalChildData = {
                        ...originalChildData,
                        ...updateData
                    };

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

        async function logoutBhw() {
            const response = await fetch('php/supabase/bhw/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = 'login';
            } else {
                alert('Logout failed: ' + data.message);
            }
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

        async function viewChildInformation(baby_id) {
            const formData = new FormData();
            formData.append('baby_id', baby_id);

            try {
                const response = await fetch('php/supabase/bhw/child_information.php', {
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
                const childImgEl = document.getElementById('childImage');
                if (childImgEl) {
                    childImgEl.style.cursor = 'zoom-in';
                    childImgEl.onclick = () => openChildImage(childImgEl.src);
                }

                document.querySelector('.childinformation-container').dataset.babyId = baby_id;

                document.querySelector('#acceptButton').onclick = () => {
                    acceptRecord(baby_id);
                };

                document.querySelector('#rejectButton').onclick = () => {
                    rejectRecord(baby_id);
                };

                await loadVaccinationRecords(baby_id);
                setChildInfoEditing(false);
                showDetailsView(true);
            } catch (err) {
                console.error('Error loading child info:', err);
                alert('Error loading child information.');
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
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('childImageOverlay');
            if (overlay) {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) closeChildImage();
                });
            }
            const thumb = document.getElementById('childImage');
            if (thumb) {
                thumb.addEventListener('click', () => {
                    if (thumb.src) openChildImage(thumb.src);
                });
            }
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const ov = document.getElementById('childImageOverlay');
                    if (ov && ov.style.display === 'flex') closeChildImage();
                }
            });
        });
    </script>
</body>

</html>