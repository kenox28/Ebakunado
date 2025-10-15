<?php session_start(); ?>
<?php 
// Handle both BHW and Midwife sessions (but BHW should only see BHW features)
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type']; // Default to bhw for BHW pages
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = $_SESSION['fname'] ." ". $_SESSION['lname'];
if($user_types != 'midwifes') {   
    $user_type = 'Barangay Health Worker';
}else{
    $user_type = 'Midwife';
}
// Debug session
if ($user_id) {
    echo "<!-- Session Active: " . $user_type . " - " . $user_id . " -->";
} else {
    echo "<!-- Session: NOT FOUND - Available sessions: " . implode(', ', array_keys($_SESSION)) . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BHW Dashboard</title>
    <link rel="stylesheet" href="/css/fonts.css" />
    <link rel="stylesheet" href="/css/base.css" />
    <link rel="stylesheet" href="/css/variables.css" />
    <link rel="stylesheet" href="/css/header.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <link rel="stylesheet" href="/css/dashboard.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main class="dashboard-main">
        <section class="dashboard-section">
            <div class="dashboard-overview">
                <h2 class="dashboard-heading">Dashboard Overview</h2>
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-info">
                            <span class="material-symbols-rounded">hourglass_top</span>
                            <p class="card-title">Pending Approvals</p>
                        </div>
                        <div class="card-count">
                            <p class="card-number" id="pendingCount">0</p>
                        </div>
                    </div>
                    <div class="card card-2">
                        <div class="card-info">
                            <span class="material-symbols-rounded">vaccines</span>
                            <p class="card-title">Today's Vaccinations</p>
                        </div>
                        <div class="card-count">
                            <p class="card-number" id="todayCount">0</p>
                        </div>
                    </div>
                    <div class="card card-3">
                        <div class="card-info">
                            <span class="material-symbols-rounded">warning</span>
                            <p class="card-title">Missed Vaccinations</p>
                        </div>
                        <div class="card-count">
                            <p class="card-number" id="missedCount">0</p>
                        </div>
                    </div>
                    <div class="card card-4">
                        <div class="card-info">
                            <span class="material-symbols-rounded">child_care</span>
                            <p class="card-title">Total Children</p>
                        </div>
                        <div class="card-count">
                            <p class="card-number" id="totalCount">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="dashboard-heading">Quick Actions</h2>
                <div class="actions-wrapper">
                    <a href="#" class="action-card action-card-1">
                        <div>
                            <span class="action-icon material-symbols-rounded">inbox</span>
                        </div>
                        <div>
                            <h4>Pending Approvals</h4>
                            <p>Review child health records</p>
                            <span class="action-count" id="pendingActionCount">0</span>
                        </div>
                    </a>
                    <a href="#" class="action-card action-card-2">
                        <div>
                            <span class="action-icon material-symbols-rounded">vaccines</span>
                        </div>
                        <div>
                            <h4>Today's Schedule</h4>
                            <p>View today's vaccination schedules</p>
                            <span class="action-count" id="todayActionCount">0</span>
                        </div>
                    </a>
                    <a href="#" class="action-card action-card-3">
                        <div>
                            <span class="action-icon material-symbols-rounded">assignment</span>
                        </div>
                        <div>
                            <h4>All Records</h4>
                            <p>Manage child health records</p>
                        </div>
                    </a>
                    <button class="action-card action-card-scanner" onclick="openQRScanner()">
                        <div>
                            <span class="action-icon material-symbols-rounded">qr_code_scanner</span>
                        </div>
                        <div>
                            <h4>QR Scanner</h4>
                            <p>Scan child QR codes</p>
                        </div>
                    </button>
                </div>
            </div>
                
            <div class="activity-task-container">
                <!-- Recent Activities -->
                <div class="activity-section">
                    <h2 class="dashboard-heading">Recent Activities</h2>
                    <div class="activity-list" id="activityList">
                        <div class="loading">
                            <p>Loading recent activity...</p>
                        </div>
                    </div>
                </div>

                <!-- Task Overview -->
                <div class="tasks-section">
                    <h2 class="dashboard-heading">Task Overview</h2>
                    <div class="task-wrapper">
                        <div class="task-card urgent" id="overdueCard">
                            <div class="task-header">
                                <h4>🚨 Overdue Tasks</h4>
                                <span class="task-count" id="overdueCount">0</span>
                            </div>
                            <p>Vaccinations that are past due date</p>
                            <a href="immunization.php" class="task-action">View Details</a>
                        </div>
                        <div class="task-card">
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
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        // Dashboard Data Loading
        async function loadDashboardData() {
            try {
                console.log('Loading BHW dashboard data...');
                const response = await fetch('../../php/supabase/bhw/get_dashboard_stats.php');

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

                await html5QrcodeInstance.start({
                        facingMode: "environment"
                    }, {
                        fps: 12,
                        qrbox: 300,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
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

                await track.applyConstraints({
                    advanced: [{
                        torch: !torchOn
                    }]
                });
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

        async function logoutBhw() {
				// const response = await fetch('../../php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('../../php/supabase/bhw/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/auth/login.php'; }
				else { alert('Logout failed: ' + data.message); }
        }
    </script>
</body>

</html>