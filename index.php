<?php
/**
 * Dashboard Homepage
 * Cybersecurity Risk Management Dashboard
 */

$page_title = 'Command Center';

require_once __DIR__ . '/includes/header.php';
require_login();

$stats = get_dashboard_stats($pdo);
$risks = get_all_risks($pdo);
$incidents = get_all_incidents($pdo);

$total_risks = count($risks);
$total_incidents = count($incidents);
$open_risks = count(array_filter($risks, fn($risk) => $risk['status'] === 'Open'));
$mitigated_risks = count(array_filter($risks, fn($risk) => $risk['status'] === 'Mitigated'));
$open_incidents = (int)($stats['open_incidents'] ?? 0);
$resolved_incidents = (int)($stats['resolved_incidents'] ?? 0);
$high_risks = (int)($stats['high_risks'] ?? 0);
$high_incidents = count(array_filter($incidents, fn($incident) => $incident['severity'] === 'High' && !$incident['resolved']));

$risk_score_total = array_sum(array_map(fn($risk) => (int)$risk['likelihood'] * (int)$risk['impact'], $risks));
$max_risk_score = max($total_risks * 25, 1);
$exposure_score = (int)round(($risk_score_total / $max_risk_score) * 100);
$exposure_width = min($exposure_score, 100);
$control_coverage = $total_risks > 0 ? (int)round((($mitigated_risks + count(array_filter($risks, fn($risk) => $risk['status'] === 'Closed'))) / $total_risks) * 100) : 0;
$incident_pressure = $total_incidents > 0 ? (int)round(($open_incidents / $total_incidents) * 100) : 0;
$critical_watchlist = $high_risks + $high_incidents;

$latest_events = [];
foreach (array_slice($incidents, 0, 4) as $incident) {
    $latest_events[] = [
        'time' => date('H:i', strtotime($incident['incident_date'] . ' 09:00:00')),
        'source' => 'SIEM',
        'message' => $incident['severity'] . ' incident: ' . $incident['title'],
        'level' => strtolower($incident['severity'])
    ];
}
foreach (array_slice($risks, 0, 3) as $risk) {
    $latest_events[] = [
        'time' => date('H:i', strtotime($risk['created_at'] ?? 'now')),
        'source' => 'GRC',
        'message' => $risk['risk_level'] . ' risk registered: ' . $risk['name'],
        'level' => strtolower($risk['risk_level'])
    ];
}
$latest_events = array_slice($latest_events, 0, 6);

$top_risks = $risks;
usort($top_risks, fn($a, $b) => (($b['likelihood'] * $b['impact']) <=> ($a['likelihood'] * $a['impact'])));
$top_risks = array_slice($top_risks, 0, 5);
?>

<div class="row align-items-end mb-4">
    <div class="col-xl-7">
        <div class="page-kicker">Enterprise Cyber Risk Operations</div>
        <h1 class="page-title">CRMD Command Center</h1>
        <p class="page-subtitle">
            Monitor risk exposure, open incidents, control posture, and threat activity from one dark SOC-style dashboard.
        </p>
    </div>
    <div class="col-xl-5 mt-3 mt-xl-0">
        <div class="status-strip">
            <div class="status-pill"><span class="status-dot"></span> Monitoring online</div>
            <div class="status-pill"><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i'); ?></div>
            <div class="status-pill"><i class="fas fa-user-shield"></i> <?php echo esc($_SESSION['role']); ?> access</div>
        </div>
    </div>
</div>

