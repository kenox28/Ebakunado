<?php include 'Include/header.php'; ?>
<style>
	/* Bond-paper style container */
	.chr-paper {
		width: 13.5in;
		max-width: 100%;
		box-sizing: border-box;
		min-height: 13in;
		margin: 0 auto;
		background: #fff;
		border: 1px solid #e0e0e0;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
		padding: 0.6in;
		font-size: 18px;
		line-height: 1.7;
	}

	.block-header {
		text-align: center;
		font-weight: 700;
		font-size: 16px;
		margin: 0 0 8px 0;
	}

	.boxed {
		border: 1px solid #000 !important;
		padding: 8px;
	}

	@media print {
		body {
			background: #fff;
		}

		.chr-paper {
			box-shadow: none;
			border: 1px solid #000;
		}

		.content {
			padding: 0;
		}
	}
</style>

<div class="content" style="padding: 24px 16px;">
	<div id="chrRoot">
		<div class="chr-paper">
			<div style="text-align:center; margin-bottom: 12px;">
				<h2 style="margin:0;">CHILD HEALTH RECORD</h2>
				<p style="margin:4px 0;">City Health Department, Ormoc City</p>
			</div>

			<!-- Child Profile Header -->
			<div id="childProfile" style="font-size: inherit; line-height: 1.6; margin-bottom: 12px;">
				<div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
					<div>
						<div>Name of Child: <span id="f_name"></span></div>
						<div>Gender: <span id="f_gender"></span></div>
						<div>Date of Birth: <span id="f_birth_date"></span></div>
						<div>Place of Birth: <span id="f_birth_place"></span></div>
						<div>Birth Weight: <span id="f_birth_weight"></span></div>
						<div>Birth Length: <span id="f_birth_height"></span></div>
						<div>Address: <span id="f_address"></span></div>
						<div>Allergies: <span id="f_allergies"></span></div>
						<div>Blood Type: <span id="f_blood_type"></span></div>
					</div>
					<div>
						<div>Family Number: <span id="f_family_no"></span></div>
						<div>LMP: <span id="f_lpm"></span></div>
						<div>PhilHealth No.: <span id="f_philhealth"></span></div>
						<div>NHTS: <span id="f_nhts"></span></div>
						<div>Non-NHTS: <span id="f_non_nhts"></span></div>
						<div>Father's Name: <span id="f_father"></span></div>
						<div>Mother's Name: <span id="f_mother"></span></div>
						<div>NB Screening: <span id="f_nb_screen"></span></div>
						<div>Family Planning: <span id="f_fp"></span></div>
					</div>
				</div>
			</div>

			<!-- Child History -->
			<div id="childHistory" style="font-size: inherit; line-height: 1.6; margin: 8px 0 16px 0;">
				<div class="block-header">CHILD HISTORY</div>
				<div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
					<div>
						<div>Date of Newborn Screening: <span id="f_nbs_date"></span></div>
						<div>Type of Delivery: <span id="f_delivery_type"></span></div>
						<div>Birth Order: <span id="f_birth_order"></span></div>
					</div>
					<div>
						<div>Place of Newborn Screening: <span id="f_nbs_place"></span></div>
						<div>Attended by: <span id="f_attended_by"></span></div>
					</div>
				</div>
			</div>

			<!-- Exclusive Breastfeeding & Complementary Feeding -->
			<div id="feedingSection" class="boxed" style="font-size: inherit; line-height: 1.6; margin: 8px 0 16px 0; padding: 8px;">
				<h3 style="margin: 0 0 8px 0; font-size: 18px; text-align: center;">Exclusive Breastfeeding & Complementary Feeding</h3>
				<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
					<div>
						<h4 style="margin: 0 0 4px 0; font-size: 16px;">Exclusive Breastfeeding:</h4>
						<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:4px; font-size: 16px;">
							<div>1st mo: <span id="f_eb_1mo"></span></div>
							<div>2nd mo: <span id="f_eb_2mo"></span></div>
							<div>3rd mo: <span id="f_eb_3mo"></span></div>
							<div>4th mo: <span id="f_eb_4mo"></span></div>
							<div>5th mo: <span id="f_eb_5mo"></span></div>
							<div>6th mo: <span id="f_eb_6mo"></span></div>
						</div>
					</div>
					<div>
						<h4 style="margin: 0 0 4px 0; font-size: 16px;">Complementary Feeding:</h4>
						<div style="font-size: 16px;">
							<div>6th mo food: <span id="f_cf_6mo"></span></div>
							<div>7th mo food: <span id="f_cf_7mo"></span></div>
							<div>8th mo food: <span id="f_cf_8mo"></span></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Mother's TD Status -->
			<div id="tdSection" class="boxed" style="font-size: inherit; line-height: 1.6; margin: 8px 0 16px 0; padding: 8px;">
				<h3 style="margin: 0 0 8px 0; font-size: 18px; text-align: center;">Mother's TD (Tetanus-Diphtheria) Status</h3>
				<div style="display:grid; grid-template-columns: repeat(5, 1fr); gap:8px; font-size: 16px;">
					<div>TD 1st dose: <span id="f_td_dose1"></span></div>
					<div>TD 2nd dose: <span id="f_td_dose2"></span></div>
					<div>TD 3rd dose: <span id="f_td_dose3"></span></div>
					<div>TD 4th dose: <span id="f_td_dose4"></span></div>
					<div>TD 5th dose: <span id="f_td_dose5"></span></div>
				</div>
			</div>

			<!-- Compact Ledger: One row per taken vaccine -->
			<div>
				<table style="width:100%; border-collapse:collapse; font-size:16px;" border="1">
					<thead>
						<tr>
							<th style="padding:4px;">Date</th>
							<th style="padding:4px;">Purpose</th>
							<th style="padding:4px;">HT</th>
							<th style="padding:4px;">WT</th>
							<th style="padding:4px;">ME/AC</th>
							<th style="padding:4px;">STATUS</th>
							<th style="padding:4px;">Condition of Baby</th>
							<th style="padding:4px;">Advice Given</th>
							<th style="padding:4px;">Next Sched Date</th>
							<th style="padding:4px;">Remarks</th>
						</tr>
					</thead>
					<tbody id="ledgerBody">
						<tr>
							<td colspan="10" style="text-align:center; padding:10px;">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div style="margin-top:12px; text-align:right;">
				<button id="reqTransferBtn" style="padding:6px 12px;">Request Transfer Copy</button>
				<button id="reqSchoolBtn" style="padding:6px 12px; margin-left:8px;">Request School Copy</button>
			</div>
		</div>
	</div>
