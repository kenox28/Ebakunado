// Password visibility toggle functionality
document
  .querySelector(".password-toggle")
  .addEventListener("click", function () {
    const passwordInput = document.querySelector(".password-input");
    const toggleIcon = this;

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleIcon.textContent = "visibility";
    } else {
      passwordInput.type = "password";
      toggleIcon.textContent = "visibility_off";
    }
  });
