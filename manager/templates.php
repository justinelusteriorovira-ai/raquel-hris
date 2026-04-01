<?php
$page_title = 'Evaluation Templates';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Handle status change (MUST be before header.php to allow redirect)
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    $tid = (int)$_GET['archive'];
    $conn->query("UPDATE evaluation_templates SET status = 'Archived' WHERE template_id = $tid");
    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Template', $tid, 'Archived evaluation template');
    redirectWith(BASE_URL . '/manager/templates.php', 'success', 'Template archived successfully.');
}

if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $tid = (int)$_GET['activate'];
    $conn->query("UPDATE evaluation_templates SET status = 'Active' WHERE template_id = $tid");
    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Template', $tid, 'Activated evaluation template');
    redirectWith(BASE_URL . '/manager/templates.php', 'success', 'Template activated successfully.');
}

// Handle permanent delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tid = (int)$_GET['delete'];
    // Delete related evaluation scores that reference this template's criteria
    $conn->query("DELETE es FROM evaluation_scores es INNER JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE ec.template_id = $tid");
    // Delete evaluations using this template
    $conn->query("DELETE FROM evaluations WHERE template_id = $tid");
    // Delete criteria (CASCADE should handle this, but be safe)
    $conn->query("DELETE FROM evaluation_criteria WHERE template_id = $tid");
    // Delete the template
    $conn->query("DELETE FROM evaluation_templates WHERE template_id = $tid");
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Template', $tid, 'Permanently deleted evaluation template');
    redirectWith(BASE_URL . '/manager/templates.php', 'success', 'Template deleted permanently.');
}

require_once '../includes/header.php';

// Fetch active/draft templates only (archived ones are on the archive page)
$templates = $conn->query("SELECT et.*, u.full_name as created_by_name,
    (SELECT COUNT(*) FROM evaluation_criteria WHERE template_id = et.template_id) as criteria_count,
    (SELECT SUM(weight) FROM evaluation_criteria WHERE template_id = et.template_id) as total_weight
    FROM evaluation_templates et
    LEFT JOIN users u ON et.created_by = u.user_id
    WHERE et.status != 'Archived'
    ORDER BY et.created_at DESC");

// Count archived templates for the archive badge
$archived_count = $conn->query("SELECT COUNT(*) as cnt FROM evaluation_templates WHERE status = 'Archived'")->fetch_assoc()['cnt'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Manage evaluation templates and criteria</p>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/manager/template-archive.php" class="btn btn-outline-secondary">
            <i class="fas fa-archive me-2"></i>Archive
            <?php if ($archived_count > 0): ?>
                <span class="badge bg-secondary ms-1"><?php echo $archived_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/manager/create-template.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Template
        </a>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-file-alt me-2"></i>Active Templates</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Description</th>
                        <th>Criteria</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($templates->num_rows === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No templates found. Create one to get started.</td></tr>
                    <?php else: ?>
                        <?php while ($t = $templates->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo e($t['template_name']); ?></strong></td>
                                <td><small><?php echo e(substr($t['description'] ?? '', 0, 60)); ?></small></td>
                                <td><span class="badge bg-secondary"><?php echo $t['criteria_count']; ?> criteria</span></td>
                                <td>
                                    <?php
                                    $tw = (float)($t['total_weight'] ?? 0);
                                    $wclass = ($tw == 100) ? 'bg-success' : 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $wclass; ?>"><?php echo $tw; ?>%</span>
                                </td>
                                <td>
                                    <?php
                                    $sclass = 'bg-secondary';
                                    if ($t['status'] === 'Active') $sclass = 'bg-success';
                                    elseif ($t['status'] === 'Archived') $sclass = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $sclass; ?>"><?php echo e($t['status']); ?></span>
                                </td>
                                <td><?php echo e($t['created_by_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/manager/edit-template.php?id=<?php echo $t['template_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($t['status'] === 'Active'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning" title="Archive"
                                                onclick="setArchiveTarget(<?php echo $t['template_id']; ?>, '<?php echo e(addslashes($t['template_name'])); ?>')"
                                                data-bs-toggle="modal" data-bs-target="#archiveModal">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="?activate=<?php echo $t['template_id']; ?>" class="btn btn-sm btn-outline-success" title="Activate">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Permanently"
                                            onclick="setDeleteTarget(<?php echo $t['template_id']; ?>, '<?php echo e(addslashes($t['template_name'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deleteTemplateModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-archive me-2"></i>Archive Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Archive template <strong id="archiveTemplateName"></strong>?</p>
                <p class="text-muted small">It can be reactivated later.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="archiveConfirmBtn" class="btn btn-warning"><i class="fas fa-archive me-1"></i>Archive</a>
            </div>
        </div>
    </div>
</div>
<script>
function setArchiveTarget(id, name) {
    document.getElementById('archiveTemplateName').textContent = name;
    document.getElementById('archiveConfirmBtn').href = '?archive=' + id;
}
function setDeleteTarget(id, name) {
    document.getElementById('deleteTemplateName').textContent = name;
    document.getElementById('deleteTemplateBtn').href = '?delete=' + id;
}
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Permanently delete <strong id="deleteTemplateName"></strong>?</p>
                <p class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>This will also remove all evaluations using this template. This cannot be undone!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteTemplateBtn" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete Permanently</a>
            </div>
        </div>
    </div>
</div>

