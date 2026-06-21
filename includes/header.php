<?php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'header.php') {
    exit('Direct access not allowed');
}

// Include auth and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Generate CSRF token
generate_csrf_token();

// Get unread notifications count
$unread_notifications = 0;
if (is_logged_in()) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $unread_notifications = $result['count'] ?? 0;
    } catch (PDOException $e) {
        // Silent fail for notifications
    }
}

$is_logged = is_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#020617">
    <title><?php echo isset($page_title) ? esc($page_title) . ' - ' : ''; ?>CRMD SOC Platform</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php if ($is_logged): ?>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Main Layout -->
    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-brand">
                    <div class="brand-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">CRMD</span>
                        <span class="brand-subtitle">SOC Platform</span>
                    </div>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Operations</div>
                    
                    <div class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Command Center</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="risks.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'risks.php' ? 'active' : ''; ?>">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>Risk Register</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a href="incidents.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'incidents.php' ? 'active' : ''; ?>">
                            <i class="fas fa-bolt"></i>
                            <span>Incident Queue</span>
                            <?php 
                            try {
                                $stmt = $pdo->prepare('SELECT COUNT(*) FROM incidents WHERE resolved = 0');
                                $stmt->execute();
                                $openIncidents = $stmt->fetchColumn();
                                if ($openIncidents > 0):
                            ?>
                                <span class="badge badge-danger"><?php echo $openIncidents; ?></span>
                            <?php endif; } catch(PDOException $e) {} ?>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Intelligence</div>
                    
                    <div class="nav-item">
                        <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                            <i class="fas fa-file-lines"></i>
                            <span>Reports</span>
                        </a>
                    </div>
                </div>
                
                <?php if (is_admin()): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Administration</div>
                    
                    <div class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo esc($_SESSION['username'] ?? 'User'); ?></div>
                        <div class="user-role"><?php echo esc(ucfirst($_SESSION['role'] ?? 'Viewer')); ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-sm btn-secondary" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="app-main">
            <!-- Top Navbar -->
            <nav class="app-navbar">
                <div class="navbar-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-header-inline">
                        <h1><?php echo isset($page_title) ? esc($page_title) : 'Dashboard'; ?></h1>
                    </div>
                </div>
                
                <div class="navbar-right">
                    <div class="navbar-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search..." id="globalSearch">
                    </div>
                    
                    <div class="navbar-actions">
                        <div class="position-relative">
                            <button class="nav-action-btn" onclick="toggleNotifications()" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <?php if ($unread_notifications > 0): ?>
                                <span class="notification-dot"></span>
                                <?php endif; ?>
                            </button>
                            
                            <!-- Notifications Panel -->
                            <div class="notifications-panel" id="notificationsPanel">
                                <div class="notifications-header">
                                    <h6><i class="fas fa-bell me-2"></i>Notifications</h6>
                                </div>
                                <div id="notificationsList">
                                    <?php
                                    try {
                                        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5');
                                        $stmt->execute([$_SESSION['user_id'] ?? 0]);
                                        $notifications = $stmt->fetchAll();
                                        if (!empty($notifications)):
                                            foreach ($notifications as $notif):
                                    ?>
                                    <a href="<?php echo esc($notif['link'] ?? '#'); ?>" class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                                        <div class="notification-title"><?php echo esc($notif['title']); ?></div>
                                        <div class="notification-message"><?php echo esc(substr($notif['message'], 0, 80)); ?>...</div>
                                        <div class="notification-time"><?php echo esc(date('M j, H:i', strtotime($notif['created_at']))); ?></div>
                                    </a>
                                    <?php 
                                            endforeach;
                                        else:
                                    ?>
                                    <div class="notification-item">
                                        <div class="notification-message text-center text-muted py-3">No new notifications</div>
                                    </div>
                                    <?php 
                                        endif;
                                    } catch(PDOException $e) {}
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="nav-action-btn" data-bs-toggle="dropdown" title="Account">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content Container -->
            <div class="main-content">
                <!-- Alert Messages -->
                <?php if (isset($_GET['welcome'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Welcome back, <?php echo esc($_SESSION['username']); ?>!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Operation completed successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> An error occurred.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['unauthorized'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-lock"></i> Access denied.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
<?php endif; ?>