<?php include 'Include/header.php'; ?>
<style>
	.badge {
		padding: 4px 8px;
		border-radius: 4px;
		font-size: 12px;
		font-weight: bold;
		text-transform: uppercase;
	}
	
	.badge-success {
		background-color: #28a745;
		color: white;
	}
	
	.badge-danger {
		background-color: #dc3545;
		color: white;
	}
	
	.badge-warning {
		background-color: #ffc107;
		color: #212529;
	}
	
	.btn {
		padding: 6px 12px;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 12px;
		text-decoration: none;
		display: inline-block;
	}
	
	.btn-sm {
		padding: 4px 8px;
		font-size: 11px;
	}
	
	.btn-primary {
		background-color: #007bff;
		color: white;
	}
	
	.btn-primary:hover {
		background-color: #0056b3;
	}
	
	.btn-success {
		background-color: #28a745;
		color: white;
	}
	
	.btn-success:hover {
		background-color: #1e7e34;
	}
	
	.loading {
		text-align: center;
		padding: 20px;
		color: #666;
	}
	
	.loading i {
		font-size: 24px;
		margin-bottom: 10px;
	}
	
	.table {
		border-collapse: collapse;
		width: 100%;
		margin-top: 10px;
	}
	
	.table th,
	.table td {
		padding: 8px;
		text-align: left;
		border-bottom: 1px solid #ddd;
	}
	
	.table th {
		background-color: #f8f9fa;
		font-weight: bold;
	}
	
	.table-hover tbody tr:hover {
		background-color: #f5f5f5;
	}
</style>

<!-- <div id="imageOverlay" onclick="closeOverlay(event)">
    <div class="content">
        <img id="overlayImage" src="" alt="Baby Card" />
        <div class="actions">
            <a id="openInNewTab" href="#" target="_blank">Open in new tab</a>
            <button onclick="hideOverlay()">Close</button>
        </div>
    </div>
