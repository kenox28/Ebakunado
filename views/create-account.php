<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account - Ebakunado</title>
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
  <link rel="stylesheet" href="css/fonts.css" />
  <link rel="stylesheet" href="css/variables.css" />
  <link rel="stylesheet" href="css/create-account-style.css?v=1.0.3" />
  <link rel="stylesheet" href="css/queries.css" />
  <link rel="stylesheet" href="css/modals.css?v=1.0.1" />
  <style>
    .policy-card {
      margin-top: 0.75rem;
    }
    .policy-card-title {
      font-weight: 600;
      color: var(--primary-color, #2f80ed);
      margin-bottom: 0.5rem;
    }
    .policy-content {
      padding: 1rem 1.25rem;
      border-radius: 0.5rem;
      background-color: rgba(47, 128, 237, 0.08);
      border: 1px solid rgba(47, 128, 237, 0.2);
      max-height: 260px;
      overflow-y: auto;
      font-size: 0.95rem;
      line-height: 1.5;
    }
    .policy-list {
      margin: 0;
      padding-left: 1.25rem;
    }
    .policy-list li {
      margin-bottom: 0.65rem;
    }
    .policy-sublist {
      margin-top: 0.35rem;
    }
    .policy-sublist li {
      margin-bottom: 0.25rem;
    }
  </style>
</head>

<body class="page-create-account">
  <main class="auth-main">
    <a class="back-to-home" href="login">
      &larr; Back to Login
    </a>

    <section class="auth-frame">
      <!-- Left Side -->
      <div class="auth-left">
        <header class="auth-header">
          <img
            class="brand-logo"
            src="assets/images/white-ebakunado-logo-with-label.png"
            alt="Ebakunado Logo" />
          <div class="brand-text">
            <h2 class="brand-title">Create Your Parent Account</h2>
            <h2 class="brand-subtitle">It’s quick, easy, and secure</h2>
            <p class="brand-desc">
              Set up an account to manage your child’s immunization schedule and receive timely reminders from Linao Health Center.
            </p>
          </div>
        </header>
        <footer class="auth-footer">
          <p class="copyright-text">
            &copy; 2025 Linao Health Center | eBakunado System
          </p>
        </footer>
      </div>

      <!-- Right Side -->
      <div class="auth-right">
        <header class="create-account-header">
          <h1 class="create-account-title">Create Account</h1>
          <p class="create-account-subtext">Complete the steps to set up your parent account.</p>

          <!-- Stepper -->
          <ol class="stepper" id="stepper">
            <li class="step active" data-step="0">
              <span class="step-index">1</span>
              <span class="step-label">Personal</span>
            </li>
            <li class="step" data-step="1">
              <span class="step-index">2</span>
              <span class="step-label">Address</span>
            </li>
            <li class="step" data-step="2">
              <span class="step-index">3</span>
              <span class="step-label">Security</span>
            </li>
          </ol>
        </header>

        <form action="#" id="CreateForm" class="create-account-form">
          <!-- CSRF Token for security -->
           
          <input type="hidden" name="csrf_token" id="csrf_token" value="" />

          <!-- Step 1: Personal Information -->
          <fieldset class="form-step active" aria-label="Personal Information">
            <h2 class="form-title">Personal Information</h2>

            <div class="form-row-2">
              <div class="input-group">
                <label class="input-label" for="fname">First Name <span class="required">*</span></label>
                <input
                  class="form-input"
                  type="text"
                  name="fname"
                  id="fname"
                  placeholder="Enter first name"
                  required />
              </div>
              <div class="input-group">
                <label class="input-label" for="lname">Last Name <span class="required">*</span></label>
                <input
                  class="form-input"
                  type="text"
                  name="lname"
                  id="lname"
                  placeholder="Enter last name"
                  required />
              </div>
            </div>

            <div class="input-group">
              <label class="input-label" for="email">Email Address <span class="required">*</span></label>
              <input
                class="form-input"
                type="email"
                name="email"
                id="email"
                placeholder="Enter email address"
                required />
            </div>

            <div class="input-group">
              <label class="input-label" for="number">Phone Number <span class="required">*</span></label>
              <input
                class="form-input"
                type="tel"
                name="number"
                id="number"
                placeholder="09xxxxxxxxx"
                pattern="^09\d{9}$"
                required />
            </div>

            <div class="input-group">
              <label class="input-label" for="gender">Gender</label>
              <select class="form-input" id="gender" name="gender">
                <option value="" disabled selected hidden>Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div class="step-actions">
              <button type="button" class="btn btn-primary next-btn">
                Next
              </button>
            </div>
          </fieldset>

          <!-- Step 2: Address Information -->
          <fieldset class="form-step" aria-label="Address Information">
            <h2 class="form-title">Address Information</h2>

            <div class="input-group">
              <label class="input-label" for="province">Province</label>
              <input
                class="form-input"
                type="text"
                id="province"
                name="province"
                placeholder="Enter province"
                required />
            </div>

            <div class="input-group">
              <label class="input-label" for="city_municipality">City/Municipality</label>
              <input
                class="form-input"
                type="text"
                id="city_municipality"
                name="city_municipality"
                placeholder="Enter city or municipality"
                required />
            </div>

            <div class="input-group">
              <label class="input-label" for="barangay">Barangay</label>
              <input
                class="form-input"
                type="text"
                id="barangay"
                name="barangay"
                placeholder="Enter barangay"
                required />
            </div>

            <div class="input-group">
              <label class="input-label" for="purok">Purok</label>
              <input
                class="form-input"
                type="text"
                id="purok"
                name="purok"
                placeholder="Enter purok"
                required />
            </div>

            <div class="step-actions">
              <button type="button" class="btn btn-outline prev-btn">
                Previous
              </button>
              <button type="button" class="btn btn-primary next-btn">
                Next
              </button>
            </div>
          </fieldset>

          <!-- Step 3: Account Security -->
          <fieldset class="form-step" aria-label="Account Security">
            <h2 class="form-title">Account Security</h2>

            <div class="input-group password-group">
              <label class="input-label" for="password">Password <span class="required">*</span></label>
              <div class="password-wrapper">
                <input
                  class="form-input password-input"
                  type="password"
                  name="password"
                  id="password"
                  placeholder="Enter password"
                  minlength="8"
                  required />
                <span class="material-symbols-rounded password-toggle">visibility_off</span>
              </div>
              <!-- Password Requirements List -->
              <div id="passwordRequirements" class="password-requirements" style="display: none;">
                <div class="requirement-item" id="req-length">
                  <span class="requirement-icon"></span>
                  <span class="requirement-text">At least 8 characters</span>
                </div>
                <div class="requirement-item" id="req-uppercase">
                  <span class="requirement-icon"></span>
                  <span class="requirement-text">1 uppercase letter</span>
                </div>
                <div class="requirement-item" id="req-lowercase">
                  <span class="requirement-icon"></span>
                  <span class="requirement-text">1 lowercase letter</span>
                </div>
                <div class="requirement-item" id="req-number">
                  <span class="requirement-icon"></span>
                  <span class="requirement-text">1 number</span>
                </div>
                <div class="requirement-item" id="req-special">
                  <span class="requirement-icon"></span>
                  <span class="requirement-text">1 special character</span>
                </div>
              </div>
            </div>

            <div class="input-group password-group">
              <label class="input-label" for="confirm_password">Confirm Password <span class="required">*</span></label>
              <div class="password-wrapper">
                <input
                  class="form-input password-input"
                  type="password"
                  name="confirm_password"
                  id="confirm_password"
                  placeholder="Confirm password"
                  required />
                <span class="material-symbols-rounded password-toggle">visibility_off</span>
              </div>
            </div>

            <div class="input-group terms-agree">
              <div class="policy-card">
                <div class="policy-card-title">Privacy Policy & Terms of Service</div>
                <div class="policy-content">
                  <ul class="policy-list">
                    <li>I am the parent or legal guardian of the child whose information will be entered in this system managed by Linao Health Center Ormoc City.</li>
                    <li>I voluntarily give consent for the collection, storage, and processing of my child's personal and health information, including immunization records, following the Data Privacy Act of 2012 (RA 10173).</li>
                    <li>I understand that my child’s data will be used only for immunization services, such as scheduling, tracking, and verifying vaccination status.</li>
                    <li>I consent that the data may be shared only with authorized health workers of Linao Health Center Ormoc City, the Local Government Unit (LGU), or the Department of Health (DOH) for legitimate health service purposes.</li>
                    <li>I understand that my child’s data will NOT be shared with unauthorized persons, agencies, or for any purpose not related to healthcare.</li>
                    <li>
                      I have the right to:
                      <ul class="policy-sublist">
                        <li>Access my child’s records</li>
                        <li>Correct inaccurate information</li>
                        <li>Withdraw consent at any time</li>
                      </ul>
                    </li>
                    <li>I understand that withdrawing my consent may limit the ability of Linao Health Center Ormoc City to provide full immunization services.</li>
                  </ul>
                </div>
              </div>
              <label class="checkbox">
                <input type="checkbox" id="agree_terms" name="agree_terms" value="yes" required />
                <span>I agree to the Privacy Policy and Terms of Service of Linao Health Center</span>
              </label>
            </div>

            <div class="step-actions">
              <button type="button" class="btn btn-outline prev-btn">Previous</button>
              <button class="btn btn-primary submit-btn" type="submit">Create Account</button>
            </div>
          </fieldset>
        </form>

        <footer class="footer-note">
          <p>Note: This setup account is for parents only.</p>
        </footer>
      </div>
    </section>
  </main>

  <script src="js/auth-handler/password-toggle.js"></script>
  <script src="js/auth-handler/create-account-stepper.js?v=1.0.1"></script>
  <script src="js/utils/ui-feedback.js"></script>
  <script src="js/supabase_js/create_account.js?v=1.0.12"></script>
</body>

</html>