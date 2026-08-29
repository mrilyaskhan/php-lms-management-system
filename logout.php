<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect the user to the login page after logging out
header('Location: login.php');
exit();
?>
