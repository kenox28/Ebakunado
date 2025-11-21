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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHR Doc Requests</title>
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/bhw/chr-doc-requests.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main>
        <div class="content">
            <h2 class="section-title">CHR Doc Requests</h2>

            <div class="table-container">
                <table class="table" aria-describedby="pageInfo">
                    <thead>
                        <tr>
                            <th>Parent Name</th>
                            <th>Child Name</th>
                            <th>Type</th>
                            <th>Requested At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="chrTableBody"></tbody>
                </table>
            </div>

            <!-- Pagination (match immunization.php IDs/classes) -->
            <div id="pager" class="pager">
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
        <script>
            let currentPage = 1;
            const pageSize = 10;

            document.addEventListener('DOMContentLoaded', () => {
                // Show skeleton rows immediately while loading
                applyTableSkeleton('#chrTableBody', getChrColsConfig(), pageSize);
                loadChrRequests(1);
            });

            function getChrColsConfig() {
                return [{
                        type: 'text',
                        widthClass: 'skeleton-col-2'
                    }, // Parent
                    {
                        type: 'text',
                        widthClass: 'skeleton-col-2'
                    }, // Child
                    {
                        type: 'pill',
                        widthClass: 'skeleton-col-6'
                    }, // Type
                    {
                        type: 'text',
                        widthClass: 'skeleton-col-3'
                    }, // Date
                    {
                        type: 'btn',
                        widthClass: ''
                    } // Action
                ];
            }

            function formatDateShort(dateStr) {
                if (!dateStr) return '-';
                try {
                    // Handle common formats: ISO or with space separator
                    const norm = String(dateStr).includes('T') ? dateStr : String(dateStr).replace(' ', 'T');
                    const d = new Date(norm);
                    if (isNaN(d.getTime())) return '-';
                    return d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                } catch (_) {
                    return '-';
                }
            }

            async function loadChrRequests(page = 1) {
                const tbody = document.getElementById('chrTableBody');
                const pagerDiv = document.getElementById('pager');
                const pageButtons = document.getElementById('pageButtons');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                // Keep table visible and show skeleton in tbody
                applyTableSkeleton(tbody, getChrColsConfig(), pageSize);

                // Ensure pager is visible while loading and pageInfo placeholder shows (like immunization.php)
                if (pagerDiv) {
                    pagerDiv.hidden = false;
                    const pageInfo = document.getElementById('pageInfo');
                    if (pageInfo && (!pageInfo.textContent || pageInfo.textContent === '' || pageInfo.textContent === '\u00A0')) {
                        pageInfo.textContent = 'Showing 0-0 of 0 entries';
                    }
                }

                // Pager loading state
                if (pageButtons) pageButtons.innerHTML = '<span class="pager-spinner" role="status" aria-label="Loading"></span>';
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;

                try {
                    const params = new URLSearchParams();
                    params.set('page', page);
                    params.set('limit', pageSize);

                    const res = await fetch(`/ebakunado/php/supabase/bhw/list_chr_doc_requests.php?${params.toString()}`);
                    const data = await res.json();

                    if (!(data && data.status === 'success')) {
                        if (typeof renderTableMessage === 'function') {
                            renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 5, kind: 'error' });
                        } else if (tbody) {
                            tbody.innerHTML = '<tr class="message-row error"><td colspan="5">Failed to load data. Please try again.</td></tr>';
                        }
                        if (pagerDiv) {
                            pagerDiv.hidden = false;
                            const pageInfo = document.getElementById('pageInfo');
                            if (pageInfo) pageInfo.textContent = 'Showing 0-0 of 0 entries';
                        }
                        return;
                    }

                    const rows = Array.isArray(data.data) ? data.data : [];

                    // Show/hide pager based on data
                    if (pagerDiv) {
                        // Keep pager visible even when no rows so user sees page info / disabled controls
                        pagerDiv.hidden = false;
                    }

                    if (rows.length === 0) {
                        if (typeof renderTableMessage === 'function') {
                            renderTableMessage(tbody, 'No records found', { colspan: 5 });
                        } else if (tbody) {
                            tbody.innerHTML = '<tr class="message-row"><td colspan="5">No records found</td></tr>';
                        }
                        updateChrPager(1, false, data.total || 0);
                        return;
                    }

                    // Render table rows
                    let html = '';
                    rows.forEach(r => {
                        const parentName = r.user_fullname || r.user_id || '';
                        const childName = r.baby_name || r.baby_id || '';
                        const type = String(r.request_type || '').toUpperCase();
                        const reqAt = formatDateShort(r.created_at);
                        html += `
                        <tr>
                            <td>${parentName}</td>
                            <td>${childName}</td>
                            <td>${type}</td>
                            <td>${reqAt}</td>
                            <td class="actions">
                                <button class="btn btn-primary" type="button" onclick="approveChr(${r.id}, '${String(r.request_type||'').toLowerCase()}')">Approve & Generate</button>
                                <button class="btn btn-danger" type="button" onclick="rejectChrDoc(${r.id})">Reject</button>
                            </td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;

                    // Update pagination
                    currentPage = data.page || page;
                    updateChrPager(currentPage, data.has_more === true, data.total || 0);

                } catch (e) {
                    console.error('Error loading CHR requests:', e);
                    if (typeof renderTableMessage === 'function') {
                        renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 5, kind: 'error' });
                    } else if (tbody) {
                        tbody.innerHTML = '<tr class="message-row error"><td colspan="5">Failed to load data. Please try again.</td></tr>';
                    }
                    if (pagerDiv) pagerDiv.hidden = false; // leave pager visible even on error for consistency
                }
            }

            function updateChrPager(page, hasMore, total) {
                const pagerDiv = document.getElementById('pager');
                if (!pagerDiv) return;

                const pageButtons = pagerDiv.querySelector('#pageButtons');
                const prevBtn = pagerDiv.querySelector('#prevBtn');
                const nextBtn = pagerDiv.querySelector('#nextBtn');
                const pageInfo = pagerDiv.querySelector('#pageInfo');

                if (pageButtons) pageButtons.innerHTML = `<button type="button" disabled>${page}</button>`;
                if (prevBtn) {
                    prevBtn.disabled = page <= 1;
                    prevBtn.onclick = () => {
                        if (page > 1) loadChrRequests(page - 1);
                    };
                }
                if (nextBtn) {
                    nextBtn.disabled = !hasMore;
                    nextBtn.onclick = () => {
                        if (hasMore) loadChrRequests(page + 1);
                    };
                }

                if (pageInfo && total > 0) {
                    const start = (page - 1) * pageSize + 1;
                    const end = Math.min(start + pageSize - 1, total);
                    pageInfo.textContent = `Showing ${start}-${end} of ${total} entries`;
                } else if (pageInfo) {
                    pageInfo.textContent = '';
                }
            }

            async function approveChr(requestId, requestType) {
                if (!confirm('Approve this request and generate PDF?')) return;
                try {
                    const fd = new FormData();
                    fd.append('request_id', requestId);
                    if (requestType) fd.append('request_type', requestType);
                    const res = await fetch('../../php/supabase/bhw/approve_chr_doc.php', {
                        method: 'POST',
                        body: fd
                    });
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    }
                    const j = await res.json();
                    if (j.status === 'success') {
                        alert('Approved. PDF generated.');
                        loadChrRequests(currentPage); // Reload current page
                    } else {
                        alert('Approve failed: ' + (j.message || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Approve error:', e);
                    alert('Network error approving request: ' + e.message);
                }
            }

            async function rejectChrDoc(requestId) {
                if (!confirm('Are you sure you want to reject and remove this CHR document request? This action cannot be undone.')) {
                    return;
                }
                try {
                    const fd = new FormData();
                    fd.append('request_id', requestId);
                    const res = await fetch('../../php/supabase/bhw/reject_chr_doc.php', {
                        method: 'POST',
                        body: fd
                    });
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    }
                    const j = await res.json();
                    if (j.status === 'success') {
                        alert('CHR document request rejected and removed successfully.');
                        loadChrRequests(currentPage); // Reload current page
                    } else {
                        alert('Reject failed: ' + (j.message || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Reject error:', e);
                    alert('Network error rejecting request: ' + e.message);
                }
            }
        </script>
    </main>
    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script src="../../js/header-handler/profile-menu.js" defer></script>
</body>

</html>