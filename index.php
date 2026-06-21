<?php
/**
 * Dashboard Homepage - Command Center
 * Cybersecurity Risk Management Dashboard
 */

$page_title = 'Command Center';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/header.php';

// Get dashboard data
$stats = get_dashboard_stats($pdo);
$risks = get_all_risks($pdo);
$incidents = get_all_incidents($pdo);

// Calculate metrics
$total_risks = count($risks);
$total_incidents = count($incidents);
$open_risks = count(array_filter($risks, fn($risk) => $risk['status'] === 'Open'));
$mitigated_risks = count(array_filter($risks, fn($risk) => $risk['status'] === 'Mitigated'));
$closed_risks = count(array_filter($risks, fn($risk) => $risk['status'] === 'Closed'));
$open_incidents = (int)($stats['open_incidents'] ?? 0);
$resolved_incidents = (int)($stats['resolved_incidents'] ?? 0);
$high_risks = (int)($stats['high_risks'] ?? 0);
$critical_risks = count(array_filter($risks, fn($risk) => $risk['risk_level'] === 'Critical'));
$high_incidents = count(array_filter($incidents, fn($incident) => $incident['severity'] === 'High' && !$incident['resolved']));
$critical_incidents = count(array_filter($incidents, fn($incident) => $incident['severity'] === 'Critical' && !$incident['resolved']));

// Calculate risk exposure score
$risk_score_total = array_sum(array_map(fn($risk) => (int)$risk['likelihood'] * (int)$risk['impact'], $risks));
$max_risk_score = max($total_risks * 25, 1);
$exposure_score = (int)round(($risk_score_total / $max_risk_score) * 100);
$exposure_width = min($exposure_score, 100);

// Calculate control coverage
$control_coverage = $total_risks > 0 ? (int)round((($mitigated_risks + $closed_risks) / $total_risks) * 100) : 0;

// Calculate incident pressure
$incident_pressure = $total_incidents > 0 ? (int)round(($open_incidents / $total_incidents) * 100) : 0;

// Critical watchlist
$critical_watchlist = $critical_risks + $high_risks + $critical_incidents + $high_incidents;

// Risk distribution for charts
$risk_distribution = [
    'Critical' => $critical_risks,
    'High' => $high_risks - $critical_risks,
    'Medium' => count(array_filter($risks, fn($r) => $r['risk_level'] === 'Medium')),
    'Low' => count(array_filter($risks, fn($r) => $r['risk_level'] === 'Low'))
];

// Incident severity distribution
$incident_distribution = [
    'Critical' => $critical_incidents,
    'High' => $high_incidents,
    'Medium' => count(array_filter($incidents, fn($i) => $i['severity'] === 'Medium' && !$i['resolved'])),
    'Low' => count(array_filter($incidents, fn($i) => $i['severity'] === 'Low' && !$i['resolved']))
];

// Top risks by score
$top_risks = $risks;
usort($top_risks, fn($a, $b) => (($b['likelihood'] * $b['impact']) <=> ($a['likelihood'] * $a['impact'])));
$top_risks = array_slice($top_risks, 0, 5);

// Latest events for SIEM stream
$latest_events = [];
foreach (array_slice($incidents, 0, 4) as $incident) {
    $latest_events[] = [
        'time' => date('H:i:s', strtotime($incident['incident_date'] . ' 09:00:00')),
        'source' => 'SIEM',
        'message' => $incident['severity'] . ' incident: ' . $incident['title'],
        'level' => strtolower($incident['severity'])
    ];
}
foreach (array_slice($risks, 0, 3) as $risk) {
    $latest_events[] = [
        'time' => date('H:i:s', strtotime($risk['created_at'] ?? 'now')),
        'source' => 'GRC',
        'message' => $risk['risk_level'] . ' risk: ' . $risk['name'],
        'level' => strtolower($risk['risk_level'])
    ];
}
$latest_events = array_slice($latest_events, 0, 8);

