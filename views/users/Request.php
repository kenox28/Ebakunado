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

    <!-- Family Code Entry Section -->
    <div class="family-code-section" style="background: #e8f5e8; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #4CAF50;">
        <h3 style="margin: 0 0 15px 0; color: #2e7d32;">🏷️ Have a Family Code?</h3>
        <p style="margin: 0 0 15px 0; color: #666;">Enter the code given by your BHW/Midwife to add your child</p>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="text" id="familyCode" placeholder="Enter family code (e.g., FAM-ABC123)" style="flex: 1; padding: 10px; border: 2px solid #4CAF50; border-radius: 5px; font-size: 16px;">
            <button onclick="claimChildWithCode()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Claim Child</button>
        </div>
        
        <div id="familyCodeResult" style="margin-top: 10px;"></div>
    </div>

    <div class="or-divider" style="text-align: center; margin: 30px 0; position: relative;">
        <hr style="border: none; border-top: 2px solid #ddd; margin: 0;">
        <span style="background: white; padding: 0 20px; color: #666; font-weight: bold;">OR</span>
    </div>

    <form id="requestform" method="post" enctype="multipart/form-data">
        <!-- Basic Information Section -->
        <div class="form-container">
            <h2 class="section-title">👶 Basic Child Information</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="child_fname">Baby First Name *</label>
                    <input value="example" type="text" name="child_fname" placeholder="Enter baby's first name" required>
                </div>
                
                <div class="form-group">
                    <label for="child_lname">Baby Last Name *</label>
                    <input value="example" type="text" name="child_lname" placeholder="Enter baby's last name" required>
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
                    <input value="<?php echo $place; ?>" type="text" name="child_address" value="<?php echo $place; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="birth_weight">Birth Weight (kg)</label>
                    <input type="text" name="birth_weight" placeholder="e.g., 3.2">
                </div>
                
                <div class="form-group">
                    <label for="birth_height">Birth Height (cm)</label>
                    <input type="text" name="birth_height" placeholder="e.g., 50">
                </div>
            <div class="form-group">
                <label for="blood_type">Blood Type</label>
                <input type="text" name="blood_type" placeholder="e.g., O+">
            </div>
            <div class="form-group">
                <label for="allergies">Allergies</label>
                <input type="text" name="allergies" placeholder="e.g., None">
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
                        <input value="example" type="text" name="mother_name" placeholder="Enter mother's name" required>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="mother_name">Mother Name *</label>
                        <input value="example" type="text" name="mother_name" value="<?php echo $user_fname; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="father_name">Father Name</label>
                        <input value="example" type="text" name="father_name" placeholder="Enter father's name">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="lpm">LMP (Last Menstrual Period)</label>
                    <input type="date" name="lpm">
                </div>
                <div class="form-group">
                    <label for="family_planning">Family Planning</label>
                    <input type="text" name="family_planning" placeholder="e.g., Natural, Pills, IUD">
                </div>
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
                <label><input type="checkbox" name="vaccines_received[]" value="Hepatitis B"> Hepatitis B (Birth dose)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 1st"> Pentavalent (DPT-HepB-Hib) - 1st</label>
                <label><input type="checkbox" name="vaccines_received[]" value="OPV - 1st"> OPV - 1st (Oral Polio)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="PCV - 1st"> PCV - 1st (Pneumococcal)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 2nd"> Pentavalent (DPT-HepB-Hib) - 2nd</label>
                <label><input type="checkbox" name="vaccines_received[]" value="OPV - 2nd"> OPV - 2nd (Oral Polio)</label>
                <label><input type="checkbox" name="vaccines_received[]" value="PCV - 2nd"> PCV - 2nd (Pneumococcal)</label>
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
    // const doc = await fetch('/ebakunado/php/users/request_immunization.php', {
    const doc = await fetch('/ebakunado/php/supabase/users/request_immunization.php', {
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

// Family Code Claiming Function
async function claimChildWithCode() {
    const familyCode = document.getElementById('familyCode').value.trim();
    const resultDiv = document.getElementById('familyCodeResult');
    
    if (!familyCode) {
        resultDiv.innerHTML = '<div style="color: #f44336; padding: 10px; background: #ffebee; border-radius: 4px;">Please enter a family code</div>';
        return;
    }
    
    resultDiv.innerHTML = '<div style="color: #1976d2; padding: 10px; background: #e3f2fd; border-radius: 4px;">Checking code...</div>';
    
    try {
        const formData = new FormData();
        formData.append('family_code', familyCode);
        
        const response = await fetch('/ebakunado/php/supabase/users/claim_child_with_code.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultDiv.innerHTML = `
                <div style="color: #2e7d32; padding: 15px; background: #e8f5e8; border-radius: 4px;">
                    <h4 style="margin: 0 0 10px 0;">✅ Child Added Successfully!</h4>
                    <p style="margin: 0 0 5px 0;"><strong>Child:</strong> ${data.child_name}</p>
                    <p style="margin: 0 0 5px 0;"><strong>Baby ID:</strong> ${data.baby_id}</p>
                    <p style="margin: 0;">The child has been added to your account. You can now view their records in your dashboard.</p>
                </div>
            `;
            document.getElementById('familyCode').value = '';
            
            // Redirect to children list after 3 seconds
            setTimeout(() => {
                window.location.href = 'children_list.php';
            }, 3000);
        } else {
            resultDiv.innerHTML = `<div style="color: #f44336; padding: 10px; background: #ffebee; border-radius: 4px;">❌ ${data.message}</div>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div style="color: #f44336; padding: 10px; background: #ffebee; border-radius: 4px;">❌ Error: ${error.message}</div>`;
    }
}
</script>
</html>