</div>

<script>
	function formatDate(dateString) {
		if (!dateString) return '';
		const d = new Date(dateString);
		if (Number.isNaN(d.getTime())) return String(dateString);
		// Example wanted: 10/7/25 (no leading zeros, 2-digit year)
		return d.toLocaleDateString('en-US', {
			month: 'numeric',
			day: 'numeric',
			year: '2-digit'
		});
	}

	function normalizeDateStr(d) {
		const pad = n => (n < 10 ? '0' : '') + n;
		return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
	}

	function getDoseText(n) {
		const map = {
			1: '1st dose',
			2: '2nd dose',
			3: '3rd dose',
			4: '4th dose'
		};
		return map[n] || `Dose ${n||''}`;
	}

	document.addEventListener('DOMContentLoaded', async function() {
		const params = new URLSearchParams(window.location.search);
		const babyId = params.get('baby_id') || '';
		if (!babyId) {
			alert('Missing baby_id');
			return;
		}
		// No back/print/download elements now

		try {
			// Fetch child details
			const fd = new FormData();
			fd.append('baby_id', babyId);
			const childRes = await fetch('/ebakunado/php/supabase/users/get_child_details.php', {
				method: 'POST',
				body: fd
			});
			const childJson = await childRes.json();
			const child = (childJson && childJson.status === 'success' && childJson.data && childJson.data[0]) ? childJson.data[0] : {};

			// Fill header (blanks when missing)
			document.getElementById('f_name').textContent = child.name || [(child.child_fname || ''), (child.child_lname || '')].filter(Boolean).join(' ');
			document.getElementById('f_gender').textContent = child.child_gender || child.gender || '';
			document.getElementById('f_birth_date').textContent = child.child_birth_date || '';
			document.getElementById('f_birth_place').textContent = child.place_of_birth || '';
			document.getElementById('f_birth_weight').textContent = child.birth_weight || '';
			document.getElementById('f_birth_height').textContent = child.birth_height || '';
			document.getElementById('f_address').textContent = child.address || '';
			document.getElementById('f_allergies').textContent = child.allergies || '';
			document.getElementById('f_lpm').textContent = child.lpm || '';
			document.getElementById('f_blood_type').textContent = child.blood_type || '';
			document.getElementById('f_family_no').textContent = child.family_number || '';
			document.getElementById('f_philhealth').textContent = child.philhealth_no || '';
			document.getElementById('f_nhts').textContent = child.nhts || '';
			document.getElementById('f_non_nhts').textContent = '';
			document.getElementById('f_father').textContent = child.father_name || '';
			document.getElementById('f_mother').textContent = child.mother_name || '';
			document.getElementById('f_nb_screen').textContent = '';
			document.getElementById('f_fp').textContent = child.family_planning || '';
			document.getElementById('f_nbs_date').textContent = '';
			document.getElementById('f_delivery_type').textContent = child.delivery_type || '';
			document.getElementById('f_birth_order').textContent = child.birth_order || '';
			document.getElementById('f_nbs_place').textContent = '';
			document.getElementById('f_attended_by').textContent = child.birth_attendant || '';

			// Fill Exclusive Breastfeeding data
			document.getElementById('f_eb_1mo').textContent = child.exclusive_breastfeeding_1mo ? '✓' : '';
			document.getElementById('f_eb_2mo').textContent = child.exclusive_breastfeeding_2mo ? '✓' : '';
			document.getElementById('f_eb_3mo').textContent = child.exclusive_breastfeeding_3mo ? '✓' : '';
			document.getElementById('f_eb_4mo').textContent = child.exclusive_breastfeeding_4mo ? '✓' : '';
			document.getElementById('f_eb_5mo').textContent = child.exclusive_breastfeeding_5mo ? '✓' : '';
			document.getElementById('f_eb_6mo').textContent = child.exclusive_breastfeeding_6mo ? '✓' : '';

			// Fill Complementary Feeding data
			document.getElementById('f_cf_6mo').textContent = child.complementary_feeding_6mo || '';
			document.getElementById('f_cf_7mo').textContent = child.complementary_feeding_7mo || '';
			document.getElementById('f_cf_8mo').textContent = child.complementary_feeding_8mo || '';

			// Fill Mother's TD Status data
			document.getElementById('f_td_dose1').textContent = formatDate(child.mother_td_dose1_date) || '';
			document.getElementById('f_td_dose2').textContent = formatDate(child.mother_td_dose2_date) || '';
			document.getElementById('f_td_dose3').textContent = formatDate(child.mother_td_dose3_date) || '';
			document.getElementById('f_td_dose4').textContent = formatDate(child.mother_td_dose4_date) || '';
			document.getElementById('f_td_dose5').textContent = formatDate(child.mother_td_dose5_date) || '';

			// Wire two request buttons (transfer, school)
			const reqTransferBtn = document.getElementById('reqTransferBtn');
			const reqSchoolBtn = document.getElementById('reqSchoolBtn');

			async function sendRequest(type) {
				try {
					const fd2 = new FormData();
					fd2.append('baby_id', babyId);
					fd2.append('request_type', type);
					const res2 = await fetch('/ebakunado/php/supabase/users/request_chr_doc.php', {
						method: 'POST',
						body: fd2
					});
					const j = await res2.json();
					if (j.status === 'success') {
						if (type === 'transfer') {
							reqTransferBtn.textContent = 'Transfer: Requested (pendingCHR)';
						}
						if (type === 'school') {
							reqSchoolBtn.textContent = 'School: Requested (pendingCHR)';
						}
					} else {
						alert('Request failed: ' + (j.message || 'Unknown error'));
					}
				} catch (e) {
					alert('Network error requesting CHR');
				}
			}

			if (reqTransferBtn) reqTransferBtn.onclick = () => sendRequest('transfer');
			if (reqSchoolBtn) reqSchoolBtn.onclick = () => sendRequest('school');

			// Poll statuses independently
			await refreshChrDocStatus('transfer');
			await refreshChrDocStatus('school');
			setInterval(() => refreshChrDocStatus('transfer'), 5000);
			setInterval(() => refreshChrDocStatus('school'), 5000);

			async function refreshChrDocStatus(type) {
				try {
					const res = await fetch(`/ebakunado/php/supabase/users/get_chr_doc_status.php?baby_id=${encodeURIComponent(babyId)}&request_type=${encodeURIComponent(type)}`);
					const j = await res.json();
					if (j.status === 'success' && j.data) {
						const st = j.data.status || '';
						const url = j.data.doc_url || '';
						const hasNewerRecords = j.data.has_newer_records || false;

						if (type === 'transfer') {
							if (st === 'approved' && url && !hasNewerRecords) {
								reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: Approved — see Approved Requests');
							} else if (st === 'approved' && url && hasNewerRecords) {
								reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: New Records Available - Request Updated Copy');
							} else if (st === 'pendingCHR') {
								reqTransferBtn && (reqTransferBtn.textContent = 'Transfer: Requested (pendingCHR)');
							} else {
								reqTransferBtn && (reqTransferBtn.textContent = 'Request Transfer Copy');
							}
						}
						if (type === 'school') {
							if (st === 'approved' && url && !hasNewerRecords) {
								reqSchoolBtn && (reqSchoolBtn.textContent = 'School: Approved — see Approved Requests');
							} else if (st === 'approved' && url && hasNewerRecords) {
								reqSchoolBtn && (reqSchoolBtn.textContent = 'School: New Records Available - Request Updated Copy');
							} else if (st === 'pendingCHR') {
								reqSchoolBtn && (reqSchoolBtn.textContent = 'School: Requested (pendingCHR)');
							} else {
								reqSchoolBtn && (reqSchoolBtn.textContent = 'Request School Copy');
							}
						}
					}
				} catch (e) {
					/* silent */ }
			}

			// Fetch immunization schedule for child to build compact ledger
			const schedRes = await fetch(`/ebakunado/php/supabase/users/get_immunization_schedule.php?baby_id=${encodeURIComponent(babyId)}`);
			const schedJson = await schedRes.json();
			const allRows = (schedJson && schedJson.status === 'success' && Array.isArray(schedJson.data)) ? schedJson.data : [];

			// Filter only taken records
			const takenRows = allRows.filter(r => r.status === 'taken' || r.status === 'completed');

			// Determine next upcoming schedule per taken record (earliest future, not taken)
			function nextScheduleAfter(dateStr) {
				if (!dateStr) return '';
				const future = allRows
					.filter(r => (r.status !== 'taken' && r.status !== 'completed'))
					.filter(r => {
						const due = r.catch_up_date || r.schedule_date || '';
						return due && String(due) > String(dateStr);
					})
					.sort((a, b) => String((a.catch_up_date || a.schedule_date) || '').localeCompare(String((b.catch_up_date || b.schedule_date) || '')));
				return future.length ? (future[0].catch_up_date || future[0].schedule_date || '') : '';
			}

			// Canonical vaccine order to avoid duplicates and ensure clear display
			const canonical = [
				'BCG',
				'HEPAB1 (w/in 24 hrs)',
				'HEPAB1 (More than 24hrs)',
				'Pentavalent (DPT-HepB-Hib) - 1st',
				'OPV - 1st',
				'PCV - 1st',
				'Rota Virus Vaccine - 1st',
				'Pentavalent (DPT-HepB-Hib) - 2nd',
				'OPV - 2nd',
				'PCV - 2nd',
				'Rota Virus Vaccine - 2nd',
				'Pentavalent (DPT-HepB-Hib) - 3rd',
				'OPV - 3rd',
				'PCV - 3rd',
				'MCV1 (AMV)',
				'MCV2 (MMR)'
			];

			// Pick best taken record per vaccine (earliest date_given)
			const bestByName = {};
			takenRows.forEach(r => {
				const name = String(r.vaccine_name || '');
				if (!(name in bestByName)) {
					bestByName[name] = r;
					return;
				}
				const cur = bestByName[name];
				const dNew = String(r.date_given || '');
				const dCur = String(cur.date_given || '');
				if (dNew && (!dCur || dNew < dCur)) bestByName[name] = r;
			});

			let ledgerHtml = '';
			canonical.forEach(name => {
				const rec = bestByName[name];
				if (!rec) return; // show only taken vaccines
				const date = rec.date_given || rec.schedule_date || '';
				const ht = rec.height || rec.height_cm || '';
				const wt = rec.weight || rec.weight_kg || '';
				const next = nextScheduleAfter(date);
				ledgerHtml += `
                <tr>
                    <td style=\"padding:4px;\">${formatDate(date)}</td>
                    <td style=\"padding:4px;\">${name}</td>
                    <td style=\"padding:4px;\">${ht || ''}</td>
                    <td style=\"padding:4px;\">${wt || ''}</td>
                    <td style=\"padding:4px;\"></td>
                    <td style=\"padding:4px;\">Taken</td>
                    <td style=\"padding:4px;\"></td>
                    <td style=\"padding:4px;\"></td>
                    <td style=\"padding:4px;\">${formatDate(next)}</td>
                    <td style=\"padding:4px;\"></td>
                </tr>`;
			});

			document.getElementById('ledgerBody').innerHTML = ledgerHtml || '<tr><td colspan="10" style="text-align:center; padding:10px;">No taken vaccinations yet</td></tr>';

		} catch (err) {
			console.error('CHR load error', err);
			// No ledger to update on error
		}
	});
</script>

<?php include 'Include/footer.php'; ?>