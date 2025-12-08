<?php
// pages/my_learning.php

$pageTitle = 'Pembelajaran Saya';

require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$db = getDB();

// Get enrolled courses
$stmt = $db->prepare("
    SELECT 
        c.*,
        e.enrolled_at,
        e.progress_percentage,
        e.last_accessed_at,
        e.certificate_issued,
        u.name as tutor_name,
        u.avatar as tutor_avatar,
        cat.name as category_name,
        (SELECT COUNT(*) FROM lessons l JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id) as total_lessons,
        (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id AND lp.user_id = ? AND lp.is_completed = 1) as completed_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.tutor_id = u.id
    JOIN categories cat ON c.category_id = cat.id
    WHERE e.user_id = ?
    ORDER BY e.last_accessed_at DESC, e.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$enrolledCourses = $stmt->fetchAll();

// Separate in-progress and completed
$inProgress = [];
$completed = [];

foreach ($enrolledCourses as $course) {
    if ($course['progress_percentage'] >= 100) {
        $completed[] = $course;
    } else {
        $inProgress[] = $course;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Pembelajaran Saya</h2>
                <p class="text-muted mb-0">Kelola dan lanjutkan kursus yang Anda ikuti</p>
            </div>
            <a href="/pages/courses.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Jelajahi Kursus
            </a>
        </div>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="learningTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inProgressTab">
                    <i class="bi bi-play-circle me-2"></i>Sedang Dipelajari
                    <span class="badge bg-primary ms-2"><?= count($inProgress) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completedTab">
                    <i class="bi bi-check-circle me-2"></i>Selesai
                    <span class="badge bg-success ms-2"><?= count($completed) ?></span>
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- In Progress Tab -->
            <div class="tab-pane fade show active" id="inProgressTab">
                <?php if (empty($inProgress)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
                    <h5 class="mt-3">Belum ada kursus yang sedang dipelajari</h5>
                    <p class="text-muted">Mulai jelajahi dan ikuti kursus untuk meningkatkan skill Anda</p>
                    <a href="/pages/courses.php" class="btn btn-primary">Jelajahi Kursus</a>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($inProgress as $course): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.jpg' ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>" 
                                     style="height: 160px; object-fit: cover;">
                                <div class="position-absolute bottom-0 start-0 end-0 p-2" style="background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                                    <div class="progress-custom" style="height: 6px;">
                                        <div class="progress-bar" style="width: <?= $course['progress_percentage'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="badge bg-light text-dark mb-2"><?= htmlspecialchars($course['category_name']) ?></span>
                                <h5 class="card-title fw-semibold mb-2">
                                    <a href="/pages/learn.php?course=<?= $course['id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($course['tutor_name']) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                                    <span><?= round($course['progress_percentage']) ?>% selesai</span>
                                    <span><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> lesson</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="/pages/learn.php?course=<?= $course['id'] ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-play-fill me-2"></i>Lanjutkan Belajar
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Completed Tab -->
            <div class="tab-pane fade" id="completedTab">
                <?php if (empty($completed)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-trophy fs-1 text-muted"></i>
                    <h5 class="mt-3">Belum ada kursus yang diselesaikan</h5>
                    <p class="text-muted">Terus belajar dan selesaikan kursus untuk mendapatkan sertifikat</p>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($completed as $course): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.jpg' ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>"
                                     style="height: 160px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="badge bg-light text-dark mb-2"><?= htmlspecialchars($course['category_name']) ?></span>
                                <h5 class="card-title fw-semibold mb-2">
                                    <?= htmlspecialchars($course['title']) ?>
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($course['tutor_name']) ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-calendar3 me-1"></i>Selesai pada <?= formatDate($course['last_accessed_at']) ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex gap-2">
                                    <a href="/pages/learn.php?course=<?= $course['id'] ?>" class="btn btn-outline-primary flex-fill">
                                        <i class="bi bi-play-fill me-1"></i>Tonton Lagi
                                    </a>
                                    <?php if ($course['certificate_issued']): ?>
                                    <a href="/pages/certificate.php?course=<?= $course['id'] ?>" class="btn btn-success flex-fill">
                                        <i class="bi bi-award me-1"></i>Sertifikat
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>