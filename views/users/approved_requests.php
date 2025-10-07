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
		const res = await fetch('../../php/supabase/users/get_my_chr_requests.php');
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
			html += '<tr>'+
				`<td style="padding:6px;">${r.id}</td>`+
				`<td style="padding:6px;">${r.baby_id||''}</td>`+
				`<td style=\"padding:6px;\">${r.child_name||''}</td>`+
				`<td style="padding:6px;">${(r.request_type||'').toUpperCase()}</td>`+
				`<td style="padding:6px;">${(r.approved_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
				`<td style="padding:6px; text-align:center;">${url?`<a href="${url}" target="_blank">Download</a>`:'-'}</td>`+
			'</tr>';
		});
		html += '</tbody></table>';
		root.innerHTML = html;
	}catch(e){ root.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>'; }
}
</script>

<?php include 'Include/footer.php'; ?>


