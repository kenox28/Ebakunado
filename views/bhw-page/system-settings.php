<?php session_start(); ?>
<?php
// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') ." ". ($_SESSION['lname'] ?? '');
if($user_types != 'midwifes') { $user_type = 'Barangay Health Worker'; } else { $user_type = 'Midwife'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>System Settings</title>
    <link rel="stylesheet" href="css/main.css?v=1.0.1" />
    <link rel="stylesheet" href="css/header.css?v=1.0.1" />
    <link rel="stylesheet" href="css/sidebar.css" />
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main>
        <div class="content">
            <div class="settings-container">
                <h2>⚙️ System Settings</h2>
                <p class="settings-description">Manage SMS, Email, and system-wide configurations used across the entire eBakunado system</p>
                <div class="settings-form">
                    <form id="systemSettingsForm">
                        <div class="settings-section">
                            <h3>📧 Email Configuration (System-wide)</h3>
                            <p class="section-description">Used for: Create Account verification, Password Reset, Vaccination Reminders, and other system emails</p>
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
                            <p class="section-description">Used for: OTP verification, Password Reset, Vaccination Reminders, and other system SMS</p>
                            <div class="form-group">
                                <label for="sms_api_key">API Key:</label>
                                <input type="text" id="sms_api_key" name="sms_api_key" placeholder="Enter TextBee API Key" required>
                                <small class="help-text">Your TextBee.dev API key for sending SMS</small>
                            </div>
                            <div class="form-group">
                                <label for="sms_device_id">Device ID:</label>
                                <input type="text" id="sms_device_id" name="sms_device_id" placeholder="Enter TextBee Device ID" required>
                                <small class="help-text">Your TextBee.dev device ID for SMS sending</small>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3>🔔 Automated Features</h3>
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

                        <div class="settings-section">
                            <h3>🧪 Test System Components</h3>
                            <div class="test-buttons">
                                <button type="button" onclick="testEmailConfig()" class="test-btn email-test">📧 Test Email</button>
                                <button type="button" onclick="testSMSConfig()" class="test-btn sms-test">📱 Test SMS</button>
                                <button type="button" onclick="testCreateAccount()" class="test-btn account-test">👤 Test Create Account</button>
                                <button type="button" onclick="testForgotPassword()" class="test-btn password-test">🔑 Test Forgot Password</button>
                                <button type="button" onclick="testNotificationSystem()" class="test-btn system-test">🔔 Test Vaccination Reminders</button>
                            </div>
                            <div id="testResults" class="test-results"></div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">💾 Save All Settings</button>
                            <button type="button" onclick="resetToDefaults()" class="reset-btn">🔄 Reset to Defaults</button>
                        </div>
                    </form>
                </div>

                <div class="current-settings">
                    <h3>📋 Current System Configuration</h3>
                    <div id="currentSettingsDisplay" class="settings-display">
                        <div class="loading">Loading current settings...</div>
                    </div>
                </div>

                <div class="usage-info">
                    <h3>📖 System Usage Information</h3>
                    <div class="usage-grid">
                        <div class="usage-card"><h4>📧 Email Usage</h4><ul><li>Account verification emails</li><li>Password reset confirmations</li><li>Vaccination reminder emails</li><li>System notifications</li></ul></div>
                        <div class="usage-card"><h4>📱 SMS Usage</h4><ul><li>OTP verification codes</li><li>Password reset codes</li><li>Vaccination reminders</li><li>Emergency notifications</li></ul></div>
                        <div class="usage-card"><h4>🔔 Automated Features</h4><ul><li>Daily vaccination reminders</li><li>Missed schedule alerts</li><li>System health monitoring</li><li>Backup notifications</li></ul></div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        <?php echo file_get_contents(__DIR__ . '/../../css/main.css'); ?>
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() { loadCurrentSettings(); });
        async function loadCurrentSettings() {
            try {
                const response = await fetch('php/supabase/bhw/get_system_settings.php');
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
                } else { showError('Failed to load current settings: ' + data.message); }
            } catch (error) { console.error('Error loading settings:', error); showError('Error loading settings: ' + error.message); }
        }

        function displayCurrentSettings(settings) {
            const display = document.getElementById('currentSettingsDisplay');
            let html = '<strong>Email Configuration:</strong><br>';
            html += 'Username: ' + (settings.email_username || 'Not set') + '<br>';
            html += 'Password: ' + (settings.email_password ? '••••••••' : 'Not set') + '<br><br>';
            html += '<strong>SMS Configuration:</strong><br>';
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
                const response = await fetch('php/supabase/bhw/save_system_settings.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') { showSuccess('System settings saved successfully! All features will use the new configuration.'); loadCurrentSettings(); }
                else { showError('Failed to save settings: ' + data.message); }
            } catch (error) { console.error('Error saving settings:', error); showError('Error saving settings: ' + error.message); }
        });

        async function testEmailConfig() {
            const button = event.target; const originalText = button.textContent; button.disabled = true; button.textContent = 'Testing...';
            const resultsDiv = document.getElementById('testResults'); resultsDiv.style.display = 'none';
            try {
                const formData = new FormData(); formData.append('test_type', 'email'); formData.append('email_username', document.getElementById('email_username').value); formData.append('email_password', document.getElementById('email_password').value);
                const response = await fetch('php/supabase/bhw/test_system_config.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') { resultsDiv.className = 'test-results success'; resultsDiv.innerHTML = '✅ Email configuration test successful!<br>' + data.message; resultsDiv.style.display = 'block'; }
                else { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Email test failed: ' + data.message; resultsDiv.style.display = 'block'; }
            } catch (error) { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Email test error: ' + error.message; resultsDiv.style.display = 'block'; }
            finally { button.disabled = false; button.textContent = originalText; }
        }

        async function testSMSConfig() {
            const button = event.target; const originalText = button.textContent; button.disabled = true; button.textContent = 'Testing...';
            const resultsDiv = document.getElementById('testResults'); resultsDiv.style.display = 'none';
            try {
                const formData = new FormData(); formData.append('test_type', 'sms'); formData.append('sms_api_key', document.getElementById('sms_api_key').value); formData.append('sms_device_id', document.getElementById('sms_device_id').value);
                const response = await fetch('php/supabase/bhw/test_system_config.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') { resultsDiv.className = 'test-results success'; resultsDiv.innerHTML = '✅ SMS configuration test successful!<br>' + data.message; resultsDiv.style.display = 'block'; }
                else { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ SMS test failed: ' + data.message; resultsDiv.style.display = 'block'; }
            } catch (error) { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ SMS test error: ' + error.message; resultsDiv.style.display = 'block'; }
            finally { button.disabled = false; button.textContent = originalText; }
        }

        async function testCreateAccount() {
            const button = event.target; const originalText = button.textContent; button.disabled = true; button.textContent = 'Testing...';
            const resultsDiv = document.getElementById('testResults'); resultsDiv.style.display = 'none';
            try {
                const formData = new FormData(); formData.append('test_type', 'create_account');
                const response = await fetch('php/supabase/bhw/test_system_config.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') { resultsDiv.className = 'test-results success'; resultsDiv.innerHTML = '✅ Create Account test successful!<br>' + data.message; resultsDiv.style.display = 'block'; }
                else { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Create Account test failed: ' + data.message; resultsDiv.style.display = 'block'; }
            } catch (error) { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Create Account test error: ' + error.message; resultsDiv.style.display = 'block'; }
            finally { button.disabled = false; button.textContent = originalText; }
        }

        async function testForgotPassword() {
            const button = event.target; const originalText = button.textContent; button.disabled = true; button.textContent = 'Testing...';
            const resultsDiv = document.getElementById('testResults'); resultsDiv.style.display = 'none';
            try {
                const formData = new FormData(); formData.append('test_type', 'forgot_password');
                const response = await fetch('php/supabase/bhw/test_system_config.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') { resultsDiv.className = 'test-results success'; resultsDiv.innerHTML = '✅ Forgot Password test successful!<br>' + data.message; resultsDiv.style.display = 'block'; }
                else { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Forgot Password test failed: ' + data.message; resultsDiv.style.display = 'block'; }
            } catch (error) { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Forgot Password test error: ' + error.message; resultsDiv.style.display = 'block'; }
            finally { button.disabled = false; button.textContent = originalText; }
        }

        async function testNotificationSystem() {
            const button = event.target; const originalText = button.textContent; button.disabled = true; button.textContent = 'Testing...';
            const resultsDiv = document.getElementById('testResults'); resultsDiv.style.display = 'none';
            try {
                const response = await fetch('php/supabase/bhw/send_schedule_notifications.php');
                const data = await response.json();
                if (data.status === 'success') { resultsDiv.className = 'test-results success'; resultsDiv.innerHTML = '✅ Vaccination reminder system test successful!<br>' + 'Notifications sent: ' + data.notifications_sent + '<br>' + 'Date checked: ' + data.date; resultsDiv.style.display = 'block'; }
                else { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Vaccination reminder test failed: ' + data.message; resultsDiv.style.display = 'block'; }
            } catch (error) { resultsDiv.className = 'test-results error'; resultsDiv.innerHTML = '❌ Vaccination reminder test error: ' + error.message; resultsDiv.style.display = 'block'; }
            finally { button.disabled = false; button.textContent = originalText; }
        }

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
            ensureMessageDiv('success-message');
            document.querySelector('.success-message').innerHTML = '✅ ' + message;
            document.querySelector('.success-message').style.display = 'block';
            setTimeout(() => { document.querySelector('.success-message').style.display = 'none'; }, 5000);
        }

        function showError(message) {
            ensureMessageDiv('error-message');
            document.querySelector('.error-message').innerHTML = '❌ ' + message;
            document.querySelector('.error-message').style.display = 'block';
            setTimeout(() => { document.querySelector('.error-message').style.display = 'none'; }, 5000);
        }

        function ensureMessageDiv(cls) {
            const container = document.querySelector('.settings-form');
            let div = document.querySelector('.' + cls);
            if (!div) { div = document.createElement('div'); div.className = cls; container.insertBefore(div, container.firstChild); }
        }
        </script>
    </main>
    <script src="js/header-handler/profile-menu.js" defer></script>
</body>
</html>

