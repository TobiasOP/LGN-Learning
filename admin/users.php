<?php
// admin/users.php

$pageTitle = 'Kelola Users';

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$db = getDB();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$search = sanitize($_GET['search'] ?? '');
$role = sanitize($_GET['role'] ?? '');

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where .= " AND role = ?";
    $params[] = $role;
}

// Get total
$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get users
$stmt = $db->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId && $userId !== $_SESSION['user_id']) {
        switch ($action) {
            case 'toggle_status':
                $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$userId]);
                redirect('/admin/users.php', 'Status user berhasil diubah', 'success');
                break;
                
            case 'change_role':
                $newRole = sanitize($_POST['new_role'] ?? '');
                if (in_array($newRole, ['student', 'tutor', 'admin'])) {
                    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$newRole, $userId]);
                    redirect('/admin/users.php', 'Role user berhasil diubah', 'success');
                }
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                redirect('/admin/users.php', 'User berhasil dihapus', 'success');
                break;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Kelola Users</h2>
            <span class="text-muted"><?= number_format($total) ?> total users</span>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">Semua Role</option>
                            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="tutor" <?= $role === 'tutor' ? 'selected' : '' ?>>Tutor</option>
                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="/admin/users.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $user['avatar'] ? '/' . $user['avatar'] : '/assets/images/default-avatar.png' ?>" 
                                             class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'tutor' ? 'primary' : 'secondary') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <?= $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="new_role" value="student">
                                                    <button type="submit" class="dropdown-item">Jadikan Student</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="new_role" value="tutor">
                                                    <button type="submit" class="dropdown-item">Jadikan Tutor</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="new_role" value="admin">
                                                    <button type="submit" class="dropdown-item">Jadikan Admin</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">Hapus User</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted small">Anda</span>
                                    <?php endif; ?>
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