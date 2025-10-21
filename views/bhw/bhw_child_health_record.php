<?php include 'Include/header.php'; ?>
<style>
.chr-paper { width: 13.5in; max-width: 100%; box-sizing: border-box; min-height: 13in; margin:0 auto; background:#fff; border:1px solid #e0e0e0; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:0.6in; font-size:16px; line-height:1.7; }
.block-header { text-align:center; font-weight:700; font-size:16px; margin:0 0 8px 0; }
.boxed { border:1px solid #000 !important; padding:8px; }
@media print { .chr-paper{ box-shadow:none; border:1px solid #000; } .content{ padding:0; } }
</style>

<div class="content" style="padding: 24px 16px;">
    <div id="chrRoot" class="chr-paper">
        <div style="text-align:center; margin-bottom: 12px;">
			<h2 style="margin:0;">CHILD HEALTH RECORD</h2>
			<p style="margin:4px 0;">City Health Department, Ormoc City</p>
		</div>

		<!-- Child Profile Header (Read-Only for BHW) -->
        <div id="childProfile" style="font-size: inherit; line-height: 1.7; margin-bottom: 12px; background: #f8f9fa; padding: 12px; border-radius: 4px;">
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

		<!-- Child History (Read-Only for BHW) -->
        <div id="childHistory" style="font-size: inherit; line-height: 1.7; margin: 8px 0 16px 0; background: #f8f9fa; padding: 12px; border-radius: 4px;">
            <div class="block-header" style="font-size:14px;">CHILD HISTORY</div>
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

		<!-- Exclusive Breastfeeding & Complementary Feeding (Editable) -->
        <div id="feedingSection" class="boxed" style="font-size: inherit; line-height: 1.7; margin: 8px 0 16px 0; padding: 8px;">
            <h3 style="margin: 0 0 8px 0; font-size: 18px; text-align: center;">Exclusive Breastfeeding & Complementary Feeding</h3>
			<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
				<div>
                    <h4 style="margin: 0 0 4px 0; font-size: 16px;">Exclusive Breastfeeding:</h4>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:4px; font-size: 16px;">
						<label><input type="checkbox" id="eb_1mo"> 1st mo</label>
						<label><input type="checkbox" id="eb_2mo"> 2nd mo</label>
						<label><input type="checkbox" id="eb_3mo"> 3rd mo</label>
						<label><input type="checkbox" id="eb_4mo"> 4th mo</label>
						<label><input type="checkbox" id="eb_5mo"> 5th mo</label>
						<label><input type="checkbox" id="eb_6mo"> 6th mo</label>
					</div>
				</div>
				<div>
                    <h4 style="margin: 0 0 4px 0; font-size: 16px;">Complementary Feeding:</h4>
                    <div style="font-size: 16px;">
						<div>6th mo food: <input type="text" id="cf_6mo" style="width: 100%; padding: 2px;"></div>
						<div>7th mo food: <input type="text" id="cf_7mo" style="width: 100%; padding: 2px;"></div>
						<div>8th mo food: <input type="text" id="cf_8mo" style="width: 100%; padding: 2px;"></div>
					</div>
				</div>
			</div>
			<div style="text-align: center; margin-top: 8px;">
				<button onclick="updateFeedingStatus()" style="padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px;">Update Feeding Status</button>
			</div>
		</div>

		<!-- Mother's TD Status (Editable) -->
        <div id="tdSection" class="boxed" style="font-size: inherit; line-height: 1.7; margin: 8px 0 16px 0; padding: 8px;">
            <h3 style="margin: 0 0 8px 0; font-size: 18px; text-align: center;">Mother's TD (Tetanus-Diphtheria) Status</h3>
            <div style="display:grid; grid-template-columns: repeat(5, 1fr); gap:8px; font-size: 16px;">
				<div>TD 1st dose: <input type="date" id="td_dose1" style="width: 100%; padding: 2px;"></div>
				<div>TD 2nd dose: <input type="date" id="td_dose2" style="width: 100%; padding: 2px;"></div>
				<div>TD 3rd dose: <input type="date" id="td_dose3" style="width: 100%; padding: 2px;"></div>
				<div>TD 4th dose: <input type="date" id="td_dose4" style="width: 100%; padding: 2px;"></div>
				<div>TD 5th dose: <input type="date" id="td_dose5" style="width: 100%; padding: 2px;"></div>
			</div>
			<div style="text-align: center; margin-top: 8px;">
				<button onclick="updateTDStatus()" style="padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px;">Update TD Status</button>
			</div>
		</div>

        <!-- Immunization Record (Read-only) -->
		<div>
			<h3 style="margin: 0 0 8px 0; font-size: 14px; text-align: center;">Immunization Record</h3>
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
                    <tr><td colspan="10" style="text-align:center; padding:10px;">Loading...</td></tr>
                </tbody>
			</table>
		</div>
	</div>
</div>

<script>
function formatDate(dateString){
	if(!dateString) return '';
	const d = new Date(dateString);
	if (Number.isNaN(d.getTime())) return String(dateString);
	return d.toLocaleDateString('en-US', { month:'numeric', day:'numeric', year:'2-digit' });
}

function normalizeDateStr(d){
	const pad = n => (n<10?'0':'')+n;
	return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
}

function getDoseText(n){
	const map = {1:'1st dose',2:'2nd dose',3:'3rd dose',4:'4th dose'};
	return map[n] || `Dose ${n||''}`;
}

document.addEventListener('DOMContentLoaded', async function(){
	const params = new URLSearchParams(window.location.search);
	const babyId = params.get('baby_id') || '';
    if(!babyId){
        alert('Missing baby_id');
        return;
    }

	try{
		// Fetch child details
		const fd = new FormData(); fd.append('baby_id', babyId);
		const childRes = await fetch('/ebakunado/php/supabase/bhw/get_child_details.php', { method:'POST', body: fd });
		const childJson = await childRes.json();
		const child = (childJson && childJson.status==='success' && childJson.data && childJson.data[0]) ? childJson.data[0] : {};

        // Fill header (read-only)
		document.getElementById('f_name').textContent = child.name || [(child.child_fname||''),(child.child_lname||'')].filter(Boolean).join(' ');
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

        // Set defaults for editable new fields
        const bt = document.createElement('div');
        bt.innerHTML = `\
        <div class="form-grid" style="margin:8px 0;">\
            <div>Blood Type: <input type="text" id="edit_blood_type" value="${child.blood_type||''}" style="padding:4px; width:120px;"></div>\
            <div>Allergies: <input type="text" id="edit_allergies" value="${child.allergies||''}" style="padding:4px; width:200px;"></div>\
            <div>LMP: <input type="date" id="edit_lpm" value="${child.lpm||''}" style="padding:4px; width:160px;"></div>\
            <div>Family Planning: <input type="text" id="edit_family_planning" value="${child.family_planning||''}" style="padding:4px; width:200px;"></div>\
            <div style="grid-column:1/-1; text-align:center; margin-top:6px;">\
                <button id="saveNewFields" style="padding:6px 12px; background:#17a2b8; color:white; border:none; border-radius:4px;">Save Child Info</button>\
            </div>\
        </div>`;
        document.getElementById('childProfile').appendChild(bt);

        document.getElementById('saveNewFields').onclick = async () => {
            try{
                const params = new URLSearchParams(window.location.search);
                const babyId = params.get('baby_id') || '';
                const fd = new FormData();
                fd.append('baby_id', babyId);
                fd.append('blood_type', document.getElementById('edit_blood_type').value);
                fd.append('allergies', document.getElementById('edit_allergies').value);
                fd.append('lpm', document.getElementById('edit_lpm').value);
                fd.append('family_planning', document.getElementById('edit_family_planning').value);
                const res = await fetch('/ebakunado/php/supabase/bhw/update_child_info.php', { method:'POST', body: fd });
                const j = await res.json();
                if (j.status==='success'){
                    alert('Child info updated');
                    location.reload();
                } else {
                    alert('Update failed: ' + (j.message||'Unknown error'));
                }
            }catch(e){ alert('Network error: ' + e.message); }
        };

        // Fill editable feeding data
		document.getElementById('eb_1mo').checked = child.exclusive_breastfeeding_1mo || false;
		document.getElementById('eb_2mo').checked = child.exclusive_breastfeeding_2mo || false;
		document.getElementById('eb_3mo').checked = child.exclusive_breastfeeding_3mo || false;
		document.getElementById('eb_4mo').checked = child.exclusive_breastfeeding_4mo || false;
		document.getElementById('eb_5mo').checked = child.exclusive_breastfeeding_5mo || false;
		document.getElementById('eb_6mo').checked = child.exclusive_breastfeeding_6mo || false;
		document.getElementById('cf_6mo').value = child.complementary_feeding_6mo || '';
		document.getElementById('cf_7mo').value = child.complementary_feeding_7mo || '';
		document.getElementById('cf_8mo').value = child.complementary_feeding_8mo || '';

		// Fill editable TD status data
		document.getElementById('td_dose1').value = child.mother_td_dose1_date || '';
		document.getElementById('td_dose2').value = child.mother_td_dose2_date || '';
		document.getElementById('td_dose3').value = child.mother_td_dose3_date || '';
		document.getElementById('td_dose4').value = child.mother_td_dose4_date || '';
		document.getElementById('td_dose5').value = child.mother_td_dose5_date || '';

        // Fetch immunization schedule for child to build editable ledger
        const schedRes = await fetch(`/ebakunado/php/supabase/bhw/get_immunization_records.php?baby_id=${encodeURIComponent(babyId)}`);
        const schedJson = await schedRes.json();
        const allRows = (schedJson && schedJson.status==='success' && Array.isArray(schedJson.data)) ? schedJson.data : [];

        // Build read-only ledger with all immunization records
        let ledgerHtml = '';
        if (allRows.length === 0) {
            ledgerHtml = '<tr><td colspan="10" style="text-align:center; padding:10px;">No immunization records found</td></tr>';
        } else {
            allRows.forEach(row => {
                const date = row.date_given || row.schedule_date || '';
                const ht = row.height || row.height_cm || '';
                const wt = row.weight || row.weight_kg || '';
                const status = row.status || 'scheduled';
                const statusText = status === 'taken' ? 'Taken' : (status === 'completed' ? 'Completed' : (status === 'missed' ? 'Missed' : 'Scheduled'));

                ledgerHtml += `
                    <tr>
                        <td style="padding:4px;">${formatDate(date)}</td>
                        <td style="padding:4px;">${row.vaccine_name || ''}</td>
                        <td style="padding:4px;">${ht}</td>
                        <td style="padding:4px;">${wt}</td>
                        <td style="padding:4px;"></td>
                        <td style="padding:4px;">${statusText}</td>
                        <td style="padding:4px;"></td>
                        <td style="padding:4px;"></td>
                        <td style="padding:4px;">${formatDate(row.catch_up_date || '')}</td>
                        <td style="padding:4px;"></td>
                    </tr>`;
            });
        }

        document.getElementById('ledgerBody').innerHTML = ledgerHtml;

	}catch(err){
		console.error('CHR load error', err);
        alert('Error loading child health record: ' + err.message);
	}
});

// Update functions for feeding status
async function updateFeedingStatus() {
    const params = new URLSearchParams(window.location.search);
    const babyId = params.get('baby_id') || '';
    
    try {
        const formData = new FormData();
        formData.append('baby_id', babyId);
        formData.append('exclusive_breastfeeding_1mo', document.getElementById('eb_1mo').checked ? '1' : '0');
        formData.append('exclusive_breastfeeding_2mo', document.getElementById('eb_2mo').checked ? '1' : '0');
        formData.append('exclusive_breastfeeding_3mo', document.getElementById('eb_3mo').checked ? '1' : '0');
        formData.append('exclusive_breastfeeding_4mo', document.getElementById('eb_4mo').checked ? '1' : '0');
        formData.append('exclusive_breastfeeding_5mo', document.getElementById('eb_5mo').checked ? '1' : '0');
        formData.append('exclusive_breastfeeding_6mo', document.getElementById('eb_6mo').checked ? '1' : '0');
        formData.append('complementary_feeding_6mo', document.getElementById('cf_6mo').value);
        formData.append('complementary_feeding_7mo', document.getElementById('cf_7mo').value);
        formData.append('complementary_feeding_8mo', document.getElementById('cf_8mo').value);

        const res = await fetch('/ebakunado/php/supabase/bhw/update_feeding_status.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            alert('Feeding status updated successfully!');
        } else {
            alert('Update failed: ' + data.message);
        }
    } catch (e) {
        alert('Error updating feeding status: ' + e.message);
    }
}

// Update functions for TD status
async function updateTDStatus() {
    const params = new URLSearchParams(window.location.search);
    const babyId = params.get('baby_id') || '';
    
    try {
        const formData = new FormData();
        formData.append('baby_id', babyId);
        formData.append('mother_td_dose1_date', document.getElementById('td_dose1').value);
        formData.append('mother_td_dose2_date', document.getElementById('td_dose2').value);
        formData.append('mother_td_dose3_date', document.getElementById('td_dose3').value);
        formData.append('mother_td_dose4_date', document.getElementById('td_dose4').value);
        formData.append('mother_td_dose5_date', document.getElementById('td_dose5').value);

        const res = await fetch('/ebakunado/php/supabase/bhw/update_td_status.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            alert('TD status updated successfully!');
        } else {
            alert('Update failed: ' + data.message);
        }
    } catch (e) {
        alert('Error updating TD status: ' + e.message);
    }
}

// Update functions for immunization records
async function updateImmunizationDate(recordId, newDate) {
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        formData.append('date_given', newDate);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/update_immunization_date.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status !== 'success') {
            alert('Failed to update date: ' + data.message);
        }
    } catch (e) {
        alert('Error updating date: ' + e.message);
    }
}

async function updateImmunizationHeight(recordId, newHeight) {
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        formData.append('height', newHeight);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/update_immunization_height.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status !== 'success') {
            alert('Failed to update height: ' + data.message);
        }
    } catch (e) {
        alert('Error updating height: ' + e.message);
    }
}

