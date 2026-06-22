<?php
/**
 * CARE – Footer
 */
?>
<!-- Footer -->
<footer class="care-footer mt-auto pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-heart-pulse-fill text-care fs-3"></i>
                    <span class="fw-bold fs-4 text-white">CARE</span>
                </div>
                <p class="text-white small">Medical Appointment & Information System. Your health, our priority. Connect with qualified doctors and manage your appointments with ease.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-twitter-x fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-linkedin fs-5"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= $base ?>index.php">Home</a></li>
                    <li><a href="<?= $base ?>doctors.php">Doctors</a></li>
                    <li><a href="<?= $base ?>diseases.php">Diseases</a></li>
                    <li><a href="<?= $base ?>news.php">Health News</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white fw-semibold mb-3">Account</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= $base ?>login.php">Login</a></li>
                    <li><a href="<?= $base ?>register.php">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h6 class="text-white fw-semibold mb-3">Contact</h6>
                <ul class="list-unstyled footer-links">
                    <li><i class="bi bi-envelope me-2 text-care"></i>info@care-medical.com</li>
                    <li><i class="bi bi-telephone me-2 text-care"></i>+92 300 1234567</li>
                    <li><i class="bi bi-geo-alt me-2 text-care"></i>123 Health Street, Karachi, Pakistan</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center text-white small">
            &copy; <?= date('Y') ?> <strong class="text-care">CARE</strong> – Medical Appointment & Information System. All rights reserved.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= $base ?>assets/js/script.js"></script>
</body>
</html>
