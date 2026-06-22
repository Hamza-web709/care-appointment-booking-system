<?php
/**
 * CARE – Patient: Book Appointment
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('patient', '../login.php');

$base = '../';
$pageTitle = 'Book Appointment';

$docId = (int)($_GET['doctor_id'] ?? 0);
if ($docId <= 0) {
    header('Location: search_doctors.php'); exit();
}

$userId = (int)$_SESSION['user_id'];
$patient = $conn->query("SELECT id FROM patients WHERE user_id=$userId")->fetch_assoc();
if (!$patient) {
    setFlash('error', 'Patient profile not found. Please contact an administrator.');
    header('Location: ../login.php'); exit();
}
$patId = $patient['id'];

// Get doctor details
$doctor = $conn->query("
    SELECT d.*, c.city_name, u.email 
    FROM doctors d 
    JOIN cities c ON d.city_id = c.id 
    JOIN users u ON d.user_id = u.id 
    WHERE d.id=$docId AND u.status='active'
")->fetch_assoc();

if (!$doctor) {
    setFlash('error', 'Doctor not found.');
    header('Location: search_doctors.php'); exit();
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slotId  = (int)($_POST['slot_id'] ?? 0);
    $problem = trim($_POST['problem'] ?? '');

    if ($slotId > 0 && !empty($problem)) {
        // Verify slot is available
        $slot = $conn->query("SELECT * FROM availability WHERE id=$slotId AND doctor_id=$docId AND status='available'")->fetch_assoc();
        if ($slot) {
            $conn->begin_transaction();
            try {
                // Insert appointment
                $stmt = $conn->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, problem, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param('iisss', $docId, $patId, $slot['available_date'], $slot['start_time'], $problem);
                $stmt->execute();
                
                // Mark slot as booked
                $conn->query("UPDATE availability SET status='booked' WHERE id=$slotId");
                
                $conn->commit();
                setFlash('success', 'Appointment booked successfully! Awaiting doctor approval.');
                header('Location: appointments.php'); exit();
            } catch (Exception $e) {
                $conn->rollback();
                setFlash('error', 'Booking failed. Try again.');
            }
        } else {
            setFlash('error', 'Selected slot is no longer available.');
        }
    } else {
        setFlash('error', 'Please select a time slot and describe your problem.');
    }
}

// Get upcoming available slots for this doctor
$slots = $conn->query("
    SELECT * FROM availability 
    WHERE doctor_id=$docId AND status='available' AND available_date >= CURDATE()
    ORDER BY available_date ASC, start_time ASC
");

// Group slots by date
$groupedSlots = [];
while ($s = $slots->fetch_assoc()) {
    $groupedSlots[$s['available_date']][] = $s;
}

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-calendar-plus me-2"></i>Book Appointment</h1>
        <p class="mb-0 opacity-75">Select an available time slot below</p>
    </div>
</div>
<div class="container py-5">
    <?php showFlash(); ?>
    <div class="row g-5">
        <!-- Doctor Info -->
        <div class="col-lg-4">
            <div class="care-card p-4 text-center sticky-top" style="top:100px;">
                <?php if ($doctor['profile_image']): ?>
                <img src="../uploads/profiles/<?= e($doctor['profile_image']) ?>" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;border:4px solid var(--care-primary);">
                <?php else: ?>
                <div style="width:120px;height:120px;border-radius:50%;background:var(--care-blue-light);color:var(--care-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1rem;border:4px solid var(--care-primary);">
                    <i class="bi bi-person"></i>
                </div>
                <?php endif; ?>
                <h5 class="fw-bold mb-1"><?= e($doctor['full_name']) ?></h5>
                <span class="badge bg-care mb-3"><?= e($doctor['specialization']) ?></span>
                
                <ul class="list-unstyled text-start mb-0">
                    <li class="mb-2"><i class="bi bi-geo-alt text-care me-2"></i><?= e($doctor['city_name']) ?></li>
                    <?php if ($doctor['qualification']): ?>
                    <li class="mb-2"><i class="bi bi-mortarboard text-care me-2"></i><?= e($doctor['qualification']) ?></li>
                    <?php endif; ?>
                    <?php if ($doctor['experience']): ?>
                    <li class="mb-2"><i class="bi bi-clock-history text-care me-2"></i><?= $doctor['experience'] ?> years exp.</li>
                    <?php endif; ?>
                    <li><i class="bi bi-envelope text-care me-2"></i><?= e($doctor['email']) ?></li>
                    <?php if ($doctor['address']): ?>
                    <li class="mt-2 text-muted small py-2 px-3 rounded" style="background:#f8f9fa;"><i class="bi bi-building me-1"></i><?= e($doctor['address']) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="col-lg-8">
            <div class="care-card">
                <div class="card-header"><i class="bi bi-calendar-check me-2"></i>Select available schedule</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <?php if (empty($groupedSlots)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>This doctor currently has no available time slots. Please check back later.
                        </div>
                        <?php else: ?>
                        <h6 class="fw-600 mb-3">1. Select Time Slot <span class="text-danger">*</span></h6>
                        <div class="accordion mb-4" id="slotsAccordion">
                            <?php $i=0; foreach ($groupedSlots as $date => $daySlots): $i++; ?>
                            <div class="accordion-item shadow-sm border mb-2" style="border-radius:var(--radius-md);overflow:hidden;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $i===1?'':'collapsed' ?> fw-500" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" style="background:<?= $i===1?'var(--care-blue-light)':'#fff' ?>;color:<?= $i===1?'var(--care-primary)':'var(--care-text)' ?>;">
                                        <i class="bi bi-calendar3 me-2"></i><?= formatDate($date) ?> &mdash; <?= date('l', strtotime($date)) ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i===1?'show':'' ?>" data-bs-parent="#slotsAccordion">
                                    <div class="accordion-body bg-light">
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($daySlots as $s): ?>
                                            <div class="position-relative">
                                                <input class="form-check-input position-absolute" type="radio" name="slot_id" id="slot<?= $s['id'] ?>" value="<?= $s['id'] ?>" style="opacity:0;" required>
                                                <label class="btn btn-outline-primary shadow-sm" for="slot<?= $s['id'] ?>">
                                                    <?= formatTime($s['start_time']) ?> <i class="bi bi-arrow-right mx-1"></i> <?= formatTime($s['end_time']) ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <h6 class="fw-600 mb-3 mt-4">2. Problem Description <span class="text-danger">*</span></h6>
                        <div class="mb-4">
                            <textarea name="problem" class="form-control" rows="4" placeholder="Briefly describe your medical issue or reason for visit..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-care btn-lg w-100">
                            <i class="bi bi-check2-circle me-2"></i>Confirm Appointment
                        </button>
                        <p class="text-muted small text-center mt-3 mb-0">Your appointment will be marked as "Pending" until approved by the doctor.</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Custom styling for slot radio buttons */
input[type="radio"]:checked + label {
    background-color: var(--care-primary) !important;
    color: white !important;
    border-color: var(--care-primary) !important;
    box-shadow: var(--shadow-md) !important;
}
</style>
<?php include '../includes/footer.php'; ?>
