<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the homepage or login page
header('Location: pages/login.php');
exit();