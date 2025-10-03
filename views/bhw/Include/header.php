<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Document</title>
	</head>
	<style>
		* {
			padding: 0;
			margin: 0;
		}
		body {
			height: 100vh;
			width: 100%;
			background-color: #f0f0f0;
			display: flex;
		}
		header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			border: 1px solid #000;
			width: 100%;
            
			background-color: #f0f0f0;
		}
		nav {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 10px;
		}
		aside {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding: 10px;
			width: 15%;
			height: 100%;
			background-color: green;
		}

		/* Menu links styling */
		aside a {
			display: flex;
			align-items: center;
			gap: 10px;
			text-decoration: none;
			color: #000;
			font-weight: 600;
			padding: 8px 10px;
			height: 20px;
			font-size: 18px;
			border: 1px solid #000;
		}
		/* Icon before text using data-icon */
		aside a::before {
			content: attr(data-icon);
			font-size: 18px;
			line-height: 1;
		}

		/* Collapsed state */
		aside.collapsed {
			width: 50px;
			padding-left: 6px;
			padding-right: 6px;
			align-items: center;
		}
		/* Hide link text when collapsed */
		aside.collapsed a span {
			display: none;
		}
		/* Center icons when collapsed */
		aside.collapsed a {
			justify-content: center;
			gap: 0;
		}
		/* Optionally hide the large title when collapsed */
		aside.collapsed h3 {
			display: none;
		}
		/* Adjust main area width only when collapsed */
		aside.collapsed + main {
			width: calc(100% - 50px);
		}
		/* Table container to prevent overlap and allow horizontal scroll */
		.table-container {
			width: 100%;
			max-width: 100%;
            height: 100%;
			overflow-x: auto;

		}
		.table-container table {
			width: 100%;
			border-collapse: collapse;
		}
		.table-container th,
		.table-container td {
			white-space: nowrap;
			border: 1px solid #000;
            text-align: center;
		}
		h3 {
			display: flex;
			justify-content: center;
			align-items: center;
			height: 70px;
			width: 100%;
			font-size: 18px;
			border: 1px solid #000;

			margin-bottom: 10%;
		}
		main {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 10px;
			width: 85%;
		}   


	</style>
	<body>
		<aside>
			<h3>Ebakunado</h3>
			<a href="#" data-icon="🏠"><span>Dashboard</span></a>
			<a href="./immunization.php" data-icon="💉"><span>Imuunization form</span></a>
			<a href="./pending_approval.php" data-icon="⏳"><span>Pending Approval</span></a>
			<a href="./child_health_record.php" data-icon="🧒"><span>Child Health Record</span></a>
			<a href="./target_client.php" data-icon="🎯"><span>Target Client List</span></a>
			<a href="#" onclick="openSMSNotification()" data-icon="📱"><span>SMS Notifications</span></a>
		</aside>
		<main>
			<header>
				<nav>
					<button
						class="menu-button"
						style="padding: 6px 10px; margin-right: 8px">
						☰
					</button>
					<a href="#">ebakunado</a>
				</nav>
				<nav>
					<input
						type="text"
						id="searchInput"
						placeholder="Search by Baby ID, Name, or User ID"
						style="padding: 6px 10px; width: 260px"
						oninput="filterTable()" />
					<button
						onclick="openScanner()"
						style="padding: 6px 10px; margin-left: 8px">
						Scan QR
					</button>
					<a href="#" onclick="logoutBhw()">Logout</a>
				</nav>
			</header>

			<!-- SMS Notification Modal -->
			<div id="smsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
				<div style="background: white; padding: 20px; border-radius: 8px; max-width: 90vw; max-height: 90vh; overflow-y: auto; width: 800px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
					<h2>📱 SMS Notifications</h2>
					<div>
						<button onclick="testSMSData()" style="background: #ffc107; color: #212529; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; margin-right: 10px;">Test Data</button>
						<button onclick="closeSMSModal()" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Close</button>
					</div>
				</div>

					<!-- Tab Navigation -->
					<div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
						<button id="upcomingTab" onclick="switchTab('upcoming')" style="padding: 10px 20px; border: none; background: #007bff; color: white; cursor: pointer; border-radius: 4px 4px 0 0;">Upcoming (Tomorrow)</button>
						<button id="missedTab" onclick="switchTab('missed')" style="padding: 10px 20px; border: none; background: #6c757d; color: white; cursor: pointer; border-radius: 4px 4px 0 0;">Missed Schedules</button>
					</div>

					<!-- Content Area -->
					<div id="smsContent">
						<div class="loading" style="text-align: center; padding: 40px;">
							<i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
							<p>Loading notification data...</p>
						</div>
					</div>
				</div>
			</div>

			<script>
				let currentTab = 'upcoming';
				let notificationData = null;

				function openSMSNotification() {
					document.getElementById('smsModal').style.display = 'flex';
					loadNotificationData();
				}

				function closeSMSModal() {
					document.getElementById('smsModal').style.display = 'none';
					notificationData = null;
				}

				async function testSMSData() {
					const content = document.getElementById('smsContent');
					content.innerHTML = '<div class="loading" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><p>Testing database connection...</p></div>';
					
					try {
						console.log('Testing SMS data endpoint...');
						const response = await fetch('../../php/supabase/bhw/test_sms_data.php');
						console.log('Test response status:', response.status);
						
						if (!response.ok) {
							throw new Error(`HTTP ${response.status}: ${response.statusText}`);
						}
						
						const data = await response.json();
						console.log('Test response data:', data);
						
						if (data.status === 'success') {
							let html = '<div style="padding: 20px;">';
							html += '<h3>Database Test Results</h3>';
							html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">';
							html += '<p><strong>Connection:</strong> ' + (data.data.test_connection ? '✅ Success' : '❌ Failed') + '</p>';
							html += '<p><strong>Immunization Records:</strong> ' + data.data.immunization_records_count + '</p>';
							html += '<p><strong>Child Health Records:</strong> ' + data.data.child_health_records_count + '</p>';
							html += '<p><strong>Users:</strong> ' + data.data.users_count + '</p>';
							html += '<p><strong>Pending Tomorrow (' + data.data.tomorrow_date + '):</strong> ' + data.data.pending_tomorrow_count + '</p>';
							html += '<p><strong>Missed Records:</strong> ' + data.data.missed_records_count + '</p>';
							html += '</div>';
							
							if (data.data.sample_immunization && Object.keys(data.data.sample_immunization).length > 0) {
								html += '<h4>Sample Immunization Record:</h4>';
								html += '<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;">' + JSON.stringify(data.data.sample_immunization, null, 2) + '</pre>';
							}
							
							if (data.data.pending_tomorrow_count > 0) {
								html += '<h4>Pending Tomorrow Records:</h4>';
								html += '<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;">' + JSON.stringify(data.data.pending_tomorrow, null, 2) + '</pre>';
							}
							
							html += '</div>';
							content.innerHTML = html;
						} else {
							content.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Test failed: ' + data.message + '</p></div>';
						}
					} catch (error) {
						console.error('Test error:', error);
						content.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Test Error: ' + error.message + '</p></div>';
					}
				}

				function switchTab(tab) {
					currentTab = tab;
					
					// Update tab buttons
					document.getElementById('upcomingTab').style.background = tab === 'upcoming' ? '#007bff' : '#6c757d';
					document.getElementById('missedTab').style.background = tab === 'missed' ? '#007bff' : '#6c757d';
					
					// Update content
					updateContent();
				}

				async function loadNotificationData() {
					const content = document.getElementById('smsContent');
					content.innerHTML = '<div class="loading" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><p>Loading notification data...</p></div>';
					
					try {
						console.log('Fetching SMS notifications from: ../../php/supabase/bhw/get_sms_notifications.php?type=both');
						const response = await fetch('../../php/supabase/bhw/get_sms_notifications.php?type=both');
						console.log('Response status:', response.status);
						console.log('Response headers:', response.headers);
						
						if (!response.ok) {
							throw new Error(`HTTP ${response.status}: ${response.statusText}`);
						}
						
						const data = await response.json();
						console.log('Response data:', data);
						
						if (data.status === 'success') {
							notificationData = data.data;
							updateContent();
						} else {
							console.error('API Error:', data);
							let errorMessage = 'Error loading data: ' + data.message;
							
							// Show detailed error information
							if (data.error_details) {
								errorMessage += '<br><br><strong>Error Details:</strong><br>';
								errorMessage += 'Type: ' + data.error_details.type + '<br>';
								errorMessage += 'Message: ' + data.error_details.message + '<br>';
								errorMessage += 'File: ' + data.error_details.file + '<br>';
								errorMessage += 'Line: ' + data.error_details.line + '<br>';
								
								if (data.error_details.trace && data.error_details.trace.length > 0) {
									errorMessage += '<br><strong>Stack Trace:</strong><br>';
									errorMessage += data.error_details.trace.slice(0, 5).join('<br>'); // Show first 5 lines
								}
							}
							
							if (data.debug_info) {
								errorMessage += '<br><br><strong>Debug Info:</strong><br>';
								errorMessage += 'Notification Type: ' + data.debug_info.notification_type + '<br>';
								errorMessage += 'Today: ' + data.debug_info.today + '<br>';
								errorMessage += 'Tomorrow: ' + data.debug_info.tomorrow + '<br>';
								errorMessage += 'BHW ID: ' + data.debug_info.session_bhw_id + '<br>';
							}
							
							// Also show the full JSON response for debugging
							errorMessage += '<br><br><strong>Full Response:</strong><br>';
							errorMessage += '<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; text-align: left; font-size: 12px; overflow-x: auto;">' + JSON.stringify(data, null, 2) + '</pre>';
							
							content.innerHTML = '<div style="padding: 20px; color: #dc3545; text-align: left;">' + errorMessage + '</div>';
						}
					} catch (error) {
						console.error('Error loading notification data:', error);
						content.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Network Error: ' + error.message + '</p><p>Check console for details.</p></div>';
					}
				}

				function updateContent() {
					if (!notificationData) return;
					
					const content = document.getElementById('smsContent');
					const data = notificationData[currentTab];
					const count = data.length;
					
					let html = '';
					
					// Summary
					html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">';
					html += '<h3 style="margin: 0 0 10px 0;">';
					if (currentTab === 'upcoming') {
						html += '📅 Upcoming Vaccinations (Tomorrow)';
					} else {
						html += '⚠️ Missed Vaccinations';
					}
					html += '</h3>';
					html += '<p style="margin: 0; font-size: 16px;"><strong>' + count + '</strong> ' + (count === 1 ? 'parent' : 'parents') + ' to notify</p>';
					html += '</div>';
					
					if (count === 0) {
						html += '<div style="text-align: center; padding: 40px; color: #6c757d;">';
						html += '<p>No ' + (currentTab === 'upcoming' ? 'upcoming' : 'missed') + ' schedules found.</p>';
						html += '</div>';
					} else {
						// Custom message option
						html += '<div style="margin-bottom: 20px;">';
						html += '<label style="display: block; margin-bottom: 5px; font-weight: bold;">Custom Message (Optional):</label>';
						html += '<textarea id="customMessage" placeholder="Leave empty to use default message..." style="width: 100%; height: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>';
						html += '</div>';
						
						// Send button
						html += '<div style="margin-bottom: 20px;">';
						html += '<button onclick="sendSMSNotifications()" style="background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px;">';
						html += '📱 Send SMS to ' + count + ' ' + (count === 1 ? 'Parent' : 'Parents');
						html += '</button>';
						html += '</div>';
						
						// List of recipients
						html += '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">';
						html += '<table style="width: 100%; border-collapse: collapse;">';
						html += '<thead style="background: #f8f9fa; position: sticky; top: 0;">';
						html += '<tr>';
						html += '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Parent Name</th>';
						html += '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Phone Number</th>';
						html += '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Child Name</th>';
						html += '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Vaccine</th>';
						html += '<th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Schedule Date</th>';
						html += '</tr>';
						html += '</thead>';
						html += '<tbody>';
						
						data.forEach(item => {
							html += '<tr>';
							html += '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + item.user_name + '</td>';
							html += '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + item.phone_number + '</td>';
							html += '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + item.child_name + '</td>';
							html += '<td style="padding: 10px; border-bottom: 1px solid #eee;">' + item.vaccine_name + ' (Dose ' + item.dose_number + ')</td>';
							html += '<td style="padding: 10px; border-bottom: 1px solid #eee;">';
							html += new Date(item.schedule_date).toLocaleDateString();
							if (currentTab === 'missed' && item.catch_up_date) {
								html += '<br><small style="color: #dc3545;">Catch-up: ' + new Date(item.catch_up_date).toLocaleDateString() + '</small>';
							}
							html += '</td>';
							html += '</tr>';
						});
						
						html += '</tbody>';
						html += '</table>';
						html += '</div>';
					}
					
					content.innerHTML = html;
				}

				async function sendSMSNotifications() {
					const customMessage = document.getElementById('customMessage')?.value || '';
					const button = event.target;
					const originalText = button.textContent;
					
					button.disabled = true;
					button.textContent = '📱 Sending SMS...';
					button.style.background = '#6c757d';
					
					try {
						const formData = new FormData();
						formData.append('type', currentTab);
						if (customMessage.trim()) {
							formData.append('custom_message', customMessage.trim());
						}
						
						const response = await fetch('../../php/supabase/bhw/send_sms_notifications.php', {
							method: 'POST',
							body: formData
						});
						
						const data = await response.json();
						
						if (data.status === 'success') {
							alert('SMS notifications sent successfully!\n\nSent: ' + data.sent_count + '\nFailed: ' + data.failed_count + '\nTotal: ' + data.total_count);
							
							// Reload data to refresh the list
							await loadNotificationData();
						} else {
							alert('Error sending SMS: ' + data.message);
						}
					} catch (error) {
						console.error('Error sending SMS:', error);
						alert('Error sending SMS notifications');
					} finally {
						button.disabled = false;
						button.textContent = originalText;
						button.style.background = '#28a745';
					}
				}
			</script>