<?php include 'Include/header.php'; ?>

<style>
    .request-page {
        padding: 20px;
        background-color: #f8f9fa;
        min-height: auto;
        width: 100%;
        box-sizing: border-box;
    }

    .request-header {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }

    .request-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
    }

    .request-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 16px;
    }

    .form-container {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #1976d2;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e3f2fd;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
        
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group input[type="file"] {
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #fafafa;
    }

    .form-group input[type="text"]:focus,
    .form-group input[type="date"]:focus {
        outline: none;
        border-color: #1976d2;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    }

    .radio-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .radio-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        color: #555;
        cursor: pointer;
        padding: 10px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .radio-group label:hover {
        background-color: #f0f0f0;
    }

    .radio-group input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #1976d2;
    }

    .radio-group .radio-label {
        font-weight: 600;
        color: #1976d2;
        grid-column: 1 / -1;
        margin-bottom: 10px;
        font-size: 16px;
    }

    .vaccine-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .vaccine-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .vaccine-grid label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: #fafafa;
    }

    .vaccine-grid label:hover {
        border-color: #1976d2;
        background-color: #f0f8ff;
    }

    .vaccine-grid input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #1976d2;
    }

    .submit-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
    }

    .submit-btn {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        color: white;
        border: none;
        padding: 15px 40px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .radio-group {
            grid-template-columns: 1fr;
        }
        
        .vaccine-grid {
            grid-template-columns: 1fr;
        }
        
        .request-page {
            padding: 10px;
        }
        
        .form-container,
        .vaccine-section,
        .submit-section {
            padding: 20px;
        }
    }
</style>

