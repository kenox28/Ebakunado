<?php include 'Include/header.php'; ?>

<div class="content">
	<!-- Dashboard Stats -->
	<div class="dashboard-stats">
		<div class="stat-card total">
			<div class="stat-icon">👶</div>
			<div class="stat-info">
				<h3 id="totalChildren">0</h3>
				<p>Total Children</p>
			</div>
		</div>
		<div class="stat-card approved">
			<div class="stat-icon">📋</div>
			<div class="stat-info">
				<h3 id="approvedChr">0</h3>
				<p>Approved CHR Documents</p>
			</div>
		</div>
		<div class="stat-card pending">
			<div class="stat-icon">⚠️</div>
			<div class="stat-info">
				<h3 id="missedCount">0</h3>
				<p>Missed/Delayed Immunizations</p>
			</div>
		</div>
		<div class="stat-card today">
			<div class="stat-icon">📅</div>
			<div class="stat-info">
				<h3 id="todaySchedule">0</h3>
				<p>Upcoming Schedule for Today</p>
			</div>
		</div>
	</div>

	<!-- Children List -->
	<div class="children-section">
		<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;">
			<h2 style="margin:0;">My Children</h2>
			<div style="display:flex; gap:8px;">
				<button id="btnUpcoming" class="btn btn-primary" onclick="selectFilter('upcoming')">Upcoming (<span id="upcomingCount">0</span>)</button>
				<button id="btnMissed" class="btn" onclick="selectFilter('missed')">Missed (<span id="missedCountBtn">0</span>)</button>
			</div>
		</div>
		<div class="children-list" id="childrenList">
			<div class="loading">
				<div style="display: flex; align-items: center; gap: 10px;">
					<div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite;"></div>
					<p>Loading children data...</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 20px; border-radius: 12px; max-width: 500px; text-align: center; position: relative;">
        <button id="closeQrModal" style="position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px;">×</button>
        <h3 style="margin: 0 0 15px 0;">Child QR Code</h3>
        <img id="qrModalImage" src="" alt="QR Code" style="width: 400px; height: 400px; border: 1px solid #ddd; border-radius: 8px;">
    </div>
</div>

<style>
	.dashboard-stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 20px;
		margin-bottom: 30px;
	}

	.stat-card {
		background: white;
		padding: 20px;
		border-radius: 12px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		display: flex;
		align-items: center;
		gap: 15px;
		transition: transform 0.2s ease;
	}

	.stat-card:hover {
		transform: translateY(-2px);
	}

	.stat-icon {
		font-size: 48px;
		width: 80px;
		height: 80px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: #f5f5f5;
	}

	.total .stat-icon {
		background: #e3f2fd;
	}

	.approved .stat-icon {
		background: #e8f5e8;
	}

	.pending .stat-icon {
		background: #fff3e0;
	}

	.today .stat-icon {
		background: #f3e5f5;
	}

	.stat-info h3 {
		font-size: 32px;
		margin: 0;
		color: #333;
	}

	.stat-info p {
		margin: 5px 0 0 0;
		color: #666;
		font-size: 14px;
	}

	.children-section, .activity-section {
		margin-bottom: 30px;
	}

	.children-section h2, .activity-section h2 {
		color: #333;
		margin-bottom: 15px;
		font-size: 24px;
	}

	.children-list {
		background: transparent;
		padding: 0;
	}

	.child-list-item {
		padding: 18px 20px;
		border-bottom: 1px solid #e8e9ea;
		display: flex;
		align-items: center;
		gap: 18px;
		transition: all 0.2s ease;
		background: #ffffff;
		border-radius: 8px;
		margin-bottom: 8px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.05);
	}

	.child-list-item:last-child {
		border-bottom: 1px solid #e8e9ea;
		margin-bottom: 0;
	}

	.child-list-item:hover {
		background-color: #f8f9fa;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		transform: translateY(-1px);
	}

	.child-avatar {
		width: 55px;
		height: 55px;
		border-radius: 50%;
		background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 22px;
		font-weight: bold;
		color: #1565c0;
		flex-shrink: 0;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	}

	.child-details {
		flex: 1;
		min-width: 0;
		padding: 4px 0;
	}

	.child-name {
		display: flex;
		justify-content: start;
		border: none;
		margin: 0 0 8px 0;
		color: #2c3e50;
		font-size: 25px;
		font-weight: 700;
		letter-spacing: -0.3px;
	}

	.child-schedule {
		margin: 0 0 6px 0;
		color: #34495e;
		font-size: 14px;
		font-weight: 500;
	}

	.child-schedule strong {
		color: #2c3e50;
		font-weight: 600;
	}

	.child-vaccine {
		margin: 0;
		color: #7f8c8d;
		font-size: 13px;
		font-weight: 500;
		background: #ecf0f1;
		padding: 4px 8px;
		border-radius: 12px;
		display: inline-block;
	}

	.child-actions {
		display: flex;
		gap: 10px;
		flex-shrink: 0;
	}

	.child-view-btn {
		padding: 10px 16px;
		background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
		color: white;
		border: none;
		border-radius: 8px;
		cursor: pointer;
		font-size: 14px;
		font-weight: 600;
		transition: all 0.2s ease;
		box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
	}

	.child-view-btn:hover {
		background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
		box-shadow: 0 4px 8px rgba(25, 118, 210, 0.4);
		transform: translateY(-1px);
	}

	.child-view-btn:active {
		transform: translateY(0);
		box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
	}

	.child-schedule-btn {
		padding: 10px 16px;
		background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
		color: white;
		border: none;
		border-radius: 8px;
		cursor: pointer;
		font-size: 14px;
		font-weight: 600;
		transition: all 0.2s ease;
		box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
	}

	.child-schedule-btn:hover {
		background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
		box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
		transform: translateY(-1px);
	}

	.child-schedule-btn:active {
		transform: translateY(0);
		box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
	}

	.btn {
		padding: 8px 16px;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		font-size: 14px;
		transition: background-color 0.2s ease;
	}

	.btn-primary {
		background: #1976d2;
		color: white;
	}

	.btn-primary:hover {
		background: #1565c0;
	}


	.loading {
		text-align: center;
		padding: 40px;
		color: #666;
	}

	.no-data {
		text-align: center;
		padding: 40px;
		color: #999;
	}

	.no-data .icon {
		font-size: 48px;
		margin-bottom: 10px;
	}
	
	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
