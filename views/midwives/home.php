<?php include 'Include/header.php'; ?>

    <div class="content">
	<!-- Dashboard Stats -->
	<div class="dashboard-stats">
		<div class="stat-card pending">
			<div class="stat-icon">⏳</div>
			<div class="stat-info">
				<h3 id="pendingCount">0</h3>
				<p>Pending Approvals</p>
			</div>
		</div>
		<div class="stat-card today">
			<div class="stat-icon">💉</div>
			<div class="stat-info">
				<h3 id="todayCount">0</h3>
				<p>Today's Vaccinations</p>
			</div>
		</div>
		<div class="stat-card missed">
			<div class="stat-icon">⚠️</div>
			<div class="stat-info">
				<h3 id="missedCount">0</h3>
				<p>Missed Vaccinations</p>
			</div>
		</div>
		<div class="stat-card total">
			<div class="stat-icon">👶</div>
			<div class="stat-info">
				<h3 id="totalCount">0</h3>
				<p>Total Children</p>
        </div>
    </div>
</div>

	<!-- Quick Actions -->
	<div class="quick-actions">
		<h2>Quick Actions</h2>
		<div class="actions-grid">
			<a href="pending_approval.php" class="action-card pending">
				<div class="action-icon">📋</div>
				<div class="action-info">
					<h4>Pending Approvals</h4>
					<p>Review child health records</p>
					<span class="action-count" id="pendingActionCount">0</span>
				</div>
			</a>
			<a href="immunization.php" class="action-card today">
				<div class="action-icon">💉</div>
				<div class="action-info">
					<h4>Today's Schedule</h4>
					<p>View vaccination schedules</p>
					<span class="action-count" id="todayActionCount">0</span>
				</div>
			</a>
			<a href="child_health_record.php" class="action-card records">
				<div class="action-icon">📝</div>
				<div class="action-info">
					<h4>All Records</h4>
					<p>Manage child health records</p>
				</div>
			</a>
			<button class="action-card scanner" onclick="openQRScanner()">
				<div class="action-icon">📱</div>
				<div class="action-info">
					<h4>QR Scanner</h4>
					<p>Scan child QR codes</p>
				</div>
			</button>
		</div>
	</div>

	<!-- Recent Activity -->
	<div class="activity-section">
		<h2>Recent Activity</h2>
		<div class="activity-list" id="activityList">
                                    <div class="loading">
				<div style="display: flex; align-items: center; gap: 10px;">
					<div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite;"></div>
					<p>Loading recent activity...</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Tasks Overview -->
	<div class="tasks-section">
		<h2>Tasks Overview</h2>
		<div class="tasks-grid">
			<div class="task-card urgent" id="overdueCard">
				<div class="task-header">
					<h4>🚨 Overdue Tasks</h4>
					<span class="task-count" id="overdueCount">0</span>
				</div>
				<p>Vaccinations that are past due date</p>
				<a href="immunization.php" class="task-action">View Details</a>
			</div>
			<div class="task-card warning" id="tomorrowCard">
				<div class="task-header">
					<h4>📅 Tomorrow's Tasks</h4>
					<span class="task-count" id="tomorrowCount">0</span>
				</div>
				<p>Vaccinations scheduled for tomorrow</p>
				<a href="immunization.php" class="task-action">View Details</a>
			</div>
		</div>
	</div>
</div>

<!-- QR Scanner Overlay -->
<div id="qrOverlay" style="display: none;">
	<div class="qr-content">
		<div class="qr-header">
			<h3>📱 QR Code Scanner</h3>
			<button onclick="closeQRScanner()" class="close-btn">&times;</button>
		</div>
		<div class="qr-body">
			<div id="qrReader"></div>
			<div class="qr-controls">
				<select id="cameraSelect" style="display: none;"></select>
				<br><br>
				<input type="file" id="fileInput" accept="image/*" onchange="scanFromImage(event)" style="display: block; margin: 10px auto;">
				<button id="torchBtn" onclick="toggleTorch()" style="display: none;">Torch On</button>
			</div>
		</div>
	</div>
                                    </div>

