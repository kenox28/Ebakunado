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

  // SIDEBAR MENU ITEM ACTIVATION
  const menu = document.querySelector(".sidebar-menu");
  const items = Array.from(document.querySelectorAll(".sidebar-menu-item"));
  if (!menu || !items.length) return;

  // Ensure first is active by default if none marked
  if (!document.querySelector(".sidebar-menu-item.active")) {
    items[0].classList.add("active");
  }

  // Delegate clicks to anchors; make entire item clickable
  menu.addEventListener("click", (e) => {
    const link = e.target.closest(".menu-link");
    if (!link) return;
    e.preventDefault();

    const li = link.closest(".sidebar-menu-item");
    if (!li) return;

    items.forEach((i) => i.classList.remove("active"));
    li.classList.add("active");
  });
});