async function updateImmunizationWeight(recordId, newWeight) {
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        formData.append('weight', newWeight);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/update_immunization_weight.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status !== 'success') {
            alert('Failed to update weight: ' + data.message);
        }
    } catch (e) {
        alert('Error updating weight: ' + e.message);
    }
}

async function updateImmunizationStatus(recordId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        formData.append('status', newStatus);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/update_immunization_status.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status !== 'success') {
            alert('Failed to update status: ' + data.message);
        }
    } catch (e) {
        alert('Error updating status: ' + e.message);
    }
}

async function updateNextScheduleDate(recordId, newDate) {
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        formData.append('catch_up_date', newDate);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/update_next_schedule_date.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status !== 'success') {
            alert('Failed to update next schedule date: ' + data.message);
        }
    } catch (e) {
        alert('Error updating next schedule date: ' + e.message);
    }
}

async function deleteImmunizationRecord(recordId) {
    if (!confirm('Are you sure you want to delete this immunization record?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('record_id', recordId);
        
        const res = await fetch('/ebakunado/php/supabase/bhw/delete_immunization_record.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            alert('Immunization record deleted successfully!');
            location.reload(); // Refresh the page to show updated data
        } else {
            alert('Failed to delete record: ' + data.message);
        }
    } catch (e) {
        alert('Error deleting record: ' + e.message);
    }
}
</script>

<?php include 'Include/footer.php'; ?>
