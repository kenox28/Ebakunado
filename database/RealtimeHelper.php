<?php
// Real-time Helper Functions for Supabase
// This file provides real-time functionality for your Ebakunado app

require_once 'SupabaseConfig.php';

class SupabaseRealtime {
    private $supabase_url;
    private $supabase_key;
    
    public function __construct($url, $key) {
        $this->supabase_url = rtrim($url, '/');
        $this->supabase_key = $key;
    }
    
    // Get real-time subscription URL for WebSocket connection
    public function getRealtimeUrl() {
        return str_replace('https://', 'wss://', $this->supabase_url) . '/realtime/v1/websocket';
    }
    
    // Generate JavaScript code for real-time subscriptions
    public function generateRealtimeJS($table, $callback_function = 'handleRealtimeUpdate') {
        return "
        <script>
        // Real-time subscription for $table
        const supabaseUrl = '{$this->supabase_url}';
        const supabaseKey = '{$this->supabase_key}';
        
        // Initialize Supabase client (you'll need to include the Supabase JS library)
        const { createClient } = supabase;
        const supabaseClient = createClient(supabaseUrl, supabaseKey);
        
        // Subscribe to real-time changes
        const subscription = supabaseClient
            .channel('$table-changes')
            .on('postgres_changes', 
                { 
                    event: '*', 
                    schema: 'public', 
                    table: '$table' 
                }, 
                (payload) => {
                    console.log('Real-time update received:', payload);
                    $callback_function(payload);
                }
            )
            .subscribe();
            
        // Function to handle real-time updates
        function $callback_function(payload) {
            console.log('$table updated:', payload);
            
            // Handle different event types
            switch(payload.eventType) {
                case 'INSERT':
                    handleInsert(payload.new);
                    break;
                case 'UPDATE':
                    handleUpdate(payload.new, payload.old);
                    break;
                case 'DELETE':
                    handleDelete(payload.old);
                    break;
            }
        }
        
        // Handle insert events
        function handleInsert(newRecord) {
            // Add new record to UI
            console.log('New record inserted:', newRecord);
            // You can update your UI here
        }
        
        // Handle update events
        function handleUpdate(newRecord, oldRecord) {
            // Update existing record in UI
            console.log('Record updated:', {new: newRecord, old: oldRecord});
            // You can update your UI here
        }
        
        // Handle delete events
        function handleDelete(oldRecord) {
            // Remove record from UI
            console.log('Record deleted:', oldRecord);
            // You can update your UI here
        }
        </script>
        ";
    }
    
    // Generate real-time dashboard updates
    public function generateDashboardRealtime() {
        return "
        <script>
        // Real-time dashboard updates
        const { createClient } = supabase;
        const supabaseClient = createClient('{$this->supabase_url}', '{$this->supabase_key}');
        
        // Subscribe to multiple tables for dashboard
        const dashboardSubscription = supabaseClient
            .channel('dashboard-updates')
            .on('postgres_changes', 
                { event: '*', schema: 'public', table: 'users' }, 
                (payload) => updateUserStats(payload)
            )
            .on('postgres_changes', 
                { event: '*', schema: 'public', table: 'child_health_records' }, 
                (payload) => updateChildRecordsStats(payload)
            )
            .on('postgres_changes', 
                { event: '*', schema: 'public', table: 'immunization_records' }, 
                (payload) => updateImmunizationStats(payload)
            )
            .on('postgres_changes', 
                { event: '*', schema: 'public', table: 'activity_logs' }, 
                (payload) => updateActivityLogs(payload)
            )
            .subscribe();
            
        function updateUserStats(payload) {
            // Update user count in dashboard
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('total-users').textContent = data.data.total_users;
                    }
                });
        }
        
        function updateChildRecordsStats(payload) {
            // Update child records count
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('total-child-records').textContent = data.data.total_child_records;
                    }
                });
        }
        
        function updateImmunizationStats(payload) {
            // Update immunization records count
            fetch('api/get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('total-immunization-records').textContent = data.data.total_immunization_records;
                    }
                });
        }
        
        function updateActivityLogs(payload) {
            // Add new activity log to the list
            if (payload.eventType === 'INSERT') {
                addActivityLogToUI(payload.new);
            }
        }
        
        function addActivityLogToUI(log) {
            const activityList = document.getElementById('activity-logs-list');
            if (activityList) {
                const logElement = document.createElement('div');
                logElement.className = 'activity-log-item';
                logElement.innerHTML = \`
                    <div class="log-content">
                        <strong>\${log.user_type}:</strong> \${log.description}
                        <small class="text-muted">\${new Date(log.created_at).toLocaleString()}</small>
                    </div>
                \`;
                activityList.insertBefore(logElement, activityList.firstChild);
                
                // Keep only last 10 logs visible
                while (activityList.children.length > 10) {
                    activityList.removeChild(activityList.lastChild);
                }
            }
        }
        </script>
        ";
    }
    
    // Generate real-time notifications
    public function generateNotificationSystem() {
        return "
        <script>
        // Real-time notification system
        const { createClient } = supabase;
        const supabaseClient = createClient('{$this->supabase_url}', '{$this->supabase_key}');
        
        // Subscribe to activity logs for notifications
        const notificationSubscription = supabaseClient
            .channel('notifications')
            .on('postgres_changes', 
                { event: 'INSERT', schema: 'public', table: 'activity_logs' }, 
                (payload) => showNotification(payload.new)
            )
            .subscribe();
            
        function showNotification(activity) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = \`
                <div class="notification-content">
                    <strong>\${activity.user_type}:</strong> \${activity.description}
                    <button onclick=\"this.parentElement.parentElement.remove()\">×</button>
                </div>
            \`;
            
            // Add to notification container
            let container = document.getElementById('notification-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notification-container';
                container.style.cssText = \`
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 1000;
                \`;
                document.body.appendChild(container);
            }
            
            container.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        </script>
        ";
    }
}

// Initialize real-time helper
$realtime = new SupabaseRealtime($supabase_url, $supabase_key);

// Helper functions for easy use
function enableRealtimeForTable($table, $callback = 'handleRealtimeUpdate') {
    global $realtime;
    return $realtime->generateRealtimeJS($table, $callback);
}

function enableDashboardRealtime() {
    global $realtime;
    return $realtime->generateDashboardRealtime();
}

function enableNotificationSystem() {
    global $realtime;
    return $realtime->generateNotificationSystem();
}

// Generate complete real-time setup
function generateCompleteRealtimeSetup() {
    return "
    <!-- Include Supabase JS Library -->
    <script src=\"https://unpkg.com/@supabase/supabase-js@2\"></script>
    
    " . enableDashboardRealtime() . "
    " . enableNotificationSystem() . "
    
    <style>
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
    
    .activity-log-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
    ";
}
?>
