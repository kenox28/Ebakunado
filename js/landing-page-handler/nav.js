document.addEventListener("DOMContentLoaded", () => {
	const links = Array.from(document.querySelectorAll(".nav-links .nav-button"));

	const clearActive = () => links.forEach((l) => l.classList.remove("active"));
	const activateByNav = (navKey) => {
		clearActive();
		const match = links.find((l) => l.dataset.nav === navKey);
		if (match) match.classList.add("active");
	};

	// Restore last clicked
	let stored = localStorage.getItem("activeNav");

	// If on contact page and stored is empty (direct load), default to contact
	if (!stored) {
		if (document.getElementById("contact")) stored = "contact";
		else stored = "home";
	}
	activateByNav(stored);

	// Click handling
	links.forEach((link) => {
		link.addEventListener("click", () => {
			const navKey = link.dataset.nav;
			localStorage.setItem("activeNav", navKey);
			activateByNav(navKey);
			// Allow normal navigation / anchor jump
		});
	});

	// Make sure logo sets Home active
	const logo = document.querySelector(".logo");
	if (logo) {
		logo.addEventListener("click", () => {
			localStorage.setItem("activeNav", "home");
		});
	}
});

// Mobile nav drawer
document.addEventListener("DOMContentLoaded", () => {
	const body = document.body;
	const menuIcon = document.querySelector(".menu-icon");
	const links = document.querySelectorAll(".nav-links a");

	if (!menuIcon) return;

	const open = () => {
		body.classList.add("nav-open");
		menuIcon.textContent = "close";
		menuIcon.setAttribute("aria-label", "Close menu");
		menuIcon.setAttribute("aria-expanded", "true");
	};

	const close = () => {
		body.classList.remove("nav-open");
		menuIcon.textContent = "menu";
		menuIcon.setAttribute("aria-label", "Open menu");
		menuIcon.setAttribute("aria-expanded", "false");
	};

	const toggle = () => (body.classList.contains("nav-open") ? close() : open());

	menuIcon.addEventListener("click", toggle);
	links.forEach((a) => a.addEventListener("click", close));

	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape") close();
	});

	const mq = window.matchMedia("(min-width: 768px)");
	const onChange = () => {
		if (mq.matches) close();
	};
	if (mq.addEventListener) mq.addEventListener("change", onChange);
	else mq.addListener(onChange);
});

function openCreateAccount() {
	console.log("openCreateAccount");
	window.location.href = "register";
}
