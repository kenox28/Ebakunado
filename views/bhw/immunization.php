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
				body.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
				try {
					const res = await fetch('/ebakunado/php/supabase/bhw/get_immunization_view.php');
					const data = await res.json();
					if (data.status !== 'success') { body.innerHTML = '<tr><td colspan="6">Failed to load records</td></tr>'; return; }
					if (!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="6">No records found</td></tr>'; chrRecords = []; return; }
					chrRecords = data.data;
					renderTable(chrRecords);
					populateVaccineDropdown();
                    // Default date to today and status to upcoming; apply filters on first load
                    const dateInput = document.getElementById('filterDate');
                    if (dateInput && !dateInput.value) { dateInput.value = normalizeDateStr(new Date()); }
                    const statusSelEl = document.getElementById('filterStatus');
                    if (statusSelEl) { statusSelEl.value = 'upcoming'; }
					applyFilters();
				} catch (e) { body.innerHTML = '<tr><td colspan="6">Error loading records</td></tr>'; }
			}

	function openImmunizationForm(btn) {
		const recordId = btn.getAttribute('data-record-id') || '';
		const userId = btn.getAttribute('data-user-id') || '';
		const babyId = btn.getAttribute('data-baby-id') || '';
		const childName = btn.getAttribute('data-child-name') || '';
		const vaccineName = btn.getAttribute('data-vaccine-name') || '';
		const scheduleDate = btn.getAttribute('data-schedule-date') || '';
		const catchUpDate = btn.getAttribute('data-catch-up-date') || '';

		// Get feeding status data
		const feedingData = {
			eb1mo: btn.getAttribute('data-eb-1mo') === 'true',
			eb2mo: btn.getAttribute('data-eb-2mo') === 'true',
			eb3mo: btn.getAttribute('data-eb-3mo') === 'true',
			eb4mo: btn.getAttribute('data-eb-4mo') === 'true',
			eb5mo: btn.getAttribute('data-eb-5mo') === 'true',
			eb6mo: btn.getAttribute('data-eb-6mo') === 'true',
			cf6mo: btn.getAttribute('data-cf-6mo') || '',
			cf7mo: btn.getAttribute('data-cf-7mo') || '',
			cf8mo: btn.getAttribute('data-cf-8mo') || '',
			tdDose1: btn.getAttribute('data-td-dose1') || '',
			tdDose2: btn.getAttribute('data-td-dose2') || '',
			tdDose3: btn.getAttribute('data-td-dose3') || '',
			tdDose4: btn.getAttribute('data-td-dose4') || '',
			tdDose5: btn.getAttribute('data-td-dose5') || ''
		};

		const dateToday = normalizeDateStr(new Date());

		// Function to determine feeding status based on vaccination month
		function getFeedingStatusForVaccine(vaccineName, scheduleDate) {
			// Map vaccines to their typical month ranges
			const vaccineMonths = {
				'BCG': 0, // Birth/1st month
				'HEPAB1': 0, // Within 24 hours or 1st month
				'Pentavalent': [1, 2, 3], // 2nd, 3rd, 4th months
				'OPV': [1, 2, 3], // 2nd, 3rd, 4th months
				'PCV': [1, 2, 3], // 2nd, 3rd, 4th months
				'MCV1': 8, // 9th month
				'MCV2': 14, // 15th month
				'MMR': 11 // 12th month
			};

			let relevantMonth = null;
			if (vaccineName.includes('BCG') || vaccineName.includes('HEPAB1')) {
				relevantMonth = 1; // 1st month
			} else if (vaccineName.includes('Pentavalent') || vaccineName.includes('OPV') || vaccineName.includes('PCV')) {
				// Determine dose number from vaccine name
				if (vaccineName.includes('1st')) relevantMonth = 2;
				else if (vaccineName.includes('2nd')) relevantMonth = 3;
				else if (vaccineName.includes('3rd')) relevantMonth = 4;
				else relevantMonth = 2; // Default to 1st dose
			} else if (vaccineName.includes('MCV1')) {
				relevantMonth = 6; // MCV1 is around 6th month, show 6th month complementary feeding
			} else if (vaccineName.includes('MCV2') || vaccineName.includes('MMR')) {
				relevantMonth = 8; // MCV2/MMR is around 8th month, show 8th month complementary feeding
			}

			if (!relevantMonth) return null;

			// Get feeding status for the relevant month
			if (relevantMonth <= 6) {
				// For months 1-6, show exclusive breastfeeding
				const feedingKey = `eb${relevantMonth}mo`;
				return {
					type: 'exclusive_breastfeeding',
					month: relevantMonth,
					status: feedingData[feedingKey] ? '✓' : '✗',
					text: `${relevantMonth}st month exclusive breastfeeding`
				};
			} else if (relevantMonth >= 6 && relevantMonth <= 8) {
				// For months 6-8, show complementary feeding
				const feedingKey = `cf${relevantMonth}mo`;
				const food = feedingData[feedingKey];
				return {
					type: 'complementary_feeding',
					month: relevantMonth,
					status: food ? food : 'Not recorded',
					text: `${relevantMonth}th month complementary feeding`
				};
			}

			return null;
		}

		// Function to get Mother's TD Status
		function getMotherTDStatus() {
			const tdDoses = [{
					dose: 1,
					date: feedingData.tdDose1
				},
				{
					dose: 2,
					date: feedingData.tdDose2
				},
				{
					dose: 3,
					date: feedingData.tdDose3
				},
				{
					dose: 4,
					date: feedingData.tdDose4
				},
				{
					dose: 5,
					date: feedingData.tdDose5
				}
			];

			const completedDoses = tdDoses.filter(d => d.date && d.date !== '');
			const lastCompletedDose = completedDoses.length > 0 ? completedDoses[completedDoses.length - 1] : null;
			const nextDose = completedDoses.length < 5 ? tdDoses[completedDoses.length] : null;

			return {
				completed: completedDoses.length,
				lastDose: lastCompletedDose,
				nextDose: nextDose,
				allCompleted: completedDoses.length === 5
			};
		}

		const feedingStatus = getFeedingStatusForVaccine(vaccineName, scheduleDate);
		const motherTDStatus = getMotherTDStatus();

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

					${feedingStatus ? `
					<div style="background:#f8f9fa; border: 1px solid #dee2e6; border-radius:4px; padding:8px; margin-bottom:10px;">
						<h4 style="margin:0 0 8px 0; font-size:13px; color:#495057;">Update Feeding Status for ${vaccineName}</h4>
						<div style="display:flex; align-items:center; gap:8px;">
							<span style="font-size:12px; color:#6c757d; font-weight:bold;">${feedingStatus.text}:</span>
							${feedingStatus.type === 'exclusive_breastfeeding' ? `
								<label style="font-size:12px; display:flex; align-items:center; gap:4px;">
									<input type="checkbox" id="update_feeding_status" ${feedingStatus.status === '✓' ? 'checked' : ''} style="margin:0;">
									<span>Currently breastfeeding</span>
								</label>
							` : `
								<input type="text" id="update_complementary_feeding" placeholder="Enter food given" 
									value="${feedingStatus.status !== 'Not recorded' ? feedingStatus.status : ''}" 
									style="padding:4px 6px; font-size:12px; width:200px;">
							`}
						</div>
					</div>` : ''}

					<div style="background:#e8f4f8; border: 1px solid #bee5eb; border-radius:4px; padding:8px; margin-bottom:10px;">
						<h4 style="margin:0 0 8px 0; font-size:13px; color:#0c5460;">Mother's TD (Tetanus-Diphtheria) Status</h4>
						<div style="font-size:12px; color:#0c5460; margin-bottom:8px;">
							<span style="font-weight:bold;">Completed Doses: ${motherTDStatus.completed}/5</span>
							${motherTDStatus.lastDose ? `<span style="margin-left:8px;">Last dose: ${motherTDStatus.lastDose.date}</span>` : ''}
						</div>
                        ${motherTDStatus.allCompleted ? `
                            <div style=\"font-size:12px; color:#28a745; font-weight:bold;\">✓ All TD doses completed</div>
                        ` : `
                            <div style=\"display:flex; align-items:center; gap:8px;\">
                                <input type=\"checkbox\" id=\"update_td_today\" style=\"margin:0;\" />
                                <label for=\"update_td_today\" style=\"font-size:12px; color:#0c5460;\">Record next TD dose today</label>
                            </div>
                        `}
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

					<!-- Growth Assessment Section -->
					<div id="growthAssessmentSection" style="display:none; background:#f0f7ff; border:1px solid #b3d9ff; border-radius:4px; padding:12px; margin-top:12px; margin-bottom:10px;">
						<h4 style="margin:0 0 10px 0; font-size:14px; color:#0056b3; display:flex; align-items:center; gap:6px;">
							<span style="font-size:18px;">📊</span>
							Growth Assessment
						</h4>
						<div id="growthAssessmentContent" style="font-size:12px; color:#333;">
							<p style="margin:0; color:#666;">Enter height and weight to see growth assessment</p>
						</div>
					</div>

					<!-- Dose and Lot fields removed: dose is auto-determined from record, lot/site not in schema -->

                    <div style="display:grid; grid-template-columns: 1fr; gap:10px; margin-top:10px;">
                        <div>
                            <label style="font-size:12px; color:#333;">Administered By</label>
                            <input type="text" id="im_administered_by" placeholder="Name" style="width:100%; padding:6px 8px;" />
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
					<input type="hidden" id="im_child_birth_date" value="" />
					<input type="hidden" id="im_child_gender" value="" />
				`;

		document.getElementById('immunizationFormContainer').innerHTML = html;
		document.getElementById('immunizationOverlay').style.display = 'flex';
		
		// Fetch child details for growth assessment
		fetchChildDetailsForGrowth(babyId);
		
		// Add event listeners for height and weight inputs
		setupGrowthAssessmentListeners();
	}

	function closeImmunizationForm() {
		document.getElementById('immunizationOverlay').style.display = 'none';
		document.getElementById('immunizationFormContainer').innerHTML = '';
	}

	async function submitImmunizationForm() {
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
        const tdTodayCb = document.getElementById('update_td_today');
        if (tdTodayCb && tdTodayCb.checked) {
            formData.append('update_td_dose_date', normalizeDateStr(new Date()));
        }
        // Always mark as taken on save
        formData.append('mark_completed', '1');
        const cu = document.getElementById('im_catch_up_date');
		if (cu && cu.value) formData.append('catch_up_date', cu.value);

        // Add feeding status updates if available
		const feedingCheckbox = document.getElementById('update_feeding_status');
		const feedingInput = document.getElementById('update_complementary_feeding');
		if (feedingCheckbox) {
			formData.append('update_feeding_status', feedingCheckbox.checked ? '1' : '0');
		}
		if (feedingInput) {
			formData.append('update_complementary_feeding', feedingInput.value || '');
		}

		// Add growth assessment data
		const growthWfa = document.getElementById('im_growth_wfa');
		const growthLfa = document.getElementById('im_growth_lfa');
		const growthWfl = document.getElementById('im_growth_wfl');
		const growthAgeMonths = document.getElementById('im_growth_age_months');
		
		if (growthWfa) formData.append('growth_wfa', growthWfa.value || '');
		if (growthLfa) formData.append('growth_lfa', growthLfa.value || '');
		if (growthWfl) formData.append('growth_wfl', growthWfl.value || '');
		if (growthAgeMonths) formData.append('growth_age_months', growthAgeMonths.value || '');

		try {
			const res = await fetch('/ebakunado/php/supabase/shared/save_immunization.php', { method: 'POST', body: formData });
			const responseText = await res.text();
			let data;
			try {
				data = JSON.parse(responseText);
			} catch (parseErr) {
				data = { status: 'error', message: 'Invalid server response' };
			}
			
			if (data.status === 'success') {
				closeImmunizationForm();
				await getChildHealthRecord();
				applyFilters();
				alert('Immunization saved successfully');
			} else {
				alert('Save failed: ' + (data.message || 'Unknown error'));
			}
		} catch(err) {
			alert('Network error saving immunization: ' + err.message);
		}
	}

	function renderTable(records) {
		const body = document.querySelector('#childhealthrecordBody');
		if (!records || records.length === 0) {
			body.innerHTML = '<tr><td colspan="6">No records</td></tr>';
			return;
		}
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
                                    data-catch-up-date="${item.catch_up_date || ''}"
                                    data-eb-1mo="${item.exclusive_breastfeeding_1mo || false}"
                                    data-eb-2mo="${item.exclusive_breastfeeding_2mo || false}"
                                    data-eb-3mo="${item.exclusive_breastfeeding_3mo || false}"
                                    data-eb-4mo="${item.exclusive_breastfeeding_4mo || false}"
                                    data-eb-5mo="${item.exclusive_breastfeeding_5mo || false}"
                                    data-eb-6mo="${item.exclusive_breastfeeding_6mo || false}"
                                    data-cf-6mo="${(item.complementary_feeding_6mo || '').replace(/"/g, '&quot;')}"
                                    data-cf-7mo="${(item.complementary_feeding_7mo || '').replace(/"/g, '&quot;')}"
                                    data-cf-8mo="${(item.complementary_feeding_8mo || '').replace(/"/g, '&quot;')}"
                                    data-td-dose1="${item.mother_td_dose1_date || ''}"
                                    data-td-dose2="${item.mother_td_dose2_date || ''}"
                                    data-td-dose3="${item.mother_td_dose3_date || ''}"
                                    data-td-dose4="${item.mother_td_dose4_date || ''}"
                                    data-td-dose5="${item.mother_td_dose5_date || ''}">
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
				const response = await fetch('/ebakunado/php/supabase/bhw/child_information.php', { method: 'POST', body: formData });
				const data = await response.json();
				if (data.status === 'success') {
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
				}


	}

	function closeChildInformation() {
		document.querySelector('.childinformation-container').style.display = 'none';
		document.querySelector('.table-container').style.display = 'block';
	}

	// Removed text search; filtering is driven by the filter controls only


	function populateVaccineDropdown() {
		const sel = document.getElementById('filterVaccine');
		if (!sel) return;
		const current = sel.value;

		// Get unique vaccines from the loaded data
		const vaccines = [...new Set(chrRecords.map(item => item.vaccine_name).filter(v => v))].sort();

		const options = ['<option value="all">All</option>'].concat(vaccines.map(v => `<option value="${String(v)}">${String(v)}</option>`));
		sel.innerHTML = options.join('');
		if (Array.from(sel.options).some(o => o.value === current)) sel.value = current;
	}

	function normalizeDateStr(d) {
		const pad = n => (n < 10 ? '0' : '') + n;
		return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
	}

	function applyFilters() {
		if (!chrRecords || chrRecords.length === 0) {
			renderTable([]);
			return;
		}
		const dateSel = (document.getElementById('filterDate').value || '').trim();
		const statusSel = document.getElementById('filterStatus').value;
		const vaccineSel = document.getElementById('filterVaccine').value;
		const purokQ = (document.getElementById('filterPurok').value || '').trim().toLowerCase();

		const today = new Date();
		const todayStr = normalizeDateStr(today);

		const filtered = chrRecords.filter(item => {
			// Purok filter
			if (purokQ) {
				const addr = String(item.address || '').toLowerCase();
				if (!addr.includes(purokQ)) return false;
			}

			// Vaccine filter
			if (vaccineSel !== 'all') {
				const vaccine = String(item.vaccine_name || '');
				if (vaccine !== vaccineSel) return false;
			}

			// Date filter
			if (dateSel !== '') {
				const scheduleDate = item.schedule_date || '';
				const catchUpDate = item.catch_up_date || '';
				if (scheduleDate !== dateSel && catchUpDate !== dateSel) return false;
			}

			// Status filter
			if (statusSel !== 'all') {
				const status = String(item.status || '');
				if (statusSel === 'upcoming') {
					const dueDate = item.catch_up_date || item.schedule_date || '';
					if (dueDate === '' || dueDate < todayStr || status === 'taken') return false;
				} else if (statusSel === 'missed') {
					const dueDate = item.catch_up_date || item.schedule_date || '';
					if (dueDate === '' || dueDate >= todayStr || status === 'taken') return false;
				} else if (statusSel === 'completed') {
					if (status !== 'taken') return false;
				}
			}

			return true;
		});

		renderTable(filtered);
	}

	function clearFilters() {
		document.getElementById('filterDate').value = normalizeDateStr(new Date());
		document.getElementById('filterStatus').value = 'upcoming';
		document.getElementById('filterVaccine').value = 'all';
		document.getElementById('filterPurok').value = '';
		renderTable(chrRecords);
		applyFilters();
	}

	function viewChrImage(urlEnc) {
		const url = decodeURIComponent(urlEnc);
		document.querySelector('#overlayImage').src = url;
		document.querySelector('#openInNewTab').href = url;
		document.querySelector('#imageOverlay').style.display = 'flex';
	}

	function hideOverlay() {
		document.querySelector('#imageOverlay').style.display = 'none';
		document.querySelector('#overlayImage').src = '';
	}

	function closeOverlay(e) {
		if (e.target && e.target.id === 'imageOverlay') {
			hideOverlay();
		}
	}

			async function acceptRecord(baby_id){
				const formData = new FormData(); formData.append('baby_id', baby_id);
				// const response = await fetch('/ebakunado/php/bhw/accept_chr.php', { method: 'POST', body: formData });
				const response = await fetch('/ebakunado/php/supabase/bhw/accept_chr.php', { method: 'POST', body: formData });
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
	window.addEventListener('DOMContentLoaded', function() {
		const applyBtn = document.getElementById('applyFiltersBtn');
		const clearBtn = document.getElementById('clearFiltersBtn');
		if (applyBtn) applyBtn.addEventListener('click', applyFilters);
		if (clearBtn) clearBtn.addEventListener('click', clearFilters);
	});

			// Removed QR scanner functionality and dependencies
			async function logoutBhw() {
				// const response = await fetch('/ebakunado/php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('/ebakunado/php/supabase/bhw/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/auth/login.php'; }
				else { alert('Logout failed: ' + data.message); }
			}

			/**
			 * Fetch child details for growth assessment
			 */
			async function fetchChildDetailsForGrowth(babyId) {
				if (!babyId) return;

				try {
					const formData = new FormData();
					formData.append('baby_id', babyId);
					const response = await fetch('/ebakunado/php/supabase/bhw/get_child_details.php', {
						method: 'POST',
						body: formData
					});
					const data = await response.json();
					
					if (data.status === 'success' && data.data && data.data.length > 0) {
						const child = data.data[0];
						const birthDateInput = document.getElementById('im_child_birth_date');
						const genderInput = document.getElementById('im_child_gender');
						
						if (birthDateInput) birthDateInput.value = child.child_birth_date || '';
						if (genderInput) genderInput.value = child.child_gender || '';
					}
				} catch (error) {
					console.error('Error fetching child details for growth assessment:', error);
				}
			}

			/**
			 * Setup event listeners for growth assessment
			 */
			function setupGrowthAssessmentListeners() {
				const heightInput = document.getElementById('im_height');
				const weightInput = document.getElementById('im_weight');

				if (heightInput && weightInput) {
					// Debounce function to avoid too many calculations
					let assessmentTimeout;
					const calculateGrowth = () => {
						clearTimeout(assessmentTimeout);
						assessmentTimeout = setTimeout(() => {
							performGrowthAssessment();
						}, 500); // Wait 500ms after user stops typing
					};

					heightInput.addEventListener('input', calculateGrowth);
					weightInput.addEventListener('input', calculateGrowth);
				}
			}

			/**
			 * Perform growth assessment
			 */
			async function performGrowthAssessment() {
				const birthDate = document.getElementById('im_child_birth_date')?.value;
				const gender = document.getElementById('im_child_gender')?.value;
				const weight = parseFloat(document.getElementById('im_weight')?.value);
				const height = parseFloat(document.getElementById('im_height')?.value);
				const assessmentSection = document.getElementById('growthAssessmentSection');
				const assessmentContent = document.getElementById('growthAssessmentContent');

				if (!birthDate || !gender) {
					// Child details not loaded yet
					if (assessmentSection) assessmentSection.style.display = 'none';
					return;
				}

				if (!weight && !height) {
					if (assessmentSection) assessmentSection.style.display = 'none';
					return;
				}

				try {
					await growthCalculator.loadStandards();
					const assessment = await growthCalculator.assessGrowth(birthDate, gender, weight, height);

					if (assessment.error) {
						if (assessmentContent) {
							assessmentContent.innerHTML = `<p style="margin:0; color:#dc3545;">${assessment.error}</p>`;
						}
						if (assessmentSection) assessmentSection.style.display = 'block';
						return;
					}

					// Display assessment results
					let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
					
					if (assessment.ageMonths !== null && assessment.ageMonths !== undefined) {
						html += `<div style="font-weight:bold; color:#0056b3; margin-bottom:4px;">Age: ${assessment.ageMonths} months</div>`;
					}

					// Weight-for-Age
					if (assessment.weightForAge) {
						const wfa = assessment.weightForAge;
						const colorMap = { 'normal': '#28a745', 'underweight': '#ffc107', 'severely_underweight': '#dc3545' };
						const bgColor = colorMap[wfa.status] || '#6c757d';
						html += `
							<div style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; background:${bgColor}15; border-left:3px solid ${bgColor}; border-radius:3px;">
								<span style="font-weight:bold;">Weight-for-Age:</span>
								<span style="color:${bgColor}; font-weight:bold;">${wfa.icon} ${wfa.label}</span>
							</div>
						`;
					}

					// Length-for-Age (only for 0-11 months)
					if (assessment.lengthForAge) {
						const lfa = assessment.lengthForAge;
						const colorMap = { 'normal': '#28a745', 'stunted': '#ffc107', 'severely_stunted': '#dc3545', 'tall': '#17a2b8' };
						const bgColor = colorMap[lfa.status] || '#6c757d';
						html += `
							<div style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; background:${bgColor}15; border-left:3px solid ${bgColor}; border-radius:3px;">
								<span style="font-weight:bold;">Length-for-Age:</span>
								<span style="color:${bgColor}; font-weight:bold;">${lfa.icon} ${lfa.label}</span>
							</div>
						`;
					}

					// Weight-for-Length
					if (assessment.weightForLength) {
						const wfl = assessment.weightForLength;
						const colorMap = { 'normal': '#28a745', 'sam': '#dc3545', 'mam': '#ffc107', 'overweight': '#ff9800', 'obese': '#f44336' };
						const bgColor = colorMap[wfl.status] || '#6c757d';
						html += `
							<div style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; background:${bgColor}15; border-left:3px solid ${bgColor}; border-radius:3px;">
								<span style="font-weight:bold;">Weight-for-Length:</span>
								<span style="color:${bgColor}; font-weight:bold;">${wfl.icon} ${wfl.label}</span>
							</div>
						`;
					}

					html += '</div>';

					if (assessmentContent) {
						assessmentContent.innerHTML = html;
					}
					if (assessmentSection) {
						assessmentSection.style.display = 'block';
					}

					// Store assessment data in hidden fields for saving
					storeGrowthAssessmentData(assessment);

				} catch (error) {
					console.error('Error performing growth assessment:', error);
					if (assessmentContent) {
						assessmentContent.innerHTML = '<p style="margin:0; color:#dc3545;">Error calculating growth assessment</p>';
					}
					if (assessmentSection) assessmentSection.style.display = 'block';
				}
			}

			/**
			 * Store growth assessment data in hidden fields
			 */
			function storeGrowthAssessmentData(assessment) {
				// Create or update hidden fields for growth assessment
				let container = document.getElementById('immunizationFormContainer');
				if (!container) return;

				// Remove existing growth assessment hidden fields
				const existingFields = container.querySelectorAll('[id^="im_growth_"]');
				existingFields.forEach(field => field.remove());

				// Add new hidden fields
				if (assessment.weightForAge) {
					const wfaField = document.createElement('input');
					wfaField.type = 'hidden';
					wfaField.id = 'im_growth_wfa';
					wfaField.value = assessment.weightForAge.status;
					container.appendChild(wfaField);
				}

				if (assessment.lengthForAge) {
					const lfaField = document.createElement('input');
					lfaField.type = 'hidden';
					lfaField.id = 'im_growth_lfa';
					lfaField.value = assessment.lengthForAge.status;
					container.appendChild(lfaField);
				}

				if (assessment.weightForLength) {
					const wflField = document.createElement('input');
					wflField.type = 'hidden';
					wflField.id = 'im_growth_wfl';
					wflField.value = assessment.weightForLength.status;
					container.appendChild(wflField);
				}

				// Store age in months
				if (assessment.ageMonths !== null && assessment.ageMonths !== undefined) {
					const ageField = document.createElement('input');
					ageField.type = 'hidden';
					ageField.id = 'im_growth_age_months';
					ageField.value = assessment.ageMonths;
					container.appendChild(ageField);
				}
			}
		</script>
		<script src="/ebakunado/js/growth-standards/who-growth-calculator.js"></script>

<?php include 'Include/footer.php'; ?>