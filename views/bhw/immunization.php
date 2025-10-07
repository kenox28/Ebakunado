<?php include 'Include/header.php'; ?>
<style>

</style>


		<div class="filters" style="margin:10px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
			<label style="font-size:12px; color:#333;">Date:
				<input id="filterDate" type="date" style="padding:4px 6px;">
			</label>
			<label style="font-size:12px; color:#333;">Status:
				<select id="filterStatus" style="padding:4px 6px;">
					<option value="all">All</option>
					<option value="upcoming">Upcoming</option>
					<option value="missed">Missed</option>
					<option value="completed">Completed</option>
				</select>
			</label>
			<label style="font-size:12px; color:#333;">Vaccine:
				<select id="filterVaccine" style="padding:4px 6px; min-width:160px;">
					<option value="all">All</option>
				</select>
			</label>
			<label style="font-size:12px; color:#333;">Purok:
				<input id="filterPurok" type="text" placeholder="e.g. Purok 1" style="padding:4px 6px;">
			</label>
			<button id="applyFiltersBtn" style="padding:4px 10px;">Apply</button>
			<button id="clearFiltersBtn" style="padding:4px 10px;">Clear</button>
		</div>
    <div class="table-container">
        <table class="table table-hover" id="childhealthrecord">
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Address</th>
                                <th>Vaccine</th>
                                <th>Schedule Date</th>
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
	<!-- Immunization Record Overlay -->
	<div id="immunizationOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:none; align-items:center; justify-content:center; z-index:9999;">
		<div style="background:#fff; padding:12px; width: 720px; max-width: 95vw; max-height: 90vh; overflow:auto; border-radius:6px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
				<h3 style="margin:0; font-size:16px;">Record Immunization</h3>
				<button onclick="closeImmunizationForm()" style="padding:4px 8px;">Close</button>
			</div>
			<div id="immunizationFormContainer"></div>
		</div>
	</div>
		<script>
			let chrRecords = [];

			async function getChildHealthRecord() {
				const body = document.querySelector('#childhealthrecordBody');
				body.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
				try {
					const res = await fetch('../../php/supabase/bhw/get_immunization_view.php');
					const data = await res.json();
					if (data.status !== 'success') { body.innerHTML = '<tr><td colspan="4">Failed to load records</td></tr>'; return; }
					if (!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="4">No records found</td></tr>'; chrRecords = []; return; }
					chrRecords = data.data;
					renderTable(chrRecords);
					populateVaccineDropdown();
                    // Default date to today and status to upcoming; apply filters on first load
                    const dateInput = document.getElementById('filterDate');
                    if (dateInput && !dateInput.value) { dateInput.value = normalizeDateStr(new Date()); }
                    const statusSelEl = document.getElementById('filterStatus');
                    if (statusSelEl) { statusSelEl.value = 'upcoming'; }
					applyFilters();
				} catch (e) { body.innerHTML = '<tr><td colspan="4">Error loading records</td></tr>'; }
			}

			function openImmunizationForm(btn){
                const recordId = btn.getAttribute('data-record-id') || '';
				const userId = btn.getAttribute('data-user-id') || '';
				const babyId = btn.getAttribute('data-baby-id') || '';
				const childName = btn.getAttribute('data-child-name') || '';
				const vaccineName = btn.getAttribute('data-vaccine-name') || '';
                const scheduleDate = btn.getAttribute('data-schedule-date') || '';
                const catchUpDate = btn.getAttribute('data-catch-up-date') || '';

				const dateToday = normalizeDateStr(new Date());

				const html = `
					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
						<div>
							<label style="font-size:12px; color:#333;">Child Name</label>
							<input type="text" id="im_child_name" value="${childName}" readonly style="width:100%; padding:6px 8px;" />
						</div>
						<div>
							<label style="font-size:12px; color:#333;">Vaccine</label>
							<input type="text" id="im_vaccine_name" value="${vaccineName}" readonly style="width:100%; padding:6px 8px;" />
						</div>
                        <div>
							<label style="font-size:12px; color:#333;">Scheduled Date</label>
							<input type="date" id="im_schedule_date" value="${scheduleDate}" readonly style="width:100%; padding:6px 8px;" />
						</div>
                        ${catchUpDate ? `
                        <div>
                            <label style=\"font-size:12px; color:#333;\">Catch-up Date</label>
                            <input type=\"date\" id=\"im_catch_up_date\" value=\"${catchUpDate}\" readonly style=\"width:100%; padding:6px 8px;\" />
                        </div>` : ''}
						<div>
							<label style="font-size:12px; color:#333;">Date Taken</label>
							<input type="date" id="im_date_taken" value="${dateToday}" style="width:100%; padding:6px 8px;" />
						</div>
					</div>

					<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;">
						<div>
							<label style="font-size:12px; color:#333;">Temperature (°C)</label>
							<input type="number" step="0.1" id="im_temperature" placeholder="e.g. 36.8" style="width:100%; padding:6px 8px;" />
						</div>
						<div>
							<label style="font-size:12px; color:#333;">Height (cm)</label>
							<input type="number" step="0.1" id="im_height" placeholder="e.g. 60" style="width:100%; padding:6px 8px;" />
						</div>
						<div>
							<label style="font-size:12px; color:#333;">Weight (kg)</label>
							<input type="number" step="0.01" id="im_weight" placeholder="e.g. 6.5" style="width:100%; padding:6px 8px;" />
						</div>
					</div>

					<!-- Dose and Lot fields removed: dose is auto-determined from record, lot/site not in schema -->

					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
						<div>
							<label style="font-size:12px; color:#333;">Administered By</label>
							<input type="text" id="im_administered_by" placeholder="Name" style="width:100%; padding:6px 8px;" />
						</div>
						<div style="display:flex; align-items:flex-end; gap:8px;">
                            <input type="checkbox" id="im_mark_completed" />
                            <label for="im_mark_completed" style="font-size:12px; color:#333;">Mark as Taken</label>
						</div>
					</div>

					<div style="margin-top:10px;">
						<label style="font-size:12px; color:#333;">Remarks</label>
						<textarea id="im_remarks" rows="3" style="width:100%; padding:6px 8px;"></textarea>
					</div>

					<div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
						<button onclick="closeImmunizationForm()" style="padding:6px 12px;">Cancel</button>
						<button onclick="submitImmunizationForm()" style="padding:6px 12px; background:#007bff; color:#fff; border:none;">Save</button>
					</div>

					<input type="hidden" id="im_record_id" value="${recordId}" />
					<input type="hidden" id="im_user_id" value="${userId}" />
					<input type="hidden" id="im_baby_id" value="${babyId}" />
				`;

				document.getElementById('immunizationFormContainer').innerHTML = html;
				document.getElementById('immunizationOverlay').style.display = 'flex';
			}

			function closeImmunizationForm(){
				document.getElementById('immunizationOverlay').style.display = 'none';
				document.getElementById('immunizationFormContainer').innerHTML = '';
			}

			async function submitImmunizationForm(){
				const formData = new FormData();
				formData.append('record_id', document.getElementById('im_record_id').value || '');
				formData.append('user_id', document.getElementById('im_user_id').value || '');
				formData.append('baby_id', document.getElementById('im_baby_id').value || '');
				formData.append('vaccine_name', document.getElementById('im_vaccine_name').value || '');
				formData.append('schedule_date', document.getElementById('im_schedule_date').value || '');
                formData.append('date_taken', document.getElementById('im_date_taken').value || '');
				formData.append('temperature', document.getElementById('im_temperature').value || '');
				formData.append('height_cm', document.getElementById('im_height').value || '');
				formData.append('weight_kg', document.getElementById('im_weight').value || '');
				// dose_number, lot_number, site removed - dose inferred from existing record
				formData.append('administered_by', document.getElementById('im_administered_by').value || '');
				formData.append('remarks', document.getElementById('im_remarks').value || '');
				formData.append('mark_completed', document.getElementById('im_mark_completed').checked ? '1' : '0');
                const cu = document.getElementById('im_catch_up_date');
                if (cu && cu.value) formData.append('catch_up_date', cu.value);

				try{
					const res = await fetch('../../php/supabase/bhw/save_immunization.php', { method: 'POST', body: formData });
					const data = await res.json().catch(() => ({ status: 'error', message: 'Invalid server response' }));
					if (data.status === 'success'){
						closeImmunizationForm();
						await getChildHealthRecord();
						applyFilters();
						alert('Immunization saved successfully');
					}else{
						alert('Save failed: ' + (data.message || 'Unknown error'));
					}
				}catch(err){
					alert('Network error saving immunization');
					console.error('save_immunization error:', err);
				}
			}

			function renderTable(records){
				const body = document.querySelector('#childhealthrecordBody');
                if (!records || records.length === 0){ body.innerHTML = '<tr><td colspan="6">No records</td></tr>'; return; }
					let rows = '';
				records.forEach(item => {
						rows += `<tr>
							<td hidden>${item.id || ''}</td>
							<td hidden>${item.user_id || ''}</td>
							<td hidden>${item.baby_id || ''}</td>
						<td>${(item.child_fname || '') + ' ' + (item.child_lname || '')}</td>
							<td>${item.address || ''}</td>
						<td>${item.vaccine_name || ''}</td>
						<td>${item.schedule_date || ''}</td>
                        <td>${item.status === 'taken' && item.date_given ? ('taken (' + item.date_given + ')') : (item.status || '')}</td>
							<td>
								<button style="padding:4px 8px;" onclick="openImmunizationForm(this)"
                                    data-record-id="${item.immunization_id || ''}"
									data-user-id="${item.user_id || ''}"
									data-baby-id="${item.baby_id || ''}"
									data-child-name="${((item.child_fname || '') + ' ' + (item.child_lname || '')).replace(/"/g, '&quot;')}"
									data-vaccine-name="${String(item.vaccine_name || '').replace(/"/g, '&quot;')}"
                                    data-schedule-date="${item.schedule_date || ''}"
                                    data-catch-up-date="${item.catch_up_date || ''}">
									Record
								</button>
							</td>
						</tr>`;
					});
					body.innerHTML = rows;
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

			// Removed text search; filtering is driven by the filter controls only


			function populateVaccineDropdown(){
				const sel = document.getElementById('filterVaccine'); if(!sel) return;
				const current = sel.value;
				
				// Get unique vaccines from the loaded data
				const vaccines = [...new Set(chrRecords.map(item => item.vaccine_name).filter(v => v))].sort();
				
				const options = ['<option value="all">All</option>'].concat(vaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));
				sel.innerHTML = options.join('');
				if (Array.from(sel.options).some(o => o.value === current)) sel.value = current;
			}

			function normalizeDateStr(d){
				const pad = n => (n<10?'0':'')+n;
				return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
			}

			function applyFilters(){
				if (!chrRecords || chrRecords.length === 0){ renderTable([]); return; }
				const dateSel = (document.getElementById('filterDate').value || '').trim();
				const statusSel = document.getElementById('filterStatus').value;
				const vaccineSel = document.getElementById('filterVaccine').value;
				const purokQ = (document.getElementById('filterPurok').value || '').trim().toLowerCase();

				const today = new Date();
				const todayStr = normalizeDateStr(today);

				const filtered = chrRecords.filter(item => {
					// Purok filter
					if (purokQ){
						const addr = String(item.address || '').toLowerCase();
						if (!addr.includes(purokQ)) return false;
					}

					// Vaccine filter
					if (vaccineSel !== 'all'){
						const vaccine = String(item.vaccine_name || '');
						if (vaccine !== vaccineSel) return false;
					}

					// Date filter
					if (dateSel !== ''){
						const scheduleDate = item.schedule_date || '';
						const catchUpDate = item.catch_up_date || '';
						if (scheduleDate !== dateSel && catchUpDate !== dateSel) return false;
					}

					// Status filter
					if (statusSel !== 'all'){
						const status = String(item.status || '');
						if (statusSel === 'upcoming'){
							const dueDate = item.catch_up_date || item.schedule_date || '';
                            if (dueDate === '' || dueDate < todayStr || status === 'taken') return false;
						} else if (statusSel === 'missed'){
							const dueDate = item.catch_up_date || item.schedule_date || '';
                            if (dueDate === '' || dueDate >= todayStr || status === 'taken') return false;
						} else if (statusSel === 'completed'){
                            if (status !== 'taken') return false;
						}
					}

					return true;
				});

				renderTable(filtered);
			}

			function clearFilters(){
				document.getElementById('filterDate').value = normalizeDateStr(new Date());
                document.getElementById('filterStatus').value = 'upcoming';
				document.getElementById('filterVaccine').value = 'all';
				document.getElementById('filterPurok').value = '';
				renderTable(chrRecords);
				applyFilters();
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




			window.addEventListener('DOMContentLoaded', getChildHealthRecord);
			window.addEventListener('DOMContentLoaded', function(){
				const applyBtn = document.getElementById('applyFiltersBtn');
				const clearBtn = document.getElementById('clearFiltersBtn');
				if (applyBtn) applyBtn.addEventListener('click', applyFilters);
				if (clearBtn) clearBtn.addEventListener('click', clearFilters);
			});

			// Removed QR scanner functionality and dependencies
			async function logoutBhw() {
				// const response = await fetch('../../php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('../../php/supabase/bhw/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/auth/login.php'; }
				else { alert('Logout failed: ' + data.message); }
			}
		</script>

<?php include 'Include/footer.php'; ?>