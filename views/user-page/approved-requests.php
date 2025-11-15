<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}


// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Requests</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css?v=1.0.3" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/skeleton-loading.css" />
    <link rel="stylesheet" href="../../css/user/approved-requests.css?v=1.0.2" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="dashboard section-title">
                <span class="material-symbols-rounded">task_alt</span>
                Approved Requests
            </h2>
        </section>

        <section class="approved-requests-section">
            <div class="content">
                <div id="approvedContainer" class="table-container">
                    <table id="approvedTable" class="table" aria-busy="true">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Baby ID</th>
                                <th>Child Name</th>
                                <th>Type</th>
                                <th>Approved At</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody id="approvedBody"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/utils/skeleton-loading.js" defer></script>
    <script>
document.addEventListener('DOMContentLoaded', loadApprovedRequests);

async function loadApprovedRequests(){
    const table = document.getElementById('approvedTable');
    const tbody = document.getElementById('approvedBody');
    if (table) table.setAttribute('aria-busy', 'true');

    // Apply skeleton shimmer rows before fetch
    function getApprovedColsConfig(){
        return [
            { type: 'text', widthClass: 'skeleton-col-1' }, // ID
            { type: 'text', widthClass: 'skeleton-col-2' }, // Baby ID
            { type: 'text', widthClass: 'skeleton-col-3' }, // Child Name
            { type: 'text', widthClass: 'skeleton-col-2' }, // Type
            { type: 'text', widthClass: 'skeleton-col-2' }, // Approved At
            { type: 'pill', widthClass: 'skeleton-col-3' }  // Download action
        ];
    }
    if (typeof applyTableSkeleton === 'function' && tbody){
        applyTableSkeleton(tbody, getApprovedColsConfig(), 10);
    } else if (tbody){
        tbody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
    }

    try{
        const res = await fetch('/ebakunado/php/supabase/users/get_my_chr_requests.php');
        if (!res.ok){
            if (typeof renderTableMessage === 'function') {
                renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 6, kind: 'error' });
            } else {
                tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
            }
            return;
        }
        const j = await res.json();
        if (!j || j.status !== 'success') {
            if (typeof renderTableMessage === 'function') {
                renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 6, kind: 'error' });
            } else {
                tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
            }
            return;
        }
        const rows = Array.isArray(j.data) ? j.data : [];

        // Helper: add fl_attachment to Cloudinary URL
        const toAttachment = (u) => {
            if (!u) return '';
            try{
                return u.replace(/\/(image|raw)\/upload\//, '/$1/upload/fl_attachment/');
            }catch(e){ return u; }
        };

            // Helper: format date as "Mon D, YYYY"
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return String(dateStr);
                return d.toLocaleDateString(undefined, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            };

        if (!rows.length){
            if (typeof renderTableMessage === 'function') {
                renderTableMessage(tbody, 'No records found', { colspan: 6 });
            } else {
                tbody.innerHTML = '<tr class="message-row"><td colspan="6">No records found</td></tr>';
            }
        } else {
            let html = '';
            rows.forEach(r => {
                const url = r.doc_url || '';
                const type = (r.request_type || '').toUpperCase();
                    const approvedAt = formatDate(r.approved_at);
                html += `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.baby_id || ''}</td>
                    <td>${r.child_name || ''}</td>
                    <td>${type}</td>
                    <td>${approvedAt}</td>
                        <td class="text-center">${url ? `<a href="${toAttachment(url)}" class="download-btn dl-direct" download><span class="material-symbols-rounded" aria-hidden="true">download</span> Download</a>` : '-'}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        // Attach direct download (no proxy)
        document.querySelectorAll('.dl-direct').forEach(a => {
            a.addEventListener('click', async (e) => {
                e.preventDefault();
                try{
                    const href = a.getAttribute('href');
                    const res = await fetch(href, { method: 'GET' });
                    if (!res.ok){
                        const text = await res.text().catch(()=> '');
                        console.error('Cloudinary direct download error:', res.status, text);
                        alert('Download failed: ' + (text || ('HTTP ' + res.status)));
                        return;
                    }
                    const blob = await res.blob();
                    const objectUrl = URL.createObjectURL(blob);
                    const tmp = document.createElement('a');
                    tmp.href = objectUrl;
                    tmp.download = 'CHR_Document_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.pdf';
                    document.body.appendChild(tmp);
                    tmp.click();
                    tmp.remove();
                    URL.revokeObjectURL(objectUrl);
                }catch(err){
                    console.error('Download network error:', err);
                    alert('Network error: ' + (err && err.message ? err.message : String(err)));
                }
            });
        });
    }catch(e){
        if (typeof renderTableMessage === 'function') {
            renderTableMessage(tbody, 'Failed to load data. Please try again.', { colspan: 6, kind: 'error' });
        } else if (tbody){
            tbody.innerHTML = '<tr class="message-row error"><td colspan="6">Failed to load data. Please try again.</td></tr>';
        }
    } finally {
        if (table) table.setAttribute('aria-busy', 'false');
    }
}
</script>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
</body>
