<?php session_start(); ?>
<?php
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
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
    <title>Vaccination Planner</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css?v=1.0.1" />
    <link rel="stylesheet" href="css/header.css?v=1.0.1" />
    <link rel="stylesheet" href="css/sidebar.css?v=1.0.1" />

    <link rel="stylesheet" href="css/notification-style.css?v=1.0.1" />
    <link rel="stylesheet" href="css/skeleton-loading.css?v=1.0.1" />
    <link rel="stylesheet" href="css/bhw/vaccination-planner.css?v=1.0.4" />
    <link rel="stylesheet" href="css/bhw/table-style.css?v=1.0.3">
</head>

<body>
    <?php include 'Include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="vaccination-planner-section">
            <div class="page-header">
                <h1 class="page-title">Vaccination Planner</h1>
                <p class="page-subtitle">Preview upcoming vaccinations, prepare batches, and assign batch dates.</p>
            </div>

            <div class="planner-stats">
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-top">
                            <div class="card-info">
                                <p class="card-title">Total Scheduled</p>
                                <p class="card-number" id="statTotal">0</p>
                            </div>
                            <div class="card-icon">
                                <span class="material-symbols-rounded" aria-hidden="true">schedule</span>
                            </div>
                        </div>
                    </div>

                    <div class="card card-2">
                        <div class="card-top">
                            <div class="card-info">
                                <p class="card-title">Top Vaccines <span class="card-subtle">(next range)</span></p>
                                <p class="card-value" id="statVaccines"></p>
                            </div>
                            <div class="card-icon">
                                <span class="material-symbols-rounded" aria-hidden="true">vaccines</span>
                            </div>
                        </div>
                    </div>

                    <div class="card card-3">
                        <div class="card-top">
                            <div class="card-info">
                                <p class="card-title">Monthly Breakdown</p>
                                <p class="card-value" id="statMonths"></p>
                            </div>
                            <div class="card-icon">
                                <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="data-table-card">
                <div class="data-table-toolbar__top">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">Vaccination Planner</h2>
                    </div>
                    <div class="data-table-toolbar__controls">
                        <button class="btn clear-btn btn-icon" onclick="resetFilters()">Clear</button>
                        <button class="btn apply-btn btn-icon" onclick="loadPlanner(1)">Apply</button>
                        <button class="btn btn-primary btn-icon" onclick="handleBatchUpdate()">
                            <span class="material-symbols-rounded">add</span>
                            Set Batch Date
                        </button>

                        <button class="btn btn-danger btn-icon" onclick="handleBatchClear()">
                            <span class="material-symbols-rounded">delete</span>
                            Clear Batch Date
                        </button>

                    </div>
                </div>

                <div class="data-table-actions">
                    <div class="filters">
                        <div class="filter-item">
                            <div class="filter-label">Start Date</div>
                            <div class="input-field">
                                <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
                                <input type="date" id="filterStart">
                            </div>
                        </div>
                        <div class="filter-item">
                            <div class="filter-label">End Date</div>
                            <div class="input-field">
                                <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
                                <input type="date" id="filterEnd">
                            </div>
                        </div>
                        <div class="filter-item">
                            <div class="filter-label">Vaccine</div>
                            <div class="input-field">
                                <span class="material-symbols-rounded" aria-hidden="true">vaccines</span>
                                <select id="filterVaccine">
                                    <option value="" disabled selected>Vaccines</option>
                                    <option value="all">All Vaccines</option>
                                </select>
                            </div>
                        </div>
                        <div class="filter-item">
                            <div class="filter-label">Status</div>
                            <div class="input-field">
                                <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                                <select id="filterStatus">
                                    <option value="" disabled selected>Status</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="missed">Missed</option>
                                    <option value="all">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table" aria-describedby="pageInfo">
                        <thead>
                            <tr>
                                <th style="width:48px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                                <th>Child</th>
                                <th>Vaccine</th>
                                <th>Guideline Date</th>
                                <th>Batch Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="plannerTable">
                            <tr class="skeleton-row">
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-1"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
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
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
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
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
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
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
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
                                    <div class="skeleton skeleton-text skeleton-col-2"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-3"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-col-4"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-pill skeleton-col-5"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-btn skeleton-col-6"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (preserve existing IDs and JS hooks) -->
                <div class="pager" id="pager">
                    <div id="pageInfo" class="page-info">&nbsp;</div>
                    <div class="pager-controls">
                        <button id="prevBtn" type="button" class="pager-btn">
                            <span class="material-symbols-rounded">chevron_backward</span>
                            Prev
                        </button>
                        <span id="pageButtons" class="page-buttons"></span>
                        <button id="nextBtn" type="button" class="pager-btn">
                            Next
                            <span class="material-symbols-rounded">chevron_forward</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Date Picker Modal -->
    <div id="dateModal" class="modal-overlay" onclick="if(event.target === this) closeDateModal()">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="dateModalTitle">
            <div class="modal-header">
                <h3 id="dateModalTitle" class="modal-title">Set Batch Schedule Date</h3>
                <button type="button" class="modal-close action-icon-btn" aria-label="Close" onclick="closeDateModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="batchDateInput">Select Date:</label>
                <input type="date" id="batchDateInput">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDateModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmBatchDate()">Confirm</button>
            </div>
        </div>
    </div>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script>
        // spinner CSS (scoped)
        const style = document.createElement('style');
        style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}`;
        document.head.appendChild(style);

        let plannerData = [];
        let currentPage = 1;
        const pageSize = 10;

        // Column config used by skeleton generator: 6 columns layout
        function getPlannerColsConfig() {
            return [{
                    type: 'text',
                    widthClass: 'skeleton-col-1'
                }, // checkbox / child
                {
                    type: 'text',
                    widthClass: 'skeleton-col-2'
                }, // child details
                {
                    type: 'text',
                    widthClass: 'skeleton-col-3'
                }, // vaccine
                {
                    type: 'text',
                    widthClass: 'skeleton-col-4'
                }, // guideline date
                {
                    type: 'text',
                    widthClass: 'skeleton-col-5'
                }, // batch date
                {
                    type: 'pill',
                    widthClass: 'skeleton-col-6'
                } // status chip
            ];
        }

        function getFilters() {
            return {
                start_date: document.getElementById('filterStart').value,
                end_date: document.getElementById('filterEnd').value,
                vaccine: document.getElementById('filterVaccine').value,
                status: document.getElementById('filterStatus').value
            };
        }

        async function loadPlanner(page = 1, opts = {}) {
            const currentFilters = getFilters();
            console.log('[Planner] Applying filters:', currentFilters, 'Page:', page);
            const params = new URLSearchParams();
            params.set('page', page);
            params.set('limit', pageSize);
            // Only include non-empty filters to avoid sending empty query params
            const startDate = (currentFilters.start_date || '').trim();
            const endDate = (currentFilters.end_date || '').trim();
            const vaccineSel = (currentFilters.vaccine || '').trim();
            const statusSel = (currentFilters.status || '').trim();
            if (startDate) params.set('start_date', startDate);
            if (endDate) params.set('end_date', endDate);
            if (vaccineSel && vaccineSel !== 'all') params.set('vaccine', vaccineSel);
            if (statusSel && statusSel !== 'all') params.set('status', statusSel);

            const tbody = document.getElementById('plannerTable');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const btnWrap = document.getElementById('pageButtons');
            const pageInfoEl = document.getElementById('pageInfo');

            // Show loading state in pagination
            if (btnWrap) btnWrap.innerHTML = `<span class="pager-spinner" aria-label="Loading" role="status"></span>`;
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            if (pageInfoEl && (!pageInfoEl.textContent || pageInfoEl.textContent === '\u00A0')) {
                pageInfoEl.textContent = 'Showing 0-0 of 0 entries';
            }

            if (!opts.keep) {
                // show skeleton rows while fetching
                applyTableSkeleton(tbody, getPlannerColsConfig(), pageSize);
            }

            try {
                const res = await fetch(`php/supabase/bhw/get_vaccination_planner.php?${params.toString()}`);
                const json = await res.json();
                console.log('[Planner] API response:', json);
                if (json.status !== 'success') {
                    throw new Error(json.message || 'Failed to load data');
                }

                plannerData = json.data.items || [];
                renderStats(json.data.stats || {});
                populateVaccineDropdown();
                renderTable();

                // Update pagination
                updatePagination(json.data.total || 0, json.data.page || 1, json.data.limit || pageSize, json.data.has_more === true);
                currentPage = json.data.page || 1;
            } catch (err) {
                console.error('[Planner] Error loading data:', err);
                tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
                updatePagination(0, 0, 0);
            }
        }

        function populateVaccineDropdown() {
            const sel = document.getElementById('filterVaccine');
            if (!sel) return;
            const current = sel.value;

            // Always start with the default prompt
            const options = [
                '<option value="all">All Vaccines</option>'
            ];

            // Get unique vaccines from the loaded data
            const vaccines = [...new Set(plannerData.map(item => item.vaccine_name).filter(v => v))].sort();
            options.push(...vaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));

            sel.innerHTML = options.join('');
            // If the current value exists, set it; otherwise, keep "all"
            if (Array.from(sel.options).some(o => o.value === current)) {
                sel.value = current;
            } else {
                sel.value = 'all';
            }
        }

        function renderStats(stats) {
            document.getElementById('statTotal').textContent = stats.total ?? 0;

            const vaccineEl = document.getElementById('statVaccines');
            const monthEl = document.getElementById('statMonths');

            vaccineEl.innerHTML = '';
            monthEl.innerHTML = '';

            // Render top vaccines as chips
            Object.entries(stats.by_vaccine || {}).slice(0, 3).forEach(([vac, count]) => {
                const chip = document.createElement('span');
                chip.className = 'chip chip--default';
                chip.textContent = `${vac}: ${count}`;
                vaccineEl.appendChild(chip);
            });

            // Render monthly breakdown as chips
            Object.entries(stats.by_month || {}).slice(0, 3).forEach(([month, count]) => {
                const chip = document.createElement('span');
                chip.className = 'chip chip--default';
                // Attempt to parse the month value as a date and format as: Mon D, YYYY
                let formatted = String(month || '');
                try {
                    const d = new Date(formatted);
                    if (!Number.isNaN(d.getTime())) {
                        formatted = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    }
                } catch (e) {
                    // keep original
                }
                chip.textContent = `${formatted}: ${count}`;
                monthEl.appendChild(chip);
            });
        }

        // --- Status chip helpers (map text -> known chip variants from main.css) ---
        function sanitizeStatus(val) {
            return String(val || '').trim().toLowerCase();
        }

        function statusVariantFromText(status) {
            // Simplified mapping: only 'missed' or 'scheduled' (default)
            const s = sanitizeStatus(status);
            if (!s) return 'scheduled';
            if (s.includes('missed') || s.includes('overdue')) return 'missed';
            // Everything else is shown as scheduled to keep the column limited to two statuses
            return 'scheduled';
        }

        function renderStatusChip(status) {
            const raw = (status == null ? '' : String(status));
            const variant = statusVariantFromText(raw);
            const label = variant === 'missed' ? 'Missed' : 'Scheduled';
            return `<span class="chip chip--${variant}">${label}</span>`;
        }

        function wireDatePickers() {
            document.querySelectorAll('.data-table-card .filters .input-field').forEach(wrapper => {
                const icon = wrapper.querySelector('.material-symbols-rounded');
                const input = wrapper.querySelector('input[type="date"]');
                if (!input) return;
                const opener = icon || wrapper;
                opener.style.cursor = 'pointer';
                opener.replaceWith(opener.cloneNode(true));
                const newOpener = wrapper.querySelector('.material-symbols-rounded') || wrapper;
                newOpener.addEventListener('click', (e) => {
                    e.stopPropagation();
                    try {
                        if (typeof input.showPicker === 'function') {
                            input.showPicker();
                        } else {
                            input.focus();
                            input.click();
                        }
                    } catch (err) {
                        input.focus();
                    }
                });
            });

            // Modal date input
            const modalInput = document.querySelector('#dateModal .modal-body input[type="date"]');
            const modalLabel = document.querySelector('#dateModal .modal-body label');
            if (modalInput && modalLabel) {
                modalLabel.style.cursor = 'pointer';
                modalLabel.addEventListener('click', (e) => {
                    e.stopPropagation();
                    try {
                        if (typeof modalInput.showPicker === 'function') modalInput.showPicker();
                        else { modalInput.focus(); modalInput.click(); }
                    } catch (_) { modalInput.focus(); }
                });
            }
        }

        function renderTable() {
            const tbody = document.getElementById('plannerTable');
            if (!plannerData.length) {
                tbody.innerHTML = '<tr class="message-row"><td colspan="6">No records found</td></tr>';
                return;
            }

            const rows = plannerData.map(item => {
                const recordId = item.record_id || item.id || '';
                if (!recordId) {
                    console.warn('[Table Render] Missing record_id for item:', item);
                }
                return `
            <tr>
                <td><input type="checkbox" class="record-select" data-record-id="${recordId}"></td>
                <td>
                    <strong>${item.child_name || 'Unknown Child'}</strong><br>
                    <small>${item.operational_date || 'No date'}</small>
                </td>
                <td>${item.vaccine_name} (Dose ${item.dose_number || 1})</td>
                <td>${formatDate(item.guideline_date)}</td>
                <td>${formatDate(item.batch_schedule_date) || '<span class="chip chip--default">Not set</span>'}</td>
                <td>${renderStatusChip(item.status)}</td>
            </tr>
        `;
            }).join('');

            tbody.innerHTML = rows;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function selectedRecordIds() {
            const ids = Array.from(document.querySelectorAll('.record-select:checked'))
                .map(cb => cb.dataset.recordId)
                .filter(id => id && id.toString().trim() !== '');
            console.log('[Selected Records]', ids);
            return ids;
        }

        let pendingRecordIds = [];

        function handleBatchUpdate() {
            const ids = selectedRecordIds();
            if (!ids.length) {
                return alert('Select at least one record.');
            }
            pendingRecordIds = ids;
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('batchDateInput').value = today;
            // Open modal using the 'is-open' class (matches modal CSS)
            document.getElementById('dateModal').classList.add('is-open');
        }

        function closeDateModal() {
            document.getElementById('dateModal').classList.remove('is-open');
            pendingRecordIds = [];
        }

        async function confirmBatchDate() {
            const dateValue = document.getElementById('batchDateInput').value;
            if (!dateValue) {
                alert('Please select a date.');
                return;
            }

            // If pendingRecordIds is empty, try to get current selection
            let idsToUpdate = pendingRecordIds && pendingRecordIds.length > 0 ?
                pendingRecordIds :
                selectedRecordIds();

            if (!idsToUpdate || idsToUpdate.length === 0) {
                alert('No records selected. Please select at least one record and try again.');
                closeDateModal();
                return;
            }

            closeDateModal();
            await updateBatchSchedule(idsToUpdate, dateValue);
            pendingRecordIds = [];
        }

        async function handleBatchClear() {
            const ids = selectedRecordIds();
            if (!ids.length) {
                return alert('Select at least one record.');
            }
            if (!confirm('Clear batch schedule date for selected records?')) return;
            await updateBatchSchedule(ids, null);
        }

        async function updateBatchSchedule(recordIds, batchDate) {
            try {
                // Ensure recordIds is an array and filter out any empty values
                const validIds = Array.isArray(recordIds) ? recordIds.filter(id => id && id.toString().trim() !== '') : [];

                if (validIds.length === 0) {
                    throw new Error('No valid records selected');
                }

                console.log('[Batch Update] Sending:', {
                    record_ids: validIds,
                    batch_schedule_date: batchDate
                });

                const res = await fetch('php/supabase/bhw/update_batch_schedule.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        record_ids: validIds,
                        batch_schedule_date: batchDate
                    })
                });

                const json = await res.json();
                console.log('[Batch Update] Response:', json);

                if (json.status !== 'success') {
                    throw new Error(json.message || 'Update failed');
                }
                alert(`Updated ${json.data.updated || 0} record(s).`);
                loadPlanner(currentPage);
            } catch (err) {
                console.error('[Batch Update] Error:', err);
                alert('Error updating batch schedule: ' + err.message);
            }
        }

        function toggleAll(master) {
            document.querySelectorAll('.record-select').forEach(cb => {
                cb.checked = master.checked;
            });
        }

        function resetFilters() {
            document.getElementById('filterStart').value = '';
            document.getElementById('filterEnd').value = '';
            document.getElementById('filterVaccine').value = 'all';
            document.getElementById('filterStatus').value = 'scheduled';
            plannerData = [];
            currentPage = 1;
            // After clearing filters, reload first page (display all data)
            loadPlanner(1);
        }

        function updatePagination(total, page, limit, hasMore = null) {
            const info = document.getElementById('pageInfo');
            const btnWrap = document.getElementById('pageButtons');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!info || !btnWrap || !prevBtn || !nextBtn) return;

            const count = plannerData ? plannerData.length : 0;
            // Always show a page-info string, even when zero
            if (!total || total === 0 || count === 0) {
                info.textContent = 'Showing 0-0 of 0 entries';
            } else {
                const start = (page - 1) * limit + 1;
                const end = start + count - 1;
                const endClamped = Math.min(end, total);
                info.textContent = `Showing ${start}-${endClamped} of ${total} entries`;
            }
            btnWrap.innerHTML = `<button type="button" data-page="${page}" disabled>${page}</button>`;
            prevBtn.disabled = page <= 1;
            const canNext = hasMore === true || (plannerData && plannerData.length === limit);
            nextBtn.disabled = !canNext;
            prevBtn.onclick = () => {
                if (page > 1) loadPlanner(page - 1, {
                    keep: true
                });
            };
            nextBtn.onclick = () => {
                if (canNext) loadPlanner(page + 1, {
                    keep: true
                });
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Set default date range to next month
            const today = new Date();
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            const lastDayOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
            document.getElementById('filterStart').value = nextMonth.toISOString().slice(0, 10);
            document.getElementById('filterEnd').value = lastDayOfNextMonth.toISOString().slice(0, 10);
            // Wire date pickers first, then load data
            try { wireDatePickers(); } catch (e) { /* noop */ }
            loadPlanner(1);
        });
    </script>
</body>

</html>