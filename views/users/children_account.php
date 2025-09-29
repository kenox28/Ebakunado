<php include '../Include/header.php'; ?>
		<main>
				<div class="childrenheader">
					<h3>Children Accounts</h3>
				</div>
                <div class="childrenbody">
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
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
		</main>
		
	<?php include '../Include/footer.php'; ?>
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
                        <td>${child.age} years</td>
                        <td>${child.weeks_old} weeks</td>
                        <td>${child.gender}</td>
                        <td>${child.vaccine || 'N/A'}</td>
                        <td>${child.dose || 'N/A'}</td>
                        <td>${child.upcoming_schedule || 'N/A'}</td>
                        <td>
                            <button onclick="viewChild('${child.baby_id}')" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                View
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            function viewChild(babyId) {
                // You can implement view functionality here
                alert(`View details for child: ${babyId}`);
            }

            function showError(message) {
                // You can use SweetAlert2 here for better error display
                console.error(message);
                // Swal.fire('Error', message, 'error');
            }

            // Load data when page loads
            getChildren();
        </script>


	</body>
</html> 