<div class="metric-grid mb-4">
    <div class="widget-card info">
        <div class="widget-topline">
            <div class="label">Total Risks</div>
            <div class="metric-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="number"><?php echo $total_risks; ?></div>
        <div class="trend"><?php echo $open_risks; ?> open items requiring treatment</div>
    </div>

    <div class="widget-card danger">
        <div class="widget-topline">
            <div class="label">Open Incidents</div>
            <div class="metric-icon"><i class="fas fa-fire-flame-curved"></i></div>
        </div>
        <div class="number"><?php echo $open_incidents; ?></div>
        <div class="trend"><?php echo $high_incidents; ?> high-severity unresolved alerts</div>
    </div>

    <div class="widget-card warning">
        <div class="widget-topline">
            <div class="label">Critical Watchlist</div>
            <div class="metric-icon"><i class="fas fa-bullseye"></i></div>
        </div>
        <div class="number"><?php echo $critical_watchlist; ?></div>
        <div class="trend">High risks plus active severe incidents</div>
    </div>

    <div class="widget-card success">
        <div class="widget-topline">
            <div class="label">Resolved Incidents</div>
            <div class="metric-icon"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="number"><?php echo $resolved_incidents; ?></div>
        <div class="trend"><?php echo $control_coverage; ?>% risk closure or mitigation coverage</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Risk Exposure Score</div>
            <div class="score-value"><?php echo $exposure_score; ?><span>/100</span></div>
            <div class="score-bar"><span style="width: <?php echo $exposure_width; ?>%;"></span></div>
            <div class="threat-meta mt-2">Exposure normalized from Likelihood × Impact across all risk records.</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Incident Pressure</div>
            <div class="score-value"><?php echo $incident_pressure; ?><span>%</span></div>
            <div class="score-bar"><span style="width: <?php echo min($incident_pressure, 100); ?>%;"></span></div>
            <div class="threat-meta mt-2">Operational load: open incidents / total incidents.</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="score-card">
            <div class="score-label">Control Coverage</div>
            <div class="score-value"><?php echo $control_coverage; ?><span>%</span></div>
            <div class="score-bar"><span style="width: <?php echo min($control_coverage, 100); ?>%;"></span></div>
            <div class="threat-meta mt-2">Coverage: mitigated + closed risks across the register.</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Threat Analytics
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
        <div class="siem-panel">
            <div class="panel-kicker">SIEM Event Stream</div>
            <div class="event-stream">
                <?php if (!empty($latest_events)): ?>
                    <?php foreach ($latest_events as $event): ?>
                        <?php
                            $level = $event['level'] ?? 'low';
                            $type = ($event['source'] ?? 'SYS') === 'SIEM' ? 'ATT&CK' : 'GRC';
                            $typeClass = strtolower($level);
                        ?>
                        <div class="event-row" data-level="<?php echo esc($level); ?>">
                            <div class="event-time"><?php echo esc($event['time']); ?></div>
                            <div class="event-source"><?php echo esc($event['source']); ?></div>
                            <div class="event-message">
                                <span class="event-type-badge <?php echo esc($typeClass); ?>">
                                    <i class="fas fa-tag"></i> <?php echo esc($type); ?>
                                </span>
                                <span class="ms-2"><?php echo esc($event['message']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                        <div class="event-row" data-level="low">
                            <div class="event-time">00:00</div>
                            <div class="event-source">SYS</div>
                            <div class="event-message">
                                <span class="event-type-badge low"><i class="fas fa-tag"></i> MONITOR</span>
                                <span class="ms-2">No security events recorded yet.</span>
                            </div>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-arrow-trend-up"></i> Highest Risk Scores
            </div>
            <div class="card-body">
                <div class="threat-list">
                    <?php foreach ($top_risks as $risk): ?>
                        <?php $score = (int)$risk['likelihood'] * (int)$risk['impact']; ?>
                        <div class="threat-item">
                            <div class="threat-icon <?php echo strtolower($risk['risk_level']); ?>">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <div>
                                <div class="threat-title"><?php echo esc($risk['name']); ?></div>
                                <div class="threat-meta">
                                    <?php echo esc($risk['status']); ?> | Likelihood <?php echo (int)$risk['likelihood']; ?>/5 | Impact <?php echo (int)$risk['impact']; ?>/5
                                </div>
                            </div>
                            <span class="badge <?php echo get_risk_badge_class($risk['risk_level']); ?>">Score <?php echo $score; ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($top_risks)): ?>
                        <div class="threat-meta">No risks have been recorded yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-table-list"></i> Active Incident Queue
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
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
                                    <td><strong><?php echo esc($incident['title']); ?></strong></td>
                                    <td><?php echo esc($incident['incident_date']); ?></td>
                                    <td><span class="badge <?php echo get_severity_badge_class($incident['severity']); ?>"><?php echo esc($incident['severity']); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $incident['resolved'] ? 'Resolved' : 'Unresolved'; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($incidents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No incidents recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <a href="risks.php" class="btn btn-primary"><i class="fas fa-plus"></i> Manage Risks</a>
                    <a href="incidents.php" class="btn btn-danger"><i class="fas fa-bolt"></i> Track Incidents</a>
                    <a href="reports.php" class="btn btn-info"><i class="fas fa-file-lines"></i> Reports</a>
                    <button type="button" class="btn btn-secondary" onclick="printPage()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
