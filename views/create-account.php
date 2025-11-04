<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account - Ebakunado</title>
  <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
  <link rel="stylesheet" href="../css/fonts.css" />
  <link rel="stylesheet" href="../css/variables.css" />
  <link rel="stylesheet" href="../css/create-account-style.css?v=1.0.3" />
  <link rel="stylesheet" href="/css/queries.css" />

  <!-- SweetAlert2 for better notifications -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="page-create-account">
  <main class="auth-main">
    <a class="back-to-home" href="../views/auth/login.php">
      &larr; Back to Login
    </a>

    <section class="auth-frame">
      <!-- Left Side -->
      <div class="auth-left">
        <header class="auth-header">
          <img
            class="brand-logo"
            src="/ebakunado/assets/images/white-ebakunado-logo-with-label.png"
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
              <select
                class="form-input"
                id="province"
                name="province"
                onchange="loadCities()"
                required>
                <option value="" disabled selected hidden>Select Province</option>
              </select>
            </div>

            <div class="input-group">
              <label class="input-label" for="city_municipality">City/Municipality</label>
              <select
                class="form-input"
                id="city_municipality"
                name="city_municipality"
                onchange="loadBarangays()"
                required>
                <option value="" disabled selected hidden>Select City/Municipality</option>
              </select>
            </div>

            <div class="input-group">
              <label class="input-label" for="barangay">Barangay</label>
              <select
                class="form-input"
                id="barangay"
                name="barangay"
                onchange="loadPuroks()"
                required>
                <option value="" disabled selected hidden>Select Barangay</option>
              </select>
            </div>

            <div class="input-group">
              <label class="input-label" for="purok">Purok</label>
              <select class="form-input" id="purok" name="purok" required>
                <option value="" disabled selected hidden>Select Purok</option>
              </select>
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
              <label class="checkbox">
                <input type="checkbox" id="agree_terms" required />
                <span>I agree to the <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a>.</span>
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

  <script src="/ebakunado/js/auth-handler/password-toggle.js"></script>
  <script src="/ebakunado/js/auth-handler/create-account-stepper.js?v=1.0.1"></script>
  <script src="/ebakunado/js/supabase_js/create_account.js?v=1.0.11"></script>
</body>

</html>