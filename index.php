<?php
/**
 * CARE – Home / Landing Page
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$base = '';
$pageTitle = 'Home';

// Get counts for stats
$totalDoctors  = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0];
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
$totalAppts    = $conn->query("SELECT COUNT(*) FROM appointments")->fetch_row()[0];
$totalCities   = $conn->query("SELECT COUNT(*) FROM cities WHERE status='active'")->fetch_row()[0];

// Featured doctors (latest 4)
$featuredDocs = $conn->query("
    SELECT d.*, c.city_name, u.email
    FROM doctors d
    JOIN cities c ON d.city_id = c.id
    JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC LIMIT 4
");

// Recent diseases (3)
$recentDiseases = $conn->query("SELECT * FROM diseases ORDER BY created_at DESC LIMIT 3");

// Recent news (3)
$recentNews = $conn->query("SELECT * FROM medical_news ORDER BY created_at DESC LIMIT 3");

include 'includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i>
                    Trusted Medical Care Platform
                </div>
                <h1 class="hero-title mb-3">
                    Your Health,<br>
                    <span class="highlight">Our Priority</span>
                </h1>
                <p class="hero-desc mb-4">
                    Connect with qualified doctors, book appointments instantly, and access comprehensive medical information — all in one secure platform.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-care btn-lg pulse-btn">
                        <i class="bi bi-person-plus me-2"></i>Get Started Free
                    </a>
                    <a href="doctors.php" class="btn btn-care-outline btn-lg text-white border-white">
                        <i class="bi bi-search me-2"></i>Find Doctors
                    </a>
                    <?php else: ?>
                    <a href="<?= getUserRole() ?>/dashboard.php" class="btn btn-care btn-lg">
                        <i class="bi bi-grid me-2"></i>Go to Dashboard
                    </a>
                    <?php endif; ?>
                </div>
                <!-- Stats row -->
                <div class="d-flex flex-wrap gap-3">
                    <div class="hero-stat">
                        <div class="value"><?= $totalDoctors ?>+</div>
                        <div class="label">Doctors</div>
                    </div>
                    <div class="hero-stat">
                        <div class="value"><?= $totalPatients ?>+</div>
                        <div class="label">Patients</div>
                    </div>
                    <div class="hero-stat">
                        <div class="value"><?= $totalAppts ?>+</div>
                        <div class="label">Appointments</div>
                    </div>
                    <div class="hero-stat">
                        <div class="value"><?= $totalCities ?>+</div>
                        <div class="label">Cities</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-card">
                    <div class="text-center mb-4">
                        <i class="bi bi-heart-pulse-fill text-care" style="font-size:4rem;"></i>
                        <h4 class="text-white mt-2">Quick Appointment Booking</h4>
                        <p class="text-white-50 small">Find the right doctor in seconds</p>
                    </div>
                    <form method="GET" action="doctors.php" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label text-white small">Specialization</label>
                            <input type="text" name="specialization" placeholder="e.g. Cardiologist, Dentist..." class="form-control">
                        </div>
                        <div>
                            <label class="form-label text-white small">City</label>
                            <select name="city_id" class="form-select">
                                <option value="">All Cities</option>
                                <?php
                                $cities = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name");
                                while ($city = $cities->fetch_assoc()):
                                ?>
                                <option value="<?= $city['id'] ?>"><?= e($city['city_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-care w-100 mt-2">
                            <i class="bi bi-search me-2"></i>Search Doctors
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURES SECTION ===== -->
<section class="py-6 py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-care px-3 py-2 mb-2">Why Choose CARE?</span>
            <h2 class="section-title text-white">Everything You Need for Better Healthcare</h2>
            <p class="section-subtitle text-white-50">CARE connects patients with top doctors through a seamless digital experience</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['bi bi-person-badge','Find Expert Doctors','Browse verified specialists by city and specialization. View profiles, qualifications, and experience.'],
                ['bi bi-calendar-check','Easy Appointments','Book appointments in real-time based on doctor availability. Manage all your visits in one place.'],
                ['bi bi-virus','Disease Resource Center','Learn about diseases, preventions, and cures from our comprehensive medical database.'],
                ['bi bi-newspaper','Health News','Stay updated with the latest medical news and breakthroughs from the health world.'],
                ['bi bi-shield-lock','Secure & Private','Your medical data is protected with modern security standards. Safe, encrypted, and private.'],
                ['bi bi-phone','Fully Responsive','Access CARE from any device — smartphone, tablet, or desktop — anytime, anywhere.'],
            ];
            foreach ($features as [$icon, $title, $desc]):
            ?>
            <div class="col-lg-4 col-md-6 observe-animate">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi <?= $icon ?>"></i></div>
                    <h5 class="fw-700 mb-2"><?= $title ?></h5>
                    <p class="text-muted small mb-0"><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FEATURED DOCTORS ===== -->
<?php if ($featuredDocs && $featuredDocs->num_rows > 0): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-care px-3 py-2 mb-2">Our Specialists</span>
                <h2 class="section-title mb-0">Featured Doctors</h2>
            </div>
            <a href="doctors.php" class="btn btn-care-outline">View All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php while ($doc = $featuredDocs->fetch_assoc()): ?>
            <div class="col-lg-3 col-md-6 observe-animate">
                <div class="doctor-card p-4 text-center h-100">
                    <div class="mb-3 d-flex justify-content-center">
                        <?php if ($doc['profile_image']): ?>
                        <img src="uploads/profiles/<?= e($doc['profile_image']) ?>" alt="<?= e($doc['full_name']) ?>" class="doctor-avatar">
                        <?php else: ?>
                        <div class="doctor-avatar-placeholder"><i class="bi bi-person"></i></div>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-1"><?= e($doc['full_name']) ?></h6>
                    <span class="badge bg-care mb-2"><?= e($doc['specialization']) ?></span>
                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i><?= e($doc['city_name']) ?></p>
                    <?php if ($doc['experience']): ?>
                    <p class="text-muted small mb-3"><i class="bi bi-clock-history me-1"></i><?= e($doc['experience']) ?> yrs experience</p>
                    <?php endif; ?>
                    <?php if (isLoggedIn() && getUserRole() === 'patient'): ?>
                    <a href="patient/book_appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn btn-care btn-sm w-100">Book Now</a>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-care-outline btn-sm w-100">Book Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== HOW IT WORKS ===== -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-teal px-3 py-2 mb-2">Simple Process</span>
            <h2 class="section-title text-white">How CARE Works</h2>
            <p class="section-subtitle text-white-50">Get medical care in 3 easy steps</p>
        </div>
        <div class="row g-4 justify-content-center text-white">
            <?php $steps = [
                ['1','bi bi-person-plus','Create Account','Register as a patient to access all features of the CARE platform securely.'],
                ['2','bi bi-search','Find Your Doctor','Search by city or specialization and browse detailed doctor profiles.'],
                ['3','bi bi-calendar-check','Book & Confirm','Select an available slot and book your appointment instantly.'],
            ]; foreach ($steps as [$num, $icon, $title, $desc]): ?>
            <div class="col-lg-4 col-md-6">
                <div class="text-center p-4">
                    <div style="position:relative;display:inline-block;margin-bottom:1.5rem;">
                        <div class="feature-icon mx-auto mb-0">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <span style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;background:var(--care-teal);color:#fff;border-radius:50%;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $num ?></span>
                    </div>
                    <h5 class="fw-bold"><?= $title ?></h5>
                    <p class="text-white-50 small"><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== RECENT DISEASES ===== -->
<?php if ($recentDiseases->num_rows > 0): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-care px-3 py-2 mb-2">Health Info</span>
                <h2 class="section-title mb-0">Disease Information</h2>
            </div>
            <a href="diseases.php" class="btn btn-care-outline">Explore All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php while ($dis = $recentDiseases->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="care-card h-100 p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="feature-icon" style="width:44px;height:44px;font-size:1.2rem;">
                            <i class="bi bi-virus"></i>
                        </div>
                        <h6 class="mb-0 fw-bold"><?= e($dis['title']) ?></h6>
                    </div>
                    <p class="text-muted small mb-3"><?= e(substr($dis['description'], 0, 120)) ?>...</p>
                    <a href="diseases.php#disease-<?= $dis['id'] ?>" class="btn btn-care btn-sm">Learn More</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== LATEST NEWS ===== -->
<?php if ($recentNews->num_rows > 0): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-teal px-3 py-2 mb- text-white">Stay Updated</span>
                <h2 class="section-title mb-0 text-white">Latest Health News</h2>
            </div>
            <a href="news.php" class="btn btn-care-outline">All News <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php while ($n = $recentNews->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="care-card h-100">
                    <?php if ($n['image'] && file_exists("uploads/news/" . $n['image'])): ?>
                    <img src="uploads/news/<?= e($n['image']) ?>" class="card-img-top" style="height:160px;object-fit:cover;" alt="">
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="height:160px;background:linear-gradient(135deg,var(--care-blue-light),var(--care-teal-light));">
                        <i class="bi bi-newspaper text-care fs-1"></i>
                    </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <p class="text-muted small mb-2"><i class="bi bi-calendar3 me-1"></i><?= formatDate($n['published_date'] ?? $n['created_at']) ?></p>
                        <h6 class="fw-bold mb-2"><?= e($n['title']) ?></h6>
                        <p class="text-muted small mb-0"><?= e(substr($n['content'], 0, 100)) ?>...</p>
                        <a href="news.php#news-<?= $n['id'] ?>" class="btn btn-care btn-sm mt-3">Read More</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CTA SECTION ===== -->
<?php if (!isLoggedIn()): ?>
<section class="py-5" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));">
    <div class="container text-center text-white py-3">
        <h2 class="fw-800 mb-3 text-white">Ready to Take Control of Your Health?</h2>
        <p class="mb-4 opacity-75 lead text-white-50">Join thousands of patients who trust CARE for their medical appointments.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn btn-light btn-lg px-5 fw-600 text-care">
                <i class="bi bi-person-plus me-2"></i>Register Now
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-5 fw-600">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
