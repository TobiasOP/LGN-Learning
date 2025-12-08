<?php
// pages/payment_success.php

$pageTitle = 'Pembayaran Berhasil';

require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$orderId = sanitize($_GET['order_id'] ?? '');

if (empty($orderId)) {
    redirect('/pages/my_learning.php');
}

$db = getDB();

// Get transaction
$stmt = $db->prepare("
    SELECT t.*, c.title as course_title, c.slug as course_slug, c.thumbnail
    FROM transactions t
    JOIN courses c ON t.course_id = c.id
    WHERE t.order_id = ? AND t.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$transaction = $stmt->fetch();

if (!$transaction) {
    redirect('/pages/my_learning.php', 'Transaksi tidak ditemukan', 'error');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <?php if ($transaction['transaction_status'] === 'success'): ?>
                        <div class="mb-4">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3">Pembayaran Berhasil!</h3>
                        <p class="text-muted mb-4">
                            Selamat! Anda sekarang terdaftar di kursus <strong><?= htmlspecialchars($transaction['course_title']) ?></strong>
                        </p>
                        
                        <div class="bg-light rounded p-4 mb-4 text-start">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Order ID</div>
                                <div class="col-6 fw-medium"><?= $transaction['order_id'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Jumlah</div>
                                <div class="col-6 fw-medium"><?= formatCurrency($transaction['final_amount']) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Metode Pembayaran</div>
                                <div class="col-6 fw-medium"><?= ucfirst(str_replace('_', ' ', $transaction['payment_type'] ?: '-')) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-muted">Tanggal</div>
                                <div class="col-6 fw-medium"><?= formatDateTime($transaction['paid_at'] ?: $transaction['created_at']) ?></div>
                            </div>
                        </div>
                        
                        <a href="/pages/learn.php?course=<?= $transaction['course_id'] ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-play-circle me-2"></i>Mulai Belajar Sekarang
                        </a>
                        
                        <?php elseif ($transaction['transaction_status'] === 'pending'): ?>
                        <div class="mb-4">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bi bi-hourglass-split text-warning" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3">Menunggu Pembayaran</h3>
                        <p class="text-muted mb-4">
                            Silakan selesaikan pembayaran Anda untuk mengakses kursus.
                        </p>
                        
                        <div class="bg-light rounded p-4 mb-4 text-start">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Order ID</div>
                                <div class="col-6 fw-medium"><?= $transaction['order_id'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Jumlah</div>
                                <div class="col-6 fw-medium"><?= formatCurrency($transaction['final_amount']) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-muted">Batas Pembayaran</div>
                                <div class="col-6 fw-medium"><?= formatDateTime($transaction['expired_at']) ?></div>
                            </div>
                        </div>
                        
                        <?php if ($transaction['snap_token']): ?>
                        <button class="btn btn-primary btn-lg" onclick="continuePayment('<?= $transaction['snap_token'] ?>')">
                            <i class="bi bi-credit-card me-2"></i>Lanjutkan Pembayaran
                        </button>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="mb-4">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3">Pembayaran Gagal</h3>
                        <p class="text-muted mb-4">
                            Maaf, pembayaran Anda tidak berhasil. Silakan coba lagi.
                        </p>
                        
                        <a href="/pages/checkout.php?course=<?= $transaction['course_id'] ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-repeat me-2"></i>Coba Lagi
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function continuePayment(snapToken) {
    snap.pay(snapToken, {
        onSuccess: function(result) {
            window.location.reload();
        },
        onPending: function(result) {
            LGN.Toast.info('Menunggu pembayaran...');
        },
        onError: function(result) {
            LGN.Toast.error('Pembayaran gagal');
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>