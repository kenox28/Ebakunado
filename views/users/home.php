<?php include 'Include/header.php'; ?>

			

                    <table id="childrenTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Vaccine</th>
                                <th>Dose</th>
                                <th>Upcoming Schedule</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="childrenTableBody">


                        </tbody>
                    </table>



	<script>
			async function getChildren() {
                try {
                    const response = await fetch('../../php/supabase/users/get_accepted_child.php');
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        populateTable(data.data);
                    } else {
                        console.error('Error:', data.message);
                        showError('Failed to load children data');
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    showError('Network error occurred');
                }
            }

            function populateTable(children) {
                const tbody = document.getElementById('childrenTableBody');
                tbody.innerHTML = '';

                if (children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No children records found</td></tr>';
                    return;
                }

                children.forEach(child => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${child.name}</td>
                        <td>${child.age == 0 ? child.weeks_old + ' weeks' : child.age + ' years'}</td>
                        <td>${child.gender}</td>
                        <td>${child.vaccine || 'N/A'}</td>
                        <td>${child.dose || 'N/A'}</td>
                        <td>${child.schedule_date || 'N/A'}</td>
                        <td>
                            <button onclick="viewChild('${child.baby_id}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                View
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }


		async function viewChild(baby_id){
			const formdata = new FormData();
			formdata.append('baby_id', baby_id);
			const response = await fetch('../../php/supabase/users/view_immunization.php', {
				method: 'POST',
				body: formdata
			});
			const data = await response.json();
			if(data.status === 'success'){
				// window.location.href = 'view_immunization.php?baby_id=' + baby_id;
				window.location.href = 'view_immunization.php?baby_id=' + baby_id;
			}else{
				alert('Error: ' + data.message);
			}

		}

		// async function getMyChildren(){
		// 	const body = document.querySelector('#childrenBody');
		// 	body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
		// 	try{
		// 		// const res = await fetch('../../php/users/get_my_children.php');

		// 		const res = await fetch('../../php/supabase/users/get_my_children.php');
		// 		const data = await res.json();
		// 		if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
		// 		if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No records</td></tr>'; return; }
		// 		body.innerHTML = data.data.map(r => {
		// 			const name = (r.child_name ? r.child_name : (r.child_fname + ' ' + r.child_lname));
		// 			return `<tr>
		// 				<td>${r.baby_id || ''}</td>
		// 				<td>${name || ''}</td>
		// 				<td>${r.child_birth_date || ''}</td>
		// 				<td>${r.status || ''}</td>
		// 				<td>
		// 					<button onclick="viewSchedule('${r.baby_id}')">View Schedule</button>
		// 					${r.babys_card ? `<a href="${r.babys_card}" target="_blank">Baby Card</a>` : ''}
		// 				</td>
		// 			</tr>`
		// 		}).join('');
		// 	}catch(e){
		// 		alert('Error loading records');
		// 	}
		// }

		// async function viewSchedule(baby_id){
		// 	const box = document.querySelector('#immunizationSchedule');
		// 	const body = document.querySelector('#scheduleBody');
		// 	box.style.display = 'block';
		// 	body.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
		// 	try{
		// 		// const res = await fetch('../../php/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
		// 		const res = await fetch('../../php/supabase/users/get_my_immunization_records.php?baby_id=' + encodeURIComponent(baby_id));
		// 		const data = await res.json();
		// 		if(data.status !== 'success'){ body.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
		// 		if(!data.data || data.data.length === 0){ body.innerHTML = '<tr><td colspan="5">No schedule yet</td></tr>'; return; }
		// 		body.innerHTML = data.data.map(v => {
		// 			return `<tr>
		// 				<td>${v.vaccine_name || ''}</td>
		// 				<td>${v.dose_number || ''}</td>
		// 				<td>${v.catch_up_date || ''}</td>
		// 				<td>${v.date_given || ''}</td>
		// 				<td>${v.status || ''}</td>
		// 			</tr>`
		// 		}).join('');
		// 	}catch(e){
		// 		alert('Error loading schedule');
		// 	}
		// }

		window.addEventListener('DOMContentLoaded', getChildren);
	</script>
	</body>
</html> 

<?php include 'Include/footer.php'; ?>




