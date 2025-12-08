<?php
// pages/learn.php - Learning Page

require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$courseId = intval($_GET['course'] ?? 0);
$lessonId = intval($_GET['lesson'] ?? 0);

if (!$courseId) {
    redirect('/pages/my_learning.php', 'Kursus tidak ditemukan', 'error');
}

// Check enrollment
if (!isEnrolled($_SESSION['user_id'], $courseId)) {
    redirect('/pages/course_detail.php?id=' . $courseId, 'Anda belum terdaftar di kursus ini', 'error');
}

$db = getDB();

// Get course
$stmt = $db->prepare("SELECT c.*, u.name as tutor_name FROM courses c JOIN users u ON c.tutor_id = u.id WHERE c.id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/my_learning.php', 'Kursus tidak ditemukan', 'error');
}

$pageTitle = $course['title'];

// Get sections with lessons
$stmt = $db->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_number");
$stmt->execute([$courseId]);
$sections = $stmt->fetchAll();

$allLessons = [];
$currentLesson = null;

foreach ($sections as &$section) {
    $stmt = $db->prepare("
        SELECT l.*, 
               (SELECT is_completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as is_completed
        FROM lessons l
        WHERE l.section_id = ?
        ORDER BY l.order_number
    ");
    $stmt->execute([$_SESSION['user_id'], $section['id']]);
    $section['lessons'] = $stmt->fetchAll();
    
    foreach ($section['lessons'] as $lesson) {
        $allLessons[] = $lesson;
        
        if ($lessonId && $lesson['id'] == $lessonId) {
            $currentLesson = $lesson;
        }
    }
}

// If no lesson specified, get first incomplete or first lesson
if (!$currentLesson && !empty($allLessons)) {
    foreach ($allLessons as $lesson) {
        if (!$lesson['is_completed']) {
            $currentLesson = $lesson;
            break;
        }
    }
    if (!$currentLesson) {
        $currentLesson = $allLessons[0];
    }
}

// Get user progress
$progress = getCourseProgress($_SESSION['user_id'], $courseId);

require_once __DIR__ . '/../includes/header.php';
?>

<main class="learn-page">
    <!-- Top Bar -->
    <div class="learn-topbar bg-dark text-white py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="/pages/course_detail.php?slug=<?= $course['slug'] ?>" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h6 class="mb-0 text-truncate" style="max-width: 400px;"><?= htmlspecialchars($course['title']) ?></h6>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="progress-custom" style="width: 200px;">
                        <div class="progress-bar course-progress-bar" style="width: <?= $progress ?>%"></div>
                    </div>
                    <span class="course-progress-text small"><?= round($progress) ?>% selesai</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Video Player -->
            <div class="col-lg-9">
                <div class="learn-content p-4">
                    <!-- Video Container -->
                    <div class="video-player-wrapper mb-4" id="videoContainer">
                        <?php if ($currentLesson): ?>
                        <div class="video-player-container">
                            <iframe 
                                src="https://drive.google.com/file/d/<?= $currentLesson['google_drive_file_id'] ?>/preview"
                                allowfullscreen
                                allow="autoplay"
                            ></iframe>
                        </div>
                        <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-dark text-white" style="height: 400px;">
                            <div class="text-center">
                                <i class="bi bi-play-circle fs-1 mb-3"></i>
                                <p>Pilih lesson untuk mulai belajar</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Lesson Info -->
                    <div class="lesson-info">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="fw-bold mb-2" id="currentLessonTitle">
                                    <?= $currentLesson ? htmlspecialchars($currentLesson['title']) : 'Pilih Lesson' ?>
                                </h4>
                                <p class="text-muted mb-0">
                                    <?= htmlspecialchars($course['tutor_name']) ?>
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" id="prevLessonBtn">
                                    <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                                </button>
                                <button class="btn btn-primary" id="markCompleteBtn">
                                    <i class="bi bi-check-circle me-1"></i>Tandai Selesai
                                </button>
                                <button class="btn btn-outline-secondary" id="nextLessonBtn">
                                    Selanjutnya<i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Lesson Description -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="fw-semibold mb-3">Deskripsi Lesson</h5>
                                <div id="lessonDescription">
                                    <?= $currentLesson ? nl2br(htmlspecialchars($currentLesson['description'] ?: 'Tidak ada deskripsi untuk lesson ini.')) : '' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Sidebar -->
            <div class="col-lg-3">
                <div class="course-sidebar">
                    <div class="course-sidebar-header">
                        <h5 class="mb-0 fw-bold">Materi Kursus</h5>
                    </div>
                    <div class="course-sidebar-content">
                        <?php foreach ($sections as $section): ?>
                        <div class="section-item">
                            <div class="section-header" data-bs-toggle="collapse" data-bs-target="#sectionContent<?= $section['id'] ?>">
                                <div>
                                    <div class="section-title"><?= htmlspecialchars($section['title']) ?></div>
                                    <div class="section-meta"><?= count($section['lessons']) ?> lessons</div>
                                </div>
                                <i class="bi bi-chevron-down section-toggle"></i>
                            </div>
                            <div class="collapse show section-content" id="sectionContent<?= $section['id'] ?>">
                                <ul class="lesson-list">
                                    <?php foreach ($section['lessons'] as $lesson): ?>
                                    <li class="lesson-item <?= ($currentLesson && $currentLesson['id'] == $lesson['id']) ? 'active' : '' ?> <?= $lesson['is_completed'] ? 'completed' : '' ?>"
                                        data-lesson-id="<?= $lesson['id'] ?>">
                                        <div class="lesson-checkbox">
                                            <?php if ($lesson['is_completed']): ?>
                                            <i class="bi bi-check"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="lesson-info">
                                            <div class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></div>
                                            <div class="lesson-meta">
                                                <span><i class="bi bi-play-circle me-1"></i><?= $lesson['duration_minutes'] ?> menit</span>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.learn-page {
    background: #f8fafc;
}

.learn-topbar {
    position: sticky;
    top: 0;
    z-index: 1000;
}

.learn-content {
    min-height: calc(100vh - 56px);
}

.course-sidebar {
    background: white;
    border-left: 1px solid #e5e7eb;
    height: calc(100vh - 56px);
    position: sticky;
    top: 56px;
    overflow-y: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    VideoPlayer.init(<?= $currentLesson ? $currentLesson['id'] : 'null' ?>);
});
</script>

<?php 
$additionalJs = ['/assets/js/video-player.js'];
require_once __DIR__ . '/../includes/footer.php'; 
?>