<?php
/**
 * CARE – Admin: Manage Medical News (CRUD)
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Manage News';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $pub_date = $_POST['published_date'] ?? null;
        $image   = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $res = uploadImage($_FILES['image'], 'news');
            if ($res['success']) $image = $res['filename'];
        }
        $stmt = $conn->prepare("INSERT INTO medical_news (title, content, image, published_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $title, $content, $image, $pub_date);
        $stmt->execute() ? setFlash('success', 'News article added.') : setFlash('error', 'Failed to add news.');
        header('Location: news.php'); exit();
    }

    if ($action === 'edit') {
        $id      = (int)$_POST['id'];
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $pub_date = $_POST['published_date'] ?? null;
        $imgClause = '';
        $imgVal = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $res = uploadImage($_FILES['image'], 'news');
            if ($res['success']) { $imgClause = ', image=?'; $imgVal = $res['filename']; }
        }
        if ($imgVal) {
            $stmt = $conn->prepare("UPDATE medical_news SET title=?, content=?, published_date=?, image=? WHERE id=?");
            $stmt->bind_param('ssssi', $title, $content, $pub_date, $imgVal, $id);
        } else {
            $stmt = $conn->prepare("UPDATE medical_news SET title=?, content=?, published_date=? WHERE id=?");
            $stmt->bind_param('sssi', $title, $content, $pub_date, $id);
        }
        $stmt->execute() ? setFlash('success', 'News updated.') : setFlash('error', 'Update failed.');
        header('Location: news.php'); exit();
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM medical_news WHERE id=$id");
        setFlash('success', 'News deleted.');
        header('Location: news.php'); exit();
    }
}

$news = $conn->query("SELECT * FROM medical_news ORDER BY created_at DESC");
include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-newspaper me-2 text-white"></i>Manage Medical News</h1>
        <p class="mb-0 opacity-75 text-white-50">Create and manage health news articles</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-white">All Articles</h5>
        <button class="btn btn-care" data-bs-toggle="modal" data-bs-target="#addNewsModal">
            <i class="bi bi-plus me-1"></i>Add Article
        </button>
    </div>

    <div class="row g-4">
        <?php while ($n = $news->fetch_assoc()): ?>
        <div class="col-md-6 col-xl-4">
            <div class="care-card h-100">
                <?php if ($n['image'] && file_exists("../uploads/news/".$n['image'])): ?>
                <img src="../uploads/news/<?= e($n['image']) ?>" class="card-img-top" style="height:160px;object-fit:cover;" alt="">
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center" style="height:140px;background:linear-gradient(135deg,var(--care-blue-light),var(--care-teal-light));">
                    <i class="bi bi-newspaper text-care" style="font-size:2.5rem;"></i>
                </div>
                <?php endif; ?>
                <div class="p-4">
                    <p class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i><?= formatDate($n['published_date'] ?? $n['created_at']) ?></p>
                    <h6 class="fw-bold mb-2"><?= e($n['title']) ?></h6>
                    <p class="text-muted small mb-3"><?= e(substr($n['content'],0,100)) ?>...</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editNews<?= $n['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete this article?"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Modal -->
        <div class="modal fade" id="editNews<?= $n['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
                    <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                        <h5 class="modal-title">Edit Article</h5>
                        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body p-4">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= e($n['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="5" required><?= e($n['content']) ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Published Date</label>
                                    <input type="date" name="published_date" class="form-control" value="<?= e($n['published_date']) ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
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

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title"><i class="bi bi-newspaper me-2"></i>Add News Article</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Published Date</label>
                            <input type="date" name="published_date" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Publish Article</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
