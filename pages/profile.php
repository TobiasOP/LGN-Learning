<?php
// pages/profile.php

$pageTitle = 'Profil Saya';

require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$user = getCurrentUser();
$db = getDB();

// Get user stats
$stats = [
    'enrolled' => $db->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?")->execute([$user['id']]) ? $db->query("SELECT COUNT(*) FROM enrollments WHERE user_id = {$user['id']}")->fetchColumn() : 0,
    'completed' => $db->query("SELECT COUNT(*) FROM enrollments WHERE user_id = {$user['id']} AND progress_percentage >= 100")->fetchColumn(),
    'certificates' => $db->query("SELECT COUNT(*) FROM certificates WHERE user_id = {$user['id']}")->fetchColumn()
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    
    $errors = [];
    
    if (empty($name) || strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter';
    }
    
    if (empty($errors)) {
        // Handle avatar upload
        $avatarPath = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['avatar'], 'avatars');
            if ($upload['success']) {
                // Delete old avatar
                if ($avatarPath) {
                    deleteFile($avatarPath);
                }
                $avatarPath = $upload['path'];
            } else {
                $errors[] = $upload['message'];
            }
        }
        
        if (empty($errors)) {
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $bio, $avatarPath, $user['id']]);
            
            $_SESSION['user_name'] = $name;
            redirect('/pages/profile.php', 'Profil berhasil diperbarui', 'success');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?= $user['avatar'] ? '/' . $user['avatar'] : '/assets/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($user['name']) ?>"
                             class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                        <p class="text-muted mb-3"><?= ucfirst($user['role']) ?></p>
                        <div class="d-flex justify-content-center gap-4 text-center">
                            <div>
                                <div class="fw-bold"><?= $stats['enrolled'] ?></div>
                                <small class="text-muted">Kursus</small>
                            </div>
                            <div>
                                <div class="fw-bold"><?= $stats['completed'] ?></div>
                                <small class="text-muted">Selesai</small>
                            </div>
                            <div>
                                <div class="fw-bold"><?= $stats['certificates'] ?></div>
                                <small class="text-muted">Sertifikat</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="list-group mt-4">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="bi bi-person me-2"></i>Edit Profil
                    </a>
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-shield-lock me-2"></i>Ubah Password
                    </a>
                    <a href="/pages/my_learning.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-collection-play me-2"></i>Kursus Saya
                    </a>
                    <a href="#transactions" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-receipt me-2"></i>Riwayat Transaksi
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold">Edit Profil</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="name" class="form-control" 
                                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" 
                                                   value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                            <small class="text-muted">Email tidak dapat diubah</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nomor Telepon</label>
                                            <input type="text" name="phone" class="form-control" 
                                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                                   placeholder="08xxxxxxxxxx">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Foto Profil</label>
                                            <input type="file" name="avatar" class="form-control" accept="image/*">
                                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bio</label>
                                        <textarea name="bio" class="form-control" rows="4" 
                                                  placeholder="Ceritakan sedikit tentang diri Anda..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Tab -->
                    <div class="tab-pane fade" id="password">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold">Ubah Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="mb-3">
                                        <label class="form-label">Password Saat Ini</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-check me-2"></i>Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transactions Tab -->
                    <div class="tab-pane fade" id="transactions">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold">Riwayat Transaksi</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php
                                $transactions = $db->prepare("
                                    SELECT t.*, c.title as course_title 
                                    FROM transactions t 
                                    JOIN courses c ON t.course_id = c.id 
                                    WHERE t.user_id = ? 
                                    ORDER BY t.created_at DESC
                                ");
                                $transactions->execute([$user['id']]);
                                $transactions = $transactions->fetchAll();
                                ?>
                                
                                <?php if (empty($transactions)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-receipt fs-1 text-muted"></i>
                                    <p class="mt-3 text-muted">Belum ada transaksi</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Kursus</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transactions as $tx): ?>
                                            <tr>
                                                <td><code><?= $tx['order_id'] ?></code></td>
                                                <td><?= htmlspecialchars($tx['course_title']) ?></td>
                                                <td><?= formatCurrency($tx['final_amount']) ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($tx['transaction_status']) {
                                                        'success' => 'bg-success',
                                                        'pending' => 'bg-warning',
                                                        'failed', 'cancel', 'expired' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucfirst($tx['transaction_status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= formatDateTime($tx['created_at']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('changePasswordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (data.new_password !== data.confirm_password) {
        LGN.Toast.error('Konfirmasi password tidak cocok');
        return;
    }
    
    if (data.new_password.length < 6) {
        LGN.Toast.error('Password minimal 6 karakter');
        return;
    }
    
    try {
        const response = await LGN.API.post('/api/auth/change_password.php', data);
        
        if (response.success) {
            LGN.Toast.success('Password berhasil diubah');
            this.reset();
        } else {
            LGN.Toast.error(response.message);
        }
    } catch (error) {
        LGN.Toast.error(error.message);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>