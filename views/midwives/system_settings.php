<?php include 'Include/header.php'; ?>

<div class="content">
    <div class="settings-container">
        <h2>⚙️ System Settings</h2>
        <p class="settings-description">Manage SMS, Email, and system-wide configurations used across the entire eBakunado system</p>
        
        <!-- Settings Form -->
        <div class="settings-form">
            <form id="systemSettingsForm">
                <!-- Email Settings Section -->
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

                <!-- SMS Settings Section -->
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

                <!-- System Features -->
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

                <!-- Test Section -->
                <div class="settings-section">
                    <h3>🧪 Test System Components</h3>
                    <div class="test-buttons">
                        <button type="button" onclick="testEmailConfig()" class="test-btn email-test">
                            📧 Test Email
                        </button>
                        <button type="button" onclick="testSMSConfig()" class="test-btn sms-test">
                            📱 Test SMS
                        </button>
                        <button type="button" onclick="testCreateAccount()" class="test-btn account-test">
                            👤 Test Create Account
                        </button>
                        <button type="button" onclick="testForgotPassword()" class="test-btn password-test">
                            🔑 Test Forgot Password
                        </button>
                        <button type="button" onclick="testNotificationSystem()" class="test-btn system-test">
                            🔔 Test Vaccination Reminders
                        </button>
                    </div>
                    <div id="testResults" class="test-results"></div>
                </div>

                <!-- Save Button -->
                <div class="form-actions">
                    <button type="submit" class="save-btn">💾 Save All Settings</button>
                    <button type="button" onclick="resetToDefaults()" class="reset-btn">🔄 Reset to Defaults</button>
                </div>
            </form>
        </div>

        <!-- Current Settings Display -->
        <div class="current-settings">
            <h3>📋 Current System Configuration</h3>
            <div id="currentSettingsDisplay" class="settings-display">
                <div class="loading">Loading current settings...</div>
            </div>
        </div>

        <!-- System Usage Information -->
        <div class="usage-info">
            <h3>📖 System Usage Information</h3>
            <div class="usage-grid">
                <div class="usage-card">
                    <h4>📧 Email Usage</h4>
                    <ul>
                        <li>Account verification emails</li>
                        <li>Password reset confirmations</li>
                        <li>Vaccination reminder emails</li>
                        <li>System notifications</li>
                    </ul>
                </div>
                <div class="usage-card">
                    <h4>📱 SMS Usage</h4>
                    <ul>
                        <li>OTP verification codes</li>
                        <li>Password reset codes</li>
                        <li>Vaccination reminders</li>
                        <li>Emergency notifications</li>
                    </ul>
                </div>
                <div class="usage-card">
                    <h4>🔔 Automated Features</h4>
                    <ul>
                        <li>Daily vaccination reminders</li>
                        <li>Missed schedule alerts</li>
                        <li>System health monitoring</li>
                        <li>Backup notifications</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.settings-description {
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.settings-form {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.settings-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.settings-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.settings-section h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-description {
    color: #666;
    font-size: 13px;
    margin-bottom: 20px;
    font-style: italic;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

.help-text {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.help-text a {
    color: #1976d2;
    text-decoration: none;
}

.help-text a:hover {
    text-decoration: underline;
}

.test-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.test-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s;
}

.email-test {
    background: #28a745;
    color: white;
}

.sms-test {
    background: #17a2b8;
    color: white;
}

.account-test {
    background: #6f42c1;
    color: white;
}

.password-test {
    background: #fd7e14;
    color: white;
}

.system-test {
    background: #ffc107;
    color: #212529;
}

.test-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.test-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.test-results {
    min-height: 50px;
    padding: 15px;
    border-radius: 4px;
    font-size: 14px;
    display: none;
}

.test-results.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    display: block;
}

.test-results.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    display: block;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.save-btn {
    background: #1976d2;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s;
}

.save-btn:hover {
    background: #1565c0;
}

.reset-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s;
}

.reset-btn:hover {
    background: #5a6268;
}

.current-settings {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.current-settings h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.settings-display {
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    font-size: 13px;
    line-height: 1.6;
}

.loading {
    text-align: center;
    color: #666;
    font-style: italic;
}

.usage-info {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.usage-info h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.usage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.usage-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 20px;
}

.usage-card h4 {
    color: #333;
    margin-bottom: 15px;
    font-size: 16px;
}

.usage-card ul {
    list-style: none;
    padding: 0;
}

.usage-card li {
    padding: 5px 0;
    color: #666;
    font-size: 14px;
    position: relative;
    padding-left: 20px;
}

.usage-card li::before {
    content: "•";
    color: #1976d2;
    font-weight: bold;
    position: absolute;
    left: 0;
}

.success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    display: none;
}

.error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    display: none;
}
</style>

<script>
// Load current settings when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentSettings();
});

