<?php
/**
 * Utility Functions
 * Database operations and data processing
 */

/**
 * Calculate risk level based on likelihood and impact
 */
function calculate_risk_level($likelihood, $impact) {
    $score = $likelihood * $impact;

    if ($score >= 20) {
        return 'Critical';
    } elseif ($score >= 15) {
        return 'High';
    } elseif ($score >= 6) {
        return 'Medium';
    } else {
        return 'Low';
    }
}

/**
 * Log user activity to audit_log
 */
function log_activity($pdo, $user_id, $username, $action, $entity_type, $entity_id = null, $description = '') {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO audit_log (user_id, username, action, entity_type, entity_id, description, ip_address) 
            VALUES (:user_id, :username, :action, :entity_type, :entity_id, :description, :ip_address)
        ');
        return $stmt->execute([
            'user_id' => $user_id,
            'username' => $username,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (PDOException $e) {
        error_log('Audit Log Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get audit log entries, optionally filtered by user
 */
function get_audit_log($pdo, $limit = 20, $user_id = null, $action = null) {
    try {
        $where = '';
        $params = [];

        if ($user_id !== null) {
            $where .= ' AND al.user_id = :user_id';
            $params['user_id'] = $user_id;
        }

        if ($action !== null) {
            $where .= ' AND al.action = :action';
            $params['action'] = $action;
        }

        $sql = "SELECT al.* FROM audit_log al WHERE 1=1 $where ORDER BY al.created_at DESC LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all risks from database
 */
function get_all_risks($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT r.*, u.username AS creator_name
            FROM risks r
            LEFT JOIN users u ON r.created_by = u.id
            ORDER BY r.created_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get risks created by a specific user
 */
function get_user_risks($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare('
            SELECT r.*, u.username AS creator_name
            FROM risks r
            LEFT JOIN users u ON r.created_by = u.id
            WHERE r.created_by = :user_id
            ORDER BY r.created_at DESC
        ');
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get risk by ID
 */
function get_risk($pdo, $id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM risks WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Add new risk with user tracking
 */
function add_risk($pdo, $name, $description, $likelihood, $impact, $status, $user_id = null) {
    try {
        $risk_level = calculate_risk_level($likelihood, $impact);

        $stmt = $pdo->prepare('
            INSERT INTO risks (name, description, likelihood, impact, risk_level, status, created_by) 
            VALUES (:name, :description, :likelihood, :impact, :risk_level, :status, :created_by)
        ');

        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'likelihood' => $likelihood,
            'impact' => $impact,
            'risk_level' => $risk_level,
            'status' => $status,
            'created_by' => $user_id
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update risk with user tracking
 */
function update_risk($pdo, $id, $name, $description, $likelihood, $impact, $status, $user_id = null) {
    try {
        $risk_level = calculate_risk_level($likelihood, $impact);

        $stmt = $pdo->prepare('
            UPDATE risks 
            SET name = :name, description = :description, likelihood = :likelihood, 
                impact = :impact, risk_level = :risk_level, status = :status,
                updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'likelihood' => $likelihood,
            'impact' => $impact,
            'risk_level' => $risk_level,
            'status' => $status,
            'updated_by' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete risk
 */
function delete_risk($pdo, $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM risks WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all incidents
 */
function get_all_incidents($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT i.*, u.username AS creator_name
            FROM incidents i
            LEFT JOIN users u ON i.created_by = u.id
            ORDER BY i.incident_date DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get incidents created by a specific user
 */
function get_user_incidents($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare('
            SELECT i.*, u.username AS creator_name
            FROM incidents i
            LEFT JOIN users u ON i.created_by = u.id
            WHERE i.created_by = :user_id
            ORDER BY i.incident_date DESC
        ');
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get incident by ID
 */
function get_incident($pdo, $id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM incidents WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Add new incident with user tracking
 */
function add_incident($pdo, $title, $description, $incident_date, $severity, $resolved, $user_id = null) {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO incidents (title, description, incident_date, severity, resolved, created_by) 
            VALUES (:title, :description, :incident_date, :severity, :resolved, :created_by)
        ');

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'incident_date' => $incident_date,
            'severity' => $severity,
            'resolved' => $resolved ? 1 : 0,
            'created_by' => $user_id
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update incident with user tracking
 */
function update_incident($pdo, $id, $title, $description, $incident_date, $severity, $resolved, $user_id = null) {
    try {
        $stmt = $pdo->prepare('
            UPDATE incidents 
            SET title = :title, description = :description, incident_date = :incident_date, 
                severity = :severity, resolved = :resolved, updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'incident_date' => $incident_date,
            'severity' => $severity,
            'resolved' => $resolved ? 1 : 0,
            'updated_by' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete incident
 */
function delete_incident($pdo, $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM incidents WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get role-based dashboard stats (admin sees all, users see their own)
 */
function get_dashboard_stats($pdo, $user_id = null) {
    try {
        $stats = [];
        $where = '';

        if ($user_id !== null) {
            $where = ' WHERE created_by = :user_id';
        }

        // Total risks
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM risks$where");
        if ($user_id !== null) $stmt->execute(['user_id' => $user_id]);
        else $stmt->execute();
        $stats['total_risks'] = $stmt->fetch()['count'];

        // Open incidents
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM incidents WHERE resolved = 0$where");
        if ($user_id !== null) $stmt->execute(['user_id' => $user_id]);
        else $stmt->execute();
        $stats['open_incidents'] = $stmt->fetch()['count'];

        // High-level risks
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM risks WHERE risk_level IN ('High', 'Critical')$where");
        if ($user_id !== null) $stmt->execute(['user_id' => $user_id]);
        else $stmt->execute();
        $stats['high_risks'] = $stmt->fetch()['count'];

        // Resolved incidents
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM incidents WHERE resolved = 1$where");
        if ($user_id !== null) $stmt->execute(['user_id' => $user_id]);
        else $stmt->execute();
        $stats['resolved_incidents'] = $stmt->fetch()['count'];

        return $stats;
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Escape output to prevent XSS
 */
function esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 */
function format_date($date) {
    return date('Y-m-d H:i', strtotime($date));
}

/**
 * Get badge class for risk level
 */
function get_risk_badge_class($level) {
    $classes = [
        'Critical' => 'badge-critical',
        'High' => 'badge-danger',
        'Medium' => 'badge-warning',
        'Low' => 'badge-success'
    ];
    return $classes[$level] ?? 'badge-secondary';
}

/**
 * Get badge class for severity
 */
function get_severity_badge_class($severity) {
    $classes = [
        'Critical' => 'badge-critical',
        'High' => 'badge-danger',
        'Medium' => 'badge-warning',
        'Low' => 'badge-info'
    ];
    return $classes[$severity] ?? 'badge-secondary';
}
