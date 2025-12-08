<?php
// pages/course_detail.php

require_once __DIR__ . '/../includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

$db = getDB();

// Get course
$stmt = $db->prepare("
    SELECT 
        c.*,
        cat.name as category_name,
        cat.slug as category_slug,
        u.id as tutor_id,
        u.name as tutor_name,
        u.avatar as tutor_avatar,
        u.bio as tutor_bio,
        (SELECT COUNT(*) FROM courses WHERE tutor_id = u.id AND is_published = 1) as tutor_courses,
        (SELECT COUNT(*) FROM enrollments e JOIN courses co ON e.course_id = co.id WHERE co.tutor_id = u.id) as tutor_students,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
        (SELECT AVG(rating) FROM reviews WHERE course_id = c.id AND is_approved = 1) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE course_id = c.id AND is_approved = 1) as review_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.tutor_id = u.id
    WHERE c.slug = ? AND c.is_published = 1
");
$stmt->execute([$slug]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

$pageTitle = $course['title'];

// Get sections with lessons
$stmt = $db->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_number");
$stmt->execute([$course['id']]);
$sections = $stmt->fetchAll();

$totalLessons = 0;
$totalDuration = 0;

foreach ($sections as &$section) {
    $stmt = $db->prepare("SELECT * FROM lessons WHERE section_id = ? ORDER BY order_number");
    $stmt->execute([$section['id']]);
    $section['lessons'] = $stmt->fetchAll();
    
    foreach ($section['lessons'] as $lesson) {
        $totalLessons++;
        $totalDuration += $lesson['duration_minutes'];
    }
}

// Get reviews
$stmt = $db->prepare("
    SELECT r.*, u.name as user_name, u.avatar as user_avatar
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.course_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$course['id']]);
$reviews = $stmt->fetchAll();

// Check enrollment
$isEnrolled = isLoggedIn() ? isEnrolled($_SESSION['user_id'], $course['id']) : false;
$userProgress = $isEnrolled ? getCourseProgress($_SESSION['user_id'], $course['id']) : 0;

// Parse what you'll learn
$whatYouLearn = $course['what_you_learn'] ? explode('|', $course['what_you_learn']) : [];
$requirements = $course['requirements'] ? explode('|', $course['requirements']) : [];

