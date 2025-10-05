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
				window.location.href = "../../php/supabase/users/logout.php"
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
