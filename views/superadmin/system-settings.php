<?php session_start(); ?>
<?php 
$current_page = 'system-settings';
$page_title = 'System Settings';
$user_id = $_SESSION['super_admin_id'];
$user_name = $_SESSION['fname'] ?? 'Super Admin';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1>System Settings</h1>
        <p>Manage SMS and Email configuration for OTP, Authentication, and Daily Notifications</p>
    </div>

    <div class="settings-container">
        <h2>⚙️ System SMS & Email Configuration</h2>
        <p class="settings-description">These settings control all SMS and Email communications in the system, including OTP codes, authentication, and daily vaccination notifications.</p>
        
        <div class="settings-form">
            <form id="systemSettingsForm">
                <div class="settings-section">
                    <h3>📧 Email Configuration</h3>
                    <div class="form-group">
                        <label for="email_username">Gmail Username:</label>
                        <input type="email" id="email_username" name="email_username" placeholder="your-email@gmail.com" required>
                        <small class="help-text">This email will be used for all system communications</small>
                    </div>
                    <div class="form-group">
                        <label for="email_password">Gmail App Password:</label>
                        <input type="password" id="email_password" name="email_password" placeholder="Enter your Gmail App Password" required>
                        <small class="help-text">Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google Account Settings</a></small>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>📱 SMS Configuration (TextBee)</h3>
                    <p class="section-description">Used for: OTP verification, Password Reset, Daily Vaccination Notifications, and all system SMS communications</p>
                    <div class="form-group">
                        <label for="sms_api_key">API Key:</label>
                        <input type="text" id="sms_api_key" name="sms_api_key" placeholder="Enter TextBee API Key" required>
                        <small class="help-text">Your TextBee.dev API key for sending all system SMS (OTP, notifications, etc.)</small>
                    </div>
                    <div class="form-group">
                        <label for="sms_device_id">Device ID:</label>
                        <input type="text" id="sms_device_id" name="sms_device_id" placeholder="Enter TextBee Device ID" required>
                        <small class="help-text">Your TextBee.dev device ID for SMS sending</small>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>🔔 System Configuration</h3>
                    <div class="form-group">
                        <label for="notification_time">Daily Vaccination Reminder Time:</label>
                        <input type="time" id="notification_time" name="notification_time" value="02:40" required>
                        <small class="help-text">Time when automatic vaccination reminders are sent (Philippines time)</small>
                    </div>
                    <div class="form-group">
                        <label for="system_name">System Name:</label>
                        <input type="text" id="system_name" name="system_name" value="eBakunado" placeholder="System name for communications">
                        <small class="help-text">Name used in SMS and email communications</small>
                    </div>
                    <div class="form-group">
                        <label for="health_center_name">Health Center Name:</label>
                        <input type="text" id="health_center_name" name="health_center_name" value="City Health Department, Ormoc City" placeholder="Health center name">
                        <small class="help-text">Official name used in communications</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="save-btn">💾 Save Settings</button>
                    <button type="button" onclick="resetToDefaults()" class="reset-btn">🔄 Reset to Defaults</button>
                </div>
            </form>
        </div>

        <div class="current-settings">
            <h3>📋 Current Configuration</h3>
            <div id="currentSettingsDisplay" class="settings-display">
                <div class="loading">Loading current settings...</div>
            </div>
        </div>
    </div>
</main>

<style>
    .settings-container {
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .settings-container h2 {
        color: #333;
        margin-bottom: 10px;
    }

    .settings-description {
        color: #666;
        margin-bottom: 30px;
    }

    .settings-section {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .settings-section h3 {
        color: #1976d2;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .help-text {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 12px;
    }

    .help-text a {
        color: #1976d2;
    }

    .form-actions {
        margin-top: 30px;
        text-align: right;
    }

    .save-btn, .reset-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        margin-left: 10px;
    }

    .save-btn {
        background: #1976d2;
        color: white;
    }

    .save-btn:hover {
        background: #1565c0;
    }

    .reset-btn {
        background: #f5f5f5;
        color: #333;
    }

    .reset-btn:hover {
        background: #e0e0e0;
    }

    .current-settings {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #e0e0e0;
    }

    .current-settings h3 {
        color: #333;
        margin-bottom: 15px;
    }

    .settings-display {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 6px;
        font-size: 14px;
        line-height: 1.8;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() { 
    loadCurrentSettings(); 
});

async function loadCurrentSettings() {
    try {
        const response = await fetch('/ebakunado/php/supabase/superadmin/get_system_settings.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            document.getElementById('email_username').value = data.settings.email_username || '';
            document.getElementById('email_password').value = data.settings.email_password || '';
            document.getElementById('sms_api_key').value = data.settings.sms_api_key || '';
            document.getElementById('sms_device_id').value = data.settings.sms_device_id || '';
            document.getElementById('notification_time').value = data.settings.notification_time || '02:40';
            document.getElementById('system_name').value = data.settings.system_name || 'eBakunado';
            document.getElementById('health_center_name').value = data.settings.health_center_name || 'City Health Department, Ormoc City';
            displayCurrentSettings(data.settings);
        } else {
            showError('Failed to load current settings: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showError('Error loading settings: ' + error.message);
    }
}

function displayCurrentSettings(settings) {
    const display = document.getElementById('currentSettingsDisplay');
    let html = '<strong>Email Configuration:</strong><br>';
    html += 'Username: ' + (settings.email_username || 'Not set') + '<br>';
    html += 'Password: ' + (settings.email_password ? '••••••••' : 'Not set') + '<br><br>';
    html += '<strong>SMS Configuration (OTP & Notifications):</strong><br>';
    html += 'API Key: ' + (settings.sms_api_key ? settings.sms_api_key.substring(0, 8) + '...' : 'Not set') + '<br>';
    html += 'Device ID: ' + (settings.sms_device_id || 'Not set') + '<br><br>';
    html += '<strong>System Configuration:</strong><br>';
    html += 'Daily Reminder Time: ' + (settings.notification_time || '02:40') + ' (Philippines time)<br>';
    html += 'System Name: ' + (settings.system_name || 'eBakunado') + '<br>';
    html += 'Health Center: ' + (settings.health_center_name || 'City Health Department, Ormoc City') + '<br>';
    html += 'Last Updated: ' + (settings.updated_at || 'Never') + '<br>';
    display.innerHTML = html;
}

document.getElementById('systemSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/ebakunado/php/supabase/superadmin/save_system_settings.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            showSuccess('System settings saved successfully! OTP, authentication, and daily notification features will use the new configuration.');
            loadCurrentSettings();
        } else {
            showError('Failed to save settings: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        showError('Error saving settings: ' + error.message);
    }
});

function resetToDefaults() {
    if (confirm('Are you sure you want to reset to default settings? This will overwrite your current configuration.')) {
        document.getElementById('email_username').value = 'iquenxzx@gmail.com';
        document.getElementById('email_password').value = 'lews hdga hdvb glym';
        document.getElementById('sms_api_key').value = '859e05f9-b29e-4071-b29f-0bd14a273bc2';
        document.getElementById('sms_device_id').value = '687e5760c87689a0c22492b3';
        document.getElementById('notification_time').value = '02:40';
        document.getElementById('system_name').value = 'eBakunado';
        document.getElementById('health_center_name').value = 'City Health Department, Ormoc City';
    }
}

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        showConfirmButton: true
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        showConfirmButton: true
    });
}

function logoutSuperAdmin() {
    window.location.href = '../../php/supabase/superadmin/logout.php';
}
</script>

<?php include 'includes/footer.php'; ?>
