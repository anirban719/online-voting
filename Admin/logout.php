<?php
require_once '../config.php';

// Destroy all session data
session_destroy();

// Redirect to admin login
header("Location: login.php");
exit();
?>
