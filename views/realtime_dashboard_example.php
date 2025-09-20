<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Dashboard - Ebakunado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .activity-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .activity-log-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            animation: fadeIn 0.3s ease-out;
        }
        .activity-log-item:last-child {
            border-bottom: none;
        }
        .log-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .log-description {
            flex: 1;
        }
        .log-time {
            color: #7f8c8d;
            font-size: 0.8em;
        }
        .notification {
            background: #4CAF50;
            color: white;
            padding: 15px;
            margin: 5px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }
        .notification-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification button {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 10px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .realtime-indicator {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #27ae60;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            z-index: 1000;
        }
        .realtime-indicator::before {
            content: "●";
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Real-time indicator -->
    <div class="realtime-indicator">Live Updates Active</div>

    <div class="dashboard-container">
        <h1>Real-time Dashboard - Ebakunado</h1>
        <p>This dashboard updates in real-time when data changes in the database.</p>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="total-users">0</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-midwives">0</div>
                <div class="stat-label">Midwives</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-bhw">0</div>
                <div class="stat-label">BHW</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-child-records">0</div>
                <div class="stat-label">Child Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-immunization-records">0</div>
                <div class="stat-label">Immunization Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-activity-logs">0</div>
                <div class="stat-label">Activity Logs</div>
            </div>
        </div>

        <!-- Activity Logs Section -->
        <div class="activity-section">
            <h2>Recent Activity (Live Updates)</h2>
            <div id="activity-logs-list">
                <p>Loading activity logs...</p>
            </div>
        </div>
    </div>

    <?php
    // Include real-time helper
    include '../database/RealtimeHelper.php';
    
    // Generate complete real-time setup
    echo generateCompleteRealtimeSetup();
    ?>

    <script>
    // Load initial data
    loadDashboardStats();
    loadActivityLogs();

    function loadDashboardStats() {
        fetch('api/get_dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('total-users').textContent = data.data.total_users;
                    document.getElementById('total-midwives').textContent = data.data.total_midwives;
                    document.getElementById('total-bhw').textContent = data.data.total_bhw;
                    document.getElementById('total-child-records').textContent = data.data.total_child_records;
                    document.getElementById('total-immunization-records').textContent = data.data.total_immunization_records;
                    document.getElementById('total-activity-logs').textContent = data.data.total_activity_logs;
                }
            })
            .catch(error => console.error('Error loading dashboard stats:', error));
    }

    function loadActivityLogs() {
        fetch('api/get_activity_logs.php?limit=10')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const container = document.getElementById('activity-logs-list');
                    container.innerHTML = '';
                    
                    data.data.forEach(log => {
                        const logElement = document.createElement('div');
                        logElement.className = 'activity-log-item';
                        logElement.innerHTML = `
                            <div class="log-content">
                                <div class="log-description">
                                    <strong>${log.user_type}:</strong> ${log.description}
                                </div>
                                <div class="log-time">
                                    ${new Date(log.created_at).toLocaleString()}
                                </div>
                            </div>
                        `;
                        container.appendChild(logElement);
                    });
                }
            })
            .catch(error => console.error('Error loading activity logs:', error));
    }

    // Override the real-time functions to work with our dashboard
    function updateUserStats(payload) {
        console.log('User stats updated:', payload);
        loadDashboardStats();
    }

    function updateChildRecordsStats(payload) {
        console.log('Child records stats updated:', payload);
        loadDashboardStats();
    }

    function updateImmunizationStats(payload) {
        console.log('Immunization stats updated:', payload);
        loadDashboardStats();
    }

    function updateActivityLogs(payload) {
        console.log('Activity logs updated:', payload);
        loadActivityLogs();
    }

    function addActivityLogToUI(log) {
        const activityList = document.getElementById('activity-logs-list');
        if (activityList) {
            const logElement = document.createElement('div');
            logElement.className = 'activity-log-item';
            logElement.innerHTML = `
                <div class="log-content">
                    <div class="log-description">
                        <strong>${log.user_type}:</strong> ${log.description}
                    </div>
                    <div class="log-time">
                        ${new Date(log.created_at).toLocaleString()}
                    </div>
                </div>
            `;
            activityList.insertBefore(logElement, activityList.firstChild);
            
            // Keep only last 10 logs visible
            while (activityList.children.length > 10) {
                activityList.removeChild(activityList.lastChild);
            }
        }
    }
    </script>
</body>
</html>
