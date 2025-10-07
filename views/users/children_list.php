<?php include 'Include/header.php'; ?>

<div class="content" style="padding: 16px;">
	<h2 style="margin: 0 0 12px 0;">My Children</h2>
	<div id="childrenContainer">
		<div class="loading" style="text-align:center; padding: 20px;">
			<p>Loading children data...</p>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function(){
	const container = document.getElementById('childrenContainer');
	try{
		const res = await fetch('../../php/supabase/users/get_accepted_child.php');
		const data = await res.json();
		if (!(data && data.status === 'success' && Array.isArray(data.data) && data.data.length > 0)){
			container.innerHTML = '<div class="no-data" style="text-align:center; padding:20px; color:#666;">No children found</div>';
			return;
		}
		const rows = data.data;
		let html = '';
		html += '<table border="1" style="width:100%; border-collapse:collapse; font-size:13px;">';
		html += '<thead><tr>'+
			'<th style="padding:6px;">Name</th>'+
			'<th style="padding:6px;">Age</th>'+
			'<th style="padding:6px;">Gender</th>'+
			'<th style="padding:6px;">Upcoming</th>'+
			'<th style="padding:6px;">Missed</th>'+
			'<th style="padding:6px;">Taken</th>'+
			'<th style="padding:6px;">Action</th>'+
			'</tr></thead>';
		html += '<tbody>';
		rows.forEach(child => {
			const fullName = (child.name) || [child.child_fname||'', child.child_lname||''].filter(Boolean).join(' ');
			const ageText = (child.age && child.age > 0) ? (child.age + ' years') : (child.weeks_old != null ? (child.weeks_old + ' weeks') : '');
			const gender = child.gender || '';
			const babyId = child.baby_id || child.id || '';
			const upc = child.scheduled_count || 0;
			const mis = child.missed_count || 0;
			const tak = child.taken_count || 0;
			html += '<tr>'+
				`<td style="padding:6px;">${fullName}</td>`+
				`<td style="padding:6px;">${ageText}</td>`+
				`<td style="padding:6px;">${gender}</td>`+
				`<td style="padding:6px; text-align:center;">${upc}</td>`+
				`<td style="padding:6px; text-align:center;">${mis}</td>`+
				`<td style="padding:6px; text-align:center;">${tak}</td>`+
				'<td style="padding:6px; text-align:center;">'+
					(babyId ? `<a href="child_health_record.php?baby_id=${encodeURIComponent(String(babyId))}" style="padding:4px 8px; display:inline-block;">View</a>` : '<span style="color:#999;">N/A</span>')+
				'</td>'+
			'</tr>';
		});
		html += '</tbody></table>';
		container.innerHTML = html;
	}catch(err){
		container.innerHTML = '<div class="no-data" style="text-align:center; padding:20px; color:#dc3545;">Error loading children</div>';
		console.error('children_list load error', err);
	}
});
</script>

<?php include 'Include/footer.php'; ?>


