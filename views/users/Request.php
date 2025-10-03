<?php include 'Include/header.php'; ?>

    <form id="requestform" method="post" enctype="multipart/form-data">
        <section class="form-section">
            <div class="form-container">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="text" name="child_fname" placeholder="Baby First Name" required value="sample ">
                <input type="text" name="child_lname" placeholder="Baby Last Name" required value="sample">
                <label for="child_gender">Baby Gender</label>
                <label for="child_gender">Male</label>
                <input type="radio" name="child_gender" placeholder="Baby Gender" value="Male" required checked>
                <label for="child_gender">Female</label>
                <input type="radio" name="child_gender" placeholder="Baby Gender" value="Female" required>
                <input type="date" name="child_birth_date" placeholder="Baby Birth Date" required value="2025-01-01">
                <input type="text" name="place_of_birth" placeholder="Place of Birth" value="sample">
                <?php if($gender == 'Male'): ?>
                    <input type="text" name="father_name" placeholder="Father Name" value="<?php echo $user_fname; ?>">
                    <input type="text" name="mother_name" placeholder="Mother Name" required value="sample">
            
                <?php else: ?>
                    <input type="text" name="mother_name" value="<?php echo $user_fname; ?>" required>
                    <input type="text" name="father_name" placeholder="Father Name" value="sample">
                <?php endif; ?>
                <input type="text" name="child_address" placeholder="Baby Address" value="<?php echo $place; ?>" required>
                <input type="text" name="birth_weight" placeholder="Baby Birth Weight" value="sample">
                <input type="text" name="birth_height" placeholder="Baby Birth Height" value="sample">
                
                <h3>Child History</h3>
                
                <div class="radio-group">
                    <label>Type of Delivery</label>
                    <label><input type="radio" name="delivery_type" value="Normal"> Normal</label>
                    <label><input type="radio" name="delivery_type" value="Caesarean Section"> Caesarean Section</label>
                </div>
                
                <div class="radio-group">
                    <label>Birth Order</label>
                    <label><input type="radio" name="birth_order" value="Single"> Single</label>
                    <label><input type="radio" name="birth_order" value="Twin"> Twin</label>
                </div>
                
                <div class="radio-group">
                    <label>Birth Attendant</label>
                    <label><input type="radio" name="birth_attendant" value="Doctor"> Doctor</label>
                    <label><input type="radio" name="birth_attendant" value="Midwife"> Midwife</label>
                    <label><input type="radio" name="birth_attendant" value="Nurse"> Nurse</label>
                    <label><input type="radio" name="birth_attendant" value="Hilot"> Hilot</label>
                    <label><input type="radio" name="birth_attendant" value="Others"> Others: <input type="text" name="birth_attendant_others" placeholder="Specify"></label>
                </div>
                
                <input type="file" name="babys_card" accept=".jpg,.jpeg,.png,.pdf" title="Upload Baby's Card (JPG, PNG, or PDF)">
            </div>

            
            <div class="vaccine-checkboxes">
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
        </section>
        <button type="submit">Request</button>
        
    </form>
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