<?php session_start(); ?>
<?php
// Handle both BHW and Midwife sessions (but BHW should only see BHW features)
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type']; // Default to bhw for BHW pages
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = $_SESSION['fname'] . " " . $_SESSION['lname'];
if ($user_types != 'midwifes') {
    $user_type = 'Barangay Health Worker';
} else {
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
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/sidebar.css" />

    <link rel="stylesheet" href="css/notification-style.css" />
    <link rel="stylesheet" href="css/skeleton-loading.css" />
    <link rel="stylesheet" href="css/bhw/dashboard.css?v=1.0.4" />

</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="dashboard section-title">
                <span class="material-symbols-rounded">dashboard</span>
                Dashboard Overview
            </h2>
        </section>
        <section class="dashboard-section">
            <div class="dashboard-overview">
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">hourglass_top</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="pendingCount">0</p>
                            <p class="card-title">Pending Approvals</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-2">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">vaccines</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="todayCount">0</p>
                            <p class="card-title">Today's Vaccinations</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-3">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">warning</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="missedCount">0</p>
                            <p class="card-title">Missed Vaccinations</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>

                    <div class="card card-4">
                        <div class="card-icon">
                            <span class="material-symbols-rounded">child_care</span>
                        </div>
                        <div class="card-info">
                            <p class="card-number" id="totalCount">0</p>
                            <p class="card-title">Total Children</p>
                            <a class="card-link" href="#">
                                <span class="material-symbols-rounded">visibility</span>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Vaccine Schedule Chart -->
            <div class="monthly-vaccine-section">
                <div class="monthly-vaccine-card">
                    <div class="monthly-vaccine-header">
                        <h3 class="monthly-vaccine-title">
                            <span class="material-symbols-rounded">calendar_month</span>
                            Monthly Vaccine Schedule
                        </h3>
                        <div class="month-nav-controls">
                            <button class="month-nav-btn" id="prevMonthBtn" title="Previous Month">
                                <span class="material-symbols-rounded">chevron_left</span>
                            </button>
                            <div class="month-display" id="monthDisplay">November 2025</div>
                            <button class="month-nav-btn" id="nextMonthBtn" title="Next Month">
                                <span class="material-symbols-rounded">chevron_right</span>
                            </button>
                        </div>
                    </div>
                    <div class="monthly-vaccine-quick-switch">
                        <button class="quick-switch-btn active" data-month="current" id="currentMonthBtn">Current Month</button>
                        <button class="quick-switch-btn" data-month="next" id="nextMonthQuickBtn">Next Month</button>
                    </div>
                    <div class="charts-wrapper">
                        <div class="chart-container chart-bar">
                            <h4 class="chart-title">All Vaccines</h4>
                            <canvas id="vaccineChart"></canvas>
                        </div>
                        <div class="chart-container chart-donut">
                            <h4 class="chart-title">Top 5 Vaccines Distribution</h4>
                            <canvas id="vaccineDonutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="activity-task-container" >
                <div class="activity-section">
                    <h2 class="dashboard-heading">Recent Activities</h2>
                    <div class="activity-list" id="activityList">
                        <div class="loading">
                            <p>Loading recent activity...</p>
                        </div>
                    </div>
                </div>

                <div class="tasks-section">
                    <h2 class="dashboard-heading">Task Overview</h2>
                    <div class="task-wrapper">
                        <div class="task-card urgent" id="overdueCard">
                            <div class="task-header">
                                <h4>🚨 Overdue Tasks</h4>
                                <span class="task-count" id="overdueCount">0</span>
                            </div>
                            <p>Vaccinations that are past due date</p>
                            <a href="health-immunizations" class="task-action">View Details</a>
                        </div>
                        <div class="task-card">
                            <div class="task-card warning" id="tomorrowCard">
                                <div class="task-header">
                                    <h4>📅 Tomorrow's Tasks</h4>
                                    <span class="task-count" id="tomorrowCount">0</span>
                                </div>
                                <p>Vaccinations scheduled for tomorrow</p>
                                <a href="health-immunizations" class="task-action">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script>
        // Dashboard Data Loading
        async function loadDashboardData() {
            try {
                console.log('Loading BHW dashboard data...');
                const response = await fetch('php/supabase/bhw/get_dashboard_stats.php');

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Dashboard data received:', data);

                if (data.status === 'success') {
                    updateStats(data.data.stats);
                    updateActivity(data.data.recent_activities);
                    updateTasks(data.data.stats);
                    if (data.data.monthly_vaccines) {
                        initializeMonthlyChart(data.data.monthly_vaccines);
                    }
                    console.log('Dashboard updated successfully');
                } else {
                    console.error('Dashboard API error:', data);
                    showError('Failed to load dashboard data: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showError('Network error: ' + error.message);
                // Ensure card numbers don't stay in perpetual skeleton state on error
                if (typeof setDashboardCardNumbers === 'function') {
                    setDashboardCardNumbers({
                        pendingCount: 0,
                        todayCount: 0,
                        missedCount: 0,
                        totalCount: 0
                    });
                } else {
                    const ids = ['pendingCount', 'todayCount', 'missedCount', 'totalCount'];
                    ids.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = '0';
                    });
                }
            }
        }

        function updateStats(stats) {
            // If skeleton API is available, replace numbers via helper for smooth cross-fade
            if (typeof setDashboardCardNumbers === 'function') {
                setDashboardCardNumbers({
                    pendingCount: stats.pending_approvals,
                    todayCount: stats.today_vaccinations,
                    missedCount: stats.missed_vaccinations,
                    totalCount: stats.total_children
                });
            } else {
                const p = document.getElementById('pendingCount');
                const t = document.getElementById('todayCount');
                const m = document.getElementById('missedCount');
                const tc = document.getElementById('totalCount');
                if (p) p.textContent = stats.pending_approvals;
                if (t) t.textContent = stats.today_vaccinations;
                if (m) m.textContent = stats.missed_vaccinations;
                if (tc) tc.textContent = stats.total_children;
            }
            // Safely update any action counters if present (currently not in markup)
            const pa = document.getElementById('pendingActionCount');
            const ta = document.getElementById('todayActionCount');
            if (pa) pa.textContent = stats.pending_approvals;
            if (ta) ta.textContent = stats.today_vaccinations;
        }

        function updateTasks(stats) {
            const overdueEl = document.getElementById('overdueCount');
            const tomorrowEl = document.getElementById('tomorrowCount');
            if (overdueEl) overdueEl.textContent = stats.overdue_tasks;
            if (tomorrowEl) tomorrowEl.textContent = stats.tomorrow_vaccinations;
        }

        function updateActivity(activities) {
            const activityList = document.getElementById('activityList');
            if (!activityList) return; // Activity section is commented out, skip update

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
            if (!activityList) {
                console.error('Dashboard error:', message);
                return; // Activity section is commented out, just log to console
            }
            activityList.innerHTML = `
			<div class="loading">
				<p style="color: #dc3545;">${message}</p>
			</div>
		`;
        }

        // Monthly Vaccine Chart
        let vaccineChart = null;
        let vaccineDonutChart = null;
        let monthlyData = null;
        let currentViewMonth = 'current'; // 'current' or 'next'

        function initializeMonthlyChart(data) {
            if (!data || !data.current_month || !data.next_month) {
                console.warn('Monthly vaccine data not available');
                return;
            }
            monthlyData = data;
            const chartCanvas = document.getElementById('vaccineChart');
            const donutCanvas = document.getElementById('vaccineDonutChart');
            if (!chartCanvas || !donutCanvas) {
                console.warn('Chart canvas not found');
                return;
            }
            renderCharts('current');
            setupMonthNavigation();
        }

        function renderCharts(monthType) {
            if (!monthlyData) return;

            const monthData = monthType === 'current' ?
                monthlyData.current_month :
                monthlyData.next_month;

            if (!monthData) return;

            // Update UI
            document.getElementById('monthDisplay').textContent = monthData.month_name;

            // Update quick switch buttons
            document.querySelectorAll('.quick-switch-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(monthType === 'current' ? 'currentMonthBtn' : 'nextMonthQuickBtn').classList.add('active');

            currentViewMonth = monthType;

            // Prepare chart data
            const labels = monthData.vaccines.map(v => {
                // Shorten long vaccine names for better display
                let name = v.name;
                if (name.includes('Pentavalent')) {
                    name = name.replace('Pentavalent (DPT-HepB-Hib)', 'Pentavalent');
                }
                if (name.includes('MCV1')) name = 'MCV1 (AMV)';
                if (name.includes('MCV2')) name = 'MCV2 (MMR)';
                return name;
            });
            const counts = monthData.vaccines.map(v => v.count);

            // Destroy existing charts if they exist
            if (vaccineChart) {
                vaccineChart.destroy();
            }
            if (vaccineDonutChart) {
                vaccineDonutChart.destroy();
            }

            // Color palette for charts
            const primaryColor = monthType === 'current' ?
                'rgba(59, 130, 246, 0.8)' :
                'rgba(20, 184, 166, 0.8)';
            const primaryColorSolid = monthType === 'current' ?
                'rgba(59, 130, 246, 1)' :
                'rgba(20, 184, 166, 1)';

            const colorPalette = [
                'rgba(59, 130, 246, 0.9)', // Blue
                'rgba(20, 184, 166, 0.9)', // Teal
                'rgba(139, 92, 246, 0.9)', // Purple
                'rgba(236, 72, 153, 0.9)', // Pink
                'rgba(251, 146, 60, 0.9)', // Orange
                'rgba(34, 197, 94, 0.9)', // Green
                'rgba(239, 68, 68, 0.9)', // Red
            ];

            // Render Bar Chart (all 14 vaccines)
            const barCtx = document.getElementById('vaccineChart').getContext('2d');
            vaccineChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Vaccines Needed',
                        data: counts,
                        backgroundColor: counts.map((count, idx) => {
                            if (count === 0) return 'rgba(229, 231, 235, 0.3)';
                            // Create gradient effect based on count
                            const intensity = Math.min(count / 20, 1);
                            return monthType === 'current' ?
                                `rgba(59, 130, 246, ${0.5 + intensity * 0.4})` :
                                `rgba(20, 184, 166, ${0.5 + intensity * 0.4})`;
                        }),
                        borderColor: counts.map(count =>
                            count === 0 ?
                            'rgba(229, 231, 235, 0.5)' :
                            primaryColorSolid
                        ),
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            padding: 14,
                            titleFont: {
                                size: 13,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 12
                            },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.x + ' vials needed';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                font: {
                                    size: 10
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                color: 'rgba(229, 231, 235, 0.4)',
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#374151',
                                maxRotation: 0,
                                minRotation: 0
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Prepare data for donut chart (top 5 vaccines)
            const vaccinesWithCounts = monthData.vaccines
                .map((v, idx) => ({
                    name: labels[idx],
                    count: v.count,
                    originalName: v.name
                }))
                .filter(v => v.count > 0)
                .sort((a, b) => b.count - a.count)
                .slice(0, 5);

            const donutLabels = vaccinesWithCounts.map(v => v.name);
            const donutCounts = vaccinesWithCounts.map(v => v.count);
            const totalDonut = donutCounts.reduce((sum, count) => sum + count, 0);

            // Render Donut Chart (top 5)
            const donutCtx = document.getElementById('vaccineDonutChart').getContext('2d');

            // Register center text plugin
            const centerTextPlugin = {
                id: 'centerText',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                    const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;

                    ctx.save();
                    ctx.font = 'bold 28px Poppins, sans-serif';
                    ctx.fillStyle = '#1F2937';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(totalDonut, centerX, centerY - 8);

                    ctx.font = '11px Poppins, sans-serif';
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('total vial', centerX, centerY + 16);
                    ctx.restore();
                }
            };

            vaccineDonutChart = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutCounts,
                        backgroundColor: colorPalette.slice(0, donutCounts.length),
                        borderColor: '#ffffff',
                        borderWidth: 2.5,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: {
                                    size: 11,
                                    family: 'Poppins, sans-serif'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = totalDonut > 0 ? ((value / totalDonut) * 100).toFixed(1) : 0;
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                strokeStyle: data.datasets[0].borderColor,
                                                lineWidth: data.datasets[0].borderWidth,
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            padding: 14,
                            titleFont: {
                                size: 13,
                                weight: '600',
                                family: 'Poppins, sans-serif'
                            },
                            bodyFont: {
                                size: 12,
                                family: 'Poppins, sans-serif'
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const percentage = totalDonut > 0 ? ((value / totalDonut) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} vials (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                },
                plugins: [centerTextPlugin]
            });
        }

        function setupMonthNavigation() {
            // Previous/Next buttons
            document.getElementById('prevMonthBtn').addEventListener('click', () => {
                if (currentViewMonth === 'next') {
                    renderCharts('current');
                }
            });

            document.getElementById('nextMonthBtn').addEventListener('click', () => {
                if (currentViewMonth === 'current') {
                    renderCharts('next');
                }
            });

            // Quick switch buttons
            document.getElementById('currentMonthBtn').addEventListener('click', () => {
                renderCharts('current');
            });

            document.getElementById('nextMonthQuickBtn').addEventListener('click', () => {
                renderCharts('next');
            });
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
                const path = window.location.pathname;
                const base = path.substring(0, path.lastIndexOf('/'));
                window.location.href = base + '/health-child/' + encodeURIComponent(match[1]);
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
            // Apply skeleton shimmer to card numbers immediately
            if (typeof applyDashboardCardNumbersSkeleton === 'function') {
                applyDashboardCardNumbersSkeleton();
            }
            // Fetch and populate real data
            loadDashboardData();
        });

        async function logoutBhw() {
            // const response = await fetch('/ebakunado/php/bhw/logout.php', { method: 'POST' });
            const response = await fetch('php/supabase/bhw/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.status === 'success') {
                // Clear JWT token from localStorage
                localStorage.removeItem('jwt_token');
                sessionStorage.clear();
                window.location.href = 'login';
            } else {
                alert('Logout failed: ' + data.message);
            }
        }
    </script>
</body>

</html>