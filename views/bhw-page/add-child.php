<?php session_start(); ?>
<?php
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_types = $_SESSION['user_type'];
$user_name = $_SESSION['fname'] ?? 'User';
$user_fullname = ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '');
if ($user_types != 'midwifes') {
    $user_type = 'Barangay Health Worker';
} else {
    $user_type = 'Midwife';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Child</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/bhw/add-child.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main>
        <section class="add-child-section">
            <div class="add-child-header">
                <div class="add-child-header-content">
                    <div class="add-child-header-text">
                        <h1>Add New Child</h1>
                        <p>Create a new child record and generate a family code for parents to claim</p>
                    </div>
                </div>
            </div>

            <form id="addChildForm" class="form-container">
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="material-symbols-rounded">person</span>
                        <span>Basic Child Information</span>
                    </h2>
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
                            <label for="province">Province *</label>
                            <select id="province" name="province" required onchange="loadCities()">
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city_municipality">City/Municipality *</label>
                            <select id="city_municipality" name="city_municipality" required onchange="loadBarangays()">
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="barangay">Barangay *</label>
                            <select id="barangay" name="barangay" required onchange="loadPuroks()">
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purok">Purok</label>
                            <select id="purok" name="purok">
                                <option value="">Select Purok</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="birth_weight">Birth Weight (kg)</label>
                            <input type="text" id="birth_weight" name="birth_weight" placeholder="e.g., 3.2">
                        </div>
                        <div class="form-group">
                            <label for="birth_height">Birth Height (cm)</label>
                            <input type="text" id="birth_height" name="birth_height" placeholder="e.g., 50">
                        </div>
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <input type="text" id="blood_type" name="blood_type" placeholder="e.g., O+">
                        </div>
                        <div class="form-group">
                            <label for="allergies">Allergies</label>
                            <input type="text" id="allergies" name="allergies" placeholder="e.g., None">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Baby Gender *</label>
                        <div class="radio-group">
                            <label class="radio-option selected">
                                <input type="radio" name="child_gender" value="Male" checked>
                                Male
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="child_gender" value="Female">
                                Female
                            </label>
                        </div>
                    </div>

                    <div class="form-grid">
                        <?php
                        $user_fname = $_SESSION['fname'] ?? '';
                        $gender = $_SESSION['gender'] ?? '';
                        ?>
                        <?php if ($gender == 'Male'): ?>
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

                    <div class="form-group">
                        <label>Type of Delivery</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="delivery_type" value="Normal"> Normal</label>
                            <label class="radio-option"><input type="radio" name="delivery_type" value="Caesarean Section"> Caesarean Section</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Birth Order</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="birth_order" value="Single"> Single</label>
                            <label class="radio-option"><input type="radio" name="birth_order" value="Twin"> Twin</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Birth Attendant</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="birth_attendant" value="Doctor"> Doctor</label>
                            <label class="radio-option"><input type="radio" name="birth_attendant" value="Midwife"> Midwife</label>
                            <label class="radio-option"><input type="radio" name="birth_attendant" value="Nurse"> Nurse</label>
                            <label class="radio-option"><input type="radio" name="birth_attendant" value="Hilot"> Hilot</label>
                            <label class="radio-option">
                                <span>Other:</span>
                                <input type="text" name="birth_attendant_others" placeholder="Specify">
                            </label>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="lpm">LMP (Last Menstrual Period)</label>
                            <input type="date" id="lpm" name="lpm">
                        </div>
                        <div class="form-group">
                            <label for="family_planning">Family Planning</label>
                            <input type="text" id="family_planning" name="family_planning" placeholder="e.g., Natural, Pills, IUD">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">
                        <span class="material-symbols-rounded">vaccines</span>
                        <span>Vaccines Already Received</span>
                    </h2>
                    <p>Check all vaccines that the child has already received:</p>

                    <div class="checkbox-grid">
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="BCG"> BCG (Tuberculosis)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="HEPAB1 (w/in 24 hrs)"> HEPAB1 (w/in 24 hrs)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="HEPAB1 (More than 24hrs)"> HEPAB1 (More than 24hrs)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 1st"> Pentavalent (DPT-HepB-Hib) - 1st</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 1st"> OPV - 1st (Oral Polio)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 1st"> PCV - 1st (Pneumococcal)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 1st"> Rota Virus Vaccine - 1st</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 2nd"> Pentavalent (DPT-HepB-Hib) - 2nd</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 2nd"> OPV - 2nd (Oral Polio)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 2nd"> PCV - 2nd (Pneumococcal)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Rota Virus Vaccine - 2nd"> Rota Virus Vaccine - 2nd</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 3rd"> Pentavalent (DPT-HepB-Hib) - 3rd</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 3rd"> OPV - 3rd (Oral Polio)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 3rd"> PCV - 3rd (Pneumococcal)</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="MCV1 (AMV)"> MCV1 (AMV) - Anti-Measles Vaccine</label>
                        <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="MCV2 (MMR)"> MCV2 (MMR) - Measles-Mumps-Rubella</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="material-symbols-rounded">person_add</span>
                    Add Child & Generate Family Code
                </button>
                <div id="resultMessage" class="result-message"></div>
            </form>
        </section>

        <script src="../../js/header-handler/profile-menu.js" defer></script>
        <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
        <script>
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const groupName = this.name;
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                        r.closest('.radio-option').classList.remove('selected');
                    });
                    this.closest('.radio-option').classList.add('selected');
                });
            });

            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.closest('.checkbox-option').classList.add('checked');
                    } else {
                        this.closest('.checkbox-option').classList.remove('checked');
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                loadProvinces();
            });

            async function loadProvinces() {
                try {
                    const response = await fetch('/ebakunado/php/supabase/admin/get_places.php?type=provinces');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const provinces = await response.json();
                    const provinceSelect = document.getElementById("province");
                    provinceSelect.innerHTML = '<option value="">Select Province</option>';
                    if (Array.isArray(provinces)) {
                        provinces.forEach((provinceObj) => {
                            const option = document.createElement("option");
                            option.value = provinceObj.province;
                            option.textContent = provinceObj.province;
                            provinceSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error("Error loading provinces:", error);
                }
            }

            async function loadCities() {
                const province = document.getElementById("province").value;
                const citySelect = document.getElementById("city_municipality");
                const barangaySelect = document.getElementById("barangay");
                const purokSelect = document.getElementById("purok");
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                purokSelect.innerHTML = '<option value="">Select Purok</option>';
                if (!province) return;
                try {
                    const response = await fetch(`/ebakunado/php/supabase/admin/get_places.php?type=cities&province=${encodeURIComponent(province)}`);
                    const cities = await response.json();
                    cities.forEach((cityObj) => {
                        const option = document.createElement("option");
                        option.value = cityObj.city_municipality;
                        option.textContent = cityObj.city_municipality;
                        citySelect.appendChild(option);
                    });
                } catch (error) {
                    console.error("Error loading cities:", error);
                }
            }

            async function loadBarangays() {
                const province = document.getElementById("province").value;
                const city = document.getElementById("city_municipality").value;
                const barangaySelect = document.getElementById("barangay");
                const purokSelect = document.getElementById("purok");
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                purokSelect.innerHTML = '<option value="">Select Purok</option>';
                if (!province || !city) return;
                try {
                    const response = await fetch(`/ebakunado/php/supabase/admin/get_places.php?type=barangays&province=${encodeURIComponent(province)}&city_municipality=${encodeURIComponent(city)}`);
                    const barangays = await response.json();
                    barangays.forEach((barangayObj) => {
                        const option = document.createElement("option");
                        option.value = barangayObj.barangay;
                        option.textContent = barangayObj.barangay;
                        barangaySelect.appendChild(option);
                    });
                } catch (error) {
                    console.error("Error loading barangays:", error);
                }
            }

            async function loadPuroks() {
                const province = document.getElementById("province").value;
                const city = document.getElementById("city_municipality").value;
                const barangay = document.getElementById("barangay").value;
                const purokSelect = document.getElementById("purok");
                purokSelect.innerHTML = '<option value="">Select Purok</option>';
                if (!province || !city || !barangay) return;
                try {
                    const response = await fetch(`/ebakunado/php/supabase/admin/get_places.php?type=puroks&province=${encodeURIComponent(province)}&city_municipality=${encodeURIComponent(city)}&barangay=${encodeURIComponent(barangay)}`);
                    const puroks = await response.json();
                    puroks.forEach((purokObj) => {
                        const option = document.createElement("option");
                        option.value = purokObj.purok;
                        option.textContent = purokObj.purok;
                        purokSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error("Error loading puroks:", error);
                }
            }

            document.getElementById('addChildForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = document.querySelector('.submit-btn');
                const resultDiv = document.getElementById('resultMessage');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Adding Child...';
                try {
                    const formData = new FormData(this);
                    const response = await fetch('/ebakunado/php/supabase/shared/create_family_code.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        resultDiv.className = 'result-message result-success';
                        let qrMessage = data.qr_code ? '<p style="color: #28a745; font-weight: bold;">🎯 QR Code generated successfully!</p>' : '';
                        resultDiv.innerHTML = `
                <h3>✅ Child Added Successfully!</h3>
                <p><strong>Family Code:</strong> ${data.family_code}</p>
                <p><strong>Baby ID:</strong> ${data.baby_id}</p>
                ${qrMessage}
                <p><strong>Share this link with the parent:</strong></p>
                <p style="background: #f0f0f0; padding: 10px; border-radius: 4px; word-break: break-all;">${data.share_link}</p>
                <p style="margin-top: 15px;"><em>The parent can use the family code to claim this child in their account.</em></p>
            `;
                        resultDiv.style.display = 'block';
                        this.reset();
                        document.querySelectorAll('.radio-option').forEach(option => option.classList.remove('selected'));
                        document.querySelectorAll('.checkbox-option').forEach(option => option.classList.remove('checked'));
                        document.getElementById('province').innerHTML = '<option value="">Select Province</option>';
                        document.getElementById('city_municipality').innerHTML = '<option value="">Select City/Municipality</option>';
                        document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
                        document.getElementById('purok').innerHTML = '<option value="">Select Purok</option>';
                        loadProvinces();
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
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Child & Generate Family Code';
                }
            });
        </script>
    </main>
</body>

</html>