<?php
/**
 * CARE – Public: Medical News
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Medical News & Updates';

// Pagination
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset= ($page - 1) * $limit;

$totalRows = $conn->query("SELECT COUNT(*) FROM medical_news")->fetch_row()[0];
$totalPages= ceil($totalRows / $limit);

$news = $conn->query("SELECT * FROM medical_news ORDER BY published_date DESC, created_at DESC LIMIT $offset, $limit");

include 'includes/header.php';
?>
<div class="page-hero">
    <div class="container text-center">
        <h1><i class="bi bi-newspaper me-2"></i>Medical News & Updates</h1>
        <p class="mb-0 mx-auto opacity-75" style="max-width:600px;">Stay up-to-date with the latest advancements in healthcare, medical research, and hospital announcements.</p>
    </div>
</div>

<div class="container py-5">
    <?php if ($totalRows > 0): ?>
    <div class="row g-4">
        <?php while ($n = $news->fetch_assoc()): ?>
        <div class="col-lg-4 col-md-6 observe-animate">
            <div class="care-card h-100 d-flex flex-column text-start overflow-hidden hover-lift">
                <?php if ($n['image'] && file_exists("uploads/news/".$n['image'])): ?>
                <img src="uploads/news/<?= e($n['image']) ?>" class="card-img-top w-100" style="height:200px;object-fit:cover;transition:transform 0.3s;" alt="" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center w-100" style="height:200px;background:linear-gradient(135deg,var(--care-blue-light),var(--care-teal-light));">
                    <i class="bi bi-image text-care" style="font-size:3rem;opacity:0.5;"></i>
                </div>
                <?php endif; ?>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-light text-care border me-2"><i class="bi bi-tag-fill me-1"></i>Health</span>
                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= formatDate($n['published_date'] ?? $n['created_at']) ?></small>
                    </div>
                    <h5 class="fw-bold mb-3 line-clamp-2"><?= e($n['title']) ?></h5>
                    <p class="text-muted small mb-4 flex-grow-1" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                        <?= e(strip_tags($n['content'])) ?>
                    </p>
                    <button class="btn btn-outline-primary btn-sm mt-auto" data-bs-toggle="modal" data-bs-target="#newsModal<?= $n['id'] ?>">Read Full Article <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>
        </div>

        <!-- News Detail Modal -->
        <div class="modal fade" id="newsModal<?= $n['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
                    <div class="modal-header border-0 pb-0 pe-4 pt-4">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <?php if ($n['image'] && file_exists("uploads/news/".$n['image'])): ?>
                        <div style="height:300px;background:url('uploads/news/<?= e($n['image']) ?>') center/cover;"></div>
                        <?php endif; ?>
                        <div class="p-4 p-md-5">
                            <span class="badge bg-care mb-3">Medical Update</span>
                            <span class="text-muted small ms-2"><i class="bi bi-calendar-event me-1"></i><?= formatDate($n['published_date'] ?? $n['created_at']) ?></span>
                            <h2 class="fw-bold mb-4"><?= e($n['title']) ?></h2>
                            <div class="text-muted" style="line-height:1.8;white-space:pre-wrap;"><?= e($n['content']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link border-0 shadow-sm rounded-start me-1" href="?page=<?= $page-1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php for ($i=1; $i<=$totalPages; $i++): ?>
            <li class="page-item <?= $i===$page ? 'active' : '' ?>">
                <a class="page-link border-0 shadow-sm mx-1 <?= $i===$page ? 'bg-care text-white' : '' ?>" href="?page=<?= $i ?>" style="border-radius:5px;"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link border-0 shadow-sm rounded-end ms-1" href="?page=<?= $page+1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
    <style>
    .pagination .page-link { color: var(--care-text); font-weight: 500; }
    .pagination .page-link:hover { color: var(--care-primary); background-color: var(--care-blue-light); }
    .page-item.active .page-link { background-color: var(--care-primary) !important; border-color: var(--care-primary) !important; color: white !important; }
    </style>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-5">
        <div style="font-size:4rem;color:var(--care-border);"><i class="bi bi-newspaper border p-4 rounded-circle bg-light"></i></div>
        <h4 class="text-muted mt-4">No News Articles Found</h4>
        <p class="text-muted small">Check back later for updates and announcements.</p>
    </div>
    <?php endif; ?>
</div>

<style>
.hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
</style>
<?php include 'includes/footer.php'; ?>
