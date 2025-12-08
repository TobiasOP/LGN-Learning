<?php
// admin/index.php

$pageTitle = 'Admin Dashboard';

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$db = getDB();

// Get stats
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'students' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'tutors' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'tutor'")->fetchColumn(),
    'courses' => $db->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'published_courses' => $db->query("SELECT COUNT(*) FROM courses WHERE is_published = 1")->fetchColumn(),
    'enrollments' => $db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
    'transactions' => $db->query("SELECT COUNT(*) FROM transactions WHERE transaction_status = 'success'")->fetchColumn(),
    'revenue' => $db->query("SELECT COALESCE(SUM(final_amount), 0) FROM transactions WHERE transaction_status = 'success'")->fetchColumn(),
    'revenue_month' => $db->query("SELECT COALESCE(SUM(final_amount), 0) FROM transactions WHERE transaction_status = 'success' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())")->fetchColumn()
];

// Recent transactions
$recentTransactions = $db->query("
    SELECT t.*, u.name as user_name, c.title as course_title
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN courses c ON t.course_id = c.id
    ORDER BY t.created_at DESC
    LIMIT 10
")->fetchAll();

// Recent users
$recentUsers = $db->query("
    SELECT * FROM users ORDER BY created_at DESC LIMIT 5
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Admin Dashboard</h2>
            <span class="text-muted"><?= formatDate(date('Y-m-d')) ?></span>
        </div>
        
        <!-- Stats Row 1 -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Total Users</h6>
                                <h2 class="mb-0"><?= number_format($stats['users']) ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="mt-3 small">
                            <span class="me-3"><?= $stats['students'] ?> Siswa</span>
                            <span><?= $stats['tutors'] ?> Tutor</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Total Kursus</h6>
                                <h2 class="mb-0"><?= number_format($stats['courses']) ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-collection-play"></i>
                            </div>
                        </div>
                        <div class="mt-3 small">
                            <?= $stats['published_courses'] ?> Dipublikasikan
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">Total Pendaftaran</h6>
                                <h2 class="mb-0"><?= number_format($stats['enrollments']) ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-person-check"></i>
                            </div>
                        </div>
                        <div class="mt-3 small">
                            <?= $stats['transactions'] ?> Transaksi Sukses
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="opacity-75">Total Revenue</h6>
                                <h2 class="mb-0"><?= formatCurrency($stats['revenue']) ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                        <div class="mt-3 small">
                            Bulan ini: <?= formatCurrency($stats['revenue_month']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <a href="/admin/users.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h6 class="mt-2 mb-0">Kelola Users</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/courses.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-journal-code fs-1 text-success"></i>
                        <h6 class="mt-2 mb-0">Kelola Kursus</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/transactions.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-receipt fs-1 text-info"></i>
                        <h6 class="mt-2 mb-0">Transaksi</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/categories.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-grid fs-1 text-warning"></i>
                        <h6 class="mt-2 mb-0">Kategori</h6>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Transactions -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Transaksi Terbaru</h5>
                        <a href="/admin/transactions.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>User</th>
                                        <th>Kursus</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $tx): ?>
                                    <tr>
                                        <td><code class="small"><?= $tx['order_id'] ?></code></td>
                                        <td><?= htmlspecialchars($tx['user_name']) ?></td>
                                        <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($tx['course_title']) ?></td>
                                        <td><?= formatCurrency($tx['final_amount']) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = match($tx['transaction_status']) {
                                                'success' => 'bg-success',
                                                'pending' => 'bg-warning text-dark',
                                                'failed', 'cancel', 'expired' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= ucfirst($tx['transaction_status']) ?></span>
                                        </td>
                                        <td class="small"><?= formatDateTime($tx['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">User Baru</h5>
                        <a href="/admin/users.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <img src="<?= $user['avatar'] ? '/' . $user['avatar'] : '/assets/images/default-avatar.png' ?>" 
                                     class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                                    <small class="text-muted"><?= $user['email'] ?></small>
                                </div>
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'tutor' ? 'primary' : 'secondary') ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>