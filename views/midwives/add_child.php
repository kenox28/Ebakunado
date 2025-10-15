<?php include 'Include/header.php'; ?>

<style>
    .add-child-page {
        padding: 20px;
        background-color: #f8f9fa;
        min-height: auto;
        width: 100%;
        box-sizing: border-box;
    }

    .add-child-header {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }

    .add-child-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
    }

    .add-child-header p {
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
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

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #1976d2;
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    }

    .radio-group {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .radio-option:hover {
        border-color: #1976d2;
        background: #f3f8ff;
    }

    .radio-option input[type="radio"] {
        margin: 0;
        width: 18px;
        height: 18px;
        accent-color: #1976d2;
    }

    .radio-option.selected {
        border-color: #1976d2;
        background: #e3f2fd;
    }

    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 10px;
        max-height: 400px;
        overflow-y: auto;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
    }

    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafafa;
    }

    .checkbox-option:hover {
        border-color: #1976d2;
        background: #f3f8ff;
    }

    .checkbox-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #1976d2;
    }

    .checkbox-option.checked {
        border-color: #4CAF50;
        background: #e8f5e8;
    }

    .submit-btn {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 20px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .submit-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .result-message {
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        font-weight: 600;
    }

    .result-success {
        background: #e8f5e8;
        color: #2e7d32;
        border: 2px solid #4CAF50;
    }

    .result-error {
        background: #ffebee;
        color: #c62828;
        border: 2px solid #f44336;
    }
</style>

<div class="add-child-page">
    <div class="add-child-header">
        <h1>👶 Add New Child</h1>
        <p>Create a new child record and generate a family code for parents to claim</p>
    </div>

    <form id="addChildForm" class="form-container">
        <!-- Basic Child Information -->
        <div class="form-section">
            <h2 class="section-title">👶 Basic Child Information</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="child_fname">Child First Name *</label>
                    <input type="text" id="child_fname" name="child_fname" placeholder="Enter baby's first name" required>
                </div>
                
                <div class="form-group">
                    <label for="child_lname">Child Last Name *</label>
                    <input type="text" id="child_lname" name="child_lname" placeholder="Enter baby's last name" required>
                </div>
                
                <div class="form-group">
                    <label for="child_birth_date">Birth Date *</label>
                    <input type="date" id="child_birth_date" name="child_birth_date" required>
                </div>
                
                <div class="form-group">
                    <label for="place_of_birth">Place of Birth</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" placeholder="Enter place of birth">
                </div>
                
                <div class="form-group">
                    <label for="child_address">Address *</label>
                    <input type="text" id="child_address" name="child_address" value="<?php echo htmlspecialchars($_SESSION['place'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="birth_weight">Birth Weight (kg)</label>
                    <input type="text" id="birth_weight" name="birth_weight" placeholder="e.g., 3.2">
                </div>
                
                <div class="form-group">
                    <label for="birth_height">Birth Height (cm)</label>
                    <input type="text" id="birth_height" name="birth_height" placeholder="e.g., 50">
                </div>
            </div>

            <!-- Gender Selection -->
            <div class="form-group">
                <label>Baby Gender *</label>
                <div class="radio-group">
                    <label class="radio-option selected">
                        <input type="radio" name="child_gender" value="Male" checked>
                        👦 Male
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="child_gender" value="Female">
                        👧 Female
                    </label>
                </div>
            </div>

            <!-- Parent Information -->
            <div class="form-grid">
                <?php 
                $user_fname = $_SESSION['fname'] ?? '';
                $gender = $_SESSION['gender'] ?? '';
                ?>
                <?php if($gender == 'Male'): ?>
                    <div class="form-group">
                        <label for="father_name">Father Name</label>
                        <input type="text" id="father_name" name="father_name" value="<?php echo htmlspecialchars($user_fname); ?>">
                    </div>
                    <div class="form-group">
                        <label for="mother_name">Mother Name *</label>
                        <input type="text" id="mother_name" name="mother_name" placeholder="Enter mother's name" required>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="mother_name">Mother Name *</label>
                        <input type="text" id="mother_name" name="mother_name" value="<?php echo htmlspecialchars($user_fname); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="father_name">Father Name</label>
                        <input type="text" id="father_name" name="father_name" placeholder="Enter father's name">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Birth Details -->
            <div class="form-group">
                <label>Type of Delivery</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="delivery_type" value="Normal">
                        🏥 Normal
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="delivery_type" value="Caesarean Section">
                        ⚕️ Caesarean Section
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Birth Order</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="birth_order" value="Single">
                        👶 Single
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="birth_order" value="Twin">
                        👶👶 Twin
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Birth Attendant</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="birth_attendant" value="Doctor">
                        👨‍⚕️ Doctor
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="birth_attendant" value="Midwife">
                        👩‍⚕️ Midwife
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="birth_attendant" value="Nurse">
                        👩‍⚕️ Nurse
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="birth_attendant" value="Hilot">
                        🤱 Hilot
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="birth_attendant" value="Others">
                        Other: <input type="text" name="birth_attendant_others" placeholder="Specify" style="margin-left: 10px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; width: 120px;">
                    </label>
                </div>
            </div>
        </div>

        <!-- Vaccines Section -->
        <div class="form-section">
            <h2 class="section-title">💉 Vaccines Already Received</h2>
            <p style="color: #666; margin-bottom: 15px;">Check all vaccines that the child has already received:</p>
            
            <div class="checkbox-grid">
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="BCG">
                    BCG (Tuberculosis)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="HEPAB1 (w/in 24 hrs)">
                    HEPAB1 (w/in 24 hrs)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="HEPAB1 (More than 24hrs)">
                    HEPAB1 (More than 24hrs)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 1st">
                    Pentavalent (DPT-HepB-Hib) - 1st
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="OPV - 1st">
                    OPV - 1st (Oral Polio)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="PCV - 1st">
                    PCV - 1st (Pneumococcal)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 1st">
                    Rota Virus Vaccine - 1st
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 2nd">
                    Pentavalent (DPT-HepB-Hib) - 2nd
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="OPV - 2nd">
                    OPV - 2nd (Oral Polio)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="PCV - 2nd">
                    PCV - 2nd (Pneumococcal)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 2nd">
                    Rota Virus Vaccine - 2nd
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 3rd">
                    Pentavalent (DPT-HepB-Hib) - 3rd
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="OPV - 3rd">
                    OPV - 3rd (Oral Polio)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="PCV - 3rd">
                    PCV - 3rd (Pneumococcal)
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="MCV1 (AMV)">
                    MCV1 (AMV) - Anti-Measles Vaccine
                </label>
                <label class="checkbox-option">
                    <input type="checkbox" name="vaccines_received[]" value="MCV2 (MMR)">
                    MCV2 (MMR) - Measles-Mumps-Rubella
                </label>
            </div>
        </div>
        
        <button type="submit" class="submit-btn">Add Child & Generate Family Code</button>
        
        <div id="resultMessage" class="result-message" style="display: none;"></div>
    </form>
</div>

<script>
// Handle radio button selection styling
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove selected class from all options in the same group
        const groupName = this.name;
        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
            r.closest('.radio-option').classList.remove('selected');
        });
        
        // Add selected class to the checked option
        this.closest('.radio-option').classList.add('selected');
    });
});

