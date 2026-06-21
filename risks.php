<?php
/**
 * Risk Management Page
 * Cybersecurity Risk Management Dashboard
 */

$page_title = 'Risk Management';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$risk = null;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid. Please try again.';
    } elseif (!is_admin()) {
        $error = 'Only administrators can modify risks.';
    } else {
        $action = $_POST['action'] ?? 'list';
        
        if ($action === 'add') {
            // Add risk
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $likelihood = (int)($_POST['likelihood'] ?? 0);
            $impact = (int)($_POST['impact'] ?? 0);
            $status = $_POST['status'] ?? 'Open';
            
            // Validate input
            if (empty($name) || empty($description) || $likelihood < 1 || $likelihood > 5 || $impact < 1 || $impact > 5) {
                $error = 'Please fill in all fields correctly.';
            } else {
                if (add_risk($pdo, $name, $description, $likelihood, $impact, $status)) {
                    $success = 'Risk added successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to add risk. Please try again.';
                }
            }
        } elseif ($action === 'edit') {
            // Edit risk
            $id = (int)($_POST['id'] ?? 0);
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $likelihood = (int)($_POST['likelihood'] ?? 0);
            $impact = (int)($_POST['impact'] ?? 0);
            $status = $_POST['status'] ?? 'Open';
            
            if (empty($name) || empty($description) || $likelihood < 1 || $likelihood > 5 || $impact < 1 || $impact > 5) {
                $error = 'Please fill in all fields correctly.';
            } else {
                if (update_risk($pdo, $id, $name, $description, $likelihood, $impact, $status)) {
                    $success = 'Risk updated successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to update risk.';
                }
            }
        } elseif ($action === 'delete') {
            // Delete risk
            $id = (int)($_POST['id'] ?? 0);
            if (delete_risk($pdo, $id)) {
                $success = 'Risk deleted successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to delete risk.';
            }
        }
    }
}

// Get risk for edit form
if (($action === 'edit' || $action === 'view') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $risk = get_risk($pdo, $id);
    if (!$risk) {
        $error = 'Risk not found.';
        $action = 'list';
    }
}
?>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-exclamation-triangle"></i> Risk Management</h2>
            <?php if (is_admin() && $action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Risk
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Messages -->
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Risks List -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table"></i> All Risks
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Likelihood</th>
                            <th>Impact</th>
                            <th>Risk Level</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $risks = get_all_risks($pdo);
                        if (!empty($risks)): 
                            foreach ($risks as $r):
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc($r['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo substr(esc($r['description']), 0, 50) . '...'; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $r['likelihood']; ?>/5</span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $r['impact']; ?>/5</span>
                                </td>
                                <td>
                                    <span class="badge <?php echo get_risk_badge_class($r['risk_level']); ?>">
                                        <?php echo $r['risk_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $r['status']; ?></span>
                                </td>
                                <td>
                                    <a href="?action=view&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (is_admin()): ?>
                                        <a href="?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete('risk');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No risks recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Form -->
    <?php if (!is_admin()): ?>
        <div class="alert alert-warning">
            <i class="fas fa-lock"></i> Only administrators can add or edit risks.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-form"></i> <?php echo ($action === 'add') ? 'Add New Risk' : 'Edit Risk'; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="risks.php">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $risk['id']; ?>">
                            <?php endif; ?>
                            <?php echo get_csrf_input(); ?>
                            
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Risk Name *</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="form-control" 
                                    value="<?php echo $action === 'edit' ? esc($risk['name']) : ''; ?>"
                                    required
                                >
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-control" 
                                    rows="4"
                                    required
                                ><?php echo $action === 'edit' ? esc($risk['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="likelihood" class="form-label">Likelihood (1-5) *</label>
                                        <input 
                                            type="number" 
                                            id="likelihood" 
                                            name="likelihood" 
                                            class="form-control" 
                                            min="1" 
                                            max="5" 
                                            value="<?php echo $action === 'edit' ? $risk['likelihood'] : '3'; ?>"
                                            required
                                        >
                                        <small class="form-text text-muted">1=Unlikely, 5=Very Likely</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="impact" class="form-label">Impact (1-5) *</label>
                                        <input 
                                            type="number" 
                                            id="impact" 
                                            name="impact" 
                                            class="form-control" 
                                            min="1" 
                                            max="5" 
                                            value="<?php echo $action === 'edit' ? $risk['impact'] : '3'; ?>"
                                            required
                                        >
                                        <small class="form-text text-muted">1=Minor, 5=Critical</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="Open" <?php echo ($action === 'edit' && $risk['status'] === 'Open') ? 'selected' : ''; ?>>Open</option>
                                    <option value="Mitigated" <?php echo ($action === 'edit' && $risk['status'] === 'Mitigated') ? 'selected' : ''; ?>>Mitigated</option>
                                    <option value="Closed" <?php echo ($action === 'edit' && $risk['status'] === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Risk
                                </button>
                                <a href="risks.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Risk Level Calculator -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calculator"></i> Risk Level Calculator
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Risk level is automatically calculated based on:</p>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Likelihood x Impact</strong></td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>High Risk:</strong> >= 15</td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Medium Risk:</strong> 6-14</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Low Risk:</strong> <= 5</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'view'): ?>
    <!-- View Risk Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Risk Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Risk Name:</strong><br>
                            <?php echo esc($risk['name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Risk Level:</strong><br>
                            <span class="badge <?php echo get_risk_badge_class($risk['risk_level']); ?> fs-6">
                                <?php echo $risk['risk_level']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            <span class="badge bg-secondary"><?php echo $risk['status']; ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            <?php echo format_date($risk['created_at']); ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Description:</strong><br>
                        <p><?php echo nl2br(esc($risk['description'])); ?></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="small text-muted">Likelihood</div>
                                <div class="h4 mb-0"><?php echo $risk['likelihood']; ?>/5</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="small text-muted">Impact</div>
                                <div class="h4 mb-0"><?php echo $risk['impact']; ?>/5</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="small text-muted">Score</div>
                                <div class="h4 mb-0"><?php echo $risk['likelihood'] * $risk['impact']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <?php if (is_admin()): ?>
                            <a href="?action=edit&id=<?php echo $risk['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        <?php endif; ?>
                        <a href="risks.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Risks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
