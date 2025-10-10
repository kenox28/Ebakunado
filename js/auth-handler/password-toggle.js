// Password visibility toggle functionality
document.querySelectorAll(".password-toggle").forEach((toggle) => {
  toggle.addEventListener("click", function () {
    const wrapper = this.closest(".password-wrapper");
    if (!wrapper) return;
    const input = wrapper.querySelector(".password-input");
    if (!input) return;

    if (input.type === "password") {
      input.type = "text";
      this.textContent = "visibility";
    } else {
      input.type = "password";
      this.textContent = "visibility_off";
    }
  });
});
