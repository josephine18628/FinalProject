<?php
/**
 * Logout API Endpoint
 * CS3 Quiz Platform
 */

require_once '../includes/functions.php';

// Destroy session and redirect to login
session_start();
session_unset();
session_destroy();

// Start a new session for the flash message
session_start();
setFlashMessage('success', 'You have been logged out successfully');

header('Location: ../index.php');
exit();
?>

