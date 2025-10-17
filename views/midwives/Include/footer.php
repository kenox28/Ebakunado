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
		</script>
	</body>
</html>
