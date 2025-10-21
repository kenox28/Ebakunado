<?php include 'Include/header.php'; ?>
                <header class="childHeader">
                    <nav>
                        <h3>Child Name: <span id="childName"></span></h3>
                        <h3>Child Age: <span id="childAge"></span></h3>
                        <h3>Child Gender: <span id="childGender"></span></h3>
                        <button id="upcomingTab">Upcoming</button>
                        <button id="takenTab">Taken</button>
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
							<th>Dose</th>
							<th>Schedule Date</th>
                <th>Status</th>
                            </tr>
                        </thead>
					<tbody id="scheduleBody">
                        </tbody>
                    </table>


	<script>
    let scheduleData = [];
    let currentTab = 'upcoming';
    let currentChild = null;
    const urlParams = new URLSearchParams(window.location.search);
    const selectedBabyId = urlParams.get('baby_id') || '';

    async function loadImmunizationSchedule() {
        try {
            const endpoint = '/ebakunado/php/supabase/users/get_immunization_schedule.php' + (selectedBabyId ? ('?baby_id=' + encodeURIComponent(selectedBabyId)) : '');
            const response = await fetch(endpoint);
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                scheduleData = Array.isArray(data.data) ? data.data : [];
                // If API returns all, filter by selectedBabyId
                if (selectedBabyId) {
                    scheduleData = scheduleData.filter(r => String(r.baby_id || '') === String(selectedBabyId));
                }
                loadChildData();
                renderVaccineCards();
                    }
                } catch (error) {
            console.error('Error loading immunization schedule:', error);
            document.getElementById('scheduleBody').innerHTML = `
                <tr><td colspan="4" style="text-align: center; padding: 20px; color: #dc3545;">Error loading schedule</td></tr>
            `;
        }
    }

    function loadChildData() {
        if (scheduleData.length === 0) {
            document.getElementById('scheduleBody').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">No immunization records found</td></tr>';
            document.getElementById('childName').textContent = 'Unknown Child';
            document.getElementById('childAge').textContent = 'Unknown age';
            document.getElementById('childGender').textContent = 'Unknown';
            return;
        }
        const firstRecord = scheduleData[0];
        currentChild = firstRecord;
        const childName = firstRecord.child_name || 'Unknown Child';
        document.getElementById('childName').textContent = childName;
        document.getElementById('childAge').textContent = 'Loading age...';
        fetchChildAge(firstRecord.baby_id);
    }

    async function fetchChildAge(baby_id) {
        try {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('/ebakunado/php/supabase/users/get_child_details.php', {
				method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status === 'success' && data.data.length > 0) {
                const child = data.data[0];
                const ageText = child.age == 0 ? child.weeks_old + ' weeks old' : child.age + ' years old';
                document.getElementById('childAge').textContent = ageText;
                // Normalize gender from API (uses child_gender key)
                const rawGender = child.child_gender || child.gender || '';
                let prettyGender = 'Unknown';
                if (rawGender) {
                    const g = String(rawGender).toLowerCase();
                    if (g.startsWith('m')) prettyGender = 'Male';
                    else if (g.startsWith('f')) prettyGender = 'Female';
                    else prettyGender = rawGender; // keep whatever custom value
                }
                document.getElementById('childGender').textContent = prettyGender;
                
                // Set QR code image
                if (child.qr_code) {
                    document.getElementById('childQrCode').src = child.qr_code;
                }
            }
                } catch (error) {
            console.error('Error fetching child age:', error);
            document.getElementById('childAge').textContent = 'Unknown age';
            document.getElementById('childGender').textContent = 'Unknown';
        }
    }

    function renderVaccineCards() {
        const tbody = document.getElementById('scheduleBody');
        
        if (scheduleData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">No immunization records found</td></tr>';
            return;
        }

        // Filter data based on current tab
        let filteredData = scheduleData;
        if (currentTab === 'upcoming') {
            filteredData = scheduleData.filter(record => 
                record.status === 'scheduled' || record.status === 'pending' || record.status === 'missed'
            );
        } else if (currentTab === 'taken') {
            filteredData = scheduleData.filter(record => 
                record.status === 'completed' || record.status === 'taken'
            );
        }

        if (filteredData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 20px;">No ${currentTab} vaccines found</td></tr>`;
                    return;
                }

        // Group by vaccine name and dose
        const groupedVaccines = {};
        filteredData.forEach(record => {
            const key = `${record.vaccine_name}_${record.dose_number}`;
            if (!groupedVaccines[key]) {
                groupedVaccines[key] = record;
            }
        });

        let rowsHTML = '';
        Object.values(groupedVaccines).forEach(record => {
            const status = getVaccineStatus(record);
            const statusText = getStatusText(record, status);
            
            rowsHTML += `
                <tr>
                    <td>${record.vaccine_name}</td>
                    <td>${getDoseText(record.dose_number)}</td>
                    <td>${record.schedule_date || 'N/A'}</td>
                    <td style="color: ${getStatusColor(status)}">${statusText}</td>
                </tr>
                    `;
                });
                
        tbody.innerHTML = rowsHTML;
    }

    function getVaccineStatus(record) {
        const today = new Date().toISOString().split('T')[0];
        
        if (record.status === 'completed' || record.status === 'taken') {
            return 'completed';
        } else if (record.status === 'missed' || (record.schedule_date && record.schedule_date < today)) {
            return 'overdue';
        } else if (record.schedule_date && record.schedule_date >= today) {
            return 'upcoming';
        } else {
            return 'missing';
        }
    }

    function getStatusClass(status) {
        switch(status) {
            case 'completed': return 'completed';
            case 'overdue': return 'overdue';
            case 'upcoming': return 'upcoming';
            default: return 'missing';
        }
    }

    function getStatusColor(status) {
        switch(status) {
            case 'completed': return '#28a745';
            case 'overdue': return '#dc3545';
            case 'upcoming': return '#ffc107';
                    default: return '#6c757d';
                }
            }

    function getStatusIcon(status) {
        switch(status) {
            case 'completed': return '✓';
            case 'overdue': return '!';
            case 'upcoming': return '↑';
            default: return '?';
        }
    }

    function getStatusText(record, status) {
        switch(status) {
            case 'completed':
                return record.date_given ? `Completed ${formatDate(record.date_given)}` : 'Completed';
            case 'overdue':
                return record.schedule_date ? `Overdue ${formatDate(record.schedule_date)}` : 'Overdue';
            case 'upcoming':
                return record.schedule_date ? `Upcoming ${formatDate(record.schedule_date)}` : 'Upcoming';
            default:
                return 'Missing Previous Dose';
        }
    }

    function getDoseText(doseNumber) {
        const doseMap = {
            1: 'First Dose',
            2: 'Second Dose', 
            3: 'Third Dose',
            4: 'Fourth Dose'
        };
        return doseMap[doseNumber] || `Dose ${doseNumber}`;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: '2-digit', 
            day: '2-digit', 
            year: 'numeric' 
        });
    }

    function switchTab(tab) {
        currentTab = tab;
        
        // Update tab buttons styling
        const upcomingBtn = document.getElementById('upcomingTab');
        const takenBtn = document.getElementById('takenTab');
        
        if (tab === 'upcoming') {
            upcomingBtn.style.backgroundColor = '#007bff';
            upcomingBtn.style.color = 'white';
            takenBtn.style.backgroundColor = '#6c757d';
            takenBtn.style.color = 'white';
        } else {
            takenBtn.style.backgroundColor = '#007bff';
            takenBtn.style.color = 'white';
            upcomingBtn.style.backgroundColor = '#6c757d';
            upcomingBtn.style.color = 'white';
        }
        
        // Re-render table
        renderVaccineCards();
    }

    // Load data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadImmunizationSchedule();
        
        // Add tab button event listeners
        document.getElementById('upcomingTab').addEventListener('click', function() {
            switchTab('upcoming');
        });
        
        document.getElementById('takenTab').addEventListener('click', function() {
            switchTab('taken');
        });
        
        // Add QR code functionality
        document.getElementById('childQrCodeButton').addEventListener('click', function() {
            const qrContainer = document.getElementById('childQrCodeContainer');
            const qrButton = document.getElementById('childQrCodeButton');
            const qrImage = document.getElementById('childQrCodeImage');
            const qrCode = document.getElementById('childQrCode');
            
            if (qrCode.src) {
                qrContainer.style.display = 'block';
                qrImage.src = qrCode.src;
                qrButton.style.display = 'none';
            }
        });
        
        // Close QR code functionality
        document.getElementById('closeQrCodeButton').addEventListener('click', function() {
            const qrContainer = document.getElementById('childQrCodeContainer');
            const qrButton = document.getElementById('childQrCodeButton');
            
            qrContainer.style.display = 'none';
            qrButton.style.display = 'block';
        });
    });
	</script>

	</body>
</html> 

<?php include 'Include/footer.php'; ?>
