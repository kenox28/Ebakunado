<?php include 'Include/header.php'; ?>

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
    // Helper: add fl_attachment to Cloudinary URL
    const toAttachment = (u) => {
        if (!u) return '';
        try{
            return u.replace(/\/(image|raw)\/upload\//, '/$1/upload/fl_attachment/');
        }catch(e){ return u; }
    };

    rows.forEach(r => {
        const url = r.doc_url||'';
			html += '<tr>'+
				`<td style="padding:6px;">${r.id}</td>`+
				`<td style="padding:6px;">${r.baby_id||''}</td>`+
				`<td style=\"padding:6px;\">${r.child_name||''}</td>`+
				`<td style="padding:6px;">${(r.request_type||'').toUpperCase()}</td>`+
				`<td style="padding:6px;">${(r.approved_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
            `<td style="padding:6px; text-align:center;">${url?`<a href="${toAttachment(url)}" class="dl-direct" download>Download</a>`:'-'}</td>`+
			'</tr>';
		});
		html += '</tbody></table>';
		root.innerHTML = html;

    // Attach direct download with console logging (no proxy)
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
	}catch(e){ root.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>'; }
}
</script>

<?php include 'Include/footer.php'; ?>


