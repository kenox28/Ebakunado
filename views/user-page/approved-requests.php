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
    <style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .dl-babycard {
        transition: opacity 0.2s;
    }
    </style>
    <link rel="stylesheet" href="../../css/header.css?v=1.0.3" />
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
                const type = (r.request_type || '').toUpperCase();
                const approvedAt = formatDate(r.approved_at);
                const safeName = String(r.child_name||'').replace(/[^A-Za-z0-9]+/g,'');
                const docUrl = r.doc_url || ''; // Server-generated Baby Card PDF URL
                html += `
                <div class="request-item">
                    <div class="request-details">
                        <div class="request-row"><strong>ID:</strong> ${r.id}</div>
                        <div class="request-row"><strong>Baby ID:</strong> ${r.baby_id || ''}</div>
                        <div class="request-row"><strong>Child Name:</strong> ${r.child_name || ''}</div>
                        <div class="request-row"><strong>Type:</strong> ${type}</div>
                        <div class="request-row"><strong>Approved At:</strong> ${approvedAt}</div>
                        <div class="request-actions">
                            <a href="#" class="download-btn dl-babycard" data-baby="${r.baby_id||''}" data-name="${safeName}" data-doc-url="${docUrl}">
                                <span class="material-symbols-rounded" aria-hidden="true">download</span>
                                Download Baby Card
                            </a>
                        </div>
                    </div>
                </div>`;
            });
            tbody.innerHTML = html;
        }

        // Attach Baby Card download handler (no CHR download on user portal)
        document.querySelectorAll('.dl-babycard').forEach(a => {
            a.addEventListener('click', async (e) => {
                e.preventDefault();
                const babyId = a.getAttribute('data-baby');
                const nameSafe = a.getAttribute('data-name') || '';
                const docUrl = a.getAttribute('data-doc-url') || '';
                
                // Store original HTML and disable button
                const originalHTML = a.innerHTML;
                a.style.pointerEvents = 'none';
                a.style.opacity = '0.6';
                a.style.cursor = 'not-allowed';
                a.innerHTML = '<span class="material-symbols-rounded" style="display:inline-block;animation:spin 1s linear infinite;" aria-hidden="true">sync</span> Downloading...';
                
                try{
                    // If doc_url exists (server-generated Baby Card), download directly
                    if (docUrl && docUrl.trim() !== '') {
                        try {
                            // Try proxy first to avoid CORS
                            const proxyRes = await fetch(`/ebakunado/php/supabase/users/download_chr_doc.php?url=${encodeURIComponent(docUrl)}`, { credentials: 'same-origin' });
                            if (proxyRes.ok) {
                                const pdfBlob = await proxyRes.blob();
                                const fileName = `BabyCard_${nameSafe || 'Child'}.pdf`;
                                saveAs(pdfBlob, fileName);
                                // Restore button
                                a.style.pointerEvents = '';
                                a.style.opacity = '1';
                                a.style.cursor = 'pointer';
                                a.innerHTML = originalHTML;
                                return;
                            }
                        } catch (err) {
                            console.error('Proxy download failed:', err);
                            // Proxy failed, try direct download
                        }
                        
                        // Fallback: direct download
                        window.open(docUrl, '_blank');
                        // Restore button after a delay
                        setTimeout(() => {
                            a.style.pointerEvents = '';
                            a.style.opacity = '1';
                            a.style.cursor = 'pointer';
                            a.innerHTML = originalHTML;
                        }, 1000);
                        return;
                    }
                    
                    // Fallback: generate client-side if doc_url is not available
                    a.innerHTML = '<span class="material-symbols-rounded" style="display:inline-block;animation:spin 1s linear infinite;" aria-hidden="true">sync</span> Generating...';
                    const fd = new FormData();
                    fd.append('baby_id', babyId);
                    const cRes = await fetch('../../php/supabase/users/get_child_details.php', { method: 'POST', body: fd });
                    const cJson = await cRes.json();
                    if (!(cJson && cJson.status === 'success' && cJson.data && cJson.data[0])) {
                        throw new Error('Child details not found');
                    }
                    const child = cJson.data[0];
                    const iRes = await fetch('../../php/supabase/users/get_my_immunization_records.php', { method: 'POST', body: fd });
                    const iJson = await iRes.json();
                    const immunizations = Array.isArray(iJson.data) ? iJson.data : [];
                    a.innerHTML = '<span class="material-symbols-rounded" style="display:inline-block;animation:spin 1s linear infinite;" aria-hidden="true">sync</span> Creating PDF...';
                    const blob = await renderBabyCardPdf(child, immunizations);
                    const fileName = `BabyCard_${nameSafe || ((child.child_fname||'') + (child.child_lname||''))}.pdf`;
                    saveAs(blob, fileName);
                    // Restore button
                    a.style.pointerEvents = '';
                    a.style.opacity = '1';
                    a.style.cursor = 'pointer';
                    a.innerHTML = originalHTML;
                } catch (err) {
                    console.error('Baby Card generation failed:', err);
                    alert('Failed to generate Baby Card: ' + (err.message || 'Unknown error'));
                    // Restore button
                    a.style.pointerEvents = '';
                    a.style.opacity = '1';
                    a.style.cursor = 'pointer';
                    a.innerHTML = originalHTML;
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

<!-- Client-side ZIP builder dependencies -->
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
async function buildAndDownloadZipClient(ctx){
    const pdfBytes = await (async () => {
        try{
            const r1 = await fetch(`../../php/supabase/users/download_chr_doc.php?url=${encodeURIComponent(ctx.docUrl)}`, { credentials: 'same-origin' });
            if (r1.ok){ return await r1.arrayBuffer(); }
        }catch(_){}
        const r2 = await fetch(ctx.docUrl, { mode: 'cors' });
        if (!r2.ok) throw new Error('Failed to fetch PDF');
        return await r2.arrayBuffer();
    })();
    const child = await (async () => {
        const fd = new FormData(); fd.append('baby_id', ctx.babyId);
        const r = await fetch('../../php/supabase/users/get_child_details.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!(j && j.status==='success' && j.data && j.data.length>0)) throw new Error('Child details not found');
        return j.data[0];
    })();
    const immunizations = await (async () => {
        const fd = new FormData(); fd.append('baby_id', ctx.babyId);
        const r = await fetch('../../php/supabase/users/get_my_immunization_records.php', { method:'POST', body: fd });
        const j = await r.json();
        if (!(j && j.status==='success' && Array.isArray(j.data))) return [];
        return j.data;
    })();
    const cardPdfBytes = await renderBabyCardPdf(child, immunizations);
    const zip = new JSZip();
    zip.file('CHR_Document.pdf', pdfBytes);
    zip.file('Baby_Card.pdf', cardPdfBytes);
    const content = await zip.generateAsync({ type: 'blob' });
    const filename = `CHR_Package_${ctx.babyId}_${new Date().toISOString().slice(0,19).replace(/[:T]/g,'-')}.zip`;
    saveAs(content, filename);
}

function formatDateShort(iso){
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) {
        const p = (iso+'').split('T')[0].split('-'); 
        if (p.length===3){ 
            const m = parseInt(p[1],10), day=parseInt(p[2],10), y=parseInt(p[0],10)%100; 
            return `${m}/${day}/${y.toString().padStart(2,'0')}`; 
        }
        return iso;
    }
    const m = d.getMonth()+1, day = d.getDate(), y = (d.getFullYear()%100).toString().padStart(2,'0');
    return `${m}/${day}/${y}`;
}

async function renderBabyCardPdf(child, immunizations){
    const { jsPDF } = window.jspdf;
    // Load layout
    let layout=null; try{ const r=await fetch('../../assets/config/babycard_layout.json',{cache:'no-store'}); layout=await r.json(); }catch(_){}
    // Load background with fallback
    async function loadBg(src){ return new Promise((res)=>{ const i=new Image(); i.onload=()=>res(i); i.onerror=()=>res(null); i.src=src; }); }
    function toAbs(p){ if (!p) return p; return p.startsWith('../../') ? p : ('../../' + p.replace(/^\/+/,'') ); }
    let bg = null;
    if (layout && layout.background_path){ bg = await loadBg(toAbs(layout.background_path)); }
    if (!bg || !bg.naturalWidth){ bg = await loadBg(toAbs((layout && layout.fallback_background_path) || '/ebakunado/assets/images/babycard.jpg')); }
    if (!bg || !bg.naturalWidth){ throw new Error('Background not found'); }
    const page = (layout && layout.page) || { orientation:'landscape', unit:'mm', format:'a4' };
    const doc = new jsPDF({ orientation:page.orientation||'landscape', unit:page.unit||'mm', format:page.format||'a4' });
    const pageW = doc.internal.pageSize.getWidth(), pageH = doc.internal.pageSize.getHeight();
    const c=document.createElement('canvas'); c.width=bg.naturalWidth; c.height=bg.naturalHeight; c.getContext('2d').drawImage(bg,0,0);
    doc.addImage(c.toDataURL('image/jpeg',0.95),'JPEG',0,0,pageW,pageH);
    function draw(text,xpct,ypct,pt,align='left',maxWidthPct=null){
        const xNum=Number(xpct), yNum=Number(ypct); if(!isFinite(xNum)||!isFinite(yNum)) return;
        const txt=String(text??''); const size=pt||((layout&&layout.fonts&&layout.fonts.details_pt)||12);
        doc.setFont('helvetica','normal'); doc.setFontSize(size);
        if (maxWidthPct){ let wMax=(maxWidthPct/100)*pageW,s=size; while(s>8){doc.setFontSize(s); if(doc.getTextWidth(txt)<=wMax)break; s-=0.5;} }
        const x=(xNum/100)*pageW, y=(yNum/100)*pageH; const o=(align&& (align==='center'||align==='right'))?{align}:{};
        doc.setTextColor(255,255,255); doc.text(txt,x+0.5,y+0.8,o); doc.setTextColor(20,30,40); doc.text(txt,x,y,o);
    }
    const b=(layout&&layout.boxes)||{}, f=(layout&&layout.fonts)||{}, ex=(layout&&layout.extras)||{};
    const childName = `${child.child_fname||''} ${child.child_lname||''}`.trim();
    draw(childName, b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r1)||21, f.details_pt||12, 'left', b.max_width_pct||26);
    draw((child.child_birth_date||'').slice(0,10), b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r2)||26, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.place_of_birth||'', b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r3)||30, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.address||'', b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r4)||35, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.mother_name||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r1)||21, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.father_name||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r2)||26, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.birth_height||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r3)||30, f.details_pt||12, 'left', b.max_width_pct||26);
    const rightR4X=(typeof b.right_r4_x_pct==='number'?b.right_r4_x_pct:(b.right_x_pct||77));
    const rightR4Y=(typeof b.right_r4_y_pct==='number'?b.right_r4_y_pct:((b.rows_y_pct&&b.rows_y_pct.r4)||35));
    draw(child.birth_weight||'', rightR4X, rightR4Y, f.details_pt||12, 'left', b.max_width_pct||26);
    const g=(child.child_gender||'').toUpperCase();
    if (g.startsWith('M') && b.sex_m){ draw('X', b.sex_m.x_pct, b.sex_m.y_pct, f.details_pt||12, 'center'); }
    else if (g.startsWith('F') && b.sex_f){ draw('X', b.sex_f.x_pct, b.sex_f.y_pct, f.details_pt||12, 'center'); }
    if (ex.health_center){ draw((ex.health_center.text||''), ex.health_center.x_pct, ex.health_center.y_pct, f.details_pt||12, 'left', ex.health_center.max_width_pct||22); }
    if (ex.barangay){ draw((child.address||'').split(',').pop()?.trim()||'', ex.barangay.x_pct, ex.barangay.y_pct, f.details_pt||12, 'left', ex.barangay.max_width_pct||22); }
    if (ex.family_no){ draw(child.family_number||'', ex.family_no.x_pct, ex.family_no.y_pct, f.details_pt||12, 'left', ex.family_no.max_width_pct||22); }
    const v=(layout&&layout.vaccines)||{};
    function vkey(name){ const n=(name||'').toUpperCase(); if(n.includes('BCG'))return'BCG'; if(n.includes('HEP'))return'HEPATITIS B'; if(n.includes('PENTA')||n.includes('HIB'))return'PENTAVALENT'; if(n.includes('OPV')||n.includes('ORAL POLIO'))return'OPV'; if(n.includes('IPV')||n.includes('INACTIVATED'))return'IPV'; if(n.includes('PCV')||n.includes('PNEUMO'))return'PCV'; if(n.includes('MMR')||n.includes('MEASLES'))return'MMR'; return null; }
    immunizations
        .filter((v) => v.date_given)
        .forEach((v) => {
            const key = vkey(v.vaccine_name);
            if (!key) return;
            const dose = parseInt(v.dose_number, 10) || 1;
            let xp = v.cols_x_pct.c1;
            if (key === 'MMR') {
                // 2 doses → c1, c2
                xp = (dose === 1) ? v.cols_x_pct.c1 : v.cols_x_pct.c2;
            } else if (key === 'BCG' || key === 'HEPATITIS B' || key === 'IPV') {
                // single slot
                xp = v.cols_x_pct.c1;
            } else {
                // PENTAVALENT / OPV / PCV → c1/c2/c3
                if (dose === 1)      { xp = v.cols_x_pct.c1; }
                else if (dose === 2) { xp = v.cols_x_pct.c2; }
                else                 { xp = v.cols_x_pct.c3; }
            }
            const y = v.rows_y_pct[key] || v.rows_y_pct[(key==='HEPATITIS B' ? 'HEPATITIS B' : key)];
            draw(
                formatDateShort(v.date_given),
                xp,
                y,
                f.vaccines_pt || 11,
                'center'
            );
        });
    return doc.output('blob');
}
function loadImage(src){
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.crossOrigin = 'anonymous';
        img.src = src;
    });
}
</script>

    <script src="../../js/header-handler/profile-menu.js?v=1.0.4" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
</body>
