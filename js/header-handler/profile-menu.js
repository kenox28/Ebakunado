// profile-menu_v2.js (aligned to profile-menu.js behavior with header_v2.php selectors)
document.addEventListener("DOMContentLoaded", () => {
	let trigger = document.getElementById("profileBtn");
	let root = document.getElementById("profileRoot");
	const menu = document.getElementById("profileMenu");

	// Fallback for user-page header markup (uses headerUser as both trigger and root)
	if ((!trigger || !root) && document.getElementById("headerUser")) {
		trigger = document.getElementById("headerUser");
		root = trigger;
	}

	if (!trigger || !menu || !root) return;

	function toggleMenu(force) {
		const willOpen =
			typeof force === "boolean" ? force : !menu.classList.contains("open");
		menu.classList.toggle("open", willOpen);
		menu.setAttribute("aria-hidden", String(!willOpen));
		trigger.setAttribute("aria-expanded", String(willOpen));
		menu.hidden = !willOpen;
		root.classList.toggle("is-open", willOpen);
	}

	trigger.addEventListener("pointerdown", () =>
		trigger.classList.add("is-pressed")
	);
	trigger.addEventListener("pointerup", () =>
		trigger.classList.remove("is-pressed")
	);
	trigger.addEventListener("pointerleave", () =>
		trigger.classList.remove("is-pressed")
	);
	trigger.addEventListener("pointercancel", () =>
		trigger.classList.remove("is-pressed")
	);

	// Toggle with click
	trigger.addEventListener("click", (e) => {
		if (menu.contains(e.target)) return;
		toggleMenu();
	});

	// Keyboard support + press feedback
	trigger.addEventListener("keydown", (e) => {
		if (e.key === "Enter" || e.key === " ") {
			trigger.classList.add("is-pressed");
			e.preventDefault();
		}
	});
	trigger.addEventListener("keyup", (e) => {
		if (e.key === "Enter" || e.key === " ") {
			trigger.classList.remove("is-pressed");
			toggleMenu();
			e.preventDefault();
		}
	});

	// Menu interactions
	menu.addEventListener("click", (e) => {
		e.stopPropagation();
		const item = e.target.closest(".menu-item");
		if (item) toggleMenu(false);
	});

	// Close on outside click / Esc
	document.addEventListener("click", (e) => {
		if (!root.contains(e.target)) toggleMenu(false);
	});
	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape") toggleMenu(false);
	});
});

async function logoutBhw() {
	const response = await fetch("../../php/supabase/bhw/logout.php", {
		method: "POST",
	});
	const data = await response.json();
	if (data.status === "success") {
		// Clear JWT token from localStorage
		localStorage.removeItem("jwt_token");
		// Also clear any other auth data if needed
		sessionStorage.clear();
		window.location.href = "../../views/auth/login.php";
	}
}
