<?php include 'Include/header.php'; ?>

<style>
/* Mobile app inspired styling */
.child-profile-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.child-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f44336;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
    margin-right: 15px;
}

.child-info h3 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 18px;
}

.child-info p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.switch-button {
    background: #f44336;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.vaccine-card {
    background: white;
    border-radius: 12px;
    margin: 10px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
}

.vaccine-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #f44336;
}

.vaccine-content {
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vaccine-info h4 {
    margin: 0 0 4px 0;
    color: #333;
    font-size: 16px;
}

.vaccine-info p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    background: #ffebee;
    color: #f44336;
}

.status-icon {
    font-size: 14px;
}

.urgent-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 15px;
    margin: 20px 0;
    color: #856404;
}

.urgent-notice h4 {
    margin: 0 0 8px 0;
    color: #856404;
    font-size: 16px;
}

.urgent-notice p {
    margin: 0;
    font-size: 14px;
}
</style>

<!-- Child Profile Card -->
<div class="child-profile-card">
    <div style="display: flex; align-items: center;">
        <div class="child-avatar" id="childAvatar">U</div>
        <div class="child-info">
            <h3 id="childName">Loading...</h3>
            <p id="childAge">Loading...</p>
        </div>
    </div>
    <button class="switch-button" onclick="window.location.href='home.php'">
        Switch <span>→</span>
    </button>
</div>

<!-- Urgent Notice -->
<div class="urgent-notice">
    <h4>⚠️ Urgent: Missed Immunizations</h4>
    <p>Some of your child's vaccines are overdue. Please contact your health center immediately to schedule catch-up vaccinations.</p>
</div>

<!-- Missed Vaccines Container -->
<div id="vaccineContainer">
    <div style="text-align: center; padding: 40px; color: #666;">
        <div style="font-size: 48px; margin-bottom: 10px;">💉</div>
        <p>Loading missed immunizations...</p>
    </div>
</div>

<script>
    let scheduleData = [];
    let currentChild = null;

    async function loadMissedImmunizations() {
        try {
            const response = await fetch('../../php/supabase/users/get_immunization_schedule.php');
            const data = await response.json();

            if (data.status === 'success') {
                scheduleData = data.data;
                loadChildData();
                renderMissedVaccines();
            }
        } catch (error) {
            console.error('Error loading immunization schedule:', error);
            document.getElementById('vaccineContainer').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #f44336;">
                    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
                    <p>Error loading missed immunizations</p>
                </div>
            `;
        }
    }

    function loadChildData() {
        if (scheduleData.length > 0) {
            const firstRecord = scheduleData[0];
            currentChild = firstRecord;
            
            const childName = firstRecord.child_name || 'Unknown Child';
            const firstLetter = childName.charAt(0).toUpperCase();
            
            document.getElementById('childAvatar').textContent = firstLetter;
            document.getElementById('childName').textContent = childName;
            document.getElementById('childAge').textContent = 'Loading age...';
            
            fetchChildAge(firstRecord.baby_id);
        }
    }

    async function fetchChildAge(baby_id) {
        try {
            const formData = new FormData();
            formData.append('baby_id', baby_id);
            const response = await fetch('../../php/supabase/users/get_child_details.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status === 'success' && data.data.length > 0) {
                const child = data.data[0];
                const ageText = child.age == 0 ? child.weeks_old + ' weeks old' : child.age + ' years old';
                document.getElementById('childAge').textContent = ageText;
            }
        } catch (error) {
            console.error('Error fetching child age:', error);
            document.getElementById('childAge').textContent = 'Unknown age';
        }
    }

    function renderMissedVaccines() {
        const container = document.getElementById('vaccineContainer');
        
        if (scheduleData.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
                    <p>No missed immunizations found</p>
                </div>
            `;
            return;
        }

        // Filter for missed/overdue vaccines only
        const today = new Date().toISOString().split('T')[0];
        const missedVaccines = scheduleData.filter(record => {
            return record.status === 'missed' || 
                   (record.status === 'scheduled' && record.schedule_date && record.schedule_date < today);
        });

        if (missedVaccines.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #28a745;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
                    <p>Great! No missed immunizations</p>
                </div>
            `;
            return;
        }

        // Group by vaccine name and dose
        const groupedVaccines = {};
        missedVaccines.forEach(record => {
            const key = `${record.vaccine_name}_${record.dose_number}`;
            if (!groupedVaccines[key]) {
                groupedVaccines[key] = record;
            }
        });

        let cardsHTML = '';
        Object.values(groupedVaccines).forEach(record => {
            const daysOverdue = record.schedule_date ? 
                Math.ceil((new Date() - new Date(record.schedule_date)) / (1000 * 60 * 60 * 24)) : 0;
            
            cardsHTML += `
                <div class="vaccine-card">
                    <div class="vaccine-content">
                        <div class="vaccine-info">
                            <h4>${record.vaccine_name}</h4>
                            <p>${getDoseText(record.dose_number)}</p>
                        </div>
                        <div class="status-badge">
                            <span class="status-icon">!</span>
                            <span>Overdue ${daysOverdue} days</span>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = cardsHTML;
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

    // Load data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadMissedImmunizations();
    });
</script>

</body>
</html>

<?php include 'Include/footer.php'; ?>
