<?php
// pages/courses.php

$pageTitle = 'Semua Kursus';

require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

// Get filters
$category = sanitize($_GET['category'] ?? '');
$level = sanitize($_GET['level'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;

// Build query
$where = ["c.is_published = 1"];
$params = [];

if ($category) {
    $where[] = "cat.slug = ?";
    $params[] = $category;
}

if ($level) {
    $where[] = "c.level = ?";
    $params[] = $level;
}

if ($search) {
    $where[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

$orderBy = match($sort) {
    'price_low' => 'COALESCE(c.discount_price, c.price) ASC',
    'price_high' => 'COALESCE(c.discount_price, c.price) DESC',
    'popular' => 'enrollment_count DESC',
    'rating' => 'avg_rating DESC',
    default => 'c.created_at DESC'
};

// Get total count
$countSql = "SELECT COUNT(*) FROM courses c JOIN categories cat ON c.category_id = cat.id WHERE $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);
$offset = ($page - 1) * $limit;

// Get courses
$sql = "
    SELECT 
        c.*,
        cat.name as category_name,
        cat.slug as category_slug,
        u.name as tutor_name,
        u.avatar as tutor_avatar,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
        (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE course_id = c.id) as review_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.tutor_id = u.id
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get categories for filter
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get current category name
$currentCategoryName = 'Semua Kursus';
if ($category) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $category) {
            $currentCategoryName = $cat['name'];
            $pageTitle = $cat['name'];
            break;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/index.php">Beranda</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($currentCategoryName) ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
                    </div>
                    <div class="card-body">
                        <form id="courseFilterForm" method="GET">
                            <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                            <?php endif; ?>
                            
                            <!-- Category Filter -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="category" id="categoryFilter" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['slug'] ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Level Filter -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Level</label>
                                <select name="level" id="levelFilter" class="form-select">
                                    <option value="">Semua Level</option>
                                    <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Pemula</option>
                                    <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Menengah</option>
                                    <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Lanjutan</option>
                                </select>
                            </div>
                            
                            <!-- Sort -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Urutkan</label>
                                <select name="sort" id="sortFilter" class="form-select">
                                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
                                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating Tertinggi</option>
                                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Terapkan Filter
                            </button>
                            
                            <?php if ($category || $level || $search): ?>
                            <a href="/pages/courses.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="bi bi-x-circle me-2"></i>Reset Filter
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Course Grid -->
            <div class="col-lg-9">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($currentCategoryName) ?></h4>
                        <p class="text-muted mb-0"><?= number_format($total) ?> kursus ditemukan</p>
                    </div>
                    
                    <?php if ($search): ?>
                    <div class="alert alert-info py-2 px-3 mb-0">
                        Hasil pencarian: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                        <a href="/pages/courses.php" class="ms-2"><i class="bi bi-x"></i></a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($courses)): ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <h5 class="mt-3">Tidak ada kursus ditemukan</h5>
                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                    <a href="/pages/courses.php" class="btn btn-primary">Lihat Semua Kursus</a>
                </div>
                <?php else: ?>
                
                <!-- Courses Grid -->
                <div class="row g-4" id="coursesGrid">
                    <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card course-card h-100">
                            <div class="card-img-wrapper">
                                <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.png' ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
                                <span class="card-badge badge badge-level <?= $course['level'] ?>">
                                    <?= ucfirst($course['level']) ?>
                                </span>
                                <?php if (isLoggedIn()): ?>
                                <div class="card-wishlist" data-course-id="<?= $course['id'] ?>">
                                    <i class="bi bi-heart"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <span class="course-category"><?= htmlspecialchars($course['category_name']) ?></span>
                                <h5 class="course-title">
                                    <a href="/pages/course_detail.php?slug=<?= $course['slug'] ?>">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </a>
                                </h5>
                                <div class="tutor-info">
                                    <img src="<?= $course['tutor_avatar'] ?: '/assets/images/default-avatar.png' ?>" 
                                         alt="<?= htmlspecialchars($course['tutor_name']) ?>" class="tutor-avatar">
                                    <span class="tutor-name"><?= htmlspecialchars($course['tutor_name']) ?></span>
                                </div>
                                <div class="course-meta">
                                    <span class="course-rating">
                                        <span class="stars"><i class="bi bi-star-fill"></i></span>
                                        <?= number_format($course['avg_rating'] ?: 0, 1) ?>
                                        <span class="text-muted">(<?= $course['review_count'] ?>)</span>
                                    </span>
                                    <span><i class="bi bi-people"></i> <?= formatNumber($course['enrollment_count']) ?></span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div>
                                    <?php if ($course['discount_price']): ?>
                                        <span class="course-price"><?= formatCurrency($course['discount_price']) ?></span>
                                        <span class="course-price-original"><?= formatCurrency($course['price']) ?></span>
                                    <?php else: ?>
                                        <span class="course-price <?= $course['price'] == 0 ? 'free' : '' ?>">
                                            <?= $course['price'] == 0 ? 'Gratis' : formatCurrency($course['price']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5" id="coursePagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php 
$additionalJs = ['/assets/js/courses.js'];
require_once __DIR__ . '/../includes/footer.php'; 
?>