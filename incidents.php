<?php
/**
 * Incident Tracking Page
 * Cybersecurity Risk Management Dashboard
 */

$page_title = 'Incident Tracking';

require_once __DIR__ . '/includes/header.php';
require_login();

$action = $_GET['action'] ?? 'list';
$incident = null;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid.';
    } elseif (!is_admin()) {
        $error = 'Only administrators can modify incidents.';
    } else {
        $action = $_POST['action'] ?? 'list';
        
        if ($action === 'add') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $incident_date = $_POST['incident_date'] ?? '';
            $severity = $_POST['severity'] ?? '';
            $resolved = isset($_POST['resolved']);
            
            if (empty($title) || empty($description) || empty($incident_date) || empty($severity)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (add_incident($pdo, $title, $description, $incident_date, $severity, $resolved)) {
                    $success = 'Incident added successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to add incident.';
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $incident_date = $_POST['incident_date'] ?? '';
            $severity = $_POST['severity'] ?? '';
            $resolved = isset($_POST['resolved']);
            
            if (empty($title) || empty($description) || empty($incident_date) || empty($severity)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (update_incident($pdo, $id, $title, $description, $incident_date, $severity, $resolved)) {
                    $success = 'Incident updated successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to update incident.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if (delete_incident($pdo, $id)) {
                $success = 'Incident deleted successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to delete incident.';
            }
        }
    }
}

if (($action === 'edit' || $action === 'view') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $incident = get_incident($pdo, $id);
    if (!$incident) {
        $error = 'Incident not found.';
        $action = 'list';
    }
}
?>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-fire-alt"></i> Incident Tracking</h2>
            <?php if (is_admin() && $action === 'list'): ?>
                <a href="?action=add" class="btn btn-danger">
                    <i class="fas fa-plus"></i> Report Incident
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div class="card">
        <div class="card-header"><i class="fas fa-table"></i> All Incidents</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $incidents = get_all_incidents($pdo);
                        if (!empty($incidents)): 
                            foreach ($incidents as $inc):
                        ?>
                            <tr>
                                <td><strong><?php echo esc($inc['title']); ?></strong></td>
                                <td><?php echo $inc['incident_date']; ?></td>
                                <td><span class="badge <?php echo get_severity_badge_class($inc['severity']); ?>"><?php echo $inc['severity']; ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo $inc['resolved'] ? 'Resolved' : 'Unresolved'; ?></span></td>
                                <td>
                                    <a href="?action=view&id=<?php echo $inc['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <?php if (is_admin()): ?>
                                        <a href="?action=edit&id=<?php echo $inc['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete('incident');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $inc['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><br>No incidents recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <?php if (!is_admin()): ?>
        <div class="alert alert-warning"><i class="fas fa-lock"></i> Only administrators can add or edit incidents.</div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="fas fa-form"></i> <?php echo ($action === 'add') ? 'Report New Incident' : 'Edit Incident'; ?></div>
                    <div class="card-body">
                        <form method="POST" action="incidents.php">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?><input type="hidden" name="id" value="<?php echo $incident['id']; ?>"><?php endif; ?>
                            <?php echo get_csrf_input(); ?>
                            
                            <div class="form-group mb-3">
                                <label for="title" class="form-label">Incident Title *</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo $action === 'edit' ? esc($incident['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $action === 'edit' ? esc($incident['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="incident_date" class="form-label">Date of Incident *</label>
                                        <input type="date" id="incident_date" name="incident_date" class="form-control" value="<?php echo $action === 'edit' ? $incident['incident_date'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="severity" class="form-label">Severity *</label>
                                        <select id="severity" name="severity" class="form-select" required>
                                            <option value="">Select severity...</option>
                                            <option value="High" <?php echo ($action === 'edit' && $incident['severity'] === 'High') ? 'selected' : ''; ?>>High</option>
                                            <option value="Medium" <?php echo ($action === 'edit' && $incident['severity'] === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                            <option value="Low" <?php echo ($action === 'edit' && $incident['severity'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" id="resolved" name="resolved" class="form-check-input" <?php echo ($action === 'edit' && $incident['resolved']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="resolved">Mark as Resolved</label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Save Incident</button>
                                <a href="incidents.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'view'): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Incident Details</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Title:</strong><br><?php echo esc($incident['title']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Severity:</strong><br><span class="badge <?php echo get_severity_badge_class($incident['severity']); ?> fs-6"><?php echo $incident['severity']; ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong><br><?php echo $incident['incident_date']; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br><span class="badge bg-secondary"><?php echo $incident['resolved'] ? 'Resolved' : 'Unresolved'; ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong><br><p><?php echo nl2br(esc($incident['description'])); ?></p>
                    </div>
                    <div class="mt-4">
                        <?php if (is_admin()): ?>
                            <a href="?action=edit&id=<?php echo $incident['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                        <?php endif; ?>
                        <a href="incidents.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