// Calculate price
$displayPrice = $course['discount_price'] ?: $course['price'];
$hasDiscount = $course['discount_price'] && $course['discount_price'] < $course['price'];
$discountPercent = $hasDiscount ? round((($course['price'] - $course['discount_price']) / $course['price']) * 100) : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <!-- Course Header -->
    <section class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="/pages/courses.php" class="text-white-50">Kursus</a></li>
                            <li class="breadcrumb-item"><a href="/pages/courses.php?category=<?= $course['category_slug'] ?>" class="text-white-50"><?= htmlspecialchars($course['category_name']) ?></a></li>
                        </ol>
                    </nav>
                    
                    <span class="badge bg-primary mb-3"><?= htmlspecialchars($course['category_name']) ?></span>
                    
                    <h1 class="fw-bold mb-3" style="color: lightblue;"><?= htmlspecialchars($course['title']) ?></h1>
                    
                    <p class="lead text-white-50 mb-4"><?= htmlspecialchars($course['short_description']) ?></p>
                    
                    <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
                        <div class="d-flex align-items-center">
                            <?= renderStars($course['avg_rating'] ?: 0, false) ?>
                            <span class="ms-2"><?= number_format($course['avg_rating'] ?: 0, 1) ?></span>
                            <span class="text-white-50 ms-1">(<?= number_format($course['review_count']) ?> ulasan)</span>
                        </div>
                        <span class="text-white-50">
                            <i class="bi bi-people me-1"></i><?= number_format($course['enrollment_count']) ?> siswa
                        </span>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <img src="<?= $course['tutor_avatar'] ?: '/assets/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($course['tutor_name']) ?>"
                             class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
                        <div>
                            <small class="text-white-50">Dibuat oleh</small>
                            <div class="fw-semibold"><?= htmlspecialchars($course['tutor_name']) ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3 text-white-50">
                        <span><i class="bi bi-calendar3 me-1"></i>Terakhir diperbarui <?= formatDate($course['updated_at']) ?></span>
                        <span><i class="bi bi-globe me-1"></i><?= $course['language'] ?: 'Indonesia' ?></span>
                        <span class="badge badge-level <?= $course['level'] ?>"><?= ucfirst($course['level']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Course Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- What You'll Learn -->
                    <?php if (!empty($whatYouLearn)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="fw-bold mb-4">Yang Akan Anda Pelajari</h4>
                            <div class="row">
                                <?php foreach ($whatYouLearn as $item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                        <span><?= htmlspecialchars(trim($item)) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Course Content/Curriculum -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">Materi Kursus</h4>
                                <span class="text-muted">
                                    <?= count($sections) ?> bagian • <?= $totalLessons ?> pelajaran • <?= formatDuration($totalDuration) ?>
                                </span>
                            </div>
                            
                            <div class="accordion" id="curriculumAccordion">
                                <?php foreach ($sections as $index => $section): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#section<?= $section['id'] ?>">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <span class="fw-semibold"><?= htmlspecialchars($section['title']) ?></span>
                                                <span class="text-muted small">
                                                    <?= count($section['lessons']) ?> pelajaran
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="section<?= $section['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                         data-bs-parent="#curriculumAccordion">
                                        <div class="accordion-body p-0">
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($section['lessons'] as $lesson): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi <?= $lesson['content_type'] === 'video' ? 'bi-play-circle' : 'bi-file-text' ?> me-3 text-primary"></i>
                                                        <div>
                                                            <div><?= htmlspecialchars($lesson['title']) ?></div>
                                                            <?php if ($lesson['is_preview']): ?>
                                                            <small class="text-primary">
                                                                <i class="bi bi-unlock me-1"></i>Preview Gratis
                                                            </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span class="text-muted small"><?= $lesson['duration_minutes'] ?> menit</span>
                                                        <?php if ($lesson['is_preview']): ?>
                                                        <button class="btn btn-sm btn-outline-primary preview-btn" 
                                                                data-lesson-id="<?= $lesson['id'] ?>">
                                                            <i class="bi bi-play-fill"></i>
                                                        </button>
                                                        <?php elseif (!$isEnrolled): ?>
                                                        <i class="bi bi-lock text-muted"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Requirements -->
                    <?php if (!empty($requirements)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="fw-bold mb-4">Persyaratan</h4>
                            <ul class="mb-0">
                                <?php foreach ($requirements as $req): ?>
                                <li class="mb-2"><?= htmlspecialchars(trim($req)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Description -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="fw-bold mb-4">Deskripsi</h4>
                            <div class="course-description">
                                <?= nl2br(htmlspecialchars($course['description'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructor -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="fw-bold mb-4">Instruktur</h4>
                            <div class="d-flex">
                                <img src="<?= $course['tutor_avatar'] ?: '/assets/images/default-avatar.png' ?>" 
                                     alt="<?= htmlspecialchars($course['tutor_name']) ?>"
                                     class="rounded-circle me-4" width="100" height="100" style="object-fit: cover;">
                                <div>
                                    <h5 class="fw-semibold mb-1"><?= htmlspecialchars($course['tutor_name']) ?></h5>
                                    <p class="text-muted mb-2">Instruktur</p>
                                    <div class="d-flex gap-4 mb-3 text-muted">
                                        <span><i class="bi bi-star-fill text-warning me-1"></i>4.8 Rating</span>
                                        <span><i class="bi bi-people me-1"></i><?= number_format($course['tutor_students']) ?> Siswa</span>
                                        <span><i class="bi bi-collection-play me-1"></i><?= $course['tutor_courses'] ?> Kursus</span>
                                    </div>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($course['tutor_bio'] ?: 'Instruktur profesional dengan pengalaman di bidangnya.')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">Ulasan Siswa</h4>
                                <span class="text-muted"><?= number_format($course['review_count']) ?> ulasan</span>
                            </div>
                            
                            <?php if (empty($reviews)): ?>
                            <p class="text-muted text-center py-4">Belum ada ulasan untuk kursus ini.</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <img src="<?= $review['user_avatar'] ?: '/assets/images/default-avatar.png' ?>" 
                                             alt="<?= htmlspecialchars($review['user_name']) ?>"
                                             class="review-avatar">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?= htmlspecialchars($review['user_name']) ?></div>
                                            <div class="d-flex align-items-center gap-2">
                                                <?= renderStars($review['rating'], false) ?>
                                                <span class="text-muted small"><?= timeAgo($review['created_at']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="review-content mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 100px;">
                        <!-- Course Preview -->
                        <?php if ($course['preview_video_id']): ?>
                        <div class="video-player-wrapper">
                            <div class="video-player-container">
                                <iframe src="https://drive.google.com/file/d/1Ab9w4BlF31K9by-Hyeq2jA5R_hy4h57s/view<?= $course['preview_video_id'] ?>/preview" allowfullscreen></iframe>
                            </div>
                        </div>
                        <?php else: ?>
                        <img src="<?= $course['thumbnail'] ?: '/assets/images/course-placeholder.jpg' ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <!-- Price -->
                            <div class="mb-4">
                                <?php if ($displayPrice == 0): ?>
                                    <span class="fs-2 fw-bold text-success">Gratis</span>
                                <?php else: ?>
                                    <span class="fs-2 fw-bold"><?= formatCurrency($displayPrice) ?></span>
                                    <?php if ($hasDiscount): ?>
                                    <span class="text-muted text-decoration-line-through ms-2"><?= formatCurrency($course['price']) ?></span>
                                    <span class="badge bg-danger ms-2"><?= $discountPercent ?>% OFF</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <?php if ($isEnrolled): ?>
                                <a href="/pages/learn.php?course=<?= $course['id'] ?>" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="bi bi-play-circle me-2"></i>Lanjutkan Belajar
                                </a>
                                <div class="progress-custom mb-3">
                                    <div class="progress-bar" style="width: <?= $userProgress ?>%"></div>
                                </div>
                                <p class="text-center text-muted mb-0"><?= round($userProgress) ?>% selesai</p>
                            <?php else: ?>
                                <?php if ($displayPrice == 0): ?>
                                <button class="btn btn-success btn-lg w-100 mb-3" id="enrollFreeBtn" data-course-id="<?= $course['id'] ?>">
                                    <i class="bi bi-unlock me-2"></i>Daftar Gratis
                                </button>
                                <?php else: ?>
                                <a href="/pages/checkout.php?course=<?= $course['id'] ?>" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="bi bi-cart-plus me-2"></i>Beli Sekarang
                                </a>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary btn-lg w-100 mb-3">
                                    <i class="bi bi-heart me-2"></i>Tambah ke Wishlist
                                </button>
                            <?php endif; ?>
                            
                            <!-- Course Includes -->
                            <h6 class="fw-bold mb-3">Kursus ini mencakup:</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-play-circle text-primary me-2"></i>
                                    <?= formatDuration($totalDuration) ?> video on-demand
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                    <?= $totalLessons ?> pelajaran
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-infinity text-primary me-2"></i>
                                    Akses selamanya
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-phone text-primary me-2"></i>
                                    Akses di mobile dan TV
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-award text-primary me-2"></i>
                                    Sertifikat penyelesaian
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="video-player-container" id="previewVideoContainer">
                    <!-- Video will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview button click
    document.querySelectorAll('.preview-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const lessonId = this.dataset.lessonId;
            
            try {
                const response = await LGN.API.get(`/api/videos/get_video.php?lesson_id=${lessonId}`);
                
                if (response.success) {
                    const container = document.getElementById('previewVideoContainer');
                    container.innerHTML = `<iframe src="${response.data.video_url}" allowfullscreen></iframe>`;
                    
                    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                    modal.show();
                }
            } catch (error) {
                LGN.Toast.error(error.message);
            }
        });
    });
    
    // Clean up video when modal closes
    document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('previewVideoContainer').innerHTML = '';
    });
    
    // Free enrollment
    const enrollFreeBtn = document.getElementById('enrollFreeBtn');
    if (enrollFreeBtn) {
        enrollFreeBtn.addEventListener('click', async function() {
            <?php if (!isLoggedIn()): ?>
            window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.href);
            return;
            <?php endif; ?>
            
            const courseId = this.dataset.courseId;
            
            try {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                
                const response = await LGN.API.post('/api/courses/enroll_free.php', { course_id: courseId });
                
                if (response.success) {
                    LGN.Toast.success('Berhasil mendaftar! Mengalihkan...');
                    setTimeout(() => {
                        window.location.href = `/pages/learn.php?course=${courseId}`;
                    }, 1000);
                }
            } catch (error) {
                LGN.Toast.error(error.message);
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-unlock me-2"></i>Daftar Gratis';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>