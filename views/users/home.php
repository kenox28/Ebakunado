<?php include 'Include/header.php'; ?>

<div class="content">
	<!-- Dashboard Stats -->
	<div class="dashboard-stats">
		<div class="stat-card upcoming">
			<div class="stat-icon">📅</div>
			<div class="stat-info">
				<h3 id="upcomingCount">0</h3>
				<p>Upcoming Vaccinations</p>
			</div>
		</div>
		<div class="stat-card missed">
			<div class="stat-icon">⚠️</div>
			<div class="stat-info">
				<h3 id="missedCount">0</h3>
				<p>Missed Vaccinations</p>
			</div>
		</div>
		<div class="stat-card completed">
			<div class="stat-icon">✅</div>
			<div class="stat-info">
				<h3 id="completedCount">0</h3>
				<p>Taken Vaccinations</p>
			</div>
		</div>
	</div>

	<!-- Children Overview -->
	<div class="children-section">
		<h2>My Children</h2>
		<div class="children-grid" id="childrenGrid">
			<div class="loading">
				<div style="display: flex; align-items: center; gap: 10px;">
					<div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite;"></div>
					<p>Loading children data...</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Recent Activity -->
	<div class="activity-section">
		<h2>Recent Activity</h2>
		<div class="activity-list" id="activityList">
			<div class="loading">
				<p>Loading recent activity...</p>
			</div>
		</div>
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

	.upcoming .stat-icon {
		background: #e3f2fd;
	}

	.missed .stat-icon {
		background: #ffebee;
	}

	.completed .stat-icon {
		background: #e8f5e8;
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

	.children-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
		gap: 20px;
	}

	.child-card {
		background: white;
		border-radius: 12px;
		padding: 20px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		transition: transform 0.2s ease;
	}

	.child-card:hover {
		transform: translateY(-2px);
	}

	.child-header {
		display: flex;
		align-items: center;
		gap: 15px;
		margin-bottom: 15px;
	}

	.child-avatar {
		width: 60px;
		height: 60px;
		border-radius: 50%;
		background: #e3f2fd;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24px;
		font-weight: bold;
		color: #1976d2;
	}

	.child-info h3 {
		margin: 0;
		color: #333;
		font-size: 18px;
	}

	.child-info p {
		margin: 5px 0 0 0;
		color: #666;
		font-size: 14px;
	}

	.child-stats {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 10px;
		margin-bottom: 15px;
	}

	.child-stat {
		text-align: center;
		padding: 10px;
		background: #f8f9fa;
		border-radius: 8px;
	}

	.child-stat h4 {
		margin: 0;
		font-size: 20px;
		color: #333;
	}

	.child-stat p {
		margin: 5px 0 0 0;
		font-size: 12px;
		color: #666;
	}

	.child-info-section {
		margin-bottom: 15px;
		padding: 10px;
		background: #f8f9fa;
		border-radius: 6px;
		font-size: 12px;
		line-height: 1.4;
	}

	.child-info-section p {
		margin: 2px 0;
		color: #666;
	}

	.child-actions {
		display: flex;
		gap: 10px;
	}

	.btn {
		flex: 1;
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

	.btn-secondary {
		background: #f5f5f5;
		color: #333;
	}

	.btn-secondary:hover {
		background: #e0e0e0;
	}

	.activity-list {
		background: white;
		border-radius: 12px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		overflow: hidden;
	}

	.activity-item {
		padding: 15px 20px;
		border-bottom: 1px solid #f0f0f0;
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.activity-item:last-child {
		border-bottom: none;
	}

	.activity-icon {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 18px;
	}

	.activity-info h4 {
		margin: 0;
		font-size: 14px;
		color: #333;
	}

	.activity-info p {
		margin: 5px 0 0 0;
		font-size: 12px;
		color: #666;
	}

	.activity-time {
		margin-left: auto;
		font-size: 12px;
		color: #999;
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
	async function loadDashboardData() {
		try {
			// Show loading state
			document.getElementById('upcomingCount').textContent = '...';
			document.getElementById('missedCount').textContent = '...';
			document.getElementById('completedCount').textContent = '...';
			
			// Load children data first (most important)
			const childrenResponse = await fetch('../../php/supabase/users/get_accepted_child.php');
			const childrenData = await childrenResponse.json();
			
			// Calculate and display stats immediately from children data
			if (childrenData.status === 'success' && childrenData.data.length > 0) {
				calculateStatsFromChildren(childrenData.data);
				renderChildrenGrid(childrenData.data);
			} else {
				document.getElementById('childrenGrid').innerHTML = `
					<div class="no-data">
						<div class="icon">👶</div>
						<p>No children registered yet</p>
						<button class="btn btn-primary" onclick="addChild()">Add Child</button>
					</div>
				`;
			}
			
			// Load other data in background
			const [statsResponse, activityResponse] = await Promise.all([
				fetch('../../php/supabase/users/get_vaccination_stats.php'),
				fetch('../../php/supabase/users/get_recent_activity.php')
			]);

			const statsData = await statsResponse.json();
			const activityData = await activityResponse.json();

			// Update activity list
			if (activityData.status === 'success' && activityData.data.length > 0) {
				renderActivityList(activityData.data);
			} else {
				document.getElementById('activityList').innerHTML = `
					<div class="no-data">
						<div class="icon">📝</div>
						<p>No recent activity</p>
					</div>
				`;
			}
		} catch (error) {
			console.error('Error loading dashboard data:', error);
			
			// Set default stats
			document.getElementById('upcomingCount').textContent = '0';
			document.getElementById('missedCount').textContent = '0';
			document.getElementById('completedCount').textContent = '0';
			
			// Show error messages
			document.getElementById('childrenGrid').innerHTML = `
				<div class="no-data">
					<div class="icon">❌</div>
					<p>Error loading children data</p>
					<small>${error.message || 'Unknown error'}</small>
				</div>
			`;
			
			document.getElementById('activityList').innerHTML = `
				<div class="no-data">
					<div class="icon">❌</div>
					<p>Error loading activity data</p>
					<small>${error.message || 'Unknown error'}</small>
				</div>
			`;
		}
	}

	function calculateStatsFromChildren(children) {
		let upcomingCount = 0;
		let missedCount = 0;
		let takenCount = 0;

		children.forEach(child => {
			// Use the vaccination counts from the API
			takenCount += child.taken_count || 0;
			missedCount += child.missed_count || 0;
			upcomingCount += child.scheduled_count || 0;
		});

		// Update the stats display
		document.getElementById('upcomingCount').textContent = upcomingCount;
		document.getElementById('missedCount').textContent = missedCount;
		document.getElementById('completedCount').textContent = takenCount;
	}

	function renderChildrenGrid(children) {
		const grid = document.getElementById('childrenGrid');
		let html = '';

		children.forEach(child => {
			// Use correct field names from API
			const fullName = child.name || 'Unknown Child';
			const firstLetter = fullName.charAt(0).toUpperCase();
			
			// Handle age calculation based on API response
			let ageText = 'Unknown age';
			if (child.age !== undefined && child.age !== null && child.age > 0) {
				ageText = `${child.age} years`;
			} else if (child.weeks_old !== undefined && child.weeks_old !== null) {
				ageText = `${child.weeks_old} weeks`;
			}
			
			const gender = child.gender || 'Unknown';
			const babyId = child.baby_id || child.id || '';
			const vaccine = child.vaccine || 'None';
			const dose = child.dose || '';
			const scheduleDate = child.schedule_date || 'Not scheduled';
			const status = child.status || 'Unknown';
			
			html += `
				<div class="child-card">
					<div class="child-header">
						<div class="child-avatar">${firstLetter}</div>
						<div class="child-info">
							<h3>${fullName}</h3>
							<p>${ageText} • ${gender}</p>
						</div>
					</div>
					<div class="child-stats">
						<div class="child-stat">
							<h4>${child.taken_count || 0}</h4>
							<p>Taken</p>
						</div>
						<div class="child-stat">
							<h4>${child.scheduled_count || 0}</h4>
							<p>Upcoming</p>
						</div>
						<div class="child-stat">
							<h4>${child.missed_count || 0}</h4>
							<p>Missed</p>
						</div>
					</div>
					<div class="child-info-section">
						<p><strong>Latest Vaccine:</strong> ${vaccine}</p>
						<p><strong>Next Dose:</strong> ${dose}</p>
						<p><strong>Schedule:</strong> ${scheduleDate}</p>
					</div>
					<div class="child-actions">
						<button class="btn btn-primary" onclick="viewChildDetails('${babyId}')">View Details</button>
						<button class="btn btn-secondary" onclick="viewSchedule('${babyId}')">Schedule</button>
					</div>
				</div>
			`;
		});

		grid.innerHTML = html;
	}

	function renderActivityList(activities) {
		const list = document.getElementById('activityList');
		let html = '';

		activities.slice(0, 10).forEach(activity => {
			// Safely handle undefined/null values
			const title = activity.title || 'Activity';
			const description = activity.description || 'No description';
			const type = activity.type || 'info';
			const timestamp = activity.timestamp || new Date().toISOString();
			
			const iconClass = getActivityIcon(type);
			const iconBg = getActivityIconBg(type);
			
			html += `
				<div class="activity-item">
					<div class="activity-icon" style="background: ${iconBg}; color: white;">
						${iconClass}
					</div>
					<div class="activity-info">
						<h4>${title}</h4>
						<p>${description}</p>
					</div>
					<div class="activity-time">
						${formatTime(new Date(timestamp))}
					</div>
				</div>
			`;
		});

		list.innerHTML = html;
	}

	function getActivityIcon(type) {
		switch(type) {
			case 'approval': return '✅';
			case 'rejection': return '❌';
			case 'vaccine': return '💉';
			case 'schedule': return '📅';
			default: return 'ℹ️';
		}
	}

	function getActivityIconBg(type) {
		switch(type) {
			case 'approval': return '#28a745';
			case 'rejection': return '#dc3545';
			case 'vaccine': return '#007bff';
			case 'schedule': return '#ffc107';
			default: return '#6c757d';
		}
	}

	function formatTime(timestamp) {
		const date = new Date(timestamp);
		const now = new Date();
		const diffInMinutes = Math.floor((now - date) / (1000 * 60));
		
		if (diffInMinutes < 1) return 'Just now';
		if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
		if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
		return `${Math.floor(diffInMinutes / 1440)}d ago`;
	}

	function viewChildDetails(babyId) {
		// Navigate to child details page
		window.location.href = `view_immunization.php?baby_id=${babyId}`;
	}

	function viewSchedule(babyId) {
		// Navigate to schedule page
		window.location.href = `upcoming_schedule.php?baby_id=${babyId}`;
	}

	// Load data when page loads
	document.addEventListener('DOMContentLoaded', function() {
		loadDashboardData();
	});
</script>

<?php include 'Include/footer.php'; ?>