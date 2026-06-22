<?php
/**
 * CARE – Admin: Manage Diseases + Preventions + Cures
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Manage Diseases';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_disease') {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        if ($title && $desc) {
            $stmt = $conn->prepare("INSERT INTO diseases (title, description) VALUES (?, ?)");
            $stmt->bind_param('ss', $title, $desc);
            if ($stmt->execute()) {
                $did = $conn->insert_id;
                // Preventions
                $prevs = array_filter(array_map('trim', explode("\n", $_POST['preventions'] ?? '')));
                foreach ($prevs as $prev) {
                    $ps = $conn->prepare("INSERT INTO preventions (disease_id, prevention_text) VALUES (?, ?)");
                    $ps->bind_param('is', $did, $prev);
                    $ps->execute();
                }
                // Cures
                $cures = array_filter(array_map('trim', explode("\n", $_POST['cures'] ?? '')));
                foreach ($cures as $cure) {
                    $cs = $conn->prepare("INSERT INTO cures (disease_id, cure_text) VALUES (?, ?)");
                    $cs->bind_param('is', $did, $cure);
                    $cs->execute();
                }
                setFlash('success', "Disease '$title' added successfully.");
            }
        }
        header('Location: diseases.php'); exit();
    }

    if ($action === 'delete_disease') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM diseases WHERE id=$id");
        setFlash('success', 'Disease deleted.');
        header('Location: diseases.php'); exit();
    }

    if ($action === 'edit_disease') {
        $id    = (int)$_POST['id'];
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $stmt  = $conn->prepare("UPDATE diseases SET title=?, description=? WHERE id=?");
        $stmt->bind_param('ssi', $title, $desc, $id);
        $stmt->execute();

        // Update preventions: delete old, add new
        $conn->query("DELETE FROM preventions WHERE disease_id=$id");
        $prevs = array_filter(array_map('trim', explode("\n", $_POST['preventions'] ?? '')));
        foreach ($prevs as $prev) {
            $ps = $conn->prepare("INSERT INTO preventions (disease_id, prevention_text) VALUES (?, ?)");
            $ps->bind_param('is', $id, $prev);
            $ps->execute();
        }
        $conn->query("DELETE FROM cures WHERE disease_id=$id");
        $cures = array_filter(array_map('trim', explode("\n", $_POST['cures'] ?? '')));
        foreach ($cures as $cure) {
            $cs = $conn->prepare("INSERT INTO cures (disease_id, cure_text) VALUES (?, ?)");
            $cs->bind_param('is', $id, $cure);
            $cs->execute();
        }
        setFlash('success', 'Disease updated.');
        header('Location: diseases.php'); exit();
    }
}

// Fetch diseases with counts
$diseases = $conn->query("
    SELECT d.*,
        (SELECT COUNT(*) FROM preventions p WHERE p.disease_id = d.id) as prev_count,
        (SELECT COUNT(*) FROM cures c WHERE c.disease_id = d.id) as cure_count
    FROM diseases d ORDER BY d.created_at DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-virus me-2 text-white"></i>Manage Diseases</h1>
        <p class="mb-0 opacity-75 text-white-50">Add diseases, preventions, and cures</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-white">All Diseases</h5>
        <button class="btn btn-care" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">
            <i class="bi bi-plus me-1"></i>Add Disease
        </button>
    </div>

    <div class="row g-4">
        <?php while ($d = $diseases->fetch_assoc()):
            // Fetch preventions and cures
            $prevs = $conn->query("SELECT * FROM preventions WHERE disease_id={$d['id']}");
            $cures = $conn->query("SELECT * FROM cures WHERE disease_id={$d['id']}");
            $prevText = '';
            while ($p = $prevs->fetch_assoc()) $prevText .= $p['prevention_text'] . "\n";
            $cureText = '';
            while ($c = $cures->fetch_assoc()) $cureText .= $c['cure_text'] . "\n";
        ?>
        <div class="col-md-6">
            <div class="care-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="fw-bold mb-0"><?= e($d['title']) ?></h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editDis<?= $d['id'] ?>"><i class="bi bi-pencil"></i></button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete_disease">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete this disease?"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <p class="text-muted small mb-3"><?= e(substr($d['description'],0,150)) ?>...</p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-care"><i class="bi bi-shield-check me-1"></i><?= $d['prev_count'] ?> Preventions</span>
                        <span class="badge bg-teal"><i class="bi bi-capsule me-1"></i><?= $d['cure_count'] ?> Cures</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Disease Modal -->
        <div class="modal fade" id="editDis<?= $d['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
                    <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                        <h5 class="modal-title">Edit Disease</h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body p-4">
                            <input type="hidden" name="action" value="edit_disease">
                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= e($d['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required><?= e($d['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preventions <small class="text-muted">(one per line)</small></label>
                                <textarea name="preventions" class="form-control" rows="4"><?= e(trim($prevText)) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cures <small class="text-muted">(one per line)</small></label>
                                <textarea name="cures" class="form-control" rows="4"><?= e(trim($cureText)) ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-care">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add Disease Modal -->
<div class="modal fade" id="addDiseaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title"><i class="bi bi-virus me-2"></i>Add New Disease</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add_disease">
                    <div class="mb-3">
                        <label class="form-label">Disease Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preventions <small class="text-muted">(one per line)</small></label>
                        <textarea name="preventions" class="form-control" rows="4" placeholder="Wash hands regularly&#10;Wear masks&#10;Get vaccinated"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cures <small class="text-muted">(one per line)</small></label>
                        <textarea name="cures" class="form-control" rows="4" placeholder="Rest and hydration&#10;Prescribed medications"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Add Disease</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