</style>

<script>
    let currentFilter = 'upcoming';

    async function fetchSummary(filter = null){
        const url = filter ? `../../php/supabase/users/get_children_summary.php?filter=${encodeURIComponent(filter)}`
                           : `../../php/supabase/users/get_children_summary.php`;
        const res = await fetch(url);
        return await res.json();
    }

    async function refreshCounts(){
        try{
            const data = await fetchSummary();
            if (data && data.status === 'success' && data.data){
                document.getElementById('upcomingCount').textContent = data.data.upcoming_count || 0;
                document.getElementById('missedCountBtn').textContent = data.data.missed_count || 0;
            }
        }catch(e){ /* silent */ }
    }

    function setActiveButton(){
        const up = document.getElementById('btnUpcoming');
        const mi = document.getElementById('btnMissed');
        if (currentFilter === 'upcoming'){
            up.classList.add('btn-primary');
            mi.classList.remove('btn-primary');
        } else {
            mi.classList.add('btn-primary');
            up.classList.remove('btn-primary');
        }
    }

    async function selectFilter(filter){
        currentFilter = filter;
        setActiveButton();
        const list = document.getElementById('childrenList');
        list.innerHTML = '<div class="loading"><div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite;"></div><p>Loading...</p></div>';
        try{
            const resp = await fetchSummary(filter);
            if (resp && resp.status === 'success'){
                renderFilteredList(resp.data.items || []);
            } else {
                list.innerHTML = '<div class="no-data"><div class="icon">❌</div><p>Failed to load list</p></div>';
            }
        }catch(e){
            list.innerHTML = '<div class="no-data"><div class="icon">❌</div><p>Network error</p></div>';
        }
        // also refresh counts in background
        refreshCounts();
    }

    function renderFilteredList(items){
        const list = document.getElementById('childrenList');
        if (!items || items.length === 0){
            list.innerHTML = '<div class="no-data"><div class="icon">👶</div><p>No records</p></div>';
            return;
        }
        let html = '';
        items.forEach(it => {
            const name = it.name || 'Unknown Child';
            const first = name.charAt(0).toUpperCase();
            const upcoming = it.upcoming_date ? formatDate(it.upcoming_date) : (currentFilter==='upcoming' ? 'No date' : '');
            const vaccine = it.upcoming_vaccine || '';
            
                         // Build missed details HTML if showing missed immunizations (show only closest missed)
             let missedDetailsHtml = '';
             if (currentFilter === 'missed' && it.closest_missed) {
                 const detail = it.closest_missed;
                 const scheduleDate = detail.schedule_date ? formatDate(detail.schedule_date) : 'Not scheduled';
                 const catchUpDate = detail.catch_up_date ? formatDate(detail.catch_up_date) : '-';
                 missedDetailsHtml = `
                     <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e0e0e0;">
                         <div style="margin-bottom: 6px; font-size: 13px;">
                             <strong>${detail.vaccine_name} (Dose ${detail.dose_number})</strong><br>
                             <span style="color: #666;">Scheduled: ${scheduleDate}</span><br>
                             <span style="color: #dc3545;">Catch Up: ${catchUpDate}</span>
                         </div>
                         ${it.missed_count > 1 ? `<div style="color: #999; font-size: 12px;">...and ${it.missed_count - 1} more missed vaccination(s)</div>` : ''}
                     </div>
                 `;
             }
            
            const badge = currentFilter==='missed' ? `<span class="child-vaccine" style="background: #fff3cd; color: #856404;">Missed: ${it.missed_count||0}</span>` : (vaccine ? `<span class=\"child-vaccine\">${vaccine}</span>` : '');
            const qrButton = it.qr_code ? `<button onclick="showQrModal('${it.qr_code.replace(/'/g, "\\'")}')" style="background: none; border: none; cursor: pointer; padding: 5px;"><img src="${it.qr_code}" alt="QR Code" style="width: 60px; height: 60px; border-radius: 8px;"></button>` : '';
            html += `
                <div class="child-list-item">
                    <div class="child-avatar">${first}</div>
                    ${qrButton}
                    <div class="child-details">
                        <h3 class="child-name">${name}</h3>
                        ${currentFilter==='upcoming' ? `<p class="child-schedule"><strong>Next:</strong> ${upcoming}</p>` : ''}
                        ${badge}
                        ${missedDetailsHtml}
                    </div>
                    <div class="child-actions">
                        <button class="child-view-btn" onclick="viewChildRecord('${it.baby_id||''}')" ${(it.baby_id?'':'disabled')}>View</button>
                        <button class="child-schedule-btn" onclick="viewSchedule('${it.baby_id||''}')" ${(it.baby_id?'':'disabled')}>View Schedule</button>
                    </div>
                </div>
            `;
        });
        list.innerHTML = html;
    }
	async function loadDashboardData() {
		try {
			// Show loading state
			document.getElementById('totalChildren').textContent = '...';
			document.getElementById('approvedChr').textContent = '...';
			document.getElementById('missedCount').textContent = '...';
			document.getElementById('todaySchedule').textContent = '...';
			
			// Load children data first (this will give us all the stats we need)
			const childrenResponse = await fetch('../../php/supabase/users/get_accepted_child.php');
			const childrenData = await childrenResponse.json();
			
			if (childrenData.status === 'success' && childrenData.data.length > 0) {
				// Calculate statistics from children data
				calculateStatsFromChildren(childrenData.data);
				
				// Render children list
				renderChildrenList(childrenData.data);
			} else {
				// Set default values when no children
				document.getElementById('totalChildren').textContent = '0';
				document.getElementById('approvedChr').textContent = '0';
				document.getElementById('missedCount').textContent = '0';
				document.getElementById('todaySchedule').textContent = '0';
				
				document.getElementById('childrenList').innerHTML = `
					<div class="no-data">
						<div class="icon">👶</div>
						<p>No children registered yet</p>
						<button class="btn btn-primary" onclick="addChild()">Add Child</button>
					</div>
				`;
			}
		} catch (error) {
			console.error('Error loading dashboard data:', error);
			
			// Set default stats
			document.getElementById('totalChildren').textContent = '0';
			document.getElementById('approvedChr').textContent = '0';
			document.getElementById('missedCount').textContent = '0';
			document.getElementById('todaySchedule').textContent = '0';
			
			// Show error message
			document.getElementById('childrenList').innerHTML = `
				<div class="no-data">
					<div class="icon">❌</div>
					<p>Error loading children data</p>
					<small>${error.message || 'Unknown error'}</small>
				</div>
			`;
		}
	}

	function calculateStatsFromChildren(children) {
		let totalChildren = children.length;
		let totalMissed = 0;
		let totalToday = 0;
		
		// Count missed immunizations and today's schedules
		children.forEach(child => {
			totalMissed += child.missed_count || 0;
			
			// Check if child has schedule for today
			if (child.schedule_date) {
				const today = new Date().toISOString().split('T')[0];
				if (child.schedule_date === today) {
					totalToday++;
				}
			}
		});

		// Update the stats display
		document.getElementById('totalChildren').textContent = totalChildren;
		document.getElementById('missedCount').textContent = totalMissed;
		document.getElementById('todaySchedule').textContent = totalToday;
		
		// Get approved CHR count (we'll load this separately)
		loadApprovedChrCount();
	}

	async function loadApprovedChrCount() {
		try {
			const response = await fetch('../../php/supabase/users/get_dashboard_summary.php');
			const data = await response.json();
			
			if (data.status === 'success') {
				document.getElementById('approvedChr').textContent = data.data.approved_chr_documents;
			} else {
				document.getElementById('approvedChr').textContent = '0';
			}
		} catch (error) {
			console.error('Error loading approved CHR count:', error);
			document.getElementById('approvedChr').textContent = '0';
		}
	}

		function renderChildrenList(children) {
		const list = document.getElementById('childrenList');
		let html = '';

		// Filter to show only accepted children
		const acceptedChildren = children.filter(child => child.status === 'accepted');

		acceptedChildren.forEach(child => {
			const fullName = child.name || 'Unknown Child';
			const firstLetter = fullName.charAt(0).toUpperCase();
			const babyId = child.baby_id || '';
			const upcomingSchedule = child.schedule_date ? formatDate(child.schedule_date) : 'No upcoming schedule';
			const vaccineName = child.vaccine || 'No vaccine scheduled';
			
				html += `
				<div class="child-list-item">
						<div class="child-avatar">${firstLetter}</div>
					<div class="child-details">
						<h3 class="child-name">${fullName}</h3>
						<p class="child-schedule"><strong>Next:</strong> ${upcomingSchedule}</p>
						<p class="child-vaccine">${vaccineName}</p>
					</div>
					<div class="child-actions">
						<button class="child-view-btn" onclick="viewChildRecord('${babyId}')" ${babyId ? '' : 'disabled'}>View</button>
						<button class="child-schedule-btn" onclick="viewSchedule('${babyId}')" ${babyId ? '' : 'disabled'}>View Schedule</button>
					</div>
				</div>
			`;
		});

		// Show message if no accepted children
		if (acceptedChildren.length === 0) {
			html = `
				<div class="no-data">
					<div class="icon">👶</div>
					<p>No approved children found</p>
					<small>Children need to be approved by BHW first</small>
				</div>
			`;
		}

		list.innerHTML = html;
	}

	function formatDate(dateString) {
		if (!dateString) return '';
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', { 
			month: 'short', 
			day: 'numeric', 
			year: 'numeric' 
		});
	}

	function viewChildRecord(babyId) {
	if (!babyId) return;
	const encoded = encodeURIComponent(String(babyId));
		window.location.href = `child_health_record.php?baby_id=${encoded}`;
}

function viewSchedule(babyId) {
	if (!babyId) return;
	const encoded = encodeURIComponent(String(babyId));
	window.location.href = `upcoming_schedule.php?baby_id=${encoded}`;
}

	function addChild() {
		window.location.href = 'Request.php';
	}

	// QR Modal functions
	function showQrModal(qrCodeUrl) {
		const modal = document.getElementById('qrModal');
		const qrImage = document.getElementById('qrModalImage');
		qrImage.src = qrCodeUrl;
		modal.style.display = 'flex';
	}

	function closeQrModal() {
		const modal = document.getElementById('qrModal');
		modal.style.display = 'none';
	}

	// Load data when page loads
	document.addEventListener('DOMContentLoaded', async function() {
		await refreshCounts();
		setActiveButton();
		selectFilter('upcoming');
		loadDashboardData();
		
		// Add event listener for close QR modal button
		document.getElementById('closeQrModal').addEventListener('click', closeQrModal);
		
		// Close modal when clicking outside
		document.getElementById('qrModal').addEventListener('click', function(e) {
			if (e.target.id === 'qrModal') {
				closeQrModal();
			}
		});
	});
</script>

<?php include 'Include/footer.php'; ?>