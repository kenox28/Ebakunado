document.addEventListener("DOMContentLoaded", () => {
	const toggleBtn = document.getElementById("menuToggle");
	const sideNav = document.getElementById("sideNav");
	if (!toggleBtn || !sideNav) return;

	// Mobile detection
	const isMobile = () => window.innerWidth <= 767;

	// SIDEBAR MENU TOGGLE

	// initialize ARIA
	const setState = (collapsed) => {
		if (isMobile()) {
			// Mobile: use sidebar-open class (sidebar hidden by default)
			document.body.classList.toggle("sidebar-open", !collapsed);
			document.body.classList.remove("sidebar-collapsed");
			toggleBtn.setAttribute("aria-expanded", String(!collapsed));
			sideNav.setAttribute("aria-hidden", String(collapsed));
		} else {
			// Desktop: use sidebar-collapsed class (sidebar visible by default)
			document.body.classList.toggle("sidebar-collapsed", collapsed);
			document.body.classList.remove("sidebar-open");
			toggleBtn.setAttribute("aria-expanded", String(!collapsed));
			sideNav.setAttribute("aria-hidden", String(collapsed));
		}
	};

	// Initialize based on screen size
	const initializeSidebar = () => {
		if (isMobile()) {
			// Mobile: start with sidebar hidden (collapsed = true)
			setState(true);
		} else {
			// Desktop: start with sidebar visible (collapsed = false)
			setState(false);
		}
	};

	// Initialize on load
	initializeSidebar();

	// click to toggle
	toggleBtn.addEventListener("click", () => {
		if (isMobile()) {
			const isOpen = document.body.classList.contains("sidebar-open");
			setState(isOpen);
		} else {
			const collapsed = !document.body.classList.contains("sidebar-collapsed");
			setState(collapsed);
		}
	});

	// Close sidebar when clicking overlay (mobile only)
	document.addEventListener("click", (e) => {
		if (
			isMobile() &&
			document.body.classList.contains("sidebar-open") &&
			!sideNav.contains(e.target) &&
			e.target !== toggleBtn
		) {
			setState(true); // Close sidebar
		}
	});

	// Close sidebar on menu item click (mobile only)
	if (isMobile()) {
		document.querySelectorAll(".menu-link").forEach((link) => {
			link.addEventListener("click", () => {
				setState(true); // Close sidebar
			});
		});
	}

	// Handle window resize
	window.addEventListener("resize", () => {
		// Reinitialize sidebar when switching between mobile/desktop
		setTimeout(() => {
			initializeSidebar();
		}, 100); // Small delay to ensure resize is complete
	});

	// allow Space/Enter to toggle (button already handles Enter in most cases)
	toggleBtn.addEventListener("keydown", (e) => {
		if (e.code === "Space") {
			e.preventDefault();
			toggleBtn.click();
		}
	});
});
