<?php
/**
 * Reports Page
 * Cybersecurity Risk Management Dashboard
 */

$page_title = 'Reports';

require_once __DIR__ . '/includes/header.php';
require_login();

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$all_risks = get_all_risks($pdo);
$all_incidents = get_all_incidents($pdo);
$stats = get_dashboard_stats($pdo);

if ($start_date && $end_date) {
    $all_incidents = array_filter($all_incidents, function($incident) use ($start_date, $end_date) {
        return $incident['incident_date'] >= $start_date && $incident['incident_date'] <= $end_date;
    });
}

$high_risk_count = count(array_filter($all_risks, fn($risk) => $risk['risk_level'] === 'High'));
$medium_risk_count = count(array_filter($all_risks, fn($risk) => $risk['risk_level'] === 'Medium'));
$low_risk_count = count(array_filter($all_risks, fn($risk) => $risk['risk_level'] === 'Low'));
$open_risk_count = count(array_filter($all_risks, fn($risk) => $risk['status'] === 'Open'));
$mitigated_risk_count = count(array_filter($all_risks, fn($risk) => $risk['status'] === 'Mitigated'));
$closed_risk_count = count(array_filter($all_risks, fn($risk) => $risk['status'] === 'Closed'));
$high_incident_count = count(array_filter($all_incidents, fn($incident) => $incident['severity'] === 'High'));
$medium_incident_count = count(array_filter($all_incidents, fn($incident) => $incident['severity'] === 'Medium'));
$low_incident_count = count(array_filter($all_incidents, fn($incident) => $incident['severity'] === 'Low'));
$resolved_count = count(array_filter($all_incidents, fn($incident) => $incident['resolved']));
$unresolved_count = count(array_filter($all_incidents, fn($incident) => !$incident['resolved']));
?>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="page-kicker">Cyber Intelligence Reporting</div>
        <h1 class="page-title"><i class="fas fa-file-lines"></i> Security Reports</h1>
        <p class="page-subtitle">Filter, review, and print executive-ready summaries of risk exposure and incident activity.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter"></i> Report Filters</div>
    <div class="card-body">
        <form method="GET" action="reports.php" class="row g-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Apply</button>
                <a href="reports.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                <button type="button" class="btn btn-success" onclick="printPage()"><i class="fas fa-print"></i> Print</button>
            </div>
        </form>
    </div>
</div>

<div id="printable-content">
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="score-card text-center">
                <div class="score-label">Total Risks</div>
                <div class="score-value justify-content-center"><?php echo (int)$stats['total_risks']; ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="score-card text-center">
                <div class="score-label">High-Level Risks</div>
                <div class="score-value justify-content-center"><?php echo (int)$stats['high_risks']; ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="score-card text-center">
                <div class="score-label">Total Incidents</div>
                <div class="score-value justify-content-center"><?php echo count($all_incidents); ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="score-card text-center">
                <div class="score-label">Resolved Incidents</div>
                <div class="score-value justify-content-center"><?php echo (int)$stats['resolved_incidents']; ?></div>
            </div>
        </div>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-header"><i class="fas fa-triangle-exclamation"></i> Risk Summary</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Likelihood x Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_risks as $risk): ?>
                            <tr>
                                <td><?php echo esc($risk['name']); ?></td>
                                <td><small><?php echo substr(esc($risk['description']), 0, 54) . '...'; ?></small></td>
                                <td><span class="badge <?php echo get_risk_badge_class($risk['risk_level']); ?>"><?php echo esc($risk['risk_level']); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo esc($risk['status']); ?></span></td>
                                <td><?php echo (int)$risk['likelihood']; ?> x <?php echo (int)$risk['impact']; ?> = <?php echo (int)$risk['likelihood'] * (int)$risk['impact']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-header"><i class="fas fa-satellite-dish"></i> Incident Summary</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Severity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_incidents as $incident): ?>
                            <tr>
                                <td><?php echo esc($incident['title']); ?></td>
                                <td><?php echo esc($incident['incident_date']); ?></td>
                                <td><span class="badge <?php echo get_severity_badge_class($incident['severity']); ?>"><?php echo esc($incident['severity']); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo $incident['resolved'] ? 'Resolved' : 'Unresolved'; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-chart-bar"></i> Risk Analysis</div>
                <div class="card-body">
                    <div class="threat-list">
                        <div class="threat-item"><div class="threat-icon high"><i class="fas fa-arrow-up"></i></div><div><div class="threat-title">High Risk</div><div class="threat-meta">Requires immediate treatment planning.</div></div><span class="badge badge-danger"><?php echo $high_risk_count; ?></span></div>
                        <div class="threat-item"><div class="threat-icon medium"><i class="fas fa-minus"></i></div><div><div class="threat-title">Medium Risk</div><div class="threat-meta">Track mitigation progress.</div></div><span class="badge badge-warning"><?php echo $medium_risk_count; ?></span></div>
                        <div class="threat-item"><div class="threat-icon low"><i class="fas fa-arrow-down"></i></div><div><div class="threat-title">Low Risk</div><div class="threat-meta">Monitor as part of normal governance.</div></div><span class="badge badge-info"><?php echo $low_risk_count; ?></span></div>
                    </div>
                    <hr>
                    <p class="mb-0 text-muted">Open: <?php echo $open_risk_count; ?> | Mitigated: <?php echo $mitigated_risk_count; ?> | Closed: <?php echo $closed_risk_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-chart-line"></i> Incident Analysis</div>
                <div class="card-body">
                    <div class="threat-list">
                        <div class="threat-item"><div class="threat-icon high"><i class="fas fa-fire-flame-curved"></i></div><div><div class="threat-title">High Severity</div><div class="threat-meta">Potentially business-impacting events.</div></div><span class="badge badge-danger"><?php echo $high_incident_count; ?></span></div>
                        <div class="threat-item"><div class="threat-icon medium"><i class="fas fa-bell"></i></div><div><div class="threat-title">Medium Severity</div><div class="threat-meta">Events requiring analyst follow-up.</div></div><span class="badge badge-warning"><?php echo $medium_incident_count; ?></span></div>
                        <div class="threat-item"><div class="threat-icon low"><i class="fas fa-circle-info"></i></div><div><div class="threat-title">Low Severity</div><div class="threat-meta">Recorded for trend visibility.</div></div><span class="badge badge-info"><?php echo $low_incident_count; ?></span></div>
                    </div>
                    <hr>
                    <p class="mb-0 text-muted">Resolved: <?php echo $resolved_count; ?> | Unresolved: <?php echo $unresolved_count; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body text-center text-muted small">
            <p>Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>Cybersecurity Risk Management Dashboard</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
