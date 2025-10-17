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
			async function logoutBhw() {
				// const response = await fetch('/ebakunado/php/bhw/logout.php', { method: 'POST' });
				const response = await fetch('/ebakunado/php/supabase/bhw/logout.php', { method: 'POST' });
				const data = await response.json();
				if (data.status === 'success') { window.location.href = '../../views/auth/login.php'; }
				else { alert('Logout failed: ' + data.message); }
			}
		</script>
	</body>
</html>