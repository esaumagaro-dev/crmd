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
        // Log the login attempt
        try {
            $stmt = $pdo->prepare('INSERT INTO login_attempts (username, ip_address, user_agent, success) VALUES (?, ?, ?, 1)');
            $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        } catch (PDOException $e) {
            // Silent fail
        }
        
        // Update last login
        try {
            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW(), failed_login_attempts = 0 WHERE username = ?');
            $stmt->execute([$username]);
        } catch (PDOException $e) {
            // Silent fail
        }
        
        header('Location: index.php?welcome=1');
        exit;
    } else {
        // Log failed attempt
        try {
            $stmt = $pdo->prepare('INSERT INTO login_attempts (username, ip_address, user_agent, success) VALUES (?, ?, ?, 0)');
            $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        } catch (PDOException $e) {
            // Silent fail
        }
        
        // Increment failed attempts
        try {
            $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE username = ?');
            $stmt->execute([$username]);
        } catch (PDOException $e) {
            // Silent fail
        }
        
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
    <meta name="theme-color" content="#020617">
    <title>Login - CRMD SOC Platform</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Hero Section -->
        <div class="login-hero">
            <div class="login-hero-badge">
                <i class="fas fa-shield-halved"></i>
                <span>Enterprise Security</span>
            </div>
            
            <h1>Cybersecurity Risk Management Dashboard</h1>
            
            <p>
                A modern SOC-style platform for risk scoring, incident tracking, threat analytics, 
                and executive-ready reporting. Monitor your security posture in real-time.
            </p>
            
            <div class="login-features">
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="login-feature-title">Risk Analytics</div>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="login-feature-title">Incident Response</div>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <div class="login-feature-title">Compliance</div>
                </div>
            </div>
        </div>
        
        <!-- Right Login Card -->
        <div class="login-card fade-in">
            <div class="login-card-header">
                <div class="login-card-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h2>Secure Login</h2>
                <p>Authenticate to access the SOC platform</p>
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
            
            <div class="login-credentials">
                <div class="login-credentials-title">
                    <i class="fas fa-key me-1"></i> Demo Credentials
                </div>
                <div class="mb-2">
                    <strong>Admin:</strong> 
                    <code>admin</code> / <code>admin123</code>
                </div>
                <div class="mb-2">
                    <strong>Viewer:</strong> 
                    <code>viewer</code> / <code>viewer123</code>
                </div>
                <div>
                    <strong>Analyst:</strong> 
                    <code>analyst</code> / <code>analyst123</code>
                </div>
            </div>
            
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-secondary border-secondary text-muted">
                            <i class="fas fa-user"></i>
                        </span>
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
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-secondary border-secondary text-muted">
                            <i class="fas fa-lock"></i>
                        </span>
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
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-right-to-bracket"></i> Login to SOC
                </button>
            </form>
            
            <div class="login-footer">
                <p class="mb-1">
                    <i class="fas fa-university me-1"></i> Arusha Technical College
                </p>
                <p class="mb-0 small">
                    Final Year Project 2026 | Cybersecurity & Digital Forensics
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
