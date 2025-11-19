</main>
		<script src="../../js/utils/ui-feedback.js"></script>
		<script>
			(function () {
				var button = document.querySelector(".menu-button");
				var aside = document.querySelector("aside");
				if (button && aside) {
					button.addEventListener("click", function () {
						aside.classList.toggle("collapsed");
					});
				}
			})();

			function toggleDropdown() {
				var dropdown = document.querySelector("#dropdown");
				if (dropdown) {
					dropdown.classList.toggle("active");
				}
			}

			async function logoutUser() {
				if (!window.UIFeedback) {
					window.location.href = "../../php/supabase/users/logout.php";
					return;
				}

				const confirmResult = await UIFeedback.showModal({
					title: "Logout",
					message: "You will be logged out of the system.",
					icon: "warning",
					confirmText: "Yes, logout",
					cancelText: "Cancel",
					showCancel: true
				});

				if (confirmResult?.action !== "confirm") return;

				const closeLoader = UIFeedback.showLoader("Logging out...");
				try {
					const response = await fetch("../../php/supabase/users/logout.php", {
						method: "POST"
					});
					const data = await response.json();
					closeLoader();

					if (data.status === "success") {
						UIFeedback.showToast({
							title: "Logged out",
							message: "You have been successfully logged out.",
							variant: "success",
							duration: 3000
						});
						setTimeout(() => {
							window.location.href = "../../views/landing-page/landing-page.html";
						}, 800);
					} else {
						UIFeedback.showToast({
							title: "Logout failed",
							message: data.message || "Please try again.",
							variant: "error"
						});
					}
				} catch (error) {
					closeLoader();
					UIFeedback.showToast({
						title: "Network error",
						message: "Please check your connection and try again.",
						variant: "error"
					});
				}
			}

			async function switchToBHWView() {
				try {
					const response = await fetch('/ebakunado/php/supabase/shared/switch_back_to_bhw.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						}
					});

					const data = await response.json();
					
					if (data.status === 'success') {
						// Redirect to BHW dashboard
						window.location.href = data.redirect_url || '/ebakunado/views/bhw-page/dashboard.php';
					} else {
						alert('Error: ' + (data.message || 'Failed to switch to BHW/Midwife view'));
					}
				} catch (error) {
					console.error('Error switching to BHW/Midwife view:', error);
					alert('Error: Failed to switch to BHW/Midwife view. Please try again.');
				}
			}
			
			function addChild() {
				// Implement add child functionality
				alert('Add Child functionality will be implemented');
			}

			// Close dropdown when clicking outside
			document.addEventListener('click', function(event) {
				var dropdown = document.querySelector("#dropdown");
				var profileLink = document.querySelector(".profile-link");
				
				if (dropdown && profileLink && !dropdown.contains(event.target)) {
					dropdown.classList.remove("active");
				}
			});
		</script>
	</body>
</html>
