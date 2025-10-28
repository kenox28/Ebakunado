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
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', loadChrRequests);

        async function loadChrRequests(){
            const container = document.getElementById('reqContainer');
            container.innerHTML = '<div class="loading" style="text-align:center; padding: 20px;">Loading requests...</div>';
            try{
                const res = await fetch('/ebakunado/php/supabase/bhw/list_chr_doc_requests.php');
                const data = await res.json();
                if (!(data && data.status === 'success')){
                    container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Failed to load requests</div>';
                    return;
                }
                const rows = Array.isArray(data.data) ? data.data : [];
                if (rows.length === 0){
                    container.innerHTML = '<div class="no-data" style="text-align:center; padding: 20px; color:#666;">No pending CHR requests</div>';
                    return;
                }
                let html = '';
                html += '<table border="1" style="width:100%; border-collapse:collapse; font-size:13px;">';
                html += '<thead><tr>'+
                    '<th style="padding:6px;">Request ID</th>'+
                    '<th style="padding:6px;">User ID</th>'+
                    '<th style="padding:6px;">Baby ID</th>'+
                    '<th style="padding:6px;">Type</th>'+
                    '<th style="padding:6px;">Status</th>'+
                    '<th style="padding:6px;">Requested At</th>'+
                    '<th style="padding:6px;">Action</th>'+
                    '</tr></thead>';
                html += '<tbody>';
                rows.forEach(r => {
                    html += '<tr>'+
                        `<td style="padding:6px;">${r.id}</td>`+
                        `<td style="padding:6px;">${r.user_id||''}</td>`+
                        `<td style="padding:6px;">${r.baby_id||''}</td>`+
                        `<td style="padding:6px;">${(r.request_type||'').toUpperCase()}</td>`+
                        `<td style="padding:6px;">${r.status||''}</td>`+
                        `<td style="padding:6px;">${(r.created_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
                        `<td style="padding:6px; text-align:center;"><button style="padding:4px 8px;" onclick="approveChr(${r.id}, '${(r.request_type||'').toLowerCase()}')">Approve & Generate</button></td>`+
                    '</tr>';
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            }catch(e){
                container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>';
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
                    loadChrRequests();
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

