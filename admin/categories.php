<?php
// admin/categories.php

$pageTitle = 'Kelola Kategori';

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$db = getDB();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = sanitize($_POST['name'] ?? '');
            $icon = sanitize($_POST['icon'] ?? 'bi-folder');
            $color = sanitize($_POST['color'] ?? '#4f46e5');
            $description = sanitize($_POST['description'] ?? '');
            
            if (!empty($name)) {
                $slug = generateSlug($name, 'categories');
                $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, color, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $icon, $color, $description]);
                redirect('/admin/categories.php', 'Kategori berhasil ditambahkan', 'success');
            }
            break;
            
        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $name = sanitize($_POST['name'] ?? '');
            $icon = sanitize($_POST['icon'] ?? 'bi-folder');
            $color = sanitize($_POST['color'] ?? '#4f46e5');
            $description = sanitize($_POST['description'] ?? '');
            
            if ($id && !empty($name)) {
                $stmt = $db->prepare("UPDATE categories SET name = ?, icon = ?, color = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $icon, $color, $description, $id]);
                redirect('/admin/categories.php', 'Kategori berhasil diperbarui', 'success');
            }
            break;
            
        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            if ($id) {
                $stmt = $db->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                redirect('/admin/categories.php', 'Status kategori diubah', 'success');
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id) {
                // Check if has courses
                $stmt = $db->prepare("SELECT COUNT(*) FROM courses WHERE category_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    redirect('/admin/categories.php', 'Tidak dapat menghapus kategori yang memiliki kursus', 'error');
                } else {
                    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    redirect('/admin/categories.php', 'Kategori berhasil dihapus', 'success');
                }
            }
            break;
    }
}

// Get categories
$categories = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM courses WHERE category_id = c.id) as course_count
    FROM categories c
    ORDER BY c.name
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Kelola Kategori</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg me-2"></i>Tambah Kategori
            </button>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th>Slug</th>
                                <th>Kursus</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px; background: <?= $cat['color'] ?>20; color: <?= $cat['color'] ?>;">
                                            <i class="bi <?= $cat['icon'] ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?= htmlspecialchars($cat['name']) ?></div>
                                            <?php if ($cat['description']): ?>
                                            <small class="text-muted"><?= htmlspecialchars(substr($cat['description'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><code><?= $cat['slug'] ?></code></td>
                                <td><?= $cat['course_count'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $cat['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $cat['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $cat['is_active'] ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $cat['is_active'] ? 'pause' : 'play' ?>"></i>
                                        </button>
                                    </form>
                                    <?php if ($cat['course_count'] == 0): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (Bootstrap Icons)</label>
                            <input type="text" name="icon" class="form-control" value="bi-folder" placeholder="bi-folder">
                            <small class="text-muted">Contoh: bi-code-slash, bi-phone, bi-graph-up</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warna</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#4f46e5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editCategoryId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" id="editCategoryIcon" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warna</label>
                            <input type="color" name="color" id="editCategoryColor" class="form-control form-control-color">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="editCategoryDescription" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('editCategoryId').value = category.id;
    document.getElementById('editCategoryName').value = category.name;
    document.getElementById('editCategoryIcon').value = category.icon;
    document.getElementById('editCategoryColor').value = category.color;
    document.getElementById('editCategoryDescription').value = category.description || '';
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>