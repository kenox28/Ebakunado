document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("menuToggle");
  const sideNav = document.getElementById("sideNav");
  if (!toggleBtn || !sideNav) return;

  // SIDEBAR MENU TOGGLE

  // initialize ARIA
  const setState = (collapsed) => {
    document.body.classList.toggle("sidebar-collapsed", collapsed);
    toggleBtn.setAttribute("aria-expanded", String(!collapsed));
    sideNav.setAttribute("aria-hidden", String(collapsed));
  };

  // start expanded
  setState(false);

  // click to toggle
  toggleBtn.addEventListener("click", () => {
    const collapsed = !document.body.classList.contains("sidebar-collapsed");
    setState(collapsed);
  });

  // allow Space/Enter to toggle (button already handles Enter in most cases)
  toggleBtn.addEventListener("keydown", (e) => {
    if (e.code === "Space") {
      e.preventDefault();
      toggleBtn.click();
    }
  });
});
