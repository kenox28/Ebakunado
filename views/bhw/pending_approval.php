<?php include 'Include/header.php'; ?>
<style>

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
	<div class="childinformation-container" style="display: none; height:100%; width:100%; background-color: gray;">
	<div style="width: 50%;">
			<h1>Child Information</h1>
			<p>Child Name: <span id="childName"></span></p>
			<p>Child Gender: <span id="childGender"></span></p>
			<p>Child Birth Date: <span id="childBirthDate"></span></p>
			<p>Child Place of Birth: <span id="childPlaceOfBirth"></span></p>
			<p>Child Address: <span id="childAddress"></span></p>
			<p>Child Weight: <span id="childWeight"></span></p>
			<p>Child Height: <span id="childHeight"></span></p>
			<p>Child Mother: <span id="childMother"></span></p>
			<p>Child Father: <span id="childFather"></span></p>
			<p>Child Birth Attendant: <span id="childBirthAttendant"></span></p>
		</div>
		<div style="width: 50%; background-color: white;">
			<button onclick="closeChildInformation()" id="closeButton">Close</button>

			<img src="" alt="" id="childImage" style="width: 100%; height: 100%; object-fit: cover;">
			<button id="acceptButton">Accept</button>
		</div>
	</div>
            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
		<script>
			async function getChildHealthRecord() {
				const body = document.querySelector('#childhealthrecordBody');
				body.innerHTML = '<tr><td colspan="21">Loading...</td></tr>';
				try {
					// const res = await fetch('../../php/bhw/get_child_health_records.php');
					const res = await fetch('../../php/supabase/bhw/pending_chr.php');
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


			async function viewChildInformation(baby_id){
				formData = new FormData();
				formData.append('baby_id', baby_id);
				const response = await fetch('../../php/supabase/bhw/child_information.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') {
					console.log(data.data);
					document.querySelector('#childName').textContent = data.data[0].child_fname + ' ' + data.data[0].child_lname;
					document.querySelector('#childGender').textContent = data.data[0].child_gender;
					document.querySelector('#childBirthDate').textContent = data.data[0].child_birth_date;
					document.querySelector('#childPlaceOfBirth').textContent = data.data[0].place_of_birth;
					document.querySelector('#childAddress').textContent = data.data[0].address;
					document.querySelector('#childWeight').textContent = data.data[0].birth_weight;
					document.querySelector('#childHeight').textContent = data.data[0].birth_height;
					document.querySelector('#childMother').textContent = data.data[0].mother_name;
					document.querySelector('#childFather').textContent = data.data[0].father_name;
					document.querySelector('#childBirthAttendant').textContent = data.data[0].birth_attendant;
					document.querySelector('#childImage').src = data.data[0].babys_card;
					document.querySelector('#acceptButton').addEventListener('click', () => { acceptRecord(baby_id); });
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
				// const response = await fetch('../../php/bhw/accept_chr.php', { method: 'POST', body: formData });
				const response = await fetch('../../php/supabase/bhw/accept_chr.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') { getChildHealthRecord(); }
				else { alert('Record not accepted: ' + data.message); }
				closeChildInformation();
			}

			async function rejectRecord(baby_id){
				const formData = new FormData(); formData.append('baby_id', baby_id);
				const response = await fetch('../../php/mysql/bhw/reject_chr.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') { getChildHealthRecord(); }
				else { alert('Record not rejected: ' + data.message); }
			}

			async function viewSchedule(baby_id, btn){
				const tr = btn.closest('tr');
				const next = tr.nextElementSibling;
				if (next && next.classList.contains('sched-row')) { next.remove(); return; }
				const res = await fetch('../../php/supabase/bhw/get_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
				const data = await res.json();
				let html = '<tr class="sched-row"><td colspan="21">';
				if (data.status !== 'success' || !data.data || data.data.length === 0) {
					html += '<div class="small">No schedule</div>';
				} else {
					html += '<table class="small"><tr><th>Vaccine</th><th>Dose #</th><th>Due</th><th>Date Given</th><th>Status</th><th>Mark Given</th></tr>';
					data.data.forEach(r => {
						html += `<tr>
							<td>${r.vaccine_name}</td>
							<td>${r.dose_number}</td>
							<td>${r.catch_up_date || ''}</td>
							<td>${r.date_given || ''}</td>
							<td>${r.status}</td>
							<td>${r.status === 'completed' ? 'Done' : `<button onclick=\"markGiven(${r.id})\">Mark</button>`}</td>
						</tr>`;
					});
					html += '</table>';
				}
				html += '</td></tr>';
				tr.insertAdjacentHTML('afterend', html);
			}

			async function markGiven(record_id){
				const date_given = prompt('Date given (YYYY-MM-DD):'); if(!date_given) return;
				const weight = prompt('Weight (kg) optional:');
				const height = prompt('Height (cm) optional:');
				const temperature = prompt('Temp (C) optional:');
				const fd = new FormData();
				fd.append('record_id', record_id);
				fd.append('date_given', date_given);
				if (weight) fd.append('weight', weight);
				if (height) fd.append('height', height);
				if (temperature) fd.append('temperature', temperature);
				const res = await fetch('../../php/supabase/bhw/mark_vaccine_given.php', { method: 'POST', body: fd });
				const data = await res.json();
				if (data.status === 'success') { getChildHealthRecord(); }
				else { alert('Update failed'); }
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
			async function logoutBhw() {
				// const response = await fetch('../../php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('../../php/supabase/bhw/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/login.php'; }
				else { alert('Logout failed: ' + data.message); }
			}
		</script>

<?php include 'Include/footer.php'; ?>