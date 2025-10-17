<?php include 'Include/header.php'; ?>
                <header class="childHeader">
                    <nav>
                        <h3>Child Name: <span id="childName"></span></h3>
                        <h3>Child Age: <span id="childAge"></span></h3>
                        <h3>Child Gender: <span id="childGender"></span></h3>
                    </nav>
                    <nav>
                        <button id="childQrCodeButton"><img id="childQrCode" src="" alt="QR Code" style="width: 100px; height: 100px;"></button>
                        <div id="childQrCodeContainer" style="display: none;"><button id="closeQrCodeButton">Close</button> <img id="childQrCodeImage" src="" alt="QR Code" style="width: 500px; height: 500px;"></div>
                        <a href="home.php">Switch Account</a>
                </nav>
                </header>
                <table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Vaccine</th>
							<th>Dose #</th>
							<th>Schedule Date</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="scheduleBody">
					</tbody>
				</table>
                    
				<!-- <div class="childrenheader">
					<h3>Child name</h3>
					<nav>
						<button>qr code</button>
						<button>Switch Account</button>
					</nav>

				</div>
				<div id="childrenTable">
				<table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Baby ID</th>
							<th>Child Name</th>
							<th>Birth Date</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="childrenBody">
						<tr><td colspan="5">Loading...</td></tr>
					</tbody>
				</table>
			</div>
			
			<div id="immunizationSchedule" style="display: none; margin-top: 20px;">
				<h3>Immunization Schedule</h3>
				<table border="1" style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr>
							<th>Vaccine</th>
							<th>Dose #</th>
							<th>Due Date</th>
							<th>Date Given</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="scheduleBody">
					</tbody>
				</table>
			</div> -->
			</section>
		</main>
		
	<?php include 'Include/footer.php'; ?>
	<script>
        async function getChildDetails(){
            const baby_id = '<?php echo $_GET['baby_id']; ?>';

            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('/ebakunado/php/supabase/users/get_child_details.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            console.log(data);
            if(data.status === 'success'){
                const child = data.data[0];
                document.getElementById('childName').innerHTML = child.name;
                document.getElementById('childAge').innerHTML = child.age == 0 ? child.weeks_old + ' weeks ' : child.age + ' years ';
                document.getElementById('childGender').innerHTML = child.gender;
                document.getElementById('childQrCode').src = child.qr_code;
                console.log(child.qr_code);
                document.getElementById('childQrCodeButton').addEventListener('click', () => {
                    document.getElementById('childQrCodeContainer').style.display = 'block';
                    document.getElementById('childQrCodeImage').src = child.qr_code;
                    document.getElementById('childQrCodeButton').style.display = 'none';



                });

                // child.age == 0 ? child.weeks_old + ' weeks' : child.age + ' years'
            }
        }
        getChildDetails();



        document.getElementById('closeQrCodeButton').addEventListener('click', () => {
            document.getElementById('childQrCodeContainer').style.display = 'none';
            document.getElementById('childQrCodeButton').style.display = 'block';
        });


        async function getImmunizationSchedule(){
            const baby_id = '<?php echo $_GET['baby_id']; ?>';
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('/ebakunado/php/supabase/users/get_my_immunization_records.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            console.log(data);
            if(data.status === 'success'){
                const immunizationSchedule = data.data;
                const scheduleBody = document.getElementById('scheduleBody');
                scheduleBody.innerHTML = '';
                immunizationSchedule.forEach(immunization => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${immunization.vaccine_name}</td>
                        <td>${immunization.dose_number}</td>
                        <td>${immunization.schedule_date}</td>
                        <td>${immunization.status}</td>
                    `;
                    scheduleBody.appendChild(row);
                });
                console.log(immunizationSchedule);
            }
        }
        getImmunizationSchedule();
	</script>
	</body>
</html> 