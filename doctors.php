<?php
/**
 * CARE – Public: Doctors Directory
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Our Doctors';

$citiesList = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name")->fetch_all(MYSQLI_ASSOC);

$city_id = $_GET['city_id'] ?? '';
$spec    = $_GET['specialization'] ?? '';

$where = ["u.status = 'active'"];
if ($city_id) $where[] = "d.city_id = " . (int)$city_id;
if ($spec)    $where[] = "d.specialization LIKE '%" . $conn->real_escape_string($spec) . "%'";

$whereClause = implode(' AND ', $where);

$sql = "
    SELECT d.*, c.city_name, u.email 
    FROM doctors d
    JOIN cities c ON d.city_id = c.id
    JOIN users u ON d.user_id = u.id
    WHERE $whereClause
    ORDER BY d.full_name ASC
";
$doctors = $conn->query($sql);

include 'includes/header.php';
?>
<div class="page-hero">
    <div class="container text-center">
        <h1><i class="bi bi-people me-2"></i>Our Medical Specialists</h1>
        <p class="mb-0 mx-auto opacity-75" style="max-width:600px;">Find the right doctor for your needs. Browse our extensive directory of highly qualified medical professionals across various specialties.</p>
    </div>
</div>

<!-- Search Bar -->
<div class="container" style="margin-top:-30px;position:relative;z-index:10;">
    <div class="care-card p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Specialization</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person-badge text-muted"></i></span>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g. Cardiologist, Dentist..." value="<?= e($spec) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">City</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-geo-alt text-muted"></i></span>
                    <select name="city_id" class="form-select">
                        <option value="">All Cities</option>
                        <?php foreach ($citiesList as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $city_id==$c['id']?'selected':'' ?>><?= e($c['city_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-care w-100 py-2"><i class="bi bi-search me-2"></i>Find Doctors</button>
            </div>
        </form>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Specialists Directory (<?= $doctors->num_rows ?>)</h5>
        <?php if ($city_id || $spec): ?>
        <a href="doctors.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clear Filters</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if ($doctors->num_rows > 0): while ($d = $doctors->fetch_assoc()): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 observe-animate">
            <div class="doctor-card p-4 text-center h-100 d-flex flex-column">
                <div class="mb-3 d-flex justify-content-center">
                    <?php if ($d['profile_image']): ?>
                    <img src="uploads/profiles/<?= e($d['profile_image']) ?>" alt="<?= e($d['full_name']) ?>" class="doctor-avatar">
                    <?php else: ?>
                    <div class="doctor-avatar-placeholder"><i class="bi bi-person"></i></div>
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold mb-1"> <?= e($d['full_name']) ?></h6>
                <span class="badge bg-care mb-2 mx-auto"><?= e($d['specialization']) ?></span>
                
                <div class="text-muted small mb-3">
                    <p class="mb-1"><i class="bi bi-geo-alt me-1"></i><?= e($d['city_name']) ?></p>
                    <?php if ($d['experience']): ?>
                    <p class="mb-0"><i class="bi bi-clock-history me-1"></i><?= e($d['experience']) ?> years experience</p>
                    <?php endif; ?>
                </div>
                
                <div class="mt-auto pt-3 border-top">
                    <a href="<?= isLoggedIn() && getUserRole() === 'patient' ? "patient/book_appointment.php?doctor_id={$d['id']}" : "login.php" ?>" class="btn btn-outline-primary w-100">
                        Ask/Book Appointment
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="col-12 text-center py-5">
            <div style="font-size:4rem;color:var(--care-border);"><i class="bi bi-search"></i></div>
            <h5 class="text-muted mt-3">No doctors found matching your criteria.</h5>
            <p class="text-muted small">Try adjusting your filters or search terms.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
