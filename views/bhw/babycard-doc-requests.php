<?php include 'Include/header.php'; ?>

<div class="content" style="padding: 15px;">
	<h2 style="margin: 0 0 12px 0;">Baby Card Requests</h2>
	<div id="reqContainer">
		<div class="loading" style="text-align:center; padding: 20px;">Loading requests...</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadBabycardRequests);

async function loadBabycardRequests(){
	const container = document.getElementById('reqContainer');
	container.innerHTML = '<div class="loading" style="text-align:center; padding: 20px;">Loading requests...</div>';
	try{
		const res = await fetch('/ebakunado/php/supabase/bhw/list_babycard_doc_requests.php');
		const data = await res.json();
		if (!(data && data.status === 'success')){
			container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Failed to load requests</div>';
			return;
		}
		const rows = Array.isArray(data.data) ? data.data : [];
		if (rows.length === 0){
			container.innerHTML = '<div class="no-data" style="text-align:center; padding: 20px; color:#666;">No pending Baby Card requests</div>';
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
				`<td style="padding:6px;">BABY CARD</td>`+
				`<td style="padding:6px;">${r.status||''}</td>`+
				`<td style="padding:6px;">${(r.created_at||'').toString().replace('T',' ').split('.')[0]}</td>`+
				`<td style="padding:6px; text-align:center;"><button style="padding:4px 8px;" onclick="approveBabycard(${r.id})">Approve & Generate</button></td>`+
			'</tr>';
		});
		html += '</tbody></table>';
		container.innerHTML = html;
	}catch(e){
		container.innerHTML = '<div style="text-align:center; color:#dc3545; padding:20px;">Network error</div>';
	}
}

async function approveBabycard(requestId){
	if (!confirm('Approve this request and generate Baby Card PDF?')) return;
	try{
		const fd = new FormData(); 
		fd.append('request_id', requestId); 
		console.log('Sending approval request for ID:', requestId);
		
		const res = await fetch('../../php/supabase/bhw/approve_babycard_doc.php', { 
			method:'POST', 
			body: fd 
		});
		console.log('Response status:', res.status, res.statusText);
		
		// Get response text first to see what we're getting
		const responseText = await res.text();
		console.log('Response text:', responseText);
		
		if (!res.ok) {
			// Try to parse as JSON to get error message
			let errorMsg = `HTTP ${res.status}: ${res.statusText}`;
			try {
				const errorJson = JSON.parse(responseText);
				errorMsg = errorJson.message || errorMsg;
				console.error('Error JSON:', errorJson);
			} catch (e) {
				console.error('Response is not JSON:', responseText);
			}
			throw new Error(errorMsg);
		}
		
		const j = JSON.parse(responseText);
		console.log('Response JSON:', j);
		
		if (j.status === 'success'){
			alert('Approved. Baby Card PDF generated.');
			loadBabycardRequests();
		} else {
			console.error('Approve failed with status:', j.status, 'Message:', j.message);
			alert('Approve failed: ' + (j.message||'Unknown error'));
		}
	}catch(e){ 
		console.error('Approve error:', e);
		console.error('Error stack:', e.stack);
		alert('Network error approving request: ' + e.message); 
	}
}
</script>

<?php include 'Include/footer.php'; ?>