<style>
	.content {
		padding: 15px;
		background-color: #f8f9fa;
		height: calc(100vh - 80px);
		overflow-y: auto;
		display: grid;
		grid-template-columns: 1fr 1fr;
		grid-template-rows: auto auto 1fr;
		gap: 15px;
		grid-template-areas: 
			"stats stats"
			"actions actions"
			"activity tasks";
	}

	.dashboard-stats {
		grid-area: stats;
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 15px;
		margin-bottom: 0;
	}

	
	.stat-card {
		background: white;
		padding: 15px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		display: flex;
		align-items: center;
		gap: 12px;
		transition: transform 0.2s ease;
	}

	.stat-card:hover {
		transform: translateY(-2px);
	}

	.stat-icon {
		font-size: 32px;
		width: 50px;
		height: 50px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: #f5f5f5;
	}

	.pending .stat-icon {
		background: #fff3e0;
	}

	.today .stat-icon {
		background: #e3f2fd;
	}

	.missed .stat-icon {
		background: #ffebee;
	}

	.total .stat-icon {
		background: #e8f5e8;
	}

	.stat-info h3 {
		font-size: 24px;
		margin: 0;
		color: #333;
		font-weight: bold;
	}

	.stat-info p {
		margin: 5px 0 0 0;
		color: #666;
		font-size: 12px;
	}

	.quick-actions {
		grid-area: actions;
		margin-bottom: 0;
	}

	.quick-actions h2 {
		margin-bottom: 15px;
		color: #333;
		font-size: 18px;
	}

	.actions-grid {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 15px;
	}

	.action-card {
		background: white;
		padding: 15px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		display: flex;
		align-items: center;
		gap: 10px;
		text-decoration: none;
		color: inherit;
		transition: all 0.2s ease;
		position: relative;
	}

	.action-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 16px rgba(0,0,0,0.15);
	}

	.action-icon {
		font-size: 24px;
		width: 40px;
		height: 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: #f5f5f5;
	}

	.pending .action-icon {
		background: #fff3e0;
	}

	.today .action-icon {
		background: #e3f2fd;
	}

	.records .action-icon {
		background: #f3e5f5;
	}

	.scanner .action-icon {
		background: #e0f2f1;
	}

	.action-info h4 {
		margin: 0 0 5px 0;
		color: #333;
		font-size: 14px;
	}

	.action-info p {
		margin: 0;
		color: #666;
		font-size: 12px;
	}

	.action-count {
		position: absolute;
		top: 15px;
		right: 15px;
		background: #dc3545;
		color: white;
		border-radius: 50%;
		width: 24px;
		height: 24px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 12px;
		font-weight: bold;
	}

	.activity-section {
		grid-area: activity;
		margin-bottom: 0;
	}

	.tasks-section {
		grid-area: tasks;
		margin-bottom: 0;
	}

	.activity-section h2, .tasks-section h2 {
		margin-bottom: 15px;
		color: #333;
		font-size: 18px;
	}

	.activity-list {
		background: white;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		padding: 15px;
		height: calc(100vh - 400px);
		overflow-y: auto;
	}

	.activity-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 0;
		border-bottom: 1px solid #f0f0f0;
	}

	.activity-item:last-child {
		border-bottom: none;
	}

	.activity-icon {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 14px;
		background: #f5f5f5;
	}

	.activity-content h5 {
		margin: 0 0 3px 0;
		color: #333;
		font-size: 12px;
	}

	.activity-content p {
		margin: 0 0 3px 0;
		color: #666;
		font-size: 11px;
	}

	.activity-time {
		font-size: 10px;
		color: #999;
	}

	.tasks-grid {
		display: flex;
		flex-direction: column;
		gap: 15px;
		height: calc(100vh - 400px);
		overflow-y: auto;
	}

	.task-card {
		background: white;
		padding: 15px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		border-left: 4px solid #ddd;
		flex-shrink: 0;
	}

	.task-card.urgent {
		border-left-color: #dc3545;
	}

	.task-card.warning {
		border-left-color: #ffc107;
	}

	.task-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 10px;
	}

	.task-header h4 {
		margin: 0;
		color: #333;
		font-size: 14px;
	}

	.task-count {
		background: #dc3545;
		color: white;
		border-radius: 12px;
		padding: 4px 12px;
		font-size: 12px;
		font-weight: bold;
	}

	.task-card.warning .task-count {
		background: #ffc107;
		color: #333;
	}

	.task-card p {
		margin: 0 0 10px 0;
		color: #666;
		font-size: 12px;
	}

	.task-action {
		display: inline-block;
		background: #007bff;
		color: white;
		padding: 6px 12px;
		border-radius: 4px;
		text-decoration: none;
		font-size: 12px;
		transition: background 0.2s ease;
	}

	.task-action:hover {
		background: #0056b3;
	}

	/* QR Scanner Styles */
	#qrOverlay {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.8);
		z-index: 1000;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.qr-content {
		background: white;
		border-radius: 12px;
		width: 90%;
		max-width: 500px;
		max-height: 80vh;
		overflow: hidden;
	}

	.qr-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px;
		border-bottom: 1px solid #eee;
		background: #f8f9fa;
	}

	.qr-header h3 {
		margin: 0;
		color: #333;
	}

	.close-btn {
		background: none;
		border: none;
		font-size: 24px;
		cursor: pointer;
		color: #666;
	}

	.qr-body {
		padding: 20px;
		text-align: center;
	}

	#qrReader {
		width: 100%;
		max-width: 400px;
		margin: 0 auto 20px auto;
	}

	.qr-controls {
		margin-top: 20px;
	}

	.loading {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 40px;
		color: #666;
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

	@media (max-width: 1200px) {
		.content {
			grid-template-columns: 1fr;
			grid-template-areas: 
				"stats"
				"actions"
				"activity"
				"tasks";
		}
		
		.dashboard-stats {
			grid-template-columns: repeat(2, 1fr);
		}
		
		.actions-grid {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media (max-width: 768px) {
		.dashboard-stats {
			grid-template-columns: 1fr;
		}
		
		.actions-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

		<script>
	// Dashboard Data Loading
	async function loadDashboardData() {
		try {
			console.log('Loading BHW dashboard data...');
			const response = await fetch('../../php/supabase/shared/get_dashboard_stats.php');
			
			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${response.statusText}`);
			}
			
			const data = await response.json();
			console.log('Dashboard data received:', data);

			if (data.status === 'success') {
				updateStats(data.data.stats);
				updateActivity(data.data.recent_activities);
				updateTasks(data.data.stats);
				console.log('Dashboard updated successfully');
			} else {
				console.error('Dashboard API error:', data);
				showError('Failed to load dashboard data: ' + data.message);
			}
		} catch (error) {
			console.error('Error loading dashboard data:', error);
			showError('Network error: ' + error.message);
		}
	}

	function updateStats(stats) {
		document.getElementById('pendingCount').textContent = stats.pending_approvals;
		document.getElementById('todayCount').textContent = stats.today_vaccinations;
		document.getElementById('missedCount').textContent = stats.missed_vaccinations;
		document.getElementById('totalCount').textContent = stats.total_children;
		document.getElementById('pendingActionCount').textContent = stats.pending_approvals;
		document.getElementById('todayActionCount').textContent = stats.today_vaccinations;
	}

	function updateTasks(stats) {
		document.getElementById('overdueCount').textContent = stats.overdue_tasks;
		document.getElementById('tomorrowCount').textContent = stats.tomorrow_vaccinations;
	}

	function updateActivity(activities) {
		const activityList = document.getElementById('activityList');
		
		if (!activities || activities.length === 0) {
			activityList.innerHTML = '<div class="loading"><p>No recent activity</p></div>';
			return;
		}

		let html = '';
		activities.forEach(activity => {
			const timeAgo = getTimeAgo(activity.timestamp);
			html += `
				<div class="activity-item">
					<div class="activity-icon">${activity.icon}</div>
					<div class="activity-content">
						<h5>${activity.title}</h5>
						<p>${activity.description}</p>
						<div class="activity-time">${timeAgo}</div>
					</div>
				</div>
			`;
		});

		activityList.innerHTML = html;
	}

	function getTimeAgo(timestamp) {
		const now = new Date();
		let time;
		
		if (timestamp.includes('T')) {
			const parts = timestamp.split('T');
			const datePart = parts[0];
			const timePart = parts[1].split('.')[0];
			time = new Date(datePart + ' ' + timePart);
				} else {
			time = new Date(timestamp.replace(' ', 'T'));
		}
		
		if (isNaN(time.getTime())) {
			return 'Unknown time';
		}
		
		const diffInSeconds = Math.floor((now - time) / 1000);
		
		if (diffInSeconds < 0) return 'In the future';
		if (diffInSeconds < 60) return 'Just now';
		if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
		if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
		if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
		return 'Over a month ago';
	}

	function showError(message) {
		const activityList = document.getElementById('activityList');
		activityList.innerHTML = `
			<div class="loading">
				<p style="color: #dc3545;">${message}</p>
			</div>
		`;
	}

	// QR Scanner Functions
			let html5QrcodeInstance = null;

	function openQRScanner() {
				const overlay = document.getElementById('qrOverlay');
				overlay.style.display = 'flex';
				console.log('[QR] Opening scanner...');
		
		// Initialize scanner after a short delay to ensure overlay is visible
		setTimeout(initializeScanner, 100);
	}

	async function initializeScanner() {
		try {
			const devices = await Html5Qrcode.getCameras().catch(err => { 
				console.log('[QR] getCameras error:', err); 
				return []; 
			});
			
					console.log('[QR] Cameras found:', devices);
			
					const camSel = document.getElementById('cameraSelect');
					camSel.innerHTML = '';
			
			if (devices && devices.length > 0) {
						devices.forEach((d, idx) => {
							const opt = document.createElement('option');
					opt.value = d.id;
					opt.textContent = d.label || ('Camera ' + (idx + 1));
					camSel.appendChild(opt);
						});
						camSel.style.display = 'inline-block';
			} else {
				camSel.style.display = 'none';
			}
			
			if (!html5QrcodeInstance) {
				html5QrcodeInstance = new Html5Qrcode("qrReader");
			}
			
					await html5QrcodeInstance.start(
						{ facingMode: "environment" },
				{ 
					fps: 12, 
					qrbox: 300, 
					formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ], 
					disableFlip: true 
				},
						onScanSuccess,
						onScanFailure
					);
			
					console.log('[QR] Scanner started');
			
		} catch (e) {
			console.error('[QR] Camera error:', e);
			alert('Camera error: ' + e.message);
		}
	}

	function closeQRScanner() {
				const overlay = document.getElementById('qrOverlay');
				overlay.style.display = 'none';
		
		try {
			if (html5QrcodeInstance) {
				html5QrcodeInstance.stop();
				html5QrcodeInstance.clear();
			}
		} catch (e) {
			console.log('[QR] Error stopping scanner:', e);
		}
	}

	function onScanSuccess(decodedText) {
				console.log('[QR] Scan success:', decodedText);
		closeQRScanner();

		// Handle the scanned QR code
				const match = decodedText.match(/baby_id=([^&\s]+)/i);
		if (match && match[1]) {
			// Redirect to child health record with the baby_id
			window.location.href = `child_health_record.php?baby_id=${encodeURIComponent(match[1])}`;
		} else {
			// Show the decoded text or handle other QR codes
			alert('QR Code scanned: ' + decodedText);
		}
	}

	function onScanFailure(err) {
		// Silently handle scan failures (normal during scanning)
	}

	async function scanFromImage(event) {
		const file = event.target && event.target.files && event.target.files[0];
		if (!file) return;
		
		console.log('[QR] Scanning from image:', file.name);
		try {
			const result = await Html5QrcodeScanner.scanFile(file, true);
			console.log('[QR] Image scan result:', result);
			onScanSuccess(result);
		} catch (err) {
			console.error('[QR] Image scan failed:', err);
			alert('Unable to read QR from image.');
				}
			}

			let torchOn = false;
	async function toggleTorch() {
		try {
					const video = document.querySelector('#qrReader video');
					const stream = video && video.srcObject ? video.srcObject : null;
					const track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
			
			if (!track) return;
			
					await track.applyConstraints({ advanced: [{ torch: !torchOn }] });
					torchOn = !torchOn;
					document.getElementById('torchBtn').textContent = torchOn ? 'Torch Off' : 'Torch On';
		} catch (err) {
			console.warn('[QR] Torch not supported:', err);
		}
	}


	// Initialize dashboard when page loads
	document.addEventListener('DOMContentLoaded', function() {
		loadDashboardData();
	});
		</script>

<?php include 'Include/footer.php'; ?>