<?php
// Start the session
session_start();

// Destroy the session to log the user out
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the login page after logout
header("Location: ../index.html");
exit();
?>