<div class="request-page">
    <div class="request-header">
        <h1>📋 Child Health Record Request</h1>
        <p>Please fill out all required information for your child's health record</p>
    </div>

    <form id="requestform" method="post" enctype="multipart/form-data">
        <!-- Basic Information Section -->
        <div class="form-container">
            <h2 class="section-title">👶 Basic Child Information</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="child_fname">Baby First Name *</label>
                    <input type="text" name="child_fname" placeholder="Enter baby's first name" required>
                </div>
                
                <div class="form-group">
                    <label for="child_lname">Baby Last Name *</label>
                    <input type="text" name="child_lname" placeholder="Enter baby's last name" required>
                </div>
                
                <div class="form-group">
                    <label for="child_birth_date">Birth Date *</label>
                    <input type="date" name="child_birth_date" required>
                </div>
                
                <div class="form-group">
                    <label for="place_of_birth">Place of Birth</label>
                    <input type="text" name="place_of_birth" placeholder="Enter place of birth">
                </div>
                
                <div class="form-group">
                    <label for="child_address">Address *</label>
                    <input type="text" name="child_address" value="<?php echo $place; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="birth_weight">Birth Weight (kg)</label>
                    <input type="text" name="birth_weight" placeholder="e.g., 3.2">
                </div>
                
                <div class="form-group">
                    <label for="birth_height">Birth Height (cm)</label>
                    <input type="text" name="birth_height" placeholder="e.g., 50">
                </div>
            </div>

            <!-- Gender Selection -->
            <div class="radio-group">
                <label class="radio-label">Baby Gender *</label>
                <label>
                    <input type="radio" name="child_gender" value="Male" required checked>
                    👦 Male
                </label>
                <label>
                    <input type="radio" name="child_gender" value="Female" required>
                    👧 Female
                </label>
            </div>

            <!-- Parent Information -->
            <div class="form-grid">
                <?php if($gender == 'Male'): ?>
                    <div class="form-group">
                        <label for="father_name">Father Name</label>
                        <input type="text" name="father_name" value="<?php echo $user_fname; ?>">
                    </div>
                    <div class="form-group">
                        <label for="mother_name">Mother Name *</label>
                        <input type="text" name="mother_name" placeholder="Enter mother's name" required>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="mother_name">Mother Name *</label>
                        <input type="text" name="mother_name" value="<?php echo $user_fname; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="father_name">Father Name</label>
                        <input type="text" name="father_name" placeholder="Enter father's name">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Birth Details -->
            <div class="radio-group">
                <label class="radio-label">Type of Delivery</label>
                <label>
                    <input type="radio" name="delivery_type" value="Normal">
                    🏥 Normal
                </label>
                <label>
                    <input type="radio" name="delivery_type" value="Caesarean Section">
                    ⚕️ Caesarean Section
                </label>
            </div>

            <div class="radio-group">
                <label class="radio-label">Birth Order</label>
                <label>
                    <input type="radio" name="birth_order" value="Single">
                    👶 Single
                </label>
                <label>
                    <input type="radio" name="birth_order" value="Twin">
                    👶👶 Twin
                </label>
            </div>

            <div class="radio-group">
                <label class="radio-label">Birth Attendant</label>
                <label>
                    <input type="radio" name="birth_attendant" value="Doctor">
                    👨‍⚕️ Doctor
                </label>
                <label>
                    <input type="radio" name="birth_attendant" value="Midwife">
                    👩‍⚕️ Midwife
                </label>
                <label>
                    <input type="radio" name="birth_attendant" value="Nurse">
                    👩‍⚕️ Nurse
                </label>
                <label>
                    <input type="radio" name="birth_attendant" value="Hilot">
                    🤱 Hilot
                </label>
                <label>
                    <input type="radio" name="birth_attendant" value="Others">
                    Other: <input type="text" name="birth_attendant_others" placeholder="Specify" style="margin-left: 10px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                </label>
            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label for="babys_card">Upload Baby's Card *</label>
                <input type="file" name="babys_card" accept=".jpg,.jpeg,.png,.pdf" title="Upload Baby's Card (JPG, PNG, or PDF)" required>
                <small style="color: #666; margin-top: 5px; display: block;">Accepted formats: JPG, PNG, PDF</small>
            </div>
        </div>

        <!-- Vaccines Section -->
        <div class="vaccine-section">
            <h2 class="section-title">💉 Vaccines Already Received</h2>
            <p style="color: #666; margin-bottom: 20px;">Check all vaccines that your child has already received:</p>
            
            <div class="vaccine-grid">
                <label><input type="checkbox" name="vaccines_received[]" value="BCG"> BCG (Tuberculosis)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="HEPAB1 (w/in 24 hrs)"> HEPAB1 (w/in 24 hrs)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="HEPAB1 (More than 24hrs)"> HEPAB1 (More than 24hrs)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 1st"> Pentavalent (DPT-HepB-Hib) - 1st</label>
                <label><input type="checkbox" name="vaccines_received[]" value="OPV - 1st"> OPV - 1st (Oral Polio)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="PCV - 1st"> PCV - 1st (Pneumococcal)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 1st"> Rota Virus Vaccine - 1st</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 2nd"> Pentavalent (DPT-HepB-Hib) - 2nd</label>
                <label><input type="checkbox" name="vaccines_received[]" value="OPV - 2nd"> OPV - 2nd (Oral Polio)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="PCV - 2nd"> PCV - 2nd (Pneumococcal)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 2nd"> Rota Virus Vaccine - 2nd</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 3rd"> Pentavalent (DPT-HepB-Hib) - 3rd</label>
                <label><input type="checkbox" name="vaccines_received[]" value="OPV - 3rd"> OPV - 3rd (Oral Polio)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="PCV - 3rd"> PCV - 3rd (Pneumococcal)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="MCV1 (AMV)"> MCV1 (AMV) - Anti-Measles Vaccine</label>
                <label><input type="checkbox" name="vaccines_received[]" value="MCV2 (MMR)"> MCV2 (MMR) - Measles-Mumps-Rubella</label>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="submit-section">
            <button type="submit" class="submit-btn">
                📋 Submit Child Health Record Request
            </button>
            <p style="color: #666; margin-top: 15px; font-size: 14px;">
                By submitting this form, you agree to provide accurate information for your child's health record.
            </p>
        </div>

        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
    </form>
</div>
<?php include 'Include/footer.php'; ?>
<script>
document.querySelector('#requestform').addEventListener('submit', function(e) {
    e.preventDefault();
    Request_Immunization();
});

async function Request_Immunization() {
    const formData = new FormData(requestform);
    // const doc = await fetch('../../php/users/request_immunization.php', {
    const doc = await fetch('../../php/supabase/users/request_immunization.php', {
        method: 'POST',
        body: formData
    });
    const data = await doc.json();
    console.log(data);
    
    if (data.status === 'success') {
        let message = 'Child health record saved successfully!\n';
        message += 'Baby ID: ' + data.baby_id + '\n';
        message += 'Total vaccine records created: ' + data.total_records_created + '\n';
        
        if (data.vaccines_transferred > 0) {
            message += 'Vaccines taken: ' + data.vaccines_transferred + '\n';
            message += 'Vaccines scheduled: ' + data.vaccines_scheduled + '\n';
            message += 'Taken vaccines are marked as "taken" status with actual schedule dates.\n';
            message += 'Remaining vaccines are scheduled for future appointments.\n';
        } else {
            message += 'All vaccines scheduled for future appointments.\n';
        }
        
        alert(message);
        requestform.reset();
    } else {
        alert('Error: ' + data.message);
    }
}
</script>
</html>