// Handle checkbox styling
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            this.closest('.checkbox-option').classList.add('checked');
        } else {
            this.closest('.checkbox-option').classList.remove('checked');
        }
    });
});

// Handle form submission
document.getElementById('addChildForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.querySelector('.submit-btn');
    const resultDiv = document.getElementById('resultMessage');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding Child...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('../../php/supabase/shared/create_family_code.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            resultDiv.className = 'result-message result-success';
            resultDiv.innerHTML = `
                <h3>✅ Child Added Successfully!</h3>
                <p><strong>Family Code:</strong> ${data.family_code}</p>
                <p><strong>Baby ID:</strong> ${data.baby_id}</p>
                <p><strong>Share this link with the parent:</strong></p>
                <p style="background: #f0f0f0; padding: 10px; border-radius: 4px; word-break: break-all;">${data.share_link}</p>
                <p style="margin-top: 15px;"><em>The parent can use the family code to claim this child in their account.</em></p>
            `;
            resultDiv.style.display = 'block';
            
            // Reset form
            this.reset();
            document.querySelectorAll('.radio-option').forEach(option => option.classList.remove('selected'));
            document.querySelectorAll('.checkbox-option').forEach(option => option.classList.remove('checked'));
            
            // Reset first radio button to selected
            document.querySelector('input[type="radio"]').closest('.radio-option').classList.add('selected');
        } else {
            resultDiv.className = 'result-message result-error';
            resultDiv.innerHTML = `<h3>❌ Error</h3><p>${data.message}</p>`;
            resultDiv.style.display = 'block';
        }
    } catch (error) {
        resultDiv.className = 'result-message result-error';
        resultDiv.innerHTML = `<h3>❌ Error</h3><p>Failed to add child: ${error.message}</p>`;
        resultDiv.style.display = 'block';
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Child & Generate Family Code';
    }
});
</script>

<?php include 'Include/footer.php'; ?>
