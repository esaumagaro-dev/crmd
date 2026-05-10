<?php
/**
 * Logout Handler
 * Cybersecurity Risk Management Dashboard
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Logout user
logout_user();

// Redirect to login
header('Location: login.php?logout=1');
exit;
?>