// Load current system settings
async function loadCurrentSettings() {
    try {
        const response = await fetch('../../php/supabase/shared/get_system_settings.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            // Populate form with current settings
            document.getElementById('email_username').value = data.settings.email_username || '';
            document.getElementById('email_password').value = data.settings.email_password || '';
            document.getElementById('sms_api_key').value = data.settings.sms_api_key || '';
            document.getElementById('sms_device_id').value = data.settings.sms_device_id || '';
            document.getElementById('notification_time').value = data.settings.notification_time || '02:40';
            document.getElementById('system_name').value = data.settings.system_name || 'eBakunado';
            document.getElementById('health_center_name').value = data.settings.health_center_name || 'City Health Department, Ormoc City';
            
            // Display current settings
            displayCurrentSettings(data.settings);
        } else {
            showError('Failed to load current settings: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showError('Error loading settings: ' + error.message);
    }
}

// Display current settings in readable format
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

// Save system settings
document.getElementById('systemSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../../php/supabase/shared/save_system_settings.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showSuccess('System settings saved successfully! All features will use the new configuration.');
            loadCurrentSettings(); // Reload to show updated settings
        } else {
            showError('Failed to save settings: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        showError('Error saving settings: ' + error.message);
    }
});

// Test email configuration
async function testEmailConfig() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('test_type', 'email');
        formData.append('email_username', document.getElementById('email_username').value);
        formData.append('email_password', document.getElementById('email_password').value);
        
        const response = await fetch('../../php/supabase/shared/test_system_config.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultsDiv.className = 'test-results success';
            resultsDiv.innerHTML = '✅ Email configuration test successful!<br>' + data.message;
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.className = 'test-results error';
            resultsDiv.innerHTML = '❌ Email test failed: ' + data.message;
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        resultsDiv.className = 'test-results error';
        resultsDiv.innerHTML = '❌ Email test error: ' + error.message;
        resultsDiv.style.display = 'block';
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Test SMS configuration
async function testSMSConfig() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('test_type', 'sms');
        formData.append('sms_api_key', document.getElementById('sms_api_key').value);
        formData.append('sms_device_id', document.getElementById('sms_device_id').value);
        
        const response = await fetch('../../php/supabase/shared/test_system_config.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultsDiv.className = 'test-results success';
            resultsDiv.innerHTML = '✅ SMS configuration test successful!<br>' + data.message;
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.className = 'test-results error';
            resultsDiv.innerHTML = '❌ SMS test failed: ' + data.message;
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        resultsDiv.className = 'test-results error';
        resultsDiv.innerHTML = '❌ SMS test error: ' + error.message;
        resultsDiv.style.display = 'block';
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Test create account flow
async function testCreateAccount() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('test_type', 'create_account');
        
        const response = await fetch('../../php/supabase/shared/test_system_config.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultsDiv.className = 'test-results success';
            resultsDiv.innerHTML = '✅ Create Account test successful!<br>' + data.message;
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.className = 'test-results error';
            resultsDiv.innerHTML = '❌ Create Account test failed: ' + data.message;
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        resultsDiv.className = 'test-results error';
        resultsDiv.innerHTML = '❌ Create Account test error: ' + error.message;
        resultsDiv.style.display = 'block';
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Test forgot password flow
async function testForgotPassword() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('test_type', 'forgot_password');
        
        const response = await fetch('../../php/supabase/shared/test_system_config.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultsDiv.className = 'test-results success';
            resultsDiv.innerHTML = '✅ Forgot Password test successful!<br>' + data.message;
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.className = 'test-results error';
            resultsDiv.innerHTML = '❌ Forgot Password test failed: ' + data.message;
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        resultsDiv.className = 'test-results error';
        resultsDiv.innerHTML = '❌ Forgot Password test error: ' + error.message;
        resultsDiv.style.display = 'block';
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Test full notification system
async function testNotificationSystem() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Testing...';
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'none';
    
    try {
        const response = await fetch('../../php/supabase/shared/send_schedule_notifications.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            resultsDiv.className = 'test-results success';
            resultsDiv.innerHTML = '✅ Vaccination reminder system test successful!<br>' + 
                'Notifications sent: ' + data.notifications_sent + '<br>' +
                'Date checked: ' + data.date;
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.className = 'test-results error';
            resultsDiv.innerHTML = '❌ Vaccination reminder test failed: ' + data.message;
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        resultsDiv.className = 'test-results error';
        resultsDiv.innerHTML = '❌ Vaccination reminder test error: ' + error.message;
        resultsDiv.style.display = 'block';
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

// Reset to default settings
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

// Show success message
function showSuccess(message) {
    const successDiv = document.querySelector('.success-message');
    if (!successDiv) {
        const div = document.createElement('div');
        div.className = 'success-message';
        document.querySelector('.settings-form').insertBefore(div, document.querySelector('.settings-form').firstChild);
    }
    document.querySelector('.success-message').innerHTML = '✅ ' + message;
    document.querySelector('.success-message').style.display = 'block';
    setTimeout(() => {
        document.querySelector('.success-message').style.display = 'none';
    }, 5000);
}

// Show error message
function showError(message) {
    const errorDiv = document.querySelector('.error-message');
    if (!errorDiv) {
        const div = document.createElement('div');
        div.className = 'error-message';
        document.querySelector('.settings-form').insertBefore(div, document.querySelector('.settings-form').firstChild);
    }
    document.querySelector('.error-message').innerHTML = '❌ ' + message;
    document.querySelector('.error-message').style.display = 'block';
    setTimeout(() => {
        document.querySelector('.error-message').style.display = 'none';
    }, 5000);
}
</script>

<?php include 'Include/footer.php'; ?>
