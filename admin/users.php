<?php
/**
 * CARE – Admin: Manage Users (CRUD)
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Manage Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_status') {
        $id = (int)$_POST['id'];
        $current = $_POST['current_status'] ?? 'active';
        $new = $current === 'active' ? 'inactive' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param('si', $new, $id);
        $stmt->execute();
        setFlash('success', 'User status updated to ' . $new . '.');
    }

    if ($action === 'change_password') {
        $id  = (int)$_POST['id'];
        $pwd = $_POST['new_password'] ?? '';
        if (strlen($pwd) >= 6) {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute() ? setFlash('success', 'Password changed.') : setFlash('error', 'Failed.');
        } else {
            setFlash('error', 'Password must be at least 6 characters.');
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($id == $_SESSION['user_id']) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            $conn->query("DELETE FROM users WHERE id=$id");
            setFlash('success', 'User deleted.');
        }
    }
    header('Location: users.php'); exit();
}

// Fetch all users with profile names using LEFT JOINs to prevent subquery multiple-row errors
$users = $conn->query("
   SELECT u.*, 
       COALESCE(a.full_name, d.full_name, p.full_name) AS full_name,
       COALESCE(d.profile_image, p.profile_image) AS profile_image
FROM users u
LEFT JOIN admins a ON a.user_id = u.id AND u.role = 'admin'
LEFT JOIN doctors d ON d.user_id = u.id AND u.role = 'doctor'
LEFT JOIN patients p ON p.user_id = u.id AND u.role = 'patient'
ORDER BY u.created_at DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-person-lock me-2"></i>Manage Users</h1>
        <p class="mb-0 opacity-75">View, activate/deactivate, and reset passwords</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-people me-2"></i>All Users</h5>
        <a href="add_user.php" class="btn btn-care">
            <i class="bi bi-person-plus-fill me-1"></i>Add User
        </a>
    </div>
    <div class="care-card">
        <div class="card-header"><i class="bi bi-people me-2"></i>All Users (<?= $users->num_rows ?>)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table care-table mb-0">
                    <thead>
                        <tr><th>#</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">

<?php if (!empty($u['profile_image'])): ?>

<img src="../uploads/profiles/<?= e($u['profile_image']) ?>" 
style="width:36px;height:36px;border-radius:50%;object-fit:cover;">

<?php else: ?>

<div style="width:36px;height:36px;border-radius:50%;
background:var(--care-blue-light);
display:flex;
align-items:center;
justify-content:center;
color:var(--care-primary);">

<i class="bi bi-person"></i>

</div>

<?php endif; ?>

<div>
<div class="fw-600"><?= e($u['full_name']) ?></div>
</div>

</div>
                            <td class="small"><?= e($u['email']) ?></td>
                            <td>
                                <?php $roleColors = ['admin'=>'danger','doctor'=>'primary','patient'=>'success']; ?>
                                <span class="badge bg-<?= $roleColors[$u['role']] ?? 'secondary' ?>"><?= ucfirst($u['role']) ?></span>
                            </td>
                            <td><?= statusBadge($u['status']) ?></td>
                            <td class="small text-muted"><?= formatDate($u['created_at']) ?></td>
                            <td>
                                <!-- Toggle status -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $u['status'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $u['status']==='active'?'btn-warning':'btn-success' ?>" title="Toggle Status">
                                        <i class="bi bi-<?= $u['status']==='active'?'pause':'play' ?>-fill"></i>
                                    </button>
                                </form>
                                <!-- Change password -->
                                <button class="btn btn-outline-primary btn-sm reset-pwd-btn" 
                                    data-id="<?= $u['id'] ?>" 
                                    data-username="<?= e($u['username']) ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#resetPwdModal">
                                    <i class="bi bi-key"></i>
                                </button>
                                <!-- Delete -->
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete user <?= e($u['username']) ?>?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SINGLE Dynamic Reset Password Modal (Fixed Flickering) -->
<div class="modal fade" id="resetPwdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="id" id="reset_user_id" value="">
                    <p class="text-muted small mb-3">Setting new password for: <strong id="reset_username_display"></strong></p>
                    <label class="form-label">New Password (min. 6 chars)</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Single Event Listener using Event Delegation to populate the modal 
    // Fixes the Bootstrap modal flickering bug caused by multiple redundant modals
    document.querySelectorAll('.reset-pwd-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('reset_user_id').value = this.dataset.id;
            document.getElementById('reset_username_display').textContent = this.dataset.username;
        });
    });
});
</script>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
