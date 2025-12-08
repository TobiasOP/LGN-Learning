<?php
// admin/transactions.php

$pageTitle = 'Transaksi';

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$db = getDB();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$status = sanitize($_GET['status'] ?? '');
$dateFrom = sanitize($_GET['date_from'] ?? '');
$dateTo = sanitize($_GET['date_to'] ?? '');

$where = "1=1";
$params = [];

if ($status) {
    $where .= " AND t.transaction_status = ?";
    $params[] = $status;
}

if ($dateFrom) {
    $where .= " AND DATE(t.created_at) >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $where .= " AND DATE(t.created_at) <= ?";
    $params[] = $dateTo;
}

// Get total
$countStmt = $db->prepare("SELECT COUNT(*) FROM transactions t WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get transactions
$stmt = $db->prepare("
    SELECT t.*, u.name as user_name, u.email as user_email, c.title as course_title
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN courses c ON t.course_id = c.id
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Summary stats
$summaryStmt = $db->prepare("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN transaction_status = 'success' THEN 1 ELSE 0 END) as success_count,
        SUM(CASE WHEN transaction_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN transaction_status = 'success' THEN final_amount ELSE 0 END) as total_revenue
    FROM transactions t
    WHERE $where
");
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Transaksi</h2>
        </div>
        
        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Transaksi</h6>
                        <h3 class="mb-0"><?= number_format($summary['total_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Sukses</h6>
                        <h3 class="mb-0 text-success"><?= number_format($summary['success_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($summary['pending_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Revenue</h6>
                        <h3 class="mb-0 text-primary"><?= formatCurrency($summary['total_revenue']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="success" <?= $status === 'success' ? 'selected' : '' ?>>Success</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                            <option value="cancel" <?= $status === 'cancel' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_from" class="form-control" placeholder="Dari tanggal" value="<?= $dateFrom ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_to" class="form-control" placeholder="Sampai tanggal" value="<?= $dateTo ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="/admin/transactions.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Kursus</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td>
                                    <code class="small"><?= $tx['order_id'] ?></code>
                                    <?php if ($tx['midtrans_transaction_id']): ?>
                                    <br><small class="text-muted">MT: <?= $tx['midtrans_transaction_id'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($tx['user_name']) ?></div>
                                    <small class="text-muted"><?= $tx['user_email'] ?></small>
                                </td>
                                <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($tx['course_title']) ?></td>
                                <td>
                                    <div><?= formatCurrency($tx['final_amount']) ?></div>
                                    <?php if ($tx['discount_amount'] > 0): ?>
                                    <small class="text-success">Diskon: <?= formatCurrency($tx['discount_amount']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $tx['payment_type'] ? ucfirst(str_replace('_', ' ', $tx['payment_type'])) : '-' ?></td>
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
                                <td>
                                    <div><?= formatDate($tx['created_at']) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($tx['created_at'])) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>