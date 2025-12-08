<?php
// pages/tutor/dashboard.php

$pageTitle = 'Dashboard Tutor';

require_once __DIR__ . '/../../includes/functions.php';

requireRole('tutor');

$db = getDB();
$tutorId = $_SESSION['user_id'];

// Get stats
$stats = [
    'courses' => $db->query("SELECT COUNT(*) FROM courses WHERE tutor_id = $tutorId")->fetchColumn(),
    'published' => $db->query("SELECT COUNT(*) FROM courses WHERE tutor_id = $tutorId AND is_published = 1")->fetchColumn(),
    'students' => $db->query("SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.tutor_id = $tutorId")->fetchColumn(),
    'revenue' => $db->query("SELECT COALESCE(SUM(t.final_amount), 0) FROM transactions t JOIN courses c ON t.course_id = c.id WHERE c.tutor_id = $tutorId AND t.transaction_status = 'success'")->fetchColumn()
];

// Get recent enrollments
$recentEnrollments = $db->query("
    SELECT e.*, u.name as student_name, u.avatar as student_avatar, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.tutor_id = $tutorId
    ORDER BY e.enrolled_at DESC
    LIMIT 5
")->fetchAll();

// Get courses
$courses = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students,
           (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as rating
    FROM courses c
    WHERE c.tutor_id = $tutorId
    ORDER BY c.created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="py-5 bg-light">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Tutor</h2>
                <p class="text-muted mb-0">Selamat datang kembali, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            </div>
            <a href="/pages/tutor/add_course.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Buat Kursus Baru
            </a>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-stat-card primary">
                    <div class="dashboard-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-collection-play"></i>
                    </div>
                    <div class="dashboard-stat-value"><?= $stats['courses'] ?></div>
                    <div class="dashboard-stat-label">Total Kursus</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-stat-card success">
                    <div class="dashboard-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="dashboard-stat-value"><?= $stats['published'] ?></div>
                    <div class="dashboard-stat-label">Kursus Aktif</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-stat-card warning">
                    <div class="dashboard-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="dashboard-stat-value"><?= formatNumber($stats['students']) ?></div>
                    <div class="dashboard-stat-label">Total Siswa</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="dashboard-stat-value"><?= formatCurrency($stats['revenue']) ?></div>
                    <div class="dashboard-stat-label">Total Pendapatan</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Enrollments -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Pendaftaran Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentEnrollments)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">Belum ada pendaftaran</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentEnrollments as $enrollment): ?>
                            <li class="list-group-item d-flex align-items-center py-3">
                                <img src="<?= $enrollment['student_avatar'] ?: '/assets/images/default-avatar.png' ?>" 
                                     class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= htmlspecialchars($enrollment['student_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($enrollment['course_title']) ?></small>
                                </div>
                                <small class="text-muted"><?= timeAgo($enrollment['enrolled_at']) ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- My Courses -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Kursus Saya</h5>
                        <a href="/pages/courses.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($courses)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-plus fs-1"></i>
                            <p class="mt-2">Belum ada kursus</p>
                            <a href="/pages/tutor/add_course.php" class="btn btn-primary btn-sm">Buat Kursus</a>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($courses as $course): ?>
                            <li class="list-group-item d-flex align-items-center py-3">
                                <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.jpg' ?>" 
                                     class="rounded me-3" width="60" height="40" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= htmlspecialchars($course['title']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-people me-1"></i><?= $course['students'] ?> siswa
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-star-fill text-warning me-1"></i><?= number_format($course['rating'] ?: 0, 1) ?>
                                    </small>
                                </div>
                                <span class="badge <?= $course['is_published'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $course['is_published'] ? 'Aktif' : 'Draft' ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>