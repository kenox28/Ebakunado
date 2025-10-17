</main>
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

				// window.location.href = "/ebakunado/php/supabase/users/logout.php"
				const result = await Swal.fire({
					title: "Are you sure?",
					text: "You will be logged out of the system",
					icon: "question",
					showCancelButton: true,
					confirmButtonColor: "#e74c3c",
					cancelButtonColor: "#95a5a6",
					confirmButtonText: "Yes, logout",
				});

				if (result.isConfirmed) {
					const response = await fetch("/ebakunado/php/supabase/users/logout.php", {
						method: "POST",
					});

					const data = await response.json();

					if (data.status === "success") {
						Swal.fire({
							icon: "success",
							title: "Logged Out",
							text: "You have been successfully logged out",
							showConfirmButton: false,
							timer: 1500,
						}).then(() => {
							window.location.href = "../../views/landing-page/landing-page.html";
						});
					} else {
						Swal.fire("Error!", data.message, "error");
					}
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
