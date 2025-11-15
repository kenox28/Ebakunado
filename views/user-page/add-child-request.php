<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}


// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Child Request</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css?v=1.0.1" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/user/add-child-request.css?v=1.0.1" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400;700" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="add-child-section">
            <div class="add-child-header">
                <div class="add-child-header-content">
                    <div class="add-child-header-text">
                        <h1>Child Health Record Request</h1>
                        <p>Please fill out all required information for your child's health record</p>
                    </div>
                </div>
            </div>

            <div class="family-code-section">
                <h2 class="section-title">
                    <span class="material-symbols-rounded">qr_code_2</span>
                    <span>Have a Family Code?</span>
                </h2>
                <p>Enter the code given by your BHW/Midwife to add your child</p>

                <div>
                    <input type="text" id="familyCode" placeholder="Enter family code (e.g., FAM-ABC123)">
                    <button onclick="claimChildWithCode()">Claim Child</button>
                </div>

                <div id="familyCodeResult"></div>
            </div>

            <div class="divider">
                <span>OR</span>
            </div>

            <form class="form-container" id="requestform" method="post" enctype="multipart/form-data">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="material-symbols-rounded">person</span>
                        <span>Basic Child Information</span>
                    </h2>

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
                    <div class="form-group">
                        <label>Baby Gender *</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="child_gender" value="Male" required>
                                Male
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="child_gender" value="Female" required>
                                Female
                            </label>
                        </div>
                    </div>

                    <!-- Parent Information -->
                    <div class="form-grid">
                        <?php if ($gender == 'Male'): ?>
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
                    <div class="form-group">
                        <label class="radio-label">Type of Delivery</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="delivery_type" value="Normal">
                                Normal
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="delivery_type" value="Caesarean Section">
                                Caesarean Section
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="radio-label">Birth Order</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="birth_order" value="Single">
                                Single
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="birth_order" value="Twin">
                                Twin
                            </label>
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

                        <!-- File Upload -->
                        <div class="form-group" id="fileUploadGroup">
                            <label>Upload Baby's Card *</label>
                            <div class="upload-card">
                                <div class="upload-dropzone" id="uploadDropzone" role="region" aria-label="Drag & Drop or Click to Upload a file; accepted formats JPG, PNG, PDF">
                                     <span class="material-symbols-rounded">cloud_upload</span>
                                    <div class="upload-text">
                                        <strong>Drag & Drop or Click to Upload</strong>
                                        <span>or press Enter/Space to browse from your device</span>
                                        <button type="button" id="triggerFileSelect" class="upload-btn">
                                            <span class="material-symbols-rounded">upload</span>
                                            Choose file
                                        </button>
                                        <span class="upload-accepted">Accepted formats: JPG, PNG, PDF (Max size: 5MB)</span>
                                    </div>
                                </div>
                                <!-- Actual file input (hidden) -->
                                <input type="file" id="babys_card" name="babys_card" accept=".jpg,.jpeg,.png,.pdf" class="visually-hidden" required>

                                <!-- Upload Preview -->
                                <div id="uploadPreview" class="upload-preview is-hidden" aria-live="polite">
                                    <div class="upload-preview__thumb">
                                        <img id="uploadPreviewImg" alt="Selected file preview" class="is-hidden" />
                                        <div id="uploadPreviewPlaceholder" class="upload-preview__placeholder">
                                            <span id="uploadPreviewIcon" class="material-symbols-rounded">insert_drive_file</span>
                                        </div>
                                    </div>
                                    <div class="upload-preview__meta">
                                        <div class="name" id="uploadPreviewName"></div>
                                        <div class="size" id="uploadPreviewSize"></div>
                                    </div>
                                    <button type="button" id="removeSelectedFile" class="remove-file is-hidden" aria-label="Remove file"><span class="material-symbols-rounded">close</span></button>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccines Section -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <span class="material-symbols-rounded">vaccines</span>
                                <span>Vaccines Already Received</span>
                            </h2>
                            <p>Check all vaccines that your child has already received:</p>

                            <div class="checkbox-grid">
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="BCG"> BCG (Tuberculosis)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Hepatitis B"> Hepatitis B (Birth dose)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 1st"> Pentavalent (DPT-HepB-Hib) - 1st</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 1st"> OPV - 1st (Oral Polio)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 1st"> PCV - 1st (Pneumococcal)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 2nd"> Pentavalent (DPT-HepB-Hib) - 2nd</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 2nd"> OPV - 2nd (Oral Polio)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 2nd"> PCV - 2nd (Pneumococcal)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="Pentavalent (DPT-HepB-Hib) - 3rd"> Pentavalent (DPT-HepB-Hib) - 3rd</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="OPV - 3rd"> OPV - 3rd (Oral Polio)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="PCV - 3rd"> PCV - 3rd (Pneumococcal)</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="MCV1 (AMV)"> MCV1 (AMV) - Anti-Measles Vaccine</label>
                                <label class="checkbox-option"><input type="checkbox" name="vaccines_received[]" value="MCV2 (MMR)"> MCV2 (MMR) - Measles-Mumps-Rubella</label>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="submit-section">
                            <button type="submit" class="submit-btn">
                                Submit Child Health Record Request
                            </button>
                            <p>By submitting this form, you agree to provide accurate information for your child's health record.</p>
                        </div>

                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    </div>
                </div>
            </form>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        // Form submit
        document.getElementById('requestform').addEventListener('submit', function(e) {
            e.preventDefault();
            Request_Immunization();
        });

        // Upload UI wiring
        (function() {
            const fileInput = document.getElementById('babys_card');
            const dropzone = document.getElementById('uploadDropzone');
            const triggerBtn = document.getElementById('triggerFileSelect');
            const removeBtn = document.getElementById('removeSelectedFile');
            const previewWrap = document.getElementById('uploadPreview');
            const previewImg = document.getElementById('uploadPreviewImg');
            const previewPlaceholder = document.getElementById('uploadPreviewPlaceholder');
            const previewIcon = document.getElementById('uploadPreviewIcon');
            const previewName = document.getElementById('uploadPreviewName');
            const previewSize = document.getElementById('uploadPreviewSize');
            let previewURL = null;

            function updateFilename() {
                const hasFile = !!(fileInput.files && fileInput.files[0]);
                if (hasFile) {
                    if (removeBtn) removeBtn.classList.remove('is-hidden');
                    updatePreview(fileInput.files[0]);
                } else {
                    if (removeBtn) removeBtn.classList.add('is-hidden');
                    clearPreview();
                }
            }

            function formatBytes(bytes) {
                if (!bytes && bytes !== 0) return '';
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = bytes === 0 ? 0 : Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + sizes[i];
            }

            function updatePreview(file) {
                if (!file) {
                    clearPreview();
                    return;
                }
                previewWrap.classList.remove('is-hidden');
                previewName.textContent = file.name;
                previewSize.textContent = formatBytes(file.size);

                const isImage = file.type && file.type.startsWith('image/');
                if (previewURL) {
                    URL.revokeObjectURL(previewURL);
                    previewURL = null;
                }

                if (isImage) {
                    previewURL = URL.createObjectURL(file);
                    previewImg.src = previewURL;
                    previewImg.classList.remove('is-hidden');
                    previewPlaceholder.classList.add('is-hidden');
                } else {
                    // non-image: show placeholder icon (PDF has special icon)
                    const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
                    previewIcon.textContent = isPdf ? 'picture_as_pdf' : 'insert_drive_file';
                    previewImg.removeAttribute('src');
                    previewImg.classList.add('is-hidden');
                    previewPlaceholder.classList.remove('is-hidden');
                }
            }

            function clearPreview() {
                if (previewURL) {
                    URL.revokeObjectURL(previewURL);
                    previewURL = null;
                }
                previewImg.removeAttribute('src');
                previewImg.classList.add('is-hidden');
                previewPlaceholder.classList.remove('is-hidden');
                previewName.textContent = '';
                previewSize.textContent = '';
                previewWrap.classList.add('is-hidden');
            }

            triggerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.click();
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fileInput.value = '';
                    updateFilename();
                });
            }

            fileInput.addEventListener('change', updateFilename);
            updateFilename();

            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });
            // Clicking anywhere on the dropzone opens the file dialog
            dropzone.addEventListener('click', function(e) {
                // prevent duplicate triggers when clicking the explicit Browse button or remove-file
                if (e.target.closest('#triggerFileSelect') || e.target.closest('#removeSelectedFile')) return;
                if (fileInput.hasAttribute('disabled')) return;
                fileInput.click();
            });

            // Make dropzone focusable and keyboard-activatable
            dropzone.setAttribute('tabindex', '0');
            dropzone.addEventListener('keydown', function(e) {
                if ((e.key === 'Enter' || e.key === ' ') && !fileInput.hasAttribute('disabled')) {
                    e.preventDefault();
                    fileInput.click();
                }
            });

            dropzone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    fileInput.files = dt.files;
                    updateFilename();
                }
            });
        })();

        async function Request_Immunization() {
            const formEl = document.getElementById('requestform');
            const fileInput = document.getElementById('babys_card');
            const fileGroup = document.getElementById('fileUploadGroup');
            const formData = new FormData(formEl);

            // Start loading
            fileGroup.classList.add('file-upload-loading');
            fileInput.setAttribute('disabled', 'disabled');

            try {
                const data = await new Promise(function(resolve, reject) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/ebakunado/php/supabase/users/request_immunization.php');

                    xhr.onload = function() {
                        try {
                            const json = JSON.parse(xhr.responseText || '{}');
                            resolve(json);
                        } catch (err) {
                            reject(new Error('Invalid server response'));
                        }
                    };

                    xhr.onerror = function() {
                        reject(new Error('Network error'));
                    };

                    xhr.send(formData);
                });

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
                    formEl.reset();
                    // reset file UI explicitly since reset may not trigger change
                    (function() {
                        const fileInput = document.getElementById('babys_card');
                        const removeBtn = document.getElementById('removeSelectedFile');
                        const previewWrap = document.getElementById('uploadPreview');
                        const previewImg = document.getElementById('uploadPreviewImg');
                        const previewPlaceholder = document.getElementById('uploadPreviewPlaceholder');
                        const previewName = document.getElementById('uploadPreviewName');
                        const previewSize = document.getElementById('uploadPreviewSize');
                        fileInput.value = '';
                        if (removeBtn) {
                            removeBtn.classList.add('is-hidden');
                        }
                        if (previewWrap) {
                            previewWrap.classList.add('is-hidden');
                        }
                        if (previewImg) {
                            previewImg.removeAttribute('src');
                            previewImg.classList.add('is-hidden');
                        }
                        if (previewPlaceholder) {
                            previewPlaceholder.classList.remove('is-hidden');
                        }
                        if (previewName) previewName.textContent = '';
                        if (previewSize) previewSize.textContent = '';
                    })();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Upload failed: ' + error.message);
            } finally {
                // End loading
                fileGroup.classList.remove('file-upload-loading');
                fileInput.removeAttribute('disabled');
            }
        }

        // Family Code Claiming Function
        async function claimChildWithCode() {
            const familyCode = document.getElementById('familyCode').value.trim();
            const resultDiv = document.getElementById('familyCodeResult');

            if (!familyCode) {
                resultDiv.innerHTML = '<div class="alert alert-error">Please enter a family code</div>';
                return;
            }

            resultDiv.innerHTML = '<div class="alert alert-info">Checking code...</div>';

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
                <div class="alert alert-success">
                    <h4 class="alert-title">Child added successfully</h4>
                    <p><strong>Child:</strong> ${data.child_name}</p>
                    <p><strong>Baby ID:</strong> ${data.baby_id}</p>
                    <p>The child has been added to your account. You can now view their records in your dashboard.</p>
                </div>
            `;
                    document.getElementById('familyCode').value = '';

                    setTimeout(() => {
                        window.location.href = 'children_list.php';
                    }, 3000);
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-error">Error: ${error.message}</div>`;
            }
        }
    </script>
</body>

</html>