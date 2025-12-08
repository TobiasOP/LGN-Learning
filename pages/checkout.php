<?php
// pages/checkout.php

$pageTitle = 'Checkout';

require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$courseId = intval($_GET['course'] ?? 0);

if (!$courseId) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

$db = getDB();

// Get course
$stmt = $db->prepare("
    SELECT c.*, u.name as tutor_name 
    FROM courses c
    JOIN users u ON c.tutor_id = u.id
    WHERE c.id = ? AND c.is_published = 1
");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

// Check if already enrolled
if (isEnrolled($_SESSION['user_id'], $courseId)) {
    redirect('/pages/learn.php?course=' . $courseId, 'Anda sudah terdaftar di kursus ini', 'info');
}

$user = getCurrentUser();
$displayPrice = $course['discount_price'] ?: $course['price'];
$hasDiscount = $course['discount_price'] && $course['discount_price'] < $course['price'];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-5 bg-light">
    <div class="container">
        <div class="checkout-wrapper">
            <div class="row g-4">
                <!-- Order Details -->
                <div class="col-lg-7">
                    <div class="checkout-card">
                        <h4 class="fw-bold mb-4">Detail Pesanan</h4>
                        
                        <!-- Course Info -->
                        <div class="order-item">
                            <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.jpg' ?>" 
                                 alt="<?= htmlspecialchars($course['title']) ?>"
                                 class="order-item-image">
                            <div class="order-item-info">
                                <h6 class="order-item-title"><?= htmlspecialchars($course['title']) ?></h6>
                                <p class="order-item-tutor mb-0">oleh <?= htmlspecialchars($course['tutor_name']) ?></p>
                            </div>
                            <div class="order-item-price">
                                <?= formatCurrency($displayPrice) ?>
                            </div>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div class="mt-4">
                            <label class="form-label fw-semibold">Punya Kode Kupon?</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="couponCode" placeholder="Masukkan kode kupon">
                                <button class="btn btn-outline-primary" type="button" id="applyCouponBtn" data-course-id="<?= $courseId ?>">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                        
                        <!-- Customer Info -->
                        <div class="mt-4">
                            <h5 class="fw-semibold mb-3">Informasi Pembeli</h5>
                            <div class="bg-light rounded p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Nama</small>
                                        <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Email</small>
                                        <div class="fw-medium"><?= htmlspecialchars($user['email']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-5">
                    <div class="checkout-card">
                        <h4 class="fw-bold mb-4">Ringkasan Pembayaran</h4>
                        
                        <div class="order-summary">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Harga Kursus</span>
                                <span><?= formatCurrency($course['price']) ?></span>
                            </div>
                            
                            <?php if ($hasDiscount): ?>
                            <div class="d-flex justify-content-between mb-3 text-success">
                                <span>Diskon</span>
                                <span>- <?= formatCurrency($course['price'] - $course['discount_price']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-3" id="discountRow" style="display: none !important;">
                                <span>Kupon</span>
                                <span class="text-success" id="discountAmount">- Rp 0</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold fs-4 text-primary" id="totalPrice"><?= formatCurrency($displayPrice) ?></span>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary btn-lg w-100 mt-4" id="payButton" data-course-id="<?= $courseId ?>">
                            <i class="bi bi-credit-card me-2"></i>Bayar Sekarang
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                Pembayaran aman dengan Midtrans
                            </small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted d-block mb-2">Metode Pembayaran yang Tersedia:</small>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark border">Bank Transfer</span>
                                <span class="badge bg-light text-dark border">E-Wallet</span>
                                <span class="badge bg-light text-dark border">QRIS</span>
                                <span class="badge bg-light text-dark border">Kartu Kredit</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guarantee -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check text-success fs-1"></i>
                            <h6 class="fw-bold mt-2">Garansi Uang Kembali 7 Hari</h6>
                            <small class="text-muted">Tidak puas dengan kursus? Ajukan pengembalian dana dalam 7 hari.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
$additionalJs = ['/assets/js/payment.js'];
require_once __DIR__ . '/../includes/footer.php'; 
?>