<?php
/**
 * Login Page
 * Cybersecurity Risk Management Dashboard
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (authenticate_user($pdo, $username, $password)) {
        header('Location: index.php?welcome=1');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

if (isset($_GET['timeout'])) {
    $info = 'Your session has expired. Please login again.';
}

if (isset($_GET['redirect'])) {
    $info = 'Please login to continue.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cybersecurity Risk Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="login-shell">
        <section class="login-hero">
            <div class="page-kicker">Cybersecurity Risk Management Dashboard</div>
            <h1>Enterprise Threat Visibility</h1>
            <p>
                A dark SOC-style dashboard for risk scoring, incident tracking, threat analytics, and executive-ready reporting.
            </p>
            <div class="login-signal-grid">
                <div class="login-signal">
                    <strong>24/7</strong>
                    <span>Security monitoring</span>
                </div>
                <div class="login-signal">
                    <strong>SIEM</strong>
                    <span>Event-driven layout</span>
                </div>
                <div class="login-signal">
                    <strong>GRC</strong>
                    <span>Risk governance</span>
                </div>
            </div>
        </section>

        <section class="login-card fade-in">
            <div class="login-header">
                <div class="login-icon"><i class="fas fa-shield-halved"></i></div>
                <h1>CRMD Secure Login</h1>
                <p>Authenticate to access the cyber risk command center.</p>
            </div>

            <?php if ($info): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($info); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="credentials-info">
                <strong><i class="fas fa-user-check"></i> Demo Credentials</strong><br>
                <small>
                    <strong>Admin:</strong> <code>admin</code> / <code>admin123</code><br>
                    <strong>Viewer:</strong> <code>viewer</code> / <code>viewer123</code>
                </small>
            </div>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket"></i> Login
                </button>
            </form>

            <div class="footer-text mt-4 small">
                <p class="mb-1">Arusha Technical College | Final Year Project</p>
                <p class="mb-0">2026 Cybersecurity Risk Management</p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
