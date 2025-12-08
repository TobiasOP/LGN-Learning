<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer class="bg-dark text-light pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4">
                <!-- About -->
                <div class="col-lg-4 col-md-6">
                    <div class="mb-3">
                        <span class="text-primary fw-bold fs-3">L</span><span class="text-success fw-bold fs-3">G</span><span class="text-warning fw-bold fs-3">N</span>
                        <span class="ms-2 text-white">Learn GeNius</span>
                    </div>
                    <p class="text-white-50 mb-4">
                        Platform e-learning terbaik untuk mengembangkan skill dan karir Anda. Belajar dari tutor profesional dengan materi berkualitas.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3">Kategori</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/pages/courses.php?category=web-development" class="text-white-50 text-decoration-none">Web Development</a></li>
                        <li class="mb-2"><a href="/pages/courses.php?category=mobile-development" class="text-white-50 text-decoration-none">Mobile Development</a></li>
                        <li class="mb-2"><a href="/pages/courses.php?category=data-science" class="text-white-50 text-decoration-none">Data Science</a></li>
                        <li class="mb-2"><a href="/pages/courses.php?category=ui-ux-design" class="text-white-50 text-decoration-none">UI/UX Design</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3">Perusahaan</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Tentang Kami</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Karir</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="/register.php?role=tutor" class="text-white-50 text-decoration-none">Menjadi Tutor</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3">Bantuan</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Hubungi Kami</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Kebijakan Privasi</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                
                <!-- Payment -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3">Pembayaran</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-white text-dark">💳 VISA</span>
                        <span class="badge bg-white text-dark">💳 Mastercard</span>
                        <span class="badge bg-white text-dark">🏦 BCA</span>
                        <span class="badge bg-white text-dark">🏦 Mandiri</span>
                        <span class="badge bg-white text-dark">📱 GoPay</span>
                        <span class="badge bg-white text-dark">📱 OVO</span>
                    </div>
                    <div class="mt-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <small class="text-white-50">Pembayaran 100% Aman</small>
                    </div>
                </div>
            </div>
            
            <hr class="my-4 border-secondary">
            
            <!-- Bottom Footer -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">
                        &copy; <?= date('Y') ?> LGN - Learn GeNius. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-white-50 mb-0">
                        Made with <i class="bi bi-heart-fill text-danger"></i> in Indonesia
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($additionalJs) && is_array($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>