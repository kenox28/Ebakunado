<?php session_start(); ?>
<?php 
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') ." ". ($_SESSION['lname'] ?? '');
if($user_types != 'midwifes') { $user_type = 'Barangay Health Worker'; } else { $user_type = 'Midwife'; }
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
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main>
        <div class="content" style="padding: 15px;">
            <h2 style="margin: 0 0 12px 0;">CHR Doc Requests</h2>
            <div id="reqContainer">
                <div class="loading" style="text-align:center; padding: 20px;">Loading requests...</div>
            </div>
            
            <!-- Pagination -->
            <div id="chrPager" class="pager" style="display: none;">
                <div id="chrPageInfo" class="page-info">&nbsp;</div>
                <div class="pager-controls">
                    <button id="chrPrevBtn" type="button" class="pager-btn">
                        <span style="margin-right: 5px;">←</span> Prev
                    </button>
                    <span id="chrPageButtons" class="page-buttons"></span>
                    <button id="chrNextBtn" type="button" class="pager-btn">
                        Next <span style="margin-left: 5px;">→</span>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .pager {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 20px;
                padding: 15px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .page-info {
                color: #666;
                font-size: 14px;
            }
            .pager-controls {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .pager-btn {
                padding: 8px 16px;
                border: 1px solid #ddd;
                background: white;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.2s;
            }
            .pager-btn:hover:not(:disabled) {
                background: #f5f5f5;
            }
            .pager-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .page-buttons {
                display: flex;
                gap: 5px;
            }
            .pager-spinner {
                width: 16px;
                height: 16px;
                border: 2px solid #e3e3e3;
                border-top-color: #1976d2;
                border-radius: 50%;
                display: inline-block;
                animation: spin 0.7s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
                background: white;
            }
            table th, table td {
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd;
            }
            table th {
                background: #f8f9fa;
                font-weight: 600;
            }
        </style>
        <script>
        let currentPage = 1;
        const pageSize = 10;

        document.addEventListener('DOMContentLoaded', () => loadChrRequests(1));

        async function loadChrRequests(page = 1){
            const container = document.getElementById('reqContainer');
            const pagerDiv = document.getElementById('chrPager');
            
            container.innerHTML = '<div class="loading" style="text-align:center; padding: 20px;">Loading requests...</div>';
            
            // Update pager
            if (pagerDiv) {
                const pageButtons = pagerDiv.querySelector('#chrPageButtons');
                const prevBtn = pagerDiv.querySelector('#chrPrevBtn');
                const nextBtn = pagerDiv.querySelector('#chrNextBtn');
                
                if (pageButtons) pageButtons.innerHTML = '<span class="pager-spinner"></span>';
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = true;
            }
            
            try{
                const params = new URLSearchParams();
                params.set('page', page);
                params.set('limit', pageSize);
                
                const res = await fetch(`/ebakunado/php/supabase/bhw/list_chr_doc_requests.php?${params.toString()}`);
                const data = await res.json();
                
                if (!(data && data.status === 'success')){
                    container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Failed to load requests</div>';
                    return;
                }
                
                const rows = Array.isArray(data.data) ? data.data : [];
                
                // Show/hide pager based on data
                if (pagerDiv && (rows.length > 0 || (data.total || 0) > 0)) {
                    pagerDiv.style.display = 'flex';
                } else if (pagerDiv) {
                    pagerDiv.style.display = 'none';
                }
                
                if (rows.length === 0){
                    container.innerHTML = '<div class="no-data" style="text-align:center; padding: 20px; color:#666;">No pending CHR requests</div>';
                    updateChrPager(1, false, data.total || 0);
                    return;
                }
                
                let html = '';
                html += '<table border="1">';
                html += '<thead><tr>'+
                    '<th style="padding:6px;">Request ID</th>'+
                    '<th style="padding:6px;">Parent Name</th>'+
                    '<th style="padding:6px;">Child Name</th>'+
                    '<th style="padding:6px;">Type</th>'+
                    '<th style="padding:6px;">Status</th>'+
                    '<th style="padding:6px;">Requested At</th>'+
                    '<th style="padding:6px;">Action</th>'+
                    '</tr></thead>';
                html += '<tbody>';
                rows.forEach(r => {
                    html += '<tr>'+
                        `<td style="padding:6px;">${r.id}</td>`+
                        `<td style="padding:6px;">${r.user_fullname||r.user_id||''}</td>`+
                        `<td style="padding:6px;">${r.baby_name||r.baby_id||''}</td>`+
                        `<td style="padding:6px;">${(r.request_type||'').toUpperCase()}</td>`+
                        `<td style="padding:6px;">${r.status||''}</td>`+
                        `<td style="padding:6px;">${(r.created_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
                        `<td style="padding:6px; text-align:center;"><button style="padding:4px 8px;" onclick="approveChr(${r.id}, '${(r.request_type||'').toLowerCase()}')">Approve & Generate</button></td>`+
                    '</tr>';
                });
                html += '</tbody></table>';
                container.innerHTML = html;
                
                // Update pagination
                currentPage = data.page || page;
                updateChrPager(currentPage, data.has_more === true, data.total || 0);
                
            }catch(e){
                console.error('Error loading CHR requests:', e);
                container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>';
            }
        }
        
        function updateChrPager(page, hasMore, total) {
            const pagerDiv = document.getElementById('chrPager');
            if (!pagerDiv) return;
            
            const pageButtons = pagerDiv.querySelector('#chrPageButtons');
            const prevBtn = pagerDiv.querySelector('#chrPrevBtn');
            const nextBtn = pagerDiv.querySelector('#chrNextBtn');
            const pageInfo = pagerDiv.querySelector('#chrPageInfo');
            
            if (pageButtons) pageButtons.innerHTML = `<button type="button" disabled style="padding:4px 8px; border:1px solid #ddd; background:#f5f5f5;">${page}</button>`;
            if (prevBtn) {
                prevBtn.disabled = page <= 1;
                prevBtn.onclick = () => { if (page > 1) loadChrRequests(page - 1); };
            }
            if (nextBtn) {
                nextBtn.disabled = !hasMore;
                nextBtn.onclick = () => { if (hasMore) loadChrRequests(page + 1); };
            }
            
            if (pageInfo && total > 0) {
                const start = (page - 1) * pageSize + 1;
                const end = Math.min(start + pageSize - 1, total);
                pageInfo.textContent = `Showing ${start}-${end} of ${total} entries`;
            } else if (pageInfo) {
                pageInfo.textContent = '';
            }
        }

        async function approveChr(requestId, requestType){
            if (!confirm('Approve this request and generate PDF?')) return;
            try{
                const fd = new FormData(); 
                fd.append('request_id', requestId); 
                if (requestType) fd.append('request_type', requestType);
                const res = await fetch('../../php/supabase/bhw/approve_chr_doc.php', { method:'POST', body: fd });
                if (!res.ok) { throw new Error(`HTTP ${res.status}: ${res.statusText}`); }
                const j = await res.json();
                if (j.status === 'success'){
                    alert('Approved. PDF generated.');
                    loadChrRequests(currentPage); // Reload current page
                } else { alert('Approve failed: ' + (j.message||'Unknown error')); }
            }catch(e){ 
                console.error('Approve error:', e);
                alert('Network error approving request: ' + e.message); 
            }
        }
        </script>
    </main>
    <script src="../../js/header-handler/profile-menu.js" defer></script>
</body>
</html>

