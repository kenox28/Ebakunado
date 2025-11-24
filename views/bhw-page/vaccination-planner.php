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
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <style>
        main.planner-container {
            padding: 20px;
            overflow-y: auto;
            overflow-x: hidden;
            height: 100%;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
            flex-shrink: 0;
        }
        .filters-grid label {
            display: flex;
            flex-direction: column;
            font-size: 13px;
            color: #333;
            gap: 4px;
        }
        .planner-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            flex-shrink: 0;
        }
        .stat-card {
            flex: 1 1 180px;
            padding: 16px;
            border-radius: 8px;
            background: #f5f7fb;
            border: 1px solid #e1e5ee;
        }
        .table-container {
            overflow-x: auto;
            overflow-y: auto;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e1e5ee;
            flex: 1;
            min-height: 0;
            max-height: calc(100vh - 400px);
            position: relative;
        }
        .table-container table {
            position: relative;
        }
        .table-container select,
        .table-container input[type="checkbox"] {
            position: relative;
            z-index: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #f0f2f8;
            text-align: left;
        }
        th {
            background: #f8fafc;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .batch-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .chip--upcoming { background:#e7f5ff; color:#1c7ed6; }
        .chip--scheduled { background:#fff3bf; color:#f08c00; }
        .chip--missed { background:#ffe3e3; color:#c92a2a; }
        .schedule-block { font-size: 12px; line-height: 1.4; }
        .schedule-block span { font-weight: 600; color:#495057; margin-right:4px; }
        .toolbar-button {
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary { background:#1971c2; color:#fff; }
        .btn-secondary { background:#e9ecef; color:#343a40; }
        .empty-state {
            padding: 40px;
            text-align: center;
            color:#868e96;
        }
        .date-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .date-modal.active {
            display: flex;
        }
        .date-modal-content {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 320px;
            max-width: 90%;
        }
        .date-modal-content h3 {
            margin: 0 0 16px 0;
            font-size: 18px;
            color: #333;
        }
        .date-modal-content label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }
        .date-modal-content input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .date-modal-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .date-modal-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .date-modal-actions .btn-cancel {
            background: #e9ecef;
            color: #343a40;
        }
        .date-modal-actions .btn-confirm {
            background: #1971c2;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include 'Include/header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<main class="planner-container">
    <h2>Vaccination Planner</h2>
    <p>Preview upcoming vaccinations, prepare batches, and assign batch dates.</p>

    <div class="filters-grid">
        <label>Start Date
            <input type="date" id="filterStart">
        </label>
        <label>End Date
            <input type="date" id="filterEnd">
        </label>
        <label>Vaccine
            <select id="filterVaccine">
                <option value="all">All Vaccines</option>
            </select>
        </label>
        <label>Status
            <select id="filterStatus">
                <option value="scheduled">Scheduled</option>
                <option value="missed">Missed</option>
                <option value="all">All</option>
            </select>
        </label>
        <div style="display:flex; align-items:flex-end; gap:8px;">
            <button class="toolbar-button btn-primary" onclick="loadPlanner(1)">Apply</button>
            <button class="toolbar-button btn-secondary" onclick="resetFilters()">Clear</button>
        </div>
    </div>

    <div class="planner-stats">
        <div class="stat-card">
            <div>Total Scheduled</div>
            <strong id="statTotal">0</strong>
        </div>
        <div class="stat-card">
            <div>Top Vaccines (next range)</div>
            <div id="statVaccines" style="font-size:13px; margin-top:6px;"></div>
        </div>
        <div class="stat-card">
            <div>Monthly Breakdown</div>
            <div id="statMonths" style="font-size:13px; margin-top:6px;"></div>
        </div>
    </div>

    <div class="batch-actions">
        <button class="toolbar-button btn-primary" onclick="handleBatchUpdate()">Set Batch Date</button>
        <button class="toolbar-button btn-secondary" onclick="handleBatchClear()">Clear Batch Date</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                <th>Child</th>
                <th>Vaccine</th>
                <th>Guideline Date</th>
                <th>Batch Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody id="plannerTable">
            <tr><td colspan="6" class="empty-state">Use the filters above to load data…</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
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
</main>

<!-- Date Picker Modal -->
<div id="dateModal" class="date-modal" onclick="if(event.target === this) closeDateModal()">
    <div class="date-modal-content">
        <h3>Set Batch Schedule Date</h3>
        <label for="batchDateInput">Select Date:</label>
        <input type="date" id="batchDateInput">
        <div class="date-modal-actions">
            <button class="btn-cancel" onclick="closeDateModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmBatchDate()">Confirm</button>
        </div>
    </div>
</div>

<script>
    // spinner CSS (scoped)
    const style = document.createElement('style');
    style.textContent = `.pager-spinner{width:16px;height:16px;border:2px solid #e3e3e3;border-top-color:var(--primary-color);border-radius:50%;display:inline-block;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}`;
    document.head.appendChild(style);
    
    let plannerData = [];
    let currentPage = 1;
    const pageSize = 10;

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
        const params = new URLSearchParams(currentFilters);
        params.set('page', page);
        params.set('limit', pageSize);
        
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
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Loading…</td></tr>';
        }

        try {
            const res = await fetch(`../../php/supabase/bhw/get_vaccination_planner.php?${params.toString()}`);
            const json = await res.json();
            console.log('[Planner] API response:', json);
            if (json.status !== 'success') {
                throw new Error(json.message || 'Failed to load data');
            }
            
            // Log debug information if available
            if (json.data.debug) {
                const dbg = json.data.debug;
                console.log('[Planner] DEBUG INFO:', {
                    'Total immunization records fetched': dbg.immunizations_total ?? dbg.immunizations_count ?? 0,
                    'After vaccine/status/date filters': dbg.immunizations_after_filters ?? 0,
                    'Children found (accepted)': dbg.children_found ?? dbg.children_count ?? 0,
                    'Final items (after barangay filter)': dbg.items_final ?? json.data.items?.length ?? 0,
                    'Date range': dbg.date_range,
                    'Filters applied': dbg.filters_applied,
                    'Filter breakdown': dbg.filter_stats
                });
                
                // Also log raw debug for inspection
                console.log('[Planner] Full debug object:', dbg);
            }
            
            plannerData = json.data.items || [];
            renderStats(json.data.stats || {});
            populateVaccineDropdown();
            renderTable(json.data.debug);
            
            // Update pagination
            updatePagination(json.data.total || 0, json.data.page || 1, json.data.limit || pageSize, json.data.has_more === true);
            currentPage = json.data.page || 1;
        } catch (err) {
            console.error('[Planner] Error loading data:', err);
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Error: ${err.message}</td></tr>`;
            updatePagination(0, 0, pageSize);
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

        Object.entries(stats.by_vaccine || {}).slice(0, 3).forEach(([vac, count]) => {
            const div = document.createElement('div');
            div.textContent = `${vac}: ${count}`;
            vaccineEl.appendChild(div);
        });

        Object.entries(stats.by_month || {}).slice(0, 3).forEach(([month, count]) => {
            const div = document.createElement('div');
            div.textContent = `${month}: ${count}`;
            monthEl.appendChild(div);
        });
    }

    function renderTable(debugInfo = null) {
        const tbody = document.getElementById('plannerTable');
        if (!plannerData.length) {
            let debugMsg = '';
            if (debugInfo) {
                const dbg = debugInfo;
                debugMsg = `<tr><td colspan="6" class="empty-state" style="text-align:left; padding:20px;">
                    <strong>No records found for the selected filters.</strong><br><br>
                    <small style="color:#6c757d;">
                        <strong>Debug Info:</strong><br>
                        • Total immunization records in database: ${dbg.immunizations_total ?? dbg.immunizations_count ?? 0}<br>
                        • After vaccine/status/date filters: ${dbg.immunizations_after_filters ?? 0}<br>
                        • Children found (status='accepted'): ${dbg.children_found ?? dbg.children_count ?? 0}<br>
                        • Final items: ${dbg.items_final ?? 0}<br>
                        • Date range applied: ${dbg.date_range?.start || 'none'} to ${dbg.date_range?.end || 'none'}<br>
                        • Status filter: ${dbg.filters_applied?.status || 'none'}<br>
                        • Vaccine filter: ${dbg.filters_applied?.vaccine || 'all'}<br>
                        <br>
                        <strong>Tip:</strong> Try setting Status to "All" and clearing the Month field to see all records.
                    </small>
                </td></tr>`;
            } else {
                debugMsg = '<tr><td colspan="6" class="empty-state">No records found for the selected filters.</td></tr>';
            }
            tbody.innerHTML = debugMsg;
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
                <td>${formatDate(item.batch_schedule_date) || '<span style="color:#868e96;">Not set</span>'}</td>
                <td><span class="chip chip--${item.status || 'scheduled'}">${item.status || 'scheduled'}</span></td>
            </tr>
        `;
        }).join('');

        tbody.innerHTML = rows;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
        document.getElementById('dateModal').classList.add('active');
    }

    function closeDateModal() {
        document.getElementById('dateModal').classList.remove('active');
        pendingRecordIds = [];
    }

    async function confirmBatchDate() {
        const dateValue = document.getElementById('batchDateInput').value;
        if (!dateValue) {
            alert('Please select a date.');
            return;
        }
        
        // If pendingRecordIds is empty, try to get current selection
        let idsToUpdate = pendingRecordIds && pendingRecordIds.length > 0 
            ? pendingRecordIds 
            : selectedRecordIds();
        
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
            
            console.log('[Batch Update] Sending:', { record_ids: validIds, batch_schedule_date: batchDate });
            
            const res = await fetch('../../php/supabase/bhw/update_batch_schedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ record_ids: validIds, batch_schedule_date: batchDate })
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
        document.getElementById('plannerTable').innerHTML = '<tr><td colspan="6" class="empty-state">Use the filters above to load data…</td></tr>';
        updatePagination(0, 0, pageSize);
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
            if (page > 1) loadPlanner(page - 1, { keep: true });
        };
        nextBtn.onclick = () => {
            if (canNext) loadPlanner(page + 1, { keep: true });
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Set default date range to next month
        const today = new Date();
        const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
        const lastDayOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
        document.getElementById('filterStart').value = nextMonth.toISOString().slice(0, 10);
        document.getElementById('filterEnd').value = lastDayOfNextMonth.toISOString().slice(0, 10);
        loadPlanner(1);
    });
</script>
<script src="../../js/header-handler/profile-menu.js" defer></script>
<script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
<script src="../../js/utils/skeleton-loading.js" defer></script>
</body>
</html>

