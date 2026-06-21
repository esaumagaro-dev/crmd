<?php
/**
 * Logout Handler
 * Cybersecurity Risk Management Dashboard
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Log the logout activity before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    log_activity($pdo, $_SESSION['user_id'], $_SESSION['username'], 'logout', 'user', null, 'User logged out');
}

// Logout user
logout_user();

// Redirect to login
header('Location: login.php?logout=1');
exit;
?>
