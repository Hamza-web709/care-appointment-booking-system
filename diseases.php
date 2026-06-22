<?php
/**
 * CARE – Public: Health & Disease Information
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Disease Information Center';

// Optional Search
$search = $_GET['q'] ?? '';
$whereClause = '';
if ($search) {
    $searchTerm = $conn->real_escape_string($search);
    $whereClause = "WHERE title LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%'";
}

$diseases = $conn->query("SELECT * FROM diseases $whereClause ORDER BY title ASC");

include 'includes/header.php';
?>
<div class="page-hero">
    <div class="container text-center">
        <h1><i class="bi bi-virus me-2"></i>Medical Information Center</h1>
        <p class="mb-0 mx-auto opacity-75" style="max-width:600px;">Search our comprehensive database of diseases, symptoms, preventions, and cures to stay informed about your health.</p>
    </div>
</div>

<!-- Search Bar -->
<div class="container" style="margin-top:-30px;position:relative;z-index:10;">
    <div class="care-card p-4 mx-auto" style="max-width:700px;">
        <form method="GET" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search for diseases or symptoms..." value="<?= e($search) ?>">
            </div>
            <button class="btn btn-care px-4" type="submit">Search</button>
        </form>
    </div>
</div>

<div class="container py-5">
    <?php if ($search): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Search Results for "<?= e($search) ?>" (<?= $diseases->num_rows ?>)</h5>
        <a href="diseases.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clear Search</a>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if ($diseases->num_rows > 0): while ($d = $diseases->fetch_assoc()): ?>
        <div class="col-lg-6 observe-animate">
            <div class="care-card h-100">
                <div class="card-header bg-white border-bottom-0 pb-0 pt-4">
                    <h4 class="fw-bold text-white mb-1"><i class="bi bi-shield-plus me-2 text-care"></i><?= e($d['title']) ?></h4>
                </div>
                <div class="card-body p-4 pt-2">
                    <p class="text-muted mb-4"><?= e($d['description']) ?></p>
                    
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-success me-2"></i>Prevention</h6>
                            <ul class="list-unstyled mb-0 ms-2">
                                <?php 
                                $prevs = $conn->query("SELECT prevention_text FROM preventions WHERE disease_id={$d['id']}");
                                if ($prevs->num_rows > 0): while ($p = $prevs->fetch_assoc()): 
                                ?>
                                <li class="mb-2 d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-success small mt-1 me-2"></i>
                                    <span class="small"><?= e($p['prevention_text']) ?></span>
                                </li>
                                <?php endwhile; else: ?>
                                <li class="small text-muted">No specific prevention data available.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-capsule text-teal me-2"></i>Cures & Treatments</h6>
                            <ul class="list-unstyled mb-0 ms-2">
                                <?php 
                                $cures = $conn->query("SELECT cure_text FROM cures WHERE disease_id={$d['id']}");
                                if ($cures->num_rows > 0): while ($c = $cures->fetch_assoc()): 
                                ?>
                                <li class="mb-2 d-flex align-items-start">
                                    <i class="bi bi-plus-circle-fill text-teal small mt-1 me-2"></i>
                                    <span class="small"><?= e($c['cure_text']) ?></span>
                                </li>
                                <?php endwhile; else: ?>
                                <li class="small text-muted">Consult a doctor for treatment options.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top-0 py-3 text-center">
                    <a href="doctors.php?specialization=<?= urlencode($d['title']) ?>" class="btn btn-outline-primary btn-sm">Find Specialists for <?= e($d['title']) ?></a>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="col-12 text-center py-5">
            <div style="font-size:4rem;color:var(--care-border);"><i class="bi bi-journal-x"></i></div>
            <h5 class="text-muted mt-3">No information found.</h5>
            <p class="text-muted small">Try a different search term.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
