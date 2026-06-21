<?php
/**
 * Utility Functions
 * Database operations and data processing
 */

/**
 * Calculate risk level based on likelihood and impact
 * High: likelihood * impact >= 15
 * Medium: likelihood * impact between 6-14
 * Low: likelihood * impact <= 5
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
 * Get all risks from database
 */
function get_all_risks($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT * FROM risks 
            ORDER BY created_at DESC
        ');
        $stmt->execute();
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
 * Add new risk
 */
function add_risk($pdo, $name, $description, $likelihood, $impact, $status) {
    try {
        $risk_level = calculate_risk_level($likelihood, $impact);
        
        $stmt = $pdo->prepare('
            INSERT INTO risks (name, description, likelihood, impact, risk_level, status) 
            VALUES (:name, :description, :likelihood, :impact, :risk_level, :status)
        ');
        
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'likelihood' => $likelihood,
            'impact' => $impact,
            'risk_level' => $risk_level,
            'status' => $status
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update risk
 */
function update_risk($pdo, $id, $name, $description, $likelihood, $impact, $status) {
    try {
        $risk_level = calculate_risk_level($likelihood, $impact);
        
        $stmt = $pdo->prepare('
            UPDATE risks 
            SET name = :name, description = :description, likelihood = :likelihood, 
                impact = :impact, risk_level = :risk_level, status = :status,
                updated_at = NOW()
            WHERE id = :id
        ');
        
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'likelihood' => $likelihood,
            'impact' => $impact,
            'risk_level' => $risk_level,
            'status' => $status
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
            SELECT * FROM incidents 
            ORDER BY incident_date DESC
        ');
        $stmt->execute();
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
 * Add new incident
 */
function add_incident($pdo, $title, $description, $incident_date, $severity, $resolved) {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO incidents (title, description, incident_date, severity, resolved) 
            VALUES (:title, :description, :incident_date, :severity, :resolved)
        ');
        
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'incident_date' => $incident_date,
            'severity' => $severity,
            'resolved' => $resolved ? 1 : 0
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update incident
 */
function update_incident($pdo, $id, $title, $description, $incident_date, $severity, $resolved) {
    try {
        $stmt = $pdo->prepare('
            UPDATE incidents 
            SET title = :title, description = :description, incident_date = :incident_date, 
                severity = :severity, resolved = :resolved, updated_at = NOW()
            WHERE id = :id
        ');
        
        return $stmt->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'incident_date' => $incident_date,
            'severity' => $severity,
            'resolved' => $resolved ? 1 : 0
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
 * Get dashboard statistics
 */
function get_dashboard_stats($pdo) {
    try {
        $stats = [];
        
        // Total risks
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM risks');
        $stmt->execute();
        $stats['total_risks'] = $stmt->fetch()['count'];
        
        // Open incidents
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM incidents WHERE resolved = 0');
        $stmt->execute();
        $stats['open_incidents'] = $stmt->fetch()['count'];
        
        // High-level risks
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM risks WHERE risk_level = "High"');
        $stmt->execute();
        $stats['high_risks'] = $stmt->fetch()['count'];
        
        // Resolved incidents
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM incidents WHERE resolved = 1');
        $stmt->execute();
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
?>
