<?php
// pages/payment_pending.php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/midtrans.php';

requireLogin();

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    redirect('/pages/courses.php', 'Transaksi tidak ditemukan', 'error');
}

$db = getDB();
$stmt = $db->prepare("SELECT t.*, c.title as course_title FROM transactions t JOIN courses c ON t.course_id = c.id WHERE t.order_id = ? LIMIT 1");
$stmt->execute([$orderId]);
$tx = $stmt->fetch();

if (!$tx) {
    redirect('/pages/courses.php', 'Transaksi tidak ditemukan', 'error');
}

// Ensure current user owns this transaction (admin may be allowed later)
if ($tx['user_id'] != $_SESSION['user_id']) {
    redirect('/pages/courses.php', 'Akses ditolak untuk transaksi ini', 'error');
}

$pageTitle = 'Pembayaran - Menunggu';
$additionalJs = ['/assets/js/payment.js'];
require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Pembayaran Menunggu</h4>
                    <p class="text-muted">Transaksi Anda sedang menunggu pembayaran. Silakan selesaikan pembayaran menggunakan metode yang tersedia.</p>

                    <div class="mb-3">
                        <strong>Order ID:</strong> <?= htmlspecialchars($tx['order_id']) ?><br>
                        <strong>Kursus:</strong> <?= htmlspecialchars($tx['course_title']) ?><br>
                        <strong>Jumlah:</strong> <?= htmlspecialchars(number_format($tx['final_amount'], 0, ',', '.')) ?> IDR<br>
                        <strong>Status:</strong> <span class="badge bg-warning text-dark"><?= htmlspecialchars($tx['transaction_status']) ?></span><br>
                        <?php if (!empty($tx['expired_at'])): ?>
                            <strong>Kadaluarsa:</strong> <?= htmlspecialchars($tx['expired_at']) ?><br>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($tx['payment_type']) || !empty($tx['payment_code'])): ?>
                        <div class="alert alert-info">
                            <p class="mb-1"><strong>Instruksi Pembayaran:</strong></p>
                            <?php if (!empty($tx['payment_type'])): ?>
                                <div>Metode: <?= htmlspecialchars($tx['payment_type']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($tx['payment_code'])): ?>
                                <div>Kode / Instruksi: <code><?= htmlspecialchars($tx['payment_code']) ?></code></div>
                            <?php endif; ?>
                            <?php if (!empty($tx['snap_redirect_url'])): ?>
                                <div class="mt-2">
                                    <a href="<?= htmlspecialchars($tx['snap_redirect_url']) ?>" target="_blank" class="btn btn-outline-primary">Buka halaman pembayaran</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <?php if (!empty($tx['snap_token'])): ?>
                            <button id="openSnapBtn" class="btn btn-primary">Lanjutkan Pembayaran</button>
                        <?php endif; ?>
                        <a href="/pages/my_learning.php" class="btn btn-secondary">Kembali ke Kursus</a>
                        <!-- <a href="/pages/transactions.php" class="btn btn-link">Lihat Transaksi</a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const snapToken = <?= json_encode($tx['snap_token'] ?? null) ?>;
    const orderId = <?= json_encode($tx['order_id']) ?>;

    const openBtn = document.getElementById('openSnapBtn');
    if (openBtn && snapToken) {
        openBtn.addEventListener('click', function() {
            if (typeof snap === 'undefined') {
                alert('Payment gateway tidak tersedia.');
                return;
            }

            snap.pay(snapToken, {
                onSuccess: function(result) {
                    window.location.href = `/pages/payment_success.php?order_id=${orderId}`;
                },
                onPending: function(result) {
                    window.location.href = `/pages/payment_pending.php?order_id=${orderId}`;
                },
                onError: function(result) {
                    alert('Terjadi kesalahan saat membuka pembayaran.');
                },
                onClose: function() {
                    // do nothing
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
