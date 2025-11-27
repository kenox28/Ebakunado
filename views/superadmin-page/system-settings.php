<?php
session_start();

if (!isset($_SESSION['super_admin_id'])) {
    header("Location: login");
    exit();
}

$user_name = $_SESSION['fname'] ?? 'Super Admin';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>System Settings</h1>
            <p>Manage SMS and Email configuration for OTP, authentication, and daily notifications.</p>
        </div>

        <section class="content-section">
            <div class="settings-card">
                <div class="settings-card__header">
                    <div>
                        <h2>⚙️ System SMS & Email Configuration</h2>
                        <p>These settings control all system communications (OTP, password reset, daily vaccination reminders).</p>
                    </div>
                </div>

                <form id="systemSettingsForm" class="settings-form">
                    <div class="settings-grid">
                        <div class="settings-block">
                            <h3>📧 Email Configuration</h3>
                            <div class="form-group">
                                <label for="email_username">Gmail Username</label>
                                <input type="email" id="email_username" name="email_username" placeholder="your-email@gmail.com" required>
                                <small class="help-text">Used for all outgoing system emails.</small>
                            </div>
                            <div class="form-group">
                                <label for="email_password">Gmail App Password</label>
                                <input type="password" id="email_password" name="email_password" placeholder="Enter Gmail App Password" required>
                                <small class="help-text">Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google Account Settings</a>.</small>
                            </div>
                        </div>

                        <div class="settings-block">
                            <h3>📱 SMS Configuration (TextBee)</h3>
                            <p class="block-description">Used for OTP verification, password reset, and daily vaccination notifications.</p>
                            <div class="form-group">
                                <label for="sms_api_key">API Key</label>
                                <input type="text" id="sms_api_key" name="sms_api_key" placeholder="Enter TextBee API Key" required>
                                <small class="help-text">Provide your TextBee.dev API key.</small>
                            </div>
                            <div class="form-group">
                                <label for="sms_device_id">Device ID</label>
                                <input type="text" id="sms_device_id" name="sms_device_id" placeholder="Enter TextBee Device ID" required>
                                <small class="help-text">Device ID used for sending SMS.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="resetToDefaults()" class="btn btn-secondary btn-icon">Reset to Defaults</button>
                        <button type="submit" class="btn btn-primary btn-icon">Save Settings</button>
                    </div>
                </form>
            </div>

            <div class="settings-card">
                <div class="settings-card__header">
                    <div>
                        <h2>📋 Current Configuration</h2>
                        <p>Snapshot of the values currently in use for all notifications.</p>
                    </div>
                </div>
                <div id="currentSettingsDisplay" class="settings-display">
                    <div class="loading">Loading current settings...</div>
                </div>
            </div>
        </section>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/utils/ui-feedback.js"></script>

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
            let html = '<div class="setting-row"><strong>Email Username:</strong> ' + (settings.email_username || 'Not set') + '</div>';
            html += '<div class="setting-row"><strong>Email Password:</strong> ' + (settings.email_password ? '••••••••' : 'Not set') + '</div>';
            html += '<div class="setting-row"><strong>SMS API Key:</strong> ' + (settings.sms_api_key ? settings.sms_api_key.substring(0, 8) + '...' : 'Not set') + '</div>';
            html += '<div class="setting-row"><strong>SMS Device ID:</strong> ' + (settings.sms_device_id || 'Not set') + '</div>';
            html += '<div class="setting-row"><strong>Daily Reminder Time:</strong> ' + (settings.notification_time || '02:40') + ' (GMT+8)</div>';
            html += '<div class="setting-row"><strong>System Name:</strong> ' + (settings.system_name || 'eBakunado') + '</div>';
            html += '<div class="setting-row"><strong>Health Center:</strong> ' + (settings.health_center_name || 'City Health Department, Ormoc City') + '</div>';
            html += '<div class="setting-row"><strong>Last Updated:</strong> ' + (settings.updated_at || 'Never') + '</div>';
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
            if (confirm('Reset to default settings? This will overwrite the current configuration.')) {
                document.getElementById('email_username').value = 'iquenxzx@gmail.com';
                document.getElementById('email_password').value = 'lews hdga hdvb glym';
                document.getElementById('sms_api_key').value = '859e05f9-b29e-4071-b29f-0bd14a273bc2';
                document.getElementById('sms_device_id').value = '687e5760c87689a0c22492b3';
            }
        }

        function showSuccess(message) {
            if (window.UIFeedback) {
                window.UIFeedback.showToast({
                    title: 'Success',
                    message,
                    variant: 'success'
                });
            } else {
                alert(message);
            }
        }

        function showError(message) {
            if (window.UIFeedback) {
                window.UIFeedback.showToast({
                    title: 'Error',
                    message,
                    variant: 'error'
                });
            } else {
                alert(message);
            }
        }
    </script>

    <style>
        .settings-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            padding: 28px;
            margin-bottom: 24px;
            border: 1px solid #edf0f7;
        }

        .settings-card__header h2 {
            margin: 0 0 6px;
            font-size: 1.8rem;
        }

        .settings-card__header p {
            margin: 0;
            color: #6b7280;
        }

        .settings-form {
            margin-top: 24px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .settings-block {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            background: #fafbff;
        }

        .settings-block h3 {
            margin: 0 0 10px;
            color: #0f172a;
        }

        .block-description {
            margin: 0 0 12px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-weight: 600;
            color: #0f172a;
        }

        .form-group input {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font: inherit;
        }

        .help-text {
            font-size: 1.2rem;
            color: #6b7280;
        }

        .help-text a {
            color: #0f6c35;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 12px;
        }

        .settings-display {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            background: #f8fafc;
            line-height: 1.7;
            font-size: 1.35rem;
        }

        .settings-display .setting-row {
            margin-bottom: 8px;
        }

        @media (max-width: 640px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</body>

</html>

