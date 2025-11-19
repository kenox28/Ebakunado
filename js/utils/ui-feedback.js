(() => {
	const ICONS = {
		success: "check_circle",
		error: "error",
		warning: "warning",
		info: "info",
	};

	let modalRefs = null;
	let loaderActive = false;

	function ensureModal() {
		if (modalRefs) return modalRefs;

		const overlay = document.createElement("div");
		overlay.className = "modal-overlay";
		overlay.innerHTML = `
			<div class="modal-card">
				<div class="modal-icon" data-role="icon">
					<span class="material-symbols-rounded">info</span>
				</div>
				<h3 class="modal-title" data-role="title"></h3>
				<p class="modal-message" data-role="message"></p>
				<div class="modal-body" data-role="body"></div>
				<div class="modal-actions" data-role="actions">
					<button type="button" class="modal-btn secondary" data-role="cancel">Cancel</button>
					<button type="button" class="modal-btn primary" data-role="confirm">Continue</button>
				</div>
			</div>
		`;
		document.body.appendChild(overlay);

		modalRefs = {
			overlay,
			card: overlay.querySelector(".modal-card"),
			iconWrap: overlay.querySelector('[data-role="icon"]'),
			icon: overlay.querySelector('[data-role="icon"] span'),
			title: overlay.querySelector('[data-role="title"]'),
			message: overlay.querySelector('[data-role="message"]'),
			body: overlay.querySelector('[data-role="body"]'),
			actions: overlay.querySelector('[data-role="actions"]'),
			cancelBtn: overlay.querySelector('[data-role="cancel"]'),
			confirmBtn: overlay.querySelector('[data-role="confirm"]'),
		};

		return modalRefs;
	}

	function ensureToastContainer() {
		let container = document.querySelector(".toast-container");
		if (!container) {
			container = document.createElement("div");
			container.className = "toast-container";
			document.body.appendChild(container);
		}
		return container;
	}

	function applyIcon(iconWrap, iconEl, variant) {
		if (!variant) {
			iconWrap.style.display = "none";
			return;
		}
		iconWrap.style.display = "flex";
		const symbol = ICONS[variant] || ICONS.info;
		iconEl.textContent = symbol;
		iconWrap.classList.remove("success", "error", "warning", "info");
		iconWrap.classList.add(variant);
	}

	function showModal(options = {}) {
		const refs = ensureModal();
		const {
			title = "",
			message = "",
			html = "",
			icon = null,
			confirmText = "Continue",
			cancelText = "Cancel",
			showCancel = false,
			showConfirm = true,
			autoClose = null,
			className = "",
			onOpen = null,
			beforeConfirm = null,
			onClose = null,
		} = options;

		return new Promise((resolve) => {
			refs.card.className = `modal-card ${className}`.trim();
			refs.title.textContent = title || "";
			refs.message.textContent = message || "";
			refs.body.innerHTML = html || "";
			refs.actions.style.display = showCancel || showConfirm ? "flex" : "none";

			if (showConfirm) {
				refs.confirmBtn.style.display = "inline-flex";
				refs.confirmBtn.textContent = confirmText;
			} else {
				refs.confirmBtn.style.display = "none";
			}

			if (showCancel) {
				refs.cancelBtn.style.display = "inline-flex";
				refs.cancelBtn.textContent = cancelText;
			} else {
				refs.cancelBtn.style.display = "none";
			}

			applyIcon(refs.iconWrap, refs.icon, icon);

			function close(result) {
				refs.overlay.classList.remove("is-visible");
				refs.confirmBtn.onclick = null;
				refs.cancelBtn.onclick = null;
				if (typeof onClose === "function") {
					onClose(result);
				}
				resolve(result);
			}

			refs.confirmBtn.onclick = async () => {
				if (typeof beforeConfirm === "function") {
					const response = await beforeConfirm(refs.card);
					if (response === false) return;
					close({
						action: "confirm",
						data: response === undefined ? true : response,
					});
				} else {
					close({ action: "confirm", data: true });
				}
			};

			refs.cancelBtn.onclick = () => close({ action: "cancel" });

			refs.overlay.classList.add("is-visible");
			if (typeof onOpen === "function") {
				onOpen(refs.card);
			}

			if (autoClose) {
				setTimeout(() => close({ action: "timeout" }), autoClose);
			}
		});
	}

	function showToast({
		title = "",
		message = "",
		variant = "info",
		duration = 5000,
	} = {}) {
		const container = ensureToastContainer();
		const toast = document.createElement("div");
		toast.className = `toast toast-${variant}`;
		toast.innerHTML = `
			<div class="toast-header">
				<div class="toast-title-row">
					<span class="material-symbols-rounded">${ICONS[variant] || ICONS.info}</span>
					<p class="toast-title">${title}</p>
				</div>
				<button class="toast-close" aria-label="Dismiss">
					<span class="material-symbols-rounded">close</span>
				</button>
			</div>
			<p class="toast-body">${message}</p>
		`;

		container.appendChild(toast);
		requestAnimationFrame(() => toast.classList.add("is-visible"));

		const close = () => {
			toast.classList.remove("is-visible");
			setTimeout(() => toast.remove(), 200);
		};

		const timer = setTimeout(close, duration);
		toast.querySelector(".toast-close").addEventListener("click", () => {
			clearTimeout(timer);
			close();
		});
	}

	function showLoader(message = "Please wait...") {
		const refs = ensureModal();
		loaderActive = true;

		refs.card.className = "modal-card modal-loading";
		refs.title.textContent = message;
		refs.message.textContent = "";
		refs.body.innerHTML =
			'<div class="modal-spinner" aria-label="Loading"></div>';
		refs.actions.style.display = "none";
		refs.iconWrap.style.display = "none";

		refs.overlay.classList.add("is-visible");

		return () => {
			if (!loaderActive) return;
			loaderActive = false;
			refs.overlay.classList.remove("is-visible");
		};
	}

	window.UIFeedback = {
		showModal,
		showToast,
		showLoader,
		closeModal() {
			const refs = ensureModal();
			refs.overlay.classList.remove("is-visible");
		},
	};
})();
