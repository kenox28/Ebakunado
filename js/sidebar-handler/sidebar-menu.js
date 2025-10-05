document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('.sidebar-menu');
  const items = Array.from(document.querySelectorAll('.sidebar-menu-item'));
  if (!menu || !items.length) return;

  // Ensure first is active by default if none marked
  if (!document.querySelector('.sidebar-menu-item.active')) {
    items[0].classList.add('active');
  }

  // Delegate clicks to anchors; make entire item clickable
  menu.addEventListener('click', (e) => {
    const link = e.target.closest('.menu-link');
    if (!link) return;
    e.preventDefault();

    const li = link.closest('.sidebar-menu-item');
    if (!li) return;

    items.forEach(i => i.classList.remove('active'));
    li.classList.add('active');
  });
});