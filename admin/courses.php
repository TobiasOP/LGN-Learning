<?php
// admin/courses.php

$pageTitle = 'Kelola Kursus';

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$db = getDB();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$search = sanitize($_GET['search'] ?? '');
$category = intval($_GET['category'] ?? 0);
$status = sanitize($_GET['status'] ?? '');
$tutor = intval($_GET['tutor'] ?? 0);

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where .= " AND c.category_id = ?";
    $params[] = $category;
}

if ($status === 'published') {
    $where .= " AND c.is_published = 1";
} elseif ($status === 'draft') {
    $where .= " AND c.is_published = 0";
}

if ($tutor) {
    $where .= " AND c.tutor_id = ?";
    $params[] = $tutor;
}

// Get total
$countStmt = $db->prepare("SELECT COUNT(*) FROM courses c WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get courses - lesson count melalui course_sections
$stmt = $db->prepare("
    SELECT c.*, 
           cat.name as category_name,
           u.name as tutor_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
           (SELECT COUNT(*) FROM lessons l 
            INNER JOIN course_sections cs ON l.section_id = cs.id 
            WHERE cs.course_id = c.id) as lesson_count,
           (SELECT COUNT(*) FROM course_sections WHERE course_id = c.id) as section_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.tutor_id = u.id
    WHERE $where
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get categories for filter
$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get tutors for filter
$tutors = $db->query("SELECT id, name FROM users WHERE role = 'tutor' ORDER BY name")->fetchAll();

// Summary stats
$summaryStmt = $db->prepare("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published_count,
        SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_count
    FROM courses c
    WHERE $where
");
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $courseId = intval($_POST['course_id'] ?? 0);
    
    if ($courseId) {
        switch ($action) {
            case 'toggle_publish':
                $stmt = $db->prepare("UPDATE courses SET is_published = NOT is_published WHERE id = ?");
                $stmt->execute([$courseId]);
                redirect('/admin/courses.php', 'Status publikasi kursus berhasil diubah', 'success');
                break;
                
            case 'toggle_featured':
                $stmt = $db->prepare("UPDATE courses SET is_featured = NOT is_featured WHERE id = ?");
                $stmt->execute([$courseId]);
                redirect('/admin/courses.php', 'Status featured kursus berhasil diubah', 'success');
                break;
                
            case 'delete':
                // Check if has enrollments
                $stmt = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                $stmt->execute([$courseId]);
                if ($stmt->fetchColumn() > 0) {
                    redirect('/admin/courses.php', 'Tidak dapat menghapus kursus yang memiliki pendaftaran', 'error');
                } else {
                    // Delete related data first
                    // Delete lessons melalui course_sections
                    $db->prepare("
                        DELETE l FROM lessons l
                        INNER JOIN course_sections cs ON l.section_id = cs.id
                        WHERE cs.course_id = ? 
                    ")->execute([$courseId]);
                    
                    // Delete course_sections
                    $db->prepare("DELETE FROM course_sections WHERE course_id = ?")->execute([$courseId]);
                    
                    // Delete course
                    $db->prepare("DELETE FROM courses WHERE id = ?")->execute([$courseId]);
                    redirect('/admin/courses.php', 'Kursus berhasil dihapus', 'success');
                }
                break;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Kelola Kursus</h2>
            <a href="/tutor/course-create.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Kursus
            </a>
        </div>
        
        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Kursus</h6>
                        <h3 class="mb-0"><?= number_format($summary['total_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Dipublikasikan</h6>
                        <h3 class="mb-0 text-success"><?= number_format($summary['published_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Draft</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($summary['draft_count']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Featured</h6>
                        <h3 class="mb-0 text-primary"><?= number_format($summary['featured_count']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari judul kursus..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Dipublikasikan</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tutor" class="form-select">
                            <option value="">Semua Tutor</option>
                            <?php foreach ($tutors as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $tutor == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="/admin/courses.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Courses Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kursus</th>
                                <th>Kategori</th>
                                <th>Tutor</th>
                                <th>Harga</th>
                                <th>Konten</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada kursus ditemukan
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $course['thumbnail'] ? '/' . $course['thumbnail'] : '/assets/images/default-course.png' ?>" 
                                             class="rounded me-3" width="60" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-medium text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars($course['title']) ?>
                                            </div>
                                            <small class="text-muted">
                                                <code><?= $course['slug'] ?></code>
                                                <?php if ($course['is_featured']): ?>
                                                <span class="badge bg-warning text-dark ms-1">Featured</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($course['category_name']): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($course['category_name']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($course['tutor_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($course['price'] > 0): ?>
                                    <div><?= formatCurrency($course['price']) ?></div>
                                    <?php if ($course['discount_price']): ?>
                                    <small class="text-success"><?= formatCurrency($course['discount_price']) ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="badge bg-success">Gratis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><span class="badge bg-info"><?= $course['section_count'] ?> section</span></div>
                                    <small class="text-muted"><?= $course['lesson_count'] ?> lessons</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $course['enrollment_count'] ?> siswa</span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $course['is_published'] ? 'success' : 'warning text-dark' ?>">
                                        <?= $course['is_published'] ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="/course/<?= $course['slug'] ?>" class="dropdown-item" target="_blank">
                                                    <i class="bi bi-eye me-2"></i>Lihat Kursus
                                                </a>
                                            </li>
                                            <li>
                                                <a href="/tutor/course-edit.php?id=<?= $course['id'] ?>" class="dropdown-item">
                                                    <i class="bi bi-pencil me-2"></i>Edit Kursus
                                                </a>
                                            </li>
                                            <li>
                                                <a href="/tutor/lessons.php?course_id=<?= $course['id'] ?>" class="dropdown-item">
                                                    <i class="bi bi-list-ul me-2"></i>Kelola Lessons
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_publish">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-<?= $course['is_published'] ? 'eye-slash' : 'eye' ?> me-2"></i>
                                                        <?= $course['is_published'] ? 'Unpublish' : 'Publish' ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_featured">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-star<?= $course['is_featured'] ? '-fill' : '' ?> me-2"></i>
                                                        <?= $course['is_featured'] ? 'Hapus Featured' : 'Jadikan Featured' ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <?php if ($course['enrollment_count'] == 0): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kursus ini? Semua sections dan lessons akan ikut terhapus.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Hapus Kursus
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
