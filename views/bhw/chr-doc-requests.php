<?php include 'Include/header.php'; ?>

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
			container.innerHTML = '<div class="no-data" style="text-align:center; padding: 20px; color:#666;">No pendingCHR requests</div>';
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
	if (!confirm('Approve this request and generate DOCX?')) return;
	try{
		const fd = new FormData(); fd.append('request_id', requestId); if (requestType) fd.append('request_type', requestType);
		const res = await fetch('/ebakunado/php/supabase/bhw/approve_chr_doc.php', { method:'POST', body: fd });
		const j = await res.json();
		if (j.status === 'success'){
			alert('Approved. DOCX generated.');
			loadChrRequests();
		} else {
			alert('Approve failed: ' + (j.message||'Unknown error'));
		}
	}catch(e){ alert('Network error approving request'); }
}
</script>

<?php include 'Include/footer.php'; ?>


