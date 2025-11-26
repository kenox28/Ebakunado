<?php
// Legacy login entry point.
// Always redirect to the new unified auth login page.
header('Location: ./auth/login.php');
exit();