<?php include 'Include/header.php'; ?>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.dl-babycard {
    cursor: pointer;
    transition: opacity 0.2s;
}
.dl-babycard:disabled {
    cursor: not-allowed;
}
</style>

<div class="content" style="padding: 16px;">
	<h2 style="margin:0 0 12px 0;">Approved Requests</h2>
	<div id="approvedRoot">
		<div style="text-align:center; padding: 20px;">Loading...</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadApprovedRequests);

async function loadApprovedRequests(){
	const root = document.getElementById('approvedRoot');
	root.innerHTML = '<div style="text-align:center; padding: 20px;">Loading...</div>';
	try{
		const res = await fetch('/ebakunado/php/supabase/users/get_my_chr_requests.php');
		const j = await res.json();
		if (!(j && j.status==='success')){ root.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Failed to load</div>'; return; }
		const rows = Array.isArray(j.data) ? j.data : [];
		if (rows.length===0){ root.innerHTML = '<div style="text-align:center; color:#666; padding:20px;">No approved requests yet</div>'; return; }
		let html = '';
		html += '<table border="1" style="width:100%; border-collapse:collapse; font-size:13px;">';
		html += '<thead><tr>'+
			'<th style="padding:6px;">ID</th>'+
			'<th style="padding:6px;">Baby ID</th>'+
			'<th style="padding:6px;">Child Name</th>'+
			'<th style="padding:6px;">Type</th>'+
			'<th style="padding:6px;">Approved At</th>'+
			'<th style="padding:6px;">Download</th>'+
			'</tr></thead>';
		html += '<tbody>';

    rows.forEach(r => {
        const url = r.doc_url||'';
        // Check if this is a Baby Card request (status was pendingBabyCard before approval)
        // For Baby Card requests, doc_url contains the Baby Card PDF
        const docUrl = r.doc_url || '';
			html += '<tr>'+
				`<td style="padding:6px;">${r.id}</td>`+
				`<td style="padding:6px;">${r.baby_id||''}</td>`+
				`<td style=\"padding:6px;\">${r.child_name||''}</td>`+
				`<td style="padding:6px;">${(r.request_type||'').toUpperCase()}</td>`+
				`<td style="padding:6px;">${(r.approved_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
				`<td style="padding:6px; text-align:center;"><a href="#" class="dl-babycard" data-baby="${r.baby_id||''}" data-name="${(r.child_name||'').replace(/[^A-Za-z0-9]+/g,'')}" data-doc-url="${docUrl}">Download Baby Card</a></td>`+
			'</tr>';
		});
		html += '</tbody></table>';
		root.innerHTML = html;

    // Attach Baby Card downloader (no CHR in user portal)
    document.querySelectorAll('.dl-babycard').forEach(a => {
        a.addEventListener('click', async (e) => {
            e.preventDefault();
            const babyId = a.getAttribute('data-baby');
            const preName = a.getAttribute('data-name') || '';
            const docUrl = a.getAttribute('data-doc-url') || '';
            
            // Store original text and disable button
            const originalText = a.textContent || a.innerText;
            const originalHTML = a.innerHTML;
            a.disabled = true;
            a.style.pointerEvents = 'none';
            a.style.opacity = '0.6';
            a.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Downloading...';
            
            try{
                // If doc_url exists (server-generated Baby Card), download directly
                if (docUrl && docUrl.trim() !== '') {
                    try {
                        // Try proxy first to avoid CORS
                        const proxyRes = await fetch(`/ebakunado/php/supabase/users/download_chr_doc.php?url=${encodeURIComponent(docUrl)}`, { credentials: 'same-origin' });
                        if (proxyRes.ok) {
                            const pdfBlob = await proxyRes.blob();
                            const baseName = preName || 'Child';
                            const filename = `BabyCard_${baseName}.pdf`;
                            saveAs(pdfBlob, filename);
                            // Restore button
                            a.disabled = false;
                            a.style.pointerEvents = '';
                            a.style.opacity = '1';
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
                        a.disabled = false;
                        a.style.pointerEvents = '';
                        a.style.opacity = '1';
                        a.innerHTML = originalHTML;
                    }, 1000);
                    return;
                }
                
                // Fallback: generate client-side if doc_url is not available
                a.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Generating...';
                const fd = new FormData();
                fd.append('baby_id', babyId);
                const cRes = await fetch('/ebakunado/php/supabase/users/get_child_details.php', { method: 'POST', body: fd });
                const cJson = await cRes.json();
                if (!(cJson && cJson.status === 'success' && cJson.data && cJson.data[0])){
                    throw new Error('Child details not found');
                }
                const child = cJson.data[0];
                const iRes = await fetch('/ebakunado/php/supabase/users/get_my_immunization_records.php', { method:'POST', body: fd });
                const iJson = await iRes.json();
                const immunizations = Array.isArray(iJson.data) ? iJson.data : [];
                a.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Creating PDF...';
                const blob = await renderBabyCardPdf(child, immunizations);
                const baseName = preName || `${(child.child_fname||'')}${(child.child_lname||'')}`.replace(/[^A-Za-z0-9]+/g,'');
                const filename = `BabyCard_${baseName || 'Child'}.pdf`;
                saveAs(blob, filename);
                // Restore button
                a.disabled = false;
                a.style.pointerEvents = '';
                a.style.opacity = '1';
                a.innerHTML = originalHTML;
            }catch(err){
                console.error('Baby Card generation failed:', err);
                alert('Failed to generate Baby Card: ' + (err.message || 'Unknown error'));
                // Restore button
                a.disabled = false;
                a.style.pointerEvents = '';
                a.style.opacity = '1';
                a.innerHTML = originalHTML;
            }
        });
    });
	}catch(e){ root.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>'; }
}
</script>

<!-- Client-side ZIP builder dependencies -->
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
async function buildAndDownloadZipClient(ctx){
    // 1) Fetch PDF bytes (try proxy first to avoid CORS)
    const pdfBytes = await (async () => {
        try{
            const r1 = await fetch(`/ebakunado/php/supabase/users/download_chr_doc.php?url=${encodeURIComponent(ctx.docUrl)}`, { credentials: 'same-origin' });
            if (r1.ok){ return await r1.arrayBuffer(); }
        }catch(_){ /* ignore */ }
        const r2 = await fetch(ctx.docUrl, { mode: 'cors' });
        if (!r2.ok) throw new Error('Failed to fetch PDF');
        return await r2.arrayBuffer();
    })();

    // 2) Fetch child profile
    const child = await (async () => {
        const fd = new FormData(); fd.append('baby_id', ctx.babyId);
        const r = await fetch('/ebakunado/php/supabase/users/get_child_details.php', { method: 'POST', body: fd });
        const j = await r.json();
        if (!(j && j.status==='success' && j.data && j.data.length>0)) throw new Error('Child details not found');
        return j.data[0];
    })();

    // 3) Fetch immunizations
    const immunizations = await (async () => {
        const fd = new FormData(); fd.append('baby_id', ctx.babyId);
        const r = await fetch('/ebakunado/php/supabase/users/get_my_immunization_records.php', { method:'POST', body: fd });
        const j = await r.json();
        if (!(j && j.status==='success' && Array.isArray(j.data))) return [];
        return j.data;
    })();

    // 4) Build Baby Card PDF with background image for 100% design match
    const cardPdfBytes = await renderBabyCardPdf(child, immunizations);

    // 5) ZIP and download
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
        // try YYYY-MM-DD
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
    // 1) Load layout JSON
    let layout;
    try{
        const r = await fetch('/ebakunado/assets/config/babycard_layout.json', { cache:'no-store' });
        layout = await r.json();
    }catch(_){
        layout = null;
    }
    // 2) Resolve background
    async function loadBg(src){
        return new Promise((resolve)=>{ const i=new Image(); i.onload=()=>resolve(i); i.onerror=()=>resolve(null); i.src=src; });
    }
    function toAbs(p){ if (!p) return p; return p.startsWith('/ebakunado/') ? p : ('/ebakunado/' + p.replace(/^\/+/,'') ); }
    let bg = null;
    if (layout && layout.background_path){ bg = await loadBg(toAbs(layout.background_path)); }
    if (!bg || !bg.naturalWidth){ bg = await loadBg(toAbs((layout && layout.fallback_background_path) || '/ebakunado/assets/images/babycard.jpg')); }
    if (!bg || !bg.naturalWidth){ throw new Error('Background not found'); }
    // 3) Create PDF
    const page = (layout && layout.page) || { orientation:'landscape', unit:'mm', format:'a4' };
    const doc = new jsPDF({ orientation: page.orientation||'landscape', unit: page.unit||'mm', format: page.format||'a4' });
    const pageW = doc.internal.pageSize.getWidth();
    const pageH = doc.internal.pageSize.getHeight();
    const c = document.createElement('canvas'); c.width = bg.naturalWidth; c.height = bg.naturalHeight;
    c.getContext('2d').drawImage(bg,0,0);
    doc.addImage(c.toDataURL('image/jpeg',0.95),'JPEG',0,0,pageW,pageH);
    // 4) Drawing helper
    function draw(text, xpct, ypct, pt, align='left', maxWidthPct=null){
        const xNum = Number(xpct), yNum = Number(ypct);
        if (!isFinite(xNum)||!isFinite(yNum)) return;
        const txt = String(text ?? '');
        const size = pt || ((layout && layout.fonts && layout.fonts.details_pt) || 12);
        doc.setFont('helvetica','normal'); doc.setFontSize(size);
        if (maxWidthPct){
            let wMax = (maxWidthPct/100)*pageW, s=size;
            while (s>8){ doc.setFontSize(s); if (doc.getTextWidth(txt)<=wMax) break; s-=0.5; }
        }
        const x = (xNum/100)*pageW, y=(yNum/100)*pageH;
        const o = (align && (align==='center'||align==='right')) ? {align} : {};
        doc.setTextColor(255,255,255); doc.text(txt, x+0.5, y+0.8, o);
        doc.setTextColor(20,30,40); doc.text(txt, x, y, o);
    }
    const b = (layout && layout.boxes) || {};
    const f = (layout && layout.fonts) || {};
    const ex = (layout && layout.extras) || {};
    // 5) Child details
    const childName = `${child.child_fname||''} ${child.child_lname||''}`.trim();
    draw(childName, b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r1)||21, f.details_pt||12, 'left', b.max_width_pct||26);
    draw((child.child_birth_date||'').slice(0,10), b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r2)||26, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.place_of_birth||'', b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r3)||30, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.address||'', b.left_x_pct||36, (b.rows_y_pct&&b.rows_y_pct.r4)||35, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.mother_name||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r1)||21, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.father_name||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r2)||26, f.details_pt||12, 'left', b.max_width_pct||26);
    draw(child.birth_height||'', b.right_x_pct||77, (b.rows_y_pct&&b.rows_y_pct.r3)||30, f.details_pt||12, 'left', b.max_width_pct||26);
    const rightR4X = (typeof b.right_r4_x_pct==='number'? b.right_r4_x_pct : (b.right_x_pct||77));
    const rightR4Y = (typeof b.right_r4_y_pct==='number'? b.right_r4_y_pct : ((b.rows_y_pct&&b.rows_y_pct.r4)||35));
    draw(child.birth_weight||'', rightR4X, rightR4Y, f.details_pt||12, 'left', b.max_width_pct||26);
    // Sex mark X only
    const g = (child.child_gender||'').toUpperCase();
    if (g.startsWith('M') && b.sex_m){ draw('X', b.sex_m.x_pct, b.sex_m.y_pct, f.details_pt||12, 'center'); }
    else if (g.startsWith('F') && b.sex_f){ draw('X', b.sex_f.x_pct, b.sex_f.y_pct, f.details_pt||12, 'center'); }
    // Extras: Health Center, Barangay, Family No
    if (ex.health_center){ draw((ex.health_center.text||''), ex.health_center.x_pct, ex.health_center.y_pct, f.details_pt||12, 'left', ex.health_center.max_width_pct||22); }
    if (ex.barangay){ draw((child.address||'').split(',').pop()?.trim()||'', ex.barangay.x_pct, ex.barangay.y_pct, f.details_pt||12, 'left', ex.barangay.max_width_pct||22); }
    if (ex.family_no){ draw(child.family_number||'', ex.family_no.x_pct, ex.family_no.y_pct, f.details_pt||12, 'left', ex.family_no.max_width_pct||22); }
    // 6) Vaccines
    const v = (layout && layout.vaccines) || {};
    function vkey(name){
        const n = (name || '').toUpperCase();
        if (n.includes('BCG')) return 'BCG';
        if (n.includes('HEP')) return 'HEPATITIS B';
        if (n.includes('PENTA') || n.includes('HIB')) return 'PENTAVALENT';
        if (n.includes('OPV') || n.includes('ORAL POLIO')) return 'OPV';
        // IPV removed from layout/schedule
        if (n.includes('PCV') || n.includes('PNEUMO')) return 'PCV';
        if (n.includes('MMR') || n.includes('MEASLES')) return 'MMR';
        return null;
    }
    immunizations.filter(r => r.date_given).forEach(r => {
        const key = vkey(r.vaccine_name); if (!key) return;
        const dose = parseInt(r.dose_number, 10) || 1;
        let xp = v.cols_x_pct ? v.cols_x_pct.c1 : 60.2;
        if (key === 'MMR') {
            // MMR has 2 doses → place in c1 (dose 1) or c2 (dose 2)
            if (dose === 2) {
                xp = (v.cols_x_pct ? v.cols_x_pct.c2 : 69.2);
            } else {
                xp = (v.cols_x_pct ? v.cols_x_pct.c1 : 60.2);
            }
        } else if (key === 'BCG' || key === 'HEPATITIS B') {
            // Single-dose rows always use the first column
            xp = (v.cols_x_pct ? v.cols_x_pct.c1 : 60.2);
        } else {
            // PENTAVALENT / OPV / PCV → 3 doses map to c1, c2, c3
            if (dose === 1)      { xp = (v.cols_x_pct ? v.cols_x_pct.c1 : 60.2); }
            else if (dose === 2) { xp = (v.cols_x_pct ? v.cols_x_pct.c2 : 69.2); }
            else                 { xp = (v.cols_x_pct ? v.cols_x_pct.c3 : 78.2); }
        }
        const y = (v.rows_y_pct && (v.rows_y_pct[key] || v.rows_y_pct[(key === 'HEPATITIS B' ? 'HEPATITIS B' : key)])) || 56.1;
        draw(formatDateShort(r.date_given), xp, y, (f.vaccines_pt || 11), 'center');
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

<?php include 'Include/footer.php'; ?>


