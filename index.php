<?php
// index.php - Homepage LGN E-Learning

$pageTitle = 'Platform E-Learning Terbaik di Indonesia';

require_once 'includes/functions.php';

$db = getDB();

// Helper function untuk gambar dengan fallback
function getImageUrl($path, $type = 'course') {
    $defaults = [
        'course' => 'https://via.placeholder.com/750x422/4f46e5/ffffff?text=LGN+Course',
        'avatar' => 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff&size=128',
        'category' => 'https://via.placeholder.com/100x100/4f46e5/ffffff?text=Category'
    ];
    
    if ($path && file_exists(__DIR__ . '/' . $path)) {
        return '/' . $path;
    }
    
    return $defaults[$type] ?? $defaults['course'];
}

// Get featured courses
try {
    $stmt = $db->query("
        SELECT 
            c.*,
            cat.name as category_name,
            u.name as tutor_name,
            u.avatar as tutor_avatar,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
            (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating
        FROM courses c
        JOIN categories cat ON c.category_id = cat.id
        JOIN users u ON c.tutor_id = u.id
        WHERE c.is_published = 1
        ORDER BY c.is_featured DESC, c.created_at DESC
        LIMIT 8
    ");
    $featuredCourses = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredCourses = [];
}

// Get categories with course count
try {
    $stmt = $db->query("
        SELECT 
            cat.*,
            (SELECT COUNT(*) FROM courses WHERE category_id = cat.id AND is_published = 1) as course_count
        FROM categories cat
        WHERE cat.is_active = 1
        ORDER BY course_count DESC
        LIMIT 8
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get stats
try {
    $stats = [
        'students' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn() ?: 0,
        'courses' => $db->query("SELECT COUNT(*) FROM courses WHERE is_published = 1")->fetchColumn() ?: 0,
        'tutors' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'tutor'")->fetchColumn() ?: 0,
        'enrollments' => $db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn() ?: 0
    ];
} catch (PDOException $e) {
    $stats = ['students' => 0, 'courses' => 0, 'tutors' => 0, 'enrollments' => 0];
}

require_once 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Belajar dari <span>Ahlinya</span>, Raih Karir Impianmu
                    </h1>
                    <p class="hero-subtitle">
                        Tingkatkan skill Anda dengan ribuan kursus berkualitas dari tutor profesional. 
                        Mulai perjalanan belajar Anda bersama LGN hari ini!
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="/pages/courses.php" class="btn btn-warning btn-lg">
                            <i class="bi bi-play-circle me-2"></i>Mulai Belajar
                        </a>
                        <a href="/register.php?role=tutor" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Jadi Tutor
                        </a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-number"><?= number_format($stats['students']) ?>+</div>
                            <div class="hero-stat-label">Siswa</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number"><?= number_format($stats['courses']) ?>+</div>
                            <div class="hero-stat-label">Kursus</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number"><?= number_format($stats['tutors']) ?>+</div>
                            <div class="hero-stat-label">Tutor</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image text-center">
                        <!-- SVG Illustration Inline -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 400" style="max-width: 100%; height: auto;">
                            <defs>
                                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#4f46e5;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Laptop -->
                            <rect x="100" y="100" width="300" height="200" rx="10" fill="#1f2937"/>
                            <rect x="110" y="110" width="280" height="170" rx="5" fill="#374151"/>
                            
                            <!-- Screen content -->
                            <rect x="130" y="130" width="100" height="8" rx="4" fill="#4f46e5"/>
                            <rect x="130" y="150" width="150" height="6" rx="3" fill="#6b7280"/>
                            <rect x="130" y="165" width="120" height="6" rx="3" fill="#6b7280"/>
                            <rect x="130" y="180" width="140" height="6" rx="3" fill="#6b7280"/>
                            
                            <!-- Video thumbnail -->
                            <rect x="260" y="130" width="120" height="80" rx="5" fill="#1f2937"/>
                            
                            <!-- Play button -->
                            <circle cx="320" cy="170" r="25" fill="url(#grad1)"/>
                            <polygon points="314,158 314,182 334,170" fill="white"/>
                            
                            <!-- Progress bar -->
                            <rect x="130" y="200" width="240" height="6" rx="3" fill="#374151"/>
                            <rect x="130" y="200" width="150" height="6" rx="3" fill="#10b981"/>
                            
                            <!-- Laptop base -->
                            <path d="M80 300 L120 300 L100 320 L400 320 L380 300 L420 300 L430 330 L70 330 Z" fill="#374151"/>
                            
                            <!-- Floating elements -->
                            <circle cx="70" cy="130" r="25" fill="#10b981" opacity="0.9"/>
                            <text x="62" y="137" fill="white" font-size="20">✓</text>
                            
                            <rect x="410" y="100" width="50" height="50" rx="10" fill="#f59e0b" opacity="0.9"/>
                            <text x="423" y="133" fill="white" font-size="24">📚</text>
                            
                            <circle cx="450" cy="250" r="20" fill="#ef4444" opacity="0.9"/>
                            <text x="442" y="257" fill="white" font-size="16">🎓</text>
                            
                            <!-- Certificate icon -->
                            <rect x="50" y="220" width="45" height="35" rx="5" fill="#8b5cf6" opacity="0.9"/>
                            <text x="60" y="243" fill="white" font-size="18">🏆</text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Jelajahi Kategori</h2>
                <p class="section-subtitle">Temukan kursus sesuai minat dan kebutuhan Anda</p>
            </div>
            
            <?php if (empty($categories)): ?>
            <div class="text-center py-4">
                <p class="text-muted">Kategori akan segera tersedia</p>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="/pages/courses.php?category=<?= htmlspecialchars($category['slug']) ?>" class="text-decoration-none">
                        <div class="card category-card h-100">
                            <div class="category-icon" style="background: <?= htmlspecialchars($category['color'] ?? '#4f46e5') ?>20; color: <?= htmlspecialchars($category['color'] ?? '#4f46e5') ?>;">
                                <i class="bi <?= htmlspecialchars($category['icon'] ?? 'bi-folder') ?>"></i>
                            </div>
                            <h5 class="category-name"><?= htmlspecialchars($category['name']) ?></h5>
                            <p class="category-count"><?= (int)$category['course_count'] ?> Kursus</p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Featured Courses -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="section-title mb-1">Kursus Unggulan</h2>
                    <p class="section-subtitle mb-0">Pilihan terbaik dari tutor profesional</p>
                </div>
                <a href="/pages/courses.php" class="btn btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            
            <?php if (empty($featuredCourses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-code fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Kursus akan segera tersedia</p>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featuredCourses as $course): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card course-card h-100">
                        <div class="card-img-wrapper">
                            <img src="<?= getCourseImage($course) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($course['title']) ?>"
                                 onerror="this.src='https://via.placeholder.com/750x422/4f46e5/ffffff?text=LGN+Course'">
                            <span class="card-badge badge badge-level <?= htmlspecialchars($course['level']) ?>">
                                <?= ucfirst(htmlspecialchars($course['level'])) ?>
                            </span>
                            <?php if (isLoggedIn()): ?>
                            <div class="card-wishlist" data-course-id="<?= (int)$course['id'] ?>">
                                <i class="bi bi-heart"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <span class="course-category"><?= htmlspecialchars($course['category_name']) ?></span>
                            <h5 class="course-title">
                                <a href="/pages/course_detail.php?slug=<?= htmlspecialchars($course['slug']) ?>">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </h5>
                            <div class="tutor-info">
                                <?php 
                                $avatarUrl = $course['tutor_avatar'] && file_exists(__DIR__ . '/' . $course['tutor_avatar'])
                                    ? '/' . $course['tutor_avatar']
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($course['tutor_name']) . '&background=4f46e5&color=fff&size=36';
                                ?>
                                <img src="<?= $avatarUrl ?>" 
                                     alt="<?= htmlspecialchars($course['tutor_name']) ?>" 
                                     class="tutor-avatar"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Tutor&background=4f46e5&color=fff&size=36'">
                                <span class="tutor-name"><?= htmlspecialchars($course['tutor_name']) ?></span>
                            </div>
                            <div class="course-meta">
                                <span class="course-rating">
                                    <span class="stars"><i class="bi bi-star-fill"></i></span>
                                    <?= number_format((float)($course['avg_rating'] ?? 0), 1) ?>
                                </span>
                                <span><i class="bi bi-people"></i> <?= number_format((int)$course['enrollment_count']) ?></span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div>
                                <?php 
                                $price = (float)$course['price'];
                                $discountPrice = $course['discount_price'] ? (float)$course['discount_price'] : null;
                                ?>
                                <?php if ($discountPrice): ?>
                                    <span class="course-price"><?= formatCurrency($discountPrice) ?></span>
                                    <span class="course-price-original"><?= formatCurrency($price) ?></span>
                                <?php else: ?>
                                    <span class="course-price <?= $price == 0 ? 'free' : '' ?>">
                                        <?= $price == 0 ? 'Gratis' : formatCurrency($price) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number"><?= number_format($stats['students']) ?>+</div>
                        <div class="stat-label">Siswa Aktif</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-collection-play"></i>
                        </div>
                        <div class="stat-number"><?= number_format($stats['courses']) ?>+</div>
                        <div class="stat-label">Kursus Tersedia</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="stat-number"><?= number_format($stats['tutors']) ?>+</div>
                        <div class="stat-label">Tutor Ahli</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="stat-number"><?= number_format($stats['enrollments']) ?>+</div>
                        <div class="stat-label">Pendaftaran</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Mengapa Memilih LGN?</h2>
                <p class="section-subtitle">Keunggulan yang membuat pembelajaran Anda lebih efektif</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-award text-primary fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Tutor Berpengalaman</h5>
                        <p class="text-muted mb-0">Belajar langsung dari praktisi industri dengan pengalaman bertahun-tahun di bidangnya.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-infinity text-success fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Akses Selamanya</h5>
                        <p class="text-muted mb-0">Sekali beli, akses kursus selamanya. Belajar kapan saja dan di mana saja sesuai keinginan.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-patch-check text-warning fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Sertifikat Resmi</h5>
                        <p class="text-muted mb-0">Dapatkan sertifikat setelah menyelesaikan kursus untuk menunjang karir Anda.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-phone text-info fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Akses Mobile</h5>
                        <p class="text-muted mb-0">Belajar dari smartphone Anda. Platform kami responsif untuk semua perangkat.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-headset text-danger fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Dukungan 24/7</h5>
                        <p class="text-muted mb-0">Tim support kami siap membantu Anda kapan saja jika ada kendala dalam belajar.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4">
                        <div class="mb-3">
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-shield-check text-secondary fs-1"></i>
                            </div>
                        </div>
                        <h5 class="fw-semibold">Garansi Uang Kembali</h5>
                        <p class="text-muted mb-0">Tidak puas? Dapatkan pengembalian dana dalam 7 hari tanpa pertanyaan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-white">
                    <h2 class="fw-bold mb-3">Siap Memulai Perjalanan Belajar Anda?</h2>
                    <p class="mb-0 opacity-90">Bergabung dengan ribuan siswa lainnya dan mulai tingkatkan skill Anda hari ini.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="/register.php" class="btn btn-warning btn-lg">
                        <i class="bi bi-rocket-takeoff me-2"></i>Daftar Sekarang - Gratis!
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

