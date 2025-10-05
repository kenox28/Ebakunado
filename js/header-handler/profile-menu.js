document.addEventListener('DOMContentLoaded', () => {
  const headerUser = document.getElementById('headerUser');
  const menu = document.getElementById('profileMenu');
  if (!headerUser || !menu) return;

  function toggleMenu(force) {
    const willOpen = typeof force === 'boolean' ? force : !menu.classList.contains('open');
    menu.classList.toggle('open', willOpen);
    menu.setAttribute('aria-hidden', String(!willOpen));
  }

  // Toggle when clicking the header user area (but not when clicking inside the menu)
  headerUser.addEventListener('click', (e) => {
    if (menu.contains(e.target)) return;
    toggleMenu();
  });

  // Clicks inside the menu: stop bubbling, close after selecting an item
  menu.addEventListener('click', (e) => {
    e.stopPropagation();
    const item = e.target.closest('.menu-item');
    if (item) toggleMenu(false);
  });

  // Close on outside click
  document.addEventListener('click', (e) => {
    if (!headerUser.contains(e.target)) toggleMenu(false);
  });

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') toggleMenu(false);
  });
});