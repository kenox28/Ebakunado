<?php include 'Include/header.php'; ?>

<div class="content" style="padding: 16px;">
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
		<h2 style="margin: 0;">My Children</h2>
		<div style="display: flex; align-items: center; gap: 8px;">
			<label for="chrFilter" style="font-size: 14px; font-weight: bold;">CHR Status:</label>
			<select id="chrFilter" style="padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
				<option value="all">All Children</option>
				<option value="pending">Pending Registration</option>
				<option value="approved" selected>Approved Children</option>
			</select>
		</div>
	</div>
	<div id="childrenContainer">
		<div class="loading" style="text-align:center; padding: 20px;">
			<p>Loading children data...</p>
		</div>
	</div>
</div>

<script>
let allChildrenData = [];
let allChrStatusData = [];

document.addEventListener('DOMContentLoaded', async function(){
	const container = document.getElementById('childrenContainer');
	const filterSelect = document.getElementById('chrFilter');
	
	// Load children data and CHR status data
    await Promise.all([loadChildrenData(), loadChrStatusData()]);
	
	// Render the table
	renderChildrenTable();
	
	// Add filter event listener
	filterSelect.addEventListener('change', function() {
		renderChildrenTable();
	});
});

async function loadChildrenData() {
	try {
		// Load all children data (not just accepted ones) for proper filtering
		const res = await fetch('../../php/supabase/users/get_accepted_child.php');
		const data = await res.json();
		allChildrenData = (data && data.status === 'success' && Array.isArray(data.data)) ? data.data : [];
	} catch(err) {
		console.error('Error loading children data:', err);
		allChildrenData = [];
	}
}

async function loadChrStatusData() {
	try {
        const res = await fetch('../../php/supabase/users/get_child_list.php');
		const data = await res.json();
		allChrStatusData = (data && data.status === 'success' && Array.isArray(data.data)) ? data.data : [];
	} catch(err) {
		console.error('Error loading CHR status data:', err);
		allChrStatusData = [];
	}
}

function renderChildrenTable() {
	const container = document.getElementById('childrenContainer');
	const filterSelect = document.getElementById('chrFilter');
	const selectedFilter = filterSelect.value;
	
	if (allChildrenData.length === 0) {
		container.innerHTML = '<div class="no-data" style="text-align:center; padding:20px; color:#666;">No children found</div>';
		return;
	}
	
	// Filter children based on selected filter
	let filteredChildren = allChildrenData.filter(child => {
		const babyId = child.baby_id || child.id || '';
		const childStatus = child.status; // This is the child registration status
		const chrStatus = getChrStatusForChild(babyId);
		
		switch(selectedFilter) {
			case 'pending':
				return childStatus === 'pending'; // Child registration is pending BHW approval
			case 'approved':
				return childStatus === 'accepted'; // Child is approved by BHW
			case 'all':
			default:
				return true; // Show all children
		}
	});
	
	if (filteredChildren.length === 0) {
		container.innerHTML = `<div class="no-data" style="text-align:center; padding:20px; color:#666;">No children found for "${filterSelect.options[filterSelect.selectedIndex].text}"</div>`;
		return;
	}
	
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
	
	filteredChildren.forEach(child => {
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
}

function getChrStatusForChild(babyId) {
	const chrData = allChrStatusData.find(item => item.baby_id === babyId);
	return chrData ? chrData.chr_status : 'none';
}

function getChrStatusText(status) {
	switch(status) {
		case 'pending':
			return 'Pending Request';
		case 'approved':
			return 'Approved';
		case 'new_records':
			return 'New Records Available';
		case 'none':
		default:
			return 'No Request';
	}
}

function getChrStatusColor(status) {
	switch(status) {
		case 'pending':
			return '#ffc107'; // Yellow
		case 'approved':
			return '#28a745'; // Green
		case 'new_records':
			return '#007bff'; // Blue
		case 'none':
		default:
			return '#6c757d'; // Gray
	}
}
</script>

<?php include 'Include/footer.php'; ?>


