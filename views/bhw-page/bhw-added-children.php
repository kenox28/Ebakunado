<?php
session_start();
// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

header('Location: health-pending?view=added');
exit();

