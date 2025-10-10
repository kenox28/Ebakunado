// Simple stepper logic with validation (moved from create-account.html)
(function () {
  const form = document.getElementById("CreateForm");
  if (!form) return;

  const steps = Array.from(form.querySelectorAll(".form-step"));
  const stepper = document.getElementById("stepper");
  let current = 0;

  const updateUI = () => {
    steps.forEach((s, i) => s.classList.toggle("active", i === current));
    if (stepper) {
      Array.from(stepper.querySelectorAll(".step")).forEach((el, i) => {
        el.classList.toggle("active", i === current);
        el.classList.toggle("completed", i < current);
      });
    }
  };

  const getCurrentFields = () =>
    steps[current].querySelectorAll("input, select, textarea");

  const validateStep = () => {
    const fields = getCurrentFields();
    for (const field of fields) {
      if (!field.checkValidity()) {
        field.reportValidity();
        return false;
      }
    }
    if (steps[current].querySelector("#confirm_password")) {
      const p = form.password.value;
      const c = form.confirm_password.value;
      if (p !== c) {
        form.confirm_password.setCustomValidity("Passwords do not match.");
        form.confirm_password.reportValidity();
        form.confirm_password.setCustomValidity("");
        return false;
      }
    }
    return true;
  };

  form.addEventListener("click", (e) => {
    if ((e.target).classList?.contains("next-btn")) {
      if (!validateStep()) return;
      current = Math.min(current + 1, steps.length - 1);
      updateUI();
    }
    if ((e.target).classList?.contains("prev-btn")) {
      current = Math.max(current - 1, 0);
      updateUI();
    }
  });

  form.addEventListener("submit", (e) => {
    if (!validateStep()) {
      e.preventDefault();
      return;
    }
    e.preventDefault();
    if (window.Swal) {
      Swal.fire({
        icon: "success",
        title: "Account created!",
        timer: 1500,
        showConfirmButton: false,
      });
    }
  });

  updateUI();
})();