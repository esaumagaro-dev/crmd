<?php
/**
 * API: Chart Data
 * Returns JSON data for dashboard charts
 * Requires authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get risks by level
    $stmt = $pdo->prepare('
        SELECT risk_level, COUNT(*) as count 
        FROM risks 
        GROUP BY risk_level
    ');
    $stmt->execute();
    $riskData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $riskByLevel = [
        'high' => (int)($riskData['High'] ?? 0),
        'medium' => (int)($riskData['Medium'] ?? 0),
        'low' => (int)($riskData['Low'] ?? 0)
    ];
    
    // Get incidents by month (last 6 months)
    $stmt = $pdo->prepare('
        SELECT DATE_FORMAT(incident_date, "%Y-%m") as month, COUNT(*) as count 
        FROM incidents 
        WHERE incident_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ');
    $stmt->execute();
    $incidentData = $stmt->fetchAll();
    
    $incidentsByMonth = [];
    foreach ($incidentData as $row) {
        $incidentsByMonth[$row['month']] = (int)$row['count'];
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'riskByLevel' => $riskByLevel,
        'incidentsByMonth' => $incidentsByMonth,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log('API Error: ' . $e->getMessage());
}
?>