</div> -->
<div id="qrOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.7); display:none; align-items:center; justify-content:center; z-index:9999;">
			<div style="background:#fff; padding:10px; max-width:90vw; max-height:90vh; display:flex; flex-direction:column; gap:8px;">
				<select id="cameraSelect" style="margin-bottom:6px; padding:4px 6px; display:none;" onchange="switchCamera(event)"></select>
				<div id="qrReader" style="width: 340px;"></div>
				<div style="display:flex; justify-content:space-between; gap:8px; align-items:center; flex-wrap:wrap;">
					<span style="font-size:12px; color:#444;">Point camera at QR code</span>
					<label style="font-size:12px;">
						<span style="margin-right:6px;">or Upload Image:</span>
						<input type="file" id="qrImageInput" accept="image/*" onchange="scanFromImage(event)" />
					</label>
					<button id="torchBtn" onclick="toggleTorch()" style="display:none;">Torch On</button>
					<button onclick="closeScanner()">Close</button>
				</div>
			</div>
		</div>
    <div class="table-container">
        <table class="table table-hover" id="childhealthrecord">
                        <thead>
                            <tr>
                                <th>Child Fname</th>
                                <th>Child Lname</th>
                                <th>Gender</th>
                                <th>Birth Date</th>
                                <th>Place of Birth</th>
                                <th>Mother</th>
                                <th>Father</th>
                                <th>Address</th>
                                <th>Weight</th>
                                <th>Height</th>
                                <th>Birth Attendant</th>
                                <th>Status</th>
								<th>Action</th>

                            </tr>
                        </thead>
                        <tbody id="childhealthrecordBody">
                            <tr>
                                <td colspan="21" class="text-center">
                                    <div class="loading">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Loading records...</p>
                                    </div>
                                </td>
        

                            </tr>
                            <tr>
                        </tbody>
                    </table>
    </div>
	<div class="childinformation-container" style="display: none; height:100%; width:100%; background-color: gray; flex-direction: column; padding: 20px; overflow-y: auto;">
		<!-- Header with close and accept buttons -->
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h1>Child Information Review</h1>
			<div>
				<button onclick="closeChildInformation()" id="closeButton" style="margin-right: 10px;">Close</button>
				<button id="acceptButton" style="background-color: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px;">Accept Record</button>
			</div>
		</div>

		<!-- Main content area -->
		<div style="display: flex; gap: 20px; margin-bottom: 20px;">
			<!-- Child details section -->
			<div style="flex: 1; background-color: white; padding: 20px; border-radius: 8px;">
				<h2>Child Details <small>(Click to edit)</small></h2>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<p><strong>Name:</strong> <input type="text" id="childName" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
					<p><strong>Gender:</strong> 
						<select id="childGender" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;">
							<option value="Male">Male</option>
							<option value="Female">Female</option>
						</select>
					</p>
					<p><strong>Birth Date:</strong> <input type="date" id="childBirthDate" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
					<p><strong>Place of Birth:</strong> <input type="text" id="childPlaceOfBirth" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
					<p><strong>Birth Weight:</strong> <input type="number" id="childWeight" step="0.01" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"> kg</p>
					<p><strong>Birth Height:</strong> <input type="number" id="childHeight" step="0.01" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"> cm</p>
					<p><strong>Mother:</strong> <input type="text" id="childMother" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
					<p><strong>Father:</strong> <input type="text" id="childFather" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
					<p><strong>Birth Attendant:</strong> 
						<select id="childBirthAttendant" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;">
							<option value="Doctor">Doctor</option>
							<option value="Midwife">Midwife</option>
							<option value="Nurse">Nurse</option>
							<option value="Hilot">Hilot</option>
							<option value="Others">Others</option>
						</select>
					</p>
					<p><strong>Delivery Type:</strong> 
						<select id="childDeliveryType" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;">
							<option value="Normal">Normal</option>
							<option value="Caesarean Section">Caesarean Section</option>
						</select>
					</p>
					<p><strong>Birth Order:</strong> 
						<select id="childBirthOrder" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;">
							<option value="Single">Single</option>
							<option value="Twin">Twin</option>
						</select>
					</p>
					<p><strong>Address:</strong> <input type="text" id="childAddress" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"></p>
				</div>
				<div style="margin-top: 15px;">
					<button onclick="saveChildInfo()" style="background-color: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-right: 10px;">Save Changes</button>
					<button onclick="resetChildInfo()" style="background-color: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px;">Reset</button>
				</div>
			</div>

			<!-- Baby card image section -->
			<div style="flex: 1; background-color: white; padding: 20px; border-radius: 8px; text-align: center;">
				<h2>Baby Card Image</h2>
				<img src="" alt="Baby Card" id="childImage" style="width: 100%; max-height: 400px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px;">
			</div>
		</div>

		<!-- Vaccination records section -->
		<div style="background-color: white; padding: 20px; border-radius: 8px;">
			<h2>Vaccination Records</h2>
			<div id="vaccinationRecordsContainer">
				<div class="loading">
					<i class="fas fa-spinner fa-spin"></i>
					<p>Loading vaccination records...</p>
				</div>
			</div>
		</div>
	</div>
            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
		<script>
			async function getChildHealthRecord() {
				const body = document.querySelector('#childhealthrecordBody');
				body.innerHTML = '<tr><td colspan="21">Loading...</td></tr>';
				try {
					// const res = await fetch('/ebakunado/php/bhw/get_child_health_records.php');
					const res = await fetch('/ebakunado/php/supabase/shared/pending_chr.php');
					const data = await res.json();
					if (data.status !== 'success') { body.innerHTML = '<tr><td colspan="21">Failed to load records</td></tr>'; return; }
					if (!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="21">No records found</td></tr>'; return; }

					let rows = '';
					data.data.forEach(item => {
						rows += `<tr>
							<td hidden>${item.id || ''}</td>
							<td hidden>${item.user_id || ''}</td>
							<td hidden>${item.baby_id || ''}</td>
							<td>${item.child_fname || ''}</td>
							<td>${item.child_lname || ''}</td>
							<td>${item.child_gender || ''}</td>
							<td>${item.child_birth_date || ''}</td>
							<td>${item.place_of_birth || ''}</td>
							<td>${item.mother_name || ''}</td>
							<td>${item.father_name || ''}</td>
							<td>${item.address || ''}</td>
							<td>${item.birth_weight || ''}</td>
							<td>${item.birth_height || ''}</td>
							<td>${item.birth_attendant || ''}</td>
							<td>${item.status || ''}</td>
							<td><button onclick=\"viewChildInformation('${item.baby_id}')\">View</button></td>

						</tr>`;
					});
					body.innerHTML = rows;
				} catch (e) { body.innerHTML = '<tr><td colspan="21">Error loading records</td></tr>'; }
			}


			// Store original data for reset functionality
			let originalChildData = {};

			async function viewChildInformation(baby_id){
				formData = new FormData();
				formData.append('baby_id', baby_id);
				const response = await fetch('/ebakunado/php/supabase/shared/child_information.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') {
					console.log(data.data);
					
					// Store original data for reset functionality
					originalChildData = data.data[0];
					
					// Populate input fields
					document.querySelector('#childName').value = data.data[0].child_fname + ' ' + data.data[0].child_lname;
					document.querySelector('#childGender').value = data.data[0].child_gender;
					document.querySelector('#childBirthDate').value = data.data[0].child_birth_date;
					document.querySelector('#childPlaceOfBirth').value = data.data[0].place_of_birth;
					document.querySelector('#childAddress').value = data.data[0].address;
					document.querySelector('#childWeight').value = data.data[0].birth_weight;
					document.querySelector('#childHeight').value = data.data[0].birth_height;
					document.querySelector('#childMother').value = data.data[0].mother_name;
					document.querySelector('#childFather').value = data.data[0].father_name;
					document.querySelector('#childBirthAttendant').value = data.data[0].birth_attendant;
					document.querySelector('#childDeliveryType').value = data.data[0].delivery_type || 'Normal';
					document.querySelector('#childBirthOrder').value = data.data[0].birth_order || 'Single';
					document.querySelector('#childImage').src = data.data[0].babys_card;
					
					// Store baby_id for later use
					document.querySelector('.childinformation-container').dataset.babyId = baby_id;
					
					// Set up accept button
					document.querySelector('#acceptButton').onclick = () => { acceptRecord(baby_id); };
					
					// Load vaccination records
					await loadVaccinationRecords(baby_id);
					
					document.querySelector('.childinformation-container').style.display = 'flex';
					document.querySelector('.table-container').style.display = 'none';
				} else {
					console.log(data.message);
				}
			}

			function closeChildInformation(){
				document.querySelector('.childinformation-container').style.display = 'none';
				document.querySelector('.table-container').style.display = 'block';
			}

			async function loadVaccinationRecords(baby_id) {
				const container = document.querySelector('#vaccinationRecordsContainer');
				container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Loading vaccination records...</p></div>';
				
				try {
					const response = await fetch('/ebakunado/php/supabase/shared/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
					const data = await response.json();
					
					if (data.status !== 'success' || !data.data || data.data.length === 0) {
						container.innerHTML = '<p style="text-align: center; color: #666;">No vaccination records found</p>';
						return;
					}

					let html = '<table class="table table-hover" style="width: 100%; margin-top: 10px;">';
					html += '<thead><tr>';
					html += '<th>Vaccine</th><th>Dose</th><th>Schedule Date</th><th>Catch-up Date</th><th>Date Given</th><th>Status</th>';
					html += '</tr></thead><tbody>';
					
					data.data.forEach(record => {
						const statusClass = record.status === 'completed' ? 'success' : 
										   record.status === 'missed' ? 'danger' : 'warning';
						const statusText = record.status.charAt(0).toUpperCase() + record.status.slice(1);
						
						html += `<tr data-record-id="${record.id}">`;
						html += `<td>${record.vaccine_name || ''}</td>`;
						html += `<td>${record.dose_number || ''}</td>`;
						html += `<td>${record.schedule_date || ''}</td>`;
						html += `<td>${record.catch_up_date || ''}</td>`;
						html += `<td>${record.date_given || ''}</td>`;
						html += `<td><span class="badge badge-${statusClass}">${statusText}</span></td>`;
						html += `</tr>`;
					});
					
					html += '</tbody></table>';
					container.innerHTML = html;
					
				} catch (error) {
					console.error('Error loading vaccination records:', error);
					container.innerHTML = '<p style="text-align: center; color: #dc3545;">Error loading vaccination records</p>';
				}
			}


			function filterTable(){
				const q = (document.getElementById('searchInput').value || '').trim().toLowerCase();
				const rows = document.querySelectorAll('#childhealthrecordBody tr');
				rows.forEach(tr => {
					const tds = tr.querySelectorAll('td');
					if (!tds || tds.length === 0) return;
					const id = (tds[0].textContent || '').toLowerCase();
					const userId = (tds[1].textContent || '').toLowerCase();
					const babyId = (tds[2].textContent || '').toLowerCase();
					const fname = (tds[3].textContent || '').toLowerCase();
					const lname = (tds[4].textContent || '').toLowerCase();
					const childName = (tds[5].textContent || '').toLowerCase();
					const text = [id,userId,babyId,fname,lname,childName].join(' ');
					tr.style.display = text.includes(q) ? '' : 'none';
				});
			}

			function viewChrImage(urlEnc){
				const url = decodeURIComponent(urlEnc);
				document.querySelector('#overlayImage').src = url;
				document.querySelector('#openInNewTab').href = url;
				document.querySelector('#imageOverlay').style.display = 'flex';
			}
			function hideOverlay(){ document.querySelector('#imageOverlay').style.display = 'none'; document.querySelector('#overlayImage').src = ''; }
			function closeOverlay(e){ if(e.target && e.target.id === 'imageOverlay'){ hideOverlay(); } }

			async function acceptRecord(baby_id){
				const formData = new FormData(); formData.append('baby_id', baby_id);
				// const response = await fetch('/ebakunado/php/bhw/accept_chr.php', { method: 'POST', body: formData });
				const response = await fetch('/ebakunado/php/supabase/shared/accept_chr.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') { getChildHealthRecord(); }
				else { alert('Record not accepted: ' + data.message); }
				closeChildInformation();
			}

			async function rejectRecord(baby_id){
				const formData = new FormData(); formData.append('baby_id', baby_id);
				const response = await fetch('/ebakunado/php/mysql/bhw/reject_chr.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') { getChildHealthRecord(); }
				else { alert('Record not rejected: ' + data.message); }
			}


			window.addEventListener('DOMContentLoaded', getChildHealthRecord);

			let html5QrcodeInstance = null;
			async function openScanner(){
				const overlay = document.getElementById('qrOverlay');
				overlay.style.display = 'flex';
				console.log('[QR] Opening scanner...');
				try{
					// Check camera permissions/devices first
					const devices = await Html5Qrcode.getCameras().catch(err => { console.log('[QR] getCameras error:', err); return []; });
					console.log('[QR] Cameras found:', devices);
					if(!devices || devices.length === 0){ console.warn('[QR] No camera devices found. Use image upload.'); }
					// Populate camera select
					const camSel = document.getElementById('cameraSelect');
					camSel.innerHTML = '';
					if (devices && devices.length > 0){
						devices.forEach((d, idx) => {
							const opt = document.createElement('option');
							opt.value = d.id; opt.textContent = d.label || ('Camera ' + (idx+1)); camSel.appendChild(opt);
						});
						camSel.style.display = 'inline-block';
						// Try enabling torch control if supported (via capabilities check after start)
					} else { camSel.style.display = 'none'; }
					if(!html5QrcodeInstance){ html5QrcodeInstance = new Html5Qrcode("qrReader"); }
					await html5QrcodeInstance.start(
						{ facingMode: "environment" },
						{ fps: 12, qrbox: 360, formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ], disableFlip: true },
						onScanSuccess,
						onScanFailure
					);
					console.log('[QR] Scanner started');
					// Show torch button if track supports torch
					try{
						const stream = await html5QrcodeInstance.getState() ? document.querySelector('#qrReader video')?.srcObject : null;
						const track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
						const caps = track && track.getCapabilities ? track.getCapabilities() : {};
						const torchBtn = document.getElementById('torchBtn');
						if (caps && caps.torch !== undefined) { torchBtn.style.display = 'inline-block'; } else { torchBtn.style.display = 'none'; }
					}catch(_){ document.getElementById('torchBtn').style.display = 'none'; }
				}catch(e){ console.error('[QR] Camera error:', e); alert('Camera error: ' + e); }
			}

			async function closeScanner(){
				const overlay = document.getElementById('qrOverlay');
				overlay.style.display = 'none';
				try{
					if(html5QrcodeInstance){ await html5QrcodeInstance.stop(); await html5QrcodeInstance.clear(); }
				}catch(_){ /* ignore */ }
			}

			async function switchCamera(e){
				const deviceId = e && e.target ? e.target.value : null;
				if(!deviceId || !html5QrcodeInstance){ return; }
				console.log('[QR] Switching camera to', deviceId);
				try{
					await html5QrcodeInstance.stop();
					await html5QrcodeInstance.clear();
					await html5QrcodeInstance.start(
						{ deviceId: { exact: deviceId } },
						{ fps: 8, qrbox: 320, formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ] },
						onScanSuccess,
						onScanFailure
					);
				}catch(err){ console.error('[QR] Switch camera failed:', err); }
			}

			function onScanSuccess(decodedText){
				console.log('[QR] Scan success:', decodedText);
				closeScanner();



				const match = decodedText.match(/baby_id=([^&\s]+)/i);
				if(match && match[1]){
					document.getElementById('searchInput').value = decodeURIComponent(match[1]);
					filterTable();
					focusRowByBabyId(decodeURIComponent(match[1]));
					return;
				}

				document.getElementById('searchInput').value = decodedText;
				filterTable();
				focusRowByBabyId(decodedText);
			}

			function onScanFailure(err){
				console.log('[QR] Scanning...', err ? String(err).slice(0,80) : '');
			}

			

			function focusRowByBabyId(babyId){
				const rows = document.querySelectorAll('#childhealthrecordBody tr');
				for (const tr of rows){
					const tds = tr.querySelectorAll('td');
					if (!tds || tds.length < 3) continue;
					const val = (tds[2].textContent || '').trim();
					if (val === babyId){
						tr.scrollIntoView({ behavior: 'smooth', block: 'center' });
						const originalBg = tr.style.backgroundColor;
						tr.style.backgroundColor = '#fff6b3';
						setTimeout(() => { tr.style.backgroundColor = originalBg || ''; }, 1500);
						break;
					}
				}
			}

			let torchOn = false;
			async function toggleTorch(){
				try{
					const video = document.querySelector('#qrReader video');
					const stream = video && video.srcObject ? video.srcObject : null;
					const track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
					if(!track){ return; }
					await track.applyConstraints({ advanced: [{ torch: !torchOn }] });
					torchOn = !torchOn;
					document.getElementById('torchBtn').textContent = torchOn ? 'Torch Off' : 'Torch On';
				}catch(err){ console.warn('[QR] Torch not supported:', err); }
			}

			async function scanFromImage(event){
				const file = event.target && event.target.files && event.target.files[0];
				if(!file){ return; }
				console.log('[QR] Scanning from image:', file.name, file.type, file.size);
				try{
					// Use Html5Qrcode to scan file
					const result = await Html5QrcodeScanner.scanFile(file, true);
					console.log('[QR] Image scan result:', result);
					onScanSuccess(result);
				}catch(err){
					console.error('[QR] Image scan failed:', err);
					alert('Unable to read QR from image.');
				}
			}
			// Save child information changes
			async function saveChildInfo() {
				const baby_id = document.querySelector('.childinformation-container').dataset.babyId;
				if (!baby_id) {
					alert('No child record selected');
					return;
				}

				// Get form data
				const nameParts = document.querySelector('#childName').value.trim().split(' ');
				const child_fname = nameParts[0] || '';
				const child_lname = nameParts.slice(1).join(' ') || '';

				const updateData = {
					baby_id: baby_id,
					child_fname: child_fname,
					child_lname: child_lname,
					child_gender: document.querySelector('#childGender').value,
					child_birth_date: document.querySelector('#childBirthDate').value,
					place_of_birth: document.querySelector('#childPlaceOfBirth').value,
					mother_name: document.querySelector('#childMother').value,
					father_name: document.querySelector('#childFather').value,
					address: document.querySelector('#childAddress').value,
					birth_weight: document.querySelector('#childWeight').value,
					birth_height: document.querySelector('#childHeight').value,
					birth_attendant: document.querySelector('#childBirthAttendant').value,
					delivery_type: document.querySelector('#childDeliveryType').value,
					birth_order: document.querySelector('#childBirthOrder').value
				};

				try {
					const formData = new FormData();
					Object.keys(updateData).forEach(key => {
						formData.append(key, updateData[key]);
					});

					const response = await fetch('/ebakunado/php/supabase/shared/update_child_info.php', {
						method: 'POST',
						body: formData
					});
					const data = await response.json();

					if (data.status === 'success') {
						alert('Child information updated successfully!');
						// Update original data for reset functionality
						originalChildData = { ...originalChildData, ...updateData };
					} else {
						alert('Failed to update child information: ' + data.message);
					}
				} catch (error) {
					console.error('Error updating child info:', error);
					alert('Error updating child information');
				}
			}

			// Reset child information to original values
			function resetChildInfo() {
				if (!originalChildData || Object.keys(originalChildData).length === 0) {
					alert('No original data to reset to');
					return;
				}

				document.querySelector('#childName').value = originalChildData.child_fname + ' ' + originalChildData.child_lname;
				document.querySelector('#childGender').value = originalChildData.child_gender;
				document.querySelector('#childBirthDate').value = originalChildData.child_birth_date;
				document.querySelector('#childPlaceOfBirth').value = originalChildData.place_of_birth;
				document.querySelector('#childAddress').value = originalChildData.address;
				document.querySelector('#childWeight').value = originalChildData.birth_weight;
				document.querySelector('#childHeight').value = originalChildData.birth_height;
				document.querySelector('#childMother').value = originalChildData.mother_name;
				document.querySelector('#childFather').value = originalChildData.father_name;
				document.querySelector('#childBirthAttendant').value = originalChildData.birth_attendant;
				document.querySelector('#childDeliveryType').value = originalChildData.delivery_type || 'Normal';
				document.querySelector('#childBirthOrder').value = originalChildData.birth_order || 'Single';
			}

			async function logoutBhw() {
				// const response = await fetch('/ebakunado/php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('/ebakunado/php/supabase/shared/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/auth/login.php'; }
				else { alert('Logout failed: ' + data.message); }
			}
		</script>

<?php include 'Include/footer.php'; ?>