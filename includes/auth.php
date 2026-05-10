<?php
/**
 * Authentication Functions
 * Handles user login, session management, and CSRF tokens
 */

// Prevent multiple inclusions
if (defined('AUTH_PHP_LOADED')) {
    return;
}
define('AUTH_PHP_LOADED', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout: 30 minutes
$timeout = 30 * 60; // 30 minutes in seconds

// Check if session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has admin role
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require login (redirect if not logged in)
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=1');
        exit;
    }
}

/**
 * Require admin role (redirect if not admin)
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: index.php?unauthorized=1');
        exit;
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

/**
 * Get CSRF token HTML hidden input
 */
function get_csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

/**
 * Authenticate user with username and password
 */
function authenticate_user($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log('Auth Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get logged-in user information
 */
function get_logged_in_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}


/**
 * Logout user
 */
function logout_user() {
    session_destroy();
}
?>
