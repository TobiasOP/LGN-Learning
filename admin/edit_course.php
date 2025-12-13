<?php
// admin/edit_course.php

require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

$courseId = intval($_GET['id'] ?? 0);

if (!$courseId) {
    redirect('/admin/courses.php', 'Kursus tidak ditemukan', 'error');
}

$db = getDB();

// Get course (admin bisa akses semua kursus)
$stmt = $db->prepare("
    SELECT c.*, u.name as tutor_name 
    FROM courses c 
    LEFT JOIN users u ON c.tutor_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/admin/courses.php', 'Kursus tidak ditemukan', 'error');
}

$pageTitle = 'Edit: ' . $course['title'];

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get tutors untuk reassign
$tutors = $db->query("SELECT id, name, email FROM users WHERE role = 'tutor' ORDER BY name")->fetchAll();

// Get sections with lessons
$stmt = $db->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_number");
$stmt->execute([$courseId]);
$sections = $stmt->fetchAll();

foreach ($sections as &$section) {
    $stmt = $db->prepare("SELECT * FROM lessons WHERE section_id = ? ORDER BY order_number");
    $stmt->execute([$section['id']]);
    $section['lessons'] = $stmt->fetchAll();
}

// Get enrollment count
$stmt = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
$stmt->execute([$courseId]);
$enrollmentCount = $stmt->fetchColumn();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_course':
            $title = sanitize($_POST['title'] ?? '');
            $tutorId = intval($_POST['tutor_id'] ?? $course['tutor_id']);
            $categoryId = intval($_POST['category_id'] ?? 0);
            $shortDescription = sanitize($_POST['short_description'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $discountPrice = floatval($_POST['discount_price'] ?? 0) ?: null;
            $level = sanitize($_POST['level'] ?? 'beginner');
            $whatYouLearn = sanitize($_POST['what_you_learn'] ?? '');
            $requirements = sanitize($_POST['requirements'] ?? '');
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle thumbnail upload
            $thumbnail = $course['thumbnail'];
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['thumbnail'], 'thumbnails');
                if ($upload['success']) {
                    if ($thumbnail) deleteFile($thumbnail);
                    $thumbnail = $upload['path'];
                }
            }
            
            // Generate new slug if title changed
            $slug = $course['slug'];
            if ($title !== $course['title']) {
                $slug = generateSlug($title, 'courses', $courseId);
            }
            
            $stmt = $db->prepare("
                UPDATE courses SET 
                    tutor_id = ?, category_id = ?, title = ?, slug = ?,
                    short_description = ?, description = ?, thumbnail = ?, 
                    price = ?, discount_price = ?, level = ?,
                    what_you_learn = ?, requirements = ?, is_featured = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $tutorId, $categoryId, $title, $slug,
                $shortDescription, $description, $thumbnail,
                $price, $discountPrice, $level,
                $whatYouLearn, $requirements, $isFeatured,
                $courseId
            ]);
            
            redirect("/admin/edit_course.php?id=$courseId", 'Kursus berhasil diperbarui', 'success');
            break;
            
        case 'add_section':
            $sectionTitle = sanitize($_POST['section_title'] ?? '');
            $sectionDescription = sanitize($_POST['section_description'] ?? '');
            
            if (!empty($sectionTitle)) {
                $orderNumber = $db->query("SELECT COALESCE(MAX(order_number), 0) + 1 FROM course_sections WHERE course_id = $courseId")->fetchColumn();
                
                $stmt = $db->prepare("INSERT INTO course_sections (course_id, title, description, order_number) VALUES (?, ?, ?, ?)");
                $stmt->execute([$courseId, $sectionTitle, $sectionDescription, $orderNumber]);
                
                redirect("/admin/edit_course.php?id=$courseId", 'Section berhasil ditambahkan', 'success');
            }
            break;
            
        case 'update_section':
            $sectionId = intval($_POST['section_id'] ?? 0);
            $sectionTitle = sanitize($_POST['section_title'] ?? '');
            $sectionDescription = sanitize($_POST['section_description'] ?? '');
            
            if ($sectionId && !empty($sectionTitle)) {
                $stmt = $db->prepare("UPDATE course_sections SET title = ?, description = ? WHERE id = ? AND course_id = ?");
                $stmt->execute([$sectionTitle, $sectionDescription, $sectionId, $courseId]);
                
                redirect("/admin/edit_course.php?id=$courseId", 'Section berhasil diperbarui', 'success');
            }
            break;
            
        case 'delete_section':
            $sectionId = intval($_POST['section_id'] ?? 0);
            
            if ($sectionId) {
                // Delete lessons in section first
                $db->prepare("DELETE FROM lessons WHERE section_id = ?")->execute([$sectionId]);
                // Delete section
                $db->prepare("DELETE FROM course_sections WHERE id = ? AND course_id = ?")->execute([$sectionId, $courseId]);
                
                updateCourseTotals($courseId);
                redirect("/admin/edit_course.php?id=$courseId", 'Section berhasil dihapus', 'success');
            }
            break;
            
        case 'add_lesson':
            $sectionId = intval($_POST['section_id'] ?? 0);
            $lessonTitle = sanitize($_POST['lesson_title'] ?? '');
            $lessonDescription = sanitize($_POST['lesson_description'] ?? '');
            $contentType = sanitize($_POST['content_type'] ?? 'video');
            $driveFileId = sanitize($_POST['drive_file_id'] ?? '');
            $driveUrl = sanitize($_POST['drive_url'] ?? '');
            $articleContent = $_POST['article_content'] ?? '';
            $durationMinutes = intval($_POST['duration_minutes'] ?? 0);
            $isPreview = isset($_POST['is_preview']) ? 1 : 0;
            
            if ($sectionId && !empty($lessonTitle)) {
                $orderNumber = $db->query("SELECT COALESCE(MAX(order_number), 0) + 1 FROM lessons WHERE section_id = $sectionId")->fetchColumn();
                
                $stmt = $db->prepare("
                    INSERT INTO lessons (section_id, title, description, content_type, google_drive_file_id, google_drive_url, article_content, duration_minutes, is_preview, order_number)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$sectionId, $lessonTitle, $lessonDescription, $contentType, $driveFileId, $driveUrl, $articleContent, $durationMinutes, $isPreview, $orderNumber]);
                
                updateCourseTotals($courseId);
                redirect("/admin/edit_course.php?id=$courseId", 'Lesson berhasil ditambahkan', 'success');
            }
            break;
            
        case 'update_lesson': 
            $lessonId = intval($_POST['lesson_id'] ?? 0);
            $lessonTitle = sanitize($_POST['lesson_title'] ?? '');
            $lessonDescription = sanitize($_POST['lesson_description'] ?? '');
            $contentType = sanitize($_POST['content_type'] ?? 'video');
            $driveFileId = sanitize($_POST['drive_file_id'] ?? '');
            $driveUrl = sanitize($_POST['drive_url'] ?? '');
            $articleContent = $_POST['article_content'] ?? '';
            $durationMinutes = intval($_POST['duration_minutes'] ?? 0);
            $isPreview = isset($_POST['is_preview']) ? 1 : 0;
            
            if ($lessonId && !empty($lessonTitle)) {
                $stmt = $db->prepare("
                    UPDATE lessons SET 
                        title = ?, description = ?, content_type = ?, 
                        google_drive_file_id = ?, google_drive_url = ?, article_content = ?,
                        duration_minutes = ?, is_preview = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$lessonTitle, $lessonDescription, $contentType, $driveFileId, $driveUrl, $articleContent, $durationMinutes, $isPreview, $lessonId]);
                
                updateCourseTotals($courseId);
                redirect("/admin/edit_course.php?id=$courseId", 'Lesson berhasil diperbarui', 'success');
            }
            break;
            
        case 'delete_lesson': 
            $lessonId = intval($_POST['lesson_id'] ?? 0);
            
            if ($lessonId) {
                $db->prepare("DELETE FROM lessons WHERE id = ?")->execute([$lessonId]);
                
                updateCourseTotals($courseId);
                redirect("/admin/edit_course.php?id=$courseId", 'Lesson berhasil dihapus', 'success');
            }
            break;
            
        case 'publish': 
            $stmt = $db->prepare("UPDATE courses SET is_published = 1 WHERE id = ?");
            $stmt->execute([$courseId]);
            redirect("/admin/edit_course.php?id=$courseId", 'Kursus berhasil dipublikasikan', 'success');
            break;
            
        case 'unpublish': 
            $stmt = $db->prepare("UPDATE courses SET is_published = 0 WHERE id = ?");
            $stmt->execute([$courseId]);
            redirect("/admin/edit_course.php?id=$courseId", 'Kursus dikembalikan ke draft', 'success');
            break;
    }
}