// Recent incidents for timeline
$recent_incidents = array_filter($incidents, fn($i) => !$i['resolved']);
usort($recent_incidents, fn($a, $b) => strtotime($b['incident_date']) - strtotime($a['incident_date']));
$recent_incidents = array_slice($recent_incidents, 0, 5);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-end">
        <div class="col-xl-8">
            <div class="page-kicker">Enterprise Cyber Risk Operations</div>
            <h1 class="page-title">SOC Command Center</h1>
            <p class="page-subtitle">
                Real-time monitoring of risk exposure, active incidents, threat intelligence, and security posture.
            </p>
        </div>
        <div class="col-xl-4 mt-3 mt-xl-0">
            <div class="status-strip justify-content-xl-end">
                <div class="status-pill">
                    <span class="status-dot"></span>
                    SOC Active
                </div>
                <div class="status-pill">
                    <i class="fas fa-clock"></i>
                    <?php echo date('Y-m-d H:i'); ?>
                </div>
                <div class="status-pill">
                    <i class="fas fa-user-shield"></i>
                    <?php echo esc(ucfirst($_SESSION['role'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metric Cards (KPI Widgets) -->
<div class="metric-grid">
    <div class="metric-card critical">
        <div class="metric-header">
            <span class="metric-label">Critical Alerts</span>
            <div class="metric-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>
        <div class="metric-value"><?php echo $critical_watchlist; ?></div>
        <div class="metric-trend up">
            <i class="fas fa-arrow-up"></i>
            Requires immediate attention
        </div>
    </div>

    <div class="metric-card high">
        <div class="metric-header">
            <span class="metric-label">Open Incidents</span>
            <div class="metric-icon">
                <i class="fas fa-fire-flame-curved"></i>
            </div>
        </div>
        <div class="metric-value"><?php echo $open_incidents; ?></div>
        <div class="metric-trend">
            <span class="text-medium"><?php echo $critical_incidents; ?> critical</span>
        </div>
    </div>

    <div class="metric-card medium">
        <div class="metric-header">
            <span class="metric-label">Open Risks</span>
            <div class="metric-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
        <div class="metric-value"><?php echo $open_risks; ?></div>
        <div class="metric-trend">
            <span class="text-medium"><?php echo $critical_risks; ?> critical level</span>
        </div>
    </div>

    <div class="metric-card low">
        <div class="metric-header">
            <span class="metric-label">Resolved</span>
            <div class="metric-icon">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
        <div class="metric-value"><?php echo $resolved_incidents; ?></div>
        <div class="metric-trend down">
            <i class="fas fa-check"></i>
            <?php echo $control_coverage; ?>% coverage
        </div>
    </div>
</div>

<!-- Score Cards Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Risk Exposure Score</div>
            <div class="score-value">
                <span class="score-number <?php echo $exposure_score > 70 ? 'text-critical' : ($exposure_score > 40 ? 'text-medium' : 'text-low'); ?>">
                    <?php echo $exposure_score; ?>
                </span>
                <span class="score-max">/100</span>
            </div>
            <div class="score-bar-container">
                <div class="score-bar <?php echo $exposure_score > 70 ? 'critical' : ($exposure_score > 40 ? 'high' : 'low'); ?>" 
                     style="width: <?php echo $exposure_width; ?>%;"></div>
            </div>
            <div class="score-description">
                Normalized from Likelihood x Impact across all risks
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Incident Pressure</div>
            <div class="score-value">
                <span class="score-number <?php echo $incident_pressure > 50 ? 'text-high' : 'text-low'; ?>">
                    <?php echo $incident_pressure; ?>
                </span>
                <span class="score-max">%</span>
            </div>
            <div class="score-bar-container">
                <div class="score-bar <?php echo $incident_pressure > 50 ? 'high' : 'low'; ?>" 
                     style="width: <?php echo min($incident_pressure, 100); ?>%;"></div>
            </div>
            <div class="score-description">
                Operational load: open incidents vs total
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Control Coverage</div>
            <div class="score-value">
                <span class="score-number <?php echo $control_coverage > 70 ? 'text-low' : 'text-medium'; ?>">
                    <?php echo $control_coverage; ?>
                </span>
                <span class="score-max">%</span>
            </div>
            <div class="score-bar-container">
                <div class="score-bar low" 
                     style="width: <?php echo min($control_coverage, 100); ?>%;"></div>
            </div>
            <div class="score-description">
                Mitigated + closed risks across the register
            </div>
        </div>
    </div>
</div>

<!-- Charts and SIEM Panel -->
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-line"></i>
                Threat Analytics Dashboard
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <canvas id="riskLevelChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <canvas id="incidentsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="siem-panel h-100">
            <div class="siem-header">
                <div>
                    <span class="siem-kicker">SIEM</span>
                    <span class="text-muted ms-2" style="font-size: 0.7rem;">Event Stream</span>
                </div>
                <div class="siem-live-indicator">
                    <span class="siem-live-dot"></span>
                    LIVE
                </div>
            </div>
            <div class="event-stream">
                <?php if (!empty($latest_events)): ?>
                    <?php foreach ($latest_events as $event): ?>
                        <div class="event-row" data-level="<?php echo esc($event['level']); ?>">
                            <div class="event-time"><?php echo esc($event['time']); ?></div>
                            <div class="event-source"><?php echo esc($event['source']); ?></div>
                            <div class="event-message">
                                <span class="event-type-badge <?php echo esc($event['level']); ?>">
                                    <?php echo esc(strtoupper($event['level'])); ?>
                                </span>
                                <span><?php echo esc($event['message']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="event-row" data-level="low">
                        <div class="event-time">--:--:--</div>
                        <div class="event-source">SYS</div>
                        <div class="event-message">
                            <span class="text-muted">No security events recorded yet.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Top Risks and Incident Queue -->
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-arrow-trend-up"></i>
                Highest Risk Scores
            </div>
            <div class="card-body">
                <div class="threat-list">
                    <?php foreach ($top_risks as $risk): ?>
                        <?php $score = (int)$risk['likelihood'] * (int)$risk['impact']; ?>
                        <div class="threat-item">
                            <div class="threat-icon <?php echo strtolower($risk['risk_level']); ?>">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <div class="threat-content">
                                <div class="threat-title"><?php echo esc($risk['name']); ?></div>
                                <div class="threat-meta">
                                    <span class="badge badge-secondary" style="font-size: 0.6rem;">
                                        <?php echo esc($risk['status']); ?>
                                    </span>
                                    L:<?php echo (int)$risk['likelihood']; ?>/5 
                                    I:<?php echo (int)$risk['impact']; ?>/5
                                </div>
                            </div>
                            <span class="badge <?php echo get_risk_badge_class($risk['risk_level']); ?>">
                                <?php echo $score; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($top_risks)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shield-halved fa-2x mb-2"></i>
                            <p class="mb-0">No risks recorded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-list-check"></i>
                Active Incident Queue
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Incident</th>
                                <th>Date</th>
                                <th>Severity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($incidents, 0, 6) as $incident): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc($incident['title']); ?></strong>
                                    </td>
                                    <td><?php echo esc($incident['incident_date']); ?></td>
                                    <td>
                                        <span class="badge <?php echo get_severity_badge_class($incident['severity']); ?>">
                                            <?php echo esc($incident['severity']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($incident['resolved']): ?>
                                            <span class="badge badge-success">Resolved</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($incidents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No incidents recorded yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
            <a href="risks.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Manage Risks
            </a>
            <a href="incidents.php" class="btn btn-danger">
                <i class="fas fa-bolt"></i> Track Incidents
            </a>
            <a href="reports.php" class="btn btn-info">
                <i class="fas fa-file-lines"></i> Generate Reports
            </a>
            <button type="button" class="btn btn-secondary" onclick="printPage()">
                <i class="fas fa-print"></i> Print Dashboard
            </button>
            <button type="button" class="btn btn-secondary" onclick="exportTableToCSV('crmd-report.csv')">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>
</div>

<script data-inline-charts>
// Chart.js configuration
document.addEventListener('DOMContentLoaded', function() {
    // Risk Level Distribution Chart
    const riskCtx = document.getElementById('riskLevelChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: [
                    <?php echo $risk_distribution['Critical']; ?>,
                    <?php echo $risk_distribution['High']; ?>,
                    <?php echo $risk_distribution['Medium']; ?>,
                    <?php echo $risk_distribution['Low']; ?>
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.85)',
                    'rgba(249, 115, 22, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(34, 197, 94, 0.85)'
                ],
                borderColor: '#0d1525',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        font: { size: 11, weight: '600' },
                        padding: 12,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Risk Level Distribution',
                    color: '#f1f5f9',
                    font: { size: 13, weight: '700' }
                },
                tooltip: {
                    backgroundColor: '#0e1628',
                    borderColor: 'rgba(6, 182, 212, 0.4)',
                    borderWidth: 1,
                    titleColor: '#f1f5f9',
                    bodyColor: '#94a3b8',
                    displayColors: true
                }
            }
        }
    });

    // Incidents Chart
    const incCtx = document.getElementById('incidentsChart').getContext('2d');
    new Chart(incCtx, {
        type: 'bar',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                label: 'Active Incidents',
                data: [
                    <?php echo $incident_distribution['Critical']; ?>,
                    <?php echo $incident_distribution['High']; ?>,
                    <?php echo $incident_distribution['Medium']; ?>,
                    <?php echo $incident_distribution['Low']; ?>
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(249, 115, 22, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(34, 197, 94, 0.7)'
                ],
                borderColor: [
                    '#ef4444',
                    '#f97316',
                    '#f59e0b',
                    '#22c55e'
                ],
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Incident Severity Distribution',
                    color: '#f1f5f9',
                    font: { size: 13, weight: '700' }
                },
                tooltip: {
                    backgroundColor: '#0e1628',
                    borderColor: 'rgba(6, 182, 212, 0.4)',
                    borderWidth: 1,
                    titleColor: '#f1f5f9',
                    bodyColor: '#94a3b8'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#64748b',
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.2)'
                    }
                },
                x: {
                    ticks: {
                        color: '#64748b'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
