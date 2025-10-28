document.addEventListener("DOMContentLoaded", () => {
	const headerUser = document.getElementById("headerUser");
	const menu = document.getElementById("profileMenu");
	if (!headerUser || !menu) return;

	function toggleMenu(force) {
		const willOpen =
			typeof force === "boolean" ? force : !menu.classList.contains("open");
		menu.classList.toggle("open", willOpen);
		menu.setAttribute("aria-hidden", String(!willOpen));
		headerUser.classList.toggle("is-open", willOpen);
		headerUser.setAttribute("aria-expanded", String(willOpen));
	}

	// Press feedback (mouse/touch)
	headerUser.addEventListener("pointerdown", () =>
		headerUser.classList.add("is-pressed")
	);
	headerUser.addEventListener("pointerup", () =>
		headerUser.classList.remove("is-pressed")
	);
	headerUser.addEventListener("pointerleave", () =>
		headerUser.classList.remove("is-pressed")
	);
	headerUser.addEventListener("pointercancel", () =>
		headerUser.classList.remove("is-pressed")
	);

	// Toggle with click
	headerUser.addEventListener("click", (e) => {
		if (menu.contains(e.target)) return;
		toggleMenu();
	});

	// Keyboard support + press feedback
	headerUser.addEventListener("keydown", (e) => {
		if (e.key === "Enter" || e.key === " ") {
			headerUser.classList.add("is-pressed");
			e.preventDefault();
		}
	});
	headerUser.addEventListener("keyup", (e) => {
		if (e.key === "Enter" || e.key === " ") {
			headerUser.classList.remove("is-pressed");
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
		if (!headerUser.contains(e.target)) toggleMenu(false);
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
		window.location.href = "../../views/auth/login.php";
	}
}