function updateCourseTotals($courseId) {
    global $db;
    
    $stats = $db->query("
        SELECT COUNT(*) as total_lessons, COALESCE(SUM(duration_minutes), 0) as total_duration
        FROM lessons l
        JOIN course_sections cs ON l.section_id = cs.id
        WHERE cs.course_id = $courseId
    ")->fetch();
    
    $stmt = $db->prepare("UPDATE courses SET total_lessons = ?, duration_hours = ? WHERE id = ?");
    $stmt->execute([$stats['total_lessons'], ceil($stats['total_duration'] / 60), $courseId]);
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="py-4 bg-light">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/admin/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/admin/courses.php">Kursus</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($course['title']) ?></h4>
                <small class="text-muted">
                    Tutor: <?= htmlspecialchars($course['tutor_name'] ?? 'Tidak ada') ?> | 
                    <?= $enrollmentCount ?> siswa terdaftar
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="/pages/course_detail.php?slug=<?= $course['slug'] ?>" class="btn btn-outline-secondary" target="_blank">
                    <i class="bi bi-eye me-2"></i>Preview
                </a>
                <?php if ($course['is_published']): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="unpublish">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-pause-circle me-2"></i>Unpublish
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="publish">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-rocket me-2"></i>Publish
                    </button>
                </form>
                <?php endif; ?>
                <a href="/admin/courses.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
        
        <!-- Status Alert -->
        <?php if (!$course['is_published']): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <div>Kursus ini masih dalam status <strong>Draft</strong> dan belum terlihat oleh siswa.</div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Course Details Form -->
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Detail Kursus</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_course">
                            
                            <div class="mb-3">
                                <label class="form-label">Judul Kursus <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?= htmlspecialchars($course['title']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tutor <span class="text-danger">*</span></label>
                                <select name="tutor_id" class="form-select" required>
                                    <?php foreach ($tutors as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $course['tutor_id'] == $t['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?> (<?= $t['email'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Admin dapat mengubah kepemilikan kursus</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" required>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $course['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Level</label>
                                    <select name="level" class="form-select">
                                        <option value="beginner" <?= $course['level'] === 'beginner' ? 'selected' : '' ?>>Pemula</option>
                                        <option value="intermediate" <?= $course['level'] === 'intermediate' ? 'selected' : '' ?>>Menengah</option>
                                        <option value="advanced" <?= $course['level'] === 'advanced' ? 'selected' : '' ?>>Lanjutan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Singkat</label>
                                <textarea name="short_description" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars($course['short_description']) ?></textarea>
                                <small class="text-muted">Maks. 500 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Lengkap</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Thumbnail</label>
                                <?php if ($course['thumbnail']): ?>
                                <div class="mb-2">
                                    <img src="<?= getCourseImage($course) ?>" class="img-fluid rounded" style="max-height: 150px;"
                                         onerror="this.src='https://via.placeholder.com/750x422/4f46e5/ffffff?text=LGN+Course'">
                                </div>
                                <?php endif; ?>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                <small class="text-muted">Ukuran yang disarankan: 750x422 px (rasio 16:9)</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="price" class="form-control" 
                                           value="<?= $course['price'] ?>" min="0" step="1000">
                                    <small class="text-muted">0 = Gratis</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Harga Diskon (Rp)</label>
                                    <input type="number" name="discount_price" class="form-control" 
                                           value="<?= $course['discount_price'] ?>" min="0" step="1000">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Yang Akan Dipelajari</label>
                                <textarea name="what_you_learn" class="form-control" rows="3" placeholder="Poin 1|Poin 2|Poin 3"><?= htmlspecialchars($course['what_you_learn']) ?></textarea>
                                <small class="text-muted">Pisahkan dengan tanda | (pipe)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Persyaratan</label>
                                <textarea name="requirements" class="form-control" rows="2" placeholder="Syarat 1|Syarat 2"><?= htmlspecialchars($course['requirements']) ?></textarea>
                                <small class="text-muted">Pisahkan dengan tanda | (pipe)</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" 
                                           <?= $course['is_featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isFeatured">
                                        <i class="bi bi-star-fill text-warning me-1"></i>Jadikan Kursus Unggulan (Featured)
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Course Stats -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2"></i>Statistik</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="fs-4 fw-bold text-primary"><?= $enrollmentCount ?></div>
                                <small class="text-muted">Siswa</small>
                            </div>
                            <div class="col-4">
                                <div class="fs-4 fw-bold text-success"><?= count($sections) ?></div>
                                <small class="text-muted">Section</small>
                            </div>
                            <div class="col-4">
                                <div class="fs-4 fw-bold text-info"><?= $course['total_lessons'] ?></div>
                                <small class="text-muted">Lesson</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="fs-5 fw-bold"><?= $course['duration_hours'] ?> jam</div>
                                <small class="text-muted">Total Durasi</small>
                            </div>
                            <div class="col-6">
                                <div class="fs-5 fw-bold"><?= formatDate($course['created_at']) ?></div>
                                <small class="text-muted">Dibuat</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Curriculum -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Materi Kursus</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Section
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sections)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-folder-plus fs-1"></i>
                            <p class="mt-2 mb-0">Belum ada materi. Mulai dengan menambahkan section.</p>
                        </div>
                        <?php else: ?>
                        <div class="accordion" id="curriculumAccordion">
                            <?php foreach ($sections as $index => $section): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#section<?= $section['id'] ?>">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                            <div>
                                                <span class="fw-semibold"><?= htmlspecialchars($section['title']) ?></span>
                                                <span class="badge bg-secondary ms-2"><?= count($section['lessons']) ?> lessons</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="section<?= $section['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>">
                                    <div class="accordion-body">
                                        <!-- Section Actions -->
                                        <div class="d-flex gap-2 mb-3">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="openEditSectionModal(<?= $section['id'] ?>, '<?= htmlspecialchars(addslashes($section['title'])) ?>', '<?= htmlspecialchars(addslashes($section['description'] ?? '')) ?>')">
                                                <i class="bi bi-pencil me-1"></i>Edit Section
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="openAddLessonModal(<?= $section['id'] ?>, '<?= htmlspecialchars(addslashes($section['title'])) ?>')">
                                                <i class="bi bi-plus-lg me-1"></i>Tambah Lesson
                                            </button>
                                            <?php if (count($section['lessons']) === 0): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus section ini?')">
                                                <input type="hidden" name="action" value="delete_section">
                                                <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Hapus Section
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($section['description']): ?>
                                        <p class="text-muted small mb-3"><?= htmlspecialchars($section['description']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (empty($section['lessons'])): ?>
                                        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Belum ada lesson di section ini.</p>
                                        <?php else: ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($section['lessons'] as $lesson): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    $iconClass = match($lesson['content_type']) {
                                                        'video' => 'bi-play-circle text-primary',
                                                        'article' => 'bi-file-text text-success',
                                                        'quiz' => 'bi-question-circle text-warning',
                                                        default => 'bi-file text-secondary'
                                                    };
                                                    ?>
                                                    <i class="bi <?= $iconClass ?> me-2 fs-5"></i>
                                                    <div>
                                                        <div class="fw-medium"><?= htmlspecialchars($lesson['title']) ?></div>
                                                        <small class="text-muted">
                                                            <?= ucfirst($lesson['content_type']) ?> • <?= $lesson['duration_minutes'] ?> menit
                                                            <?php if ($lesson['is_preview']): ?>
                                                            <span class="badge bg-info ms-1">Preview</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick='openEditLessonModal(<?= json_encode($lesson) ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus lesson ini?')">
                                                        <input type="hidden" name="action" value="delete_lesson">
                                                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_section">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-folder-plus me-2"></i>Tambah Section Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Section <span class="text-danger">*</span></label>
                        <input type="text" name="section_title" class="form-control" 
                               placeholder="Contoh: Pengenalan Web Development" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea name="section_description" class="form-control" rows="2" 
                                  placeholder="Deskripsi singkat tentang section ini"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_section">
                <input type="hidden" name="section_id" id="editSectionId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Section <span class="text-danger">*</span></label>
                        <input type="text" name="section_title" id="editSectionTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea name="section_description" id="editSectionDescription" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Lesson Modal -->
<div class="modal fade" id="addLessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_lesson">
                <input type="hidden" name="section_id" id="lessonSectionId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Lesson - <span id="lessonSectionTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul Lesson <span class="text-danger">*</span></label>
                            <input type="text" name="lesson_title" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipe Konten</label>
                            <select name="content_type" class="form-select" id="addLessonContentType" onchange="toggleContentFields('add')">
                                <option value="video">Video</option>
                                <option value="article">Artikel</option>
                                <option value="quiz">Quiz</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="lesson_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div id="addVideoFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Drive File ID</label>
                                <input type="text" name="drive_file_id" class="form-control" 
                                       placeholder="Contoh: 1ABC123def456GHI">
                                <small class="text-muted">Salin File ID dari URL Google Drive</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Drive URL (opsional)</label>
                                <input type="url" name="drive_url" class="form-control" 
                                       placeholder="https://drive.google.com/file/d/...">
                            </div>
                        </div>
                    </div>
                    
                    <div id="addArticleFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Konten Artikel</label>
                            <textarea name="article_content" class="form-control" rows="6" 
                                      placeholder="Tulis konten artikel di sini..."></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="10" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Akses</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_preview" class="form-check-input" id="addIsPreview">
                                <label class="form-check-label" for="addIsPreview">
                                    <i class="bi bi-unlock me-1"></i>Jadikan Preview Gratis
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Lesson
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_lesson">
                <input type="hidden" name="lesson_id" id="editLessonId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Lesson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul Lesson <span class="text-danger">*</span></label>
                            <input type="text" name="lesson_title" id="editLessonTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipe Konten</label>
                            <select name="content_type" class="form-select" id="editLessonContentType" onchange="toggleContentFields('edit')">
                                <option value="video">Video</option>
                                <option value="article">Artikel</option>
                                <option value="quiz">Quiz</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="lesson_description" id="editLessonDescription" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div id="editVideoFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Drive File ID</label>
                                <input type="text" name="drive_file_id" id="editLessonDriveId" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Drive URL</label>
                                <input type="url" name="drive_url" id="editLessonDriveUrl" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div id="editArticleFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Konten Artikel</label>
                            <textarea name="article_content" id="editLessonArticle" class="form-control" rows="6"></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="duration_minutes" id="editLessonDuration" class="form-control" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Akses</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_preview" class="form-check-input" id="editIsPreview">
                                <label class="form-check-label" for="editIsPreview">
                                    <i class="bi bi-unlock me-1"></i>Jadikan Preview Gratis
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle content fields based on content type
function toggleContentFields(mode) {
    const contentType = document.getElementById(mode + 'LessonContentType').value;
    const videoFields = document.getElementById(mode + 'VideoFields');
    const articleFields = document.getElementById(mode + 'ArticleFields');
    
    if (contentType === 'video') {
        videoFields.style.display = 'block';
        articleFields.style.display = 'none';
    } else if (contentType === 'article') {
        videoFields.style.display = 'none';
        articleFields.style.display = 'block';
    } else {
        videoFields.style.display = 'none';
        articleFields.style.display = 'none';
    }
}

// Open Edit Section Modal
function openEditSectionModal(sectionId, title, description) {
    document.getElementById('editSectionId').value = sectionId;
    document.getElementById('editSectionTitle').value = title;
    document.getElementById('editSectionDescription').value = description;
    new bootstrap.Modal(document.getElementById('editSectionModal')).show();
}

// Open Add Lesson Modal
function openAddLessonModal(sectionId, sectionTitle) {
    document.getElementById('lessonSectionId').value = sectionId;
    document.getElementById('lessonSectionTitle').textContent = sectionTitle;
    
    // Reset form
    document.querySelector('#addLessonModal form').reset();
    document.getElementById('addLessonContentType').value = 'video';
    toggleContentFields('add');
    
    new bootstrap.Modal(document.getElementById('addLessonModal')).show();
}

// Open Edit Lesson Modal
function openEditLessonModal(lesson) {
    document.getElementById('editLessonId').value = lesson.id;
    document.getElementById('editLessonTitle').value = lesson.title;
    document.getElementById('editLessonDescription').value = lesson.description || '';
    document.getElementById('editLessonContentType').value = lesson.content_type;
    document.getElementById('editLessonDriveId').value = lesson.google_drive_file_id || '';
    document.getElementById('editLessonDriveUrl').value = lesson.google_drive_url || '';
    document.getElementById('editLessonArticle').value = lesson.article_content || '';
    document.getElementById('editLessonDuration').value = lesson.duration_minutes;
    document.getElementById('editIsPreview').checked = lesson.is_preview == 1;
    
    toggleContentFields('edit');
    
    new bootstrap.Modal(document.getElementById('editLessonModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
