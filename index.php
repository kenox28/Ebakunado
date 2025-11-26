<?php

// Main front controller for the application
require_once __DIR__ . '/router.php';

$router = new Router();
$router->dispatch();


