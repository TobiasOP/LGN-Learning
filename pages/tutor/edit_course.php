<?php
// pages/tutor/edit_course.php

require_once __DIR__ . '/../../includes/functions.php';

requireRole('tutor');

$courseId = intval($_GET['id'] ?? 0);

if (!$courseId) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

$db = getDB();

// Get course
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ? AND tutor_id = ?");
$stmt->execute([$courseId, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php', 'Kursus tidak ditemukan', 'error');
}

$pageTitle = 'Edit: ' . $course['title'];

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get sections with lessons
$stmt = $db->prepare("SELECT * FROM course_sections WHERE course_id = ? ORDER BY order_number");
$stmt->execute([$courseId]);
$sections = $stmt->fetchAll();

foreach ($sections as &$section) {
    $stmt = $db->prepare("SELECT * FROM lessons WHERE section_id = ? ORDER BY order_number");
    $stmt->execute([$section['id']]);
    $section['lessons'] = $stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_course':
            // Update course details
            $title = sanitize($_POST['title'] ?? '');
            $categoryId = intval($_POST['category_id'] ?? 0);
            $shortDescription = sanitize($_POST['short_description'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $discountPrice = floatval($_POST['discount_price'] ?? 0) ?: null;
            $level = sanitize($_POST['level'] ?? 'beginner');
            $whatYouLearn = sanitize($_POST['what_you_learn'] ?? '');
            $requirements = sanitize($_POST['requirements'] ?? '');
            
            $thumbnail = $course['thumbnail'];
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['thumbnail'], 'thumbnails');
                if ($upload['success']) {
                    if ($thumbnail) deleteFile($thumbnail);
                    $thumbnail = $upload['path'];
                }
            }
            
            $stmt = $db->prepare("
                UPDATE courses SET 
                    category_id = ?, title = ?, short_description = ?, description = ?,
                    thumbnail = ?, price = ?, discount_price = ?, level = ?,
                    what_you_learn = ?, requirements = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $categoryId, $title, $shortDescription, $description,
                $thumbnail, $price, $discountPrice, $level,
                $whatYouLearn, $requirements, $courseId
            ]);
            
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Kursus berhasil diperbarui', 'success');
            break;
            
        case 'add_section':
            $sectionTitle = sanitize($_POST['section_title'] ?? '');
            $orderNumber = $db->query("SELECT COALESCE(MAX(order_number), 0) + 1 FROM course_sections WHERE course_id = $courseId")->fetchColumn();
            
            $stmt = $db->prepare("INSERT INTO course_sections (course_id, title, order_number) VALUES (?, ?, ?)");
            $stmt->execute([$courseId, $sectionTitle, $orderNumber]);
            
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Section berhasil ditambahkan', 'success');
            break;
            
        case 'add_lesson':
            $sectionId = intval($_POST['section_id'] ?? 0);
            $lessonTitle = sanitize($_POST['lesson_title'] ?? '');
            $lessonDescription = sanitize($_POST['lesson_description'] ?? '');
            $driveFileId = sanitize($_POST['drive_file_id'] ?? '');
            $durationMinutes = intval($_POST['duration_minutes'] ?? 0);
            $isPreview = isset($_POST['is_preview']) ? 1 : 0;
            
            $orderNumber = $db->query("SELECT COALESCE(MAX(order_number), 0) + 1 FROM lessons WHERE section_id = $sectionId")->fetchColumn();
            
            $stmt = $db->prepare("
                INSERT INTO lessons (section_id, title, description, google_drive_file_id, duration_minutes, is_preview, order_number)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$sectionId, $lessonTitle, $lessonDescription, $driveFileId, $durationMinutes, $isPreview, $orderNumber]);
            
            // Update course totals
            updateCourseTotals($courseId);
            
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Lesson berhasil ditambahkan', 'success');
            break;
            
        case 'publish':
            $stmt = $db->prepare("UPDATE courses SET is_published = 1 WHERE id = ?");
            $stmt->execute([$courseId]);
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Kursus berhasil dipublikasikan', 'success');
            break;
            
        case 'unpublish':
            $stmt = $db->prepare("UPDATE courses SET is_published = 0 WHERE id = ?");
            $stmt->execute([$courseId]);
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Kursus dikembalikan ke draft', 'success');
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

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="py-5 bg-light">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/pages/tutor/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/pages/courses.php">Kursus</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($course['title']) ?></h4>
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
            </div>
        </div>
        
        <div class="row">
            <!-- Course Details Form -->
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Detail Kursus</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_course">
                            
                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?= htmlspecialchars($course['title']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori</label>
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
                                <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($course['short_description']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Thumbnail</label>
                                <?php if ($course['thumbnail']): ?>
                                <img src="/<?= $course['thumbnail'] ?>" class="d-block mb-2 rounded" style="max-height: 100px;">
                                <?php endif; ?>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            </div>
                            
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="price" class="form-control" 
                                           value="<?= $course['price'] ?>" min="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Diskon (Rp)</label>
                                    <input type="number" name="discount_price" class="form-control" 
                                           value="<?= $course['discount_price'] ?>" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Yang Akan Dipelajari</label>
                                <textarea name="what_you_learn" class="form-control" rows="3"><?= htmlspecialchars($course['what_you_learn']) ?></textarea>
                                <small class="text-muted">Pisahkan dengan tanda |</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Persyaratan</label>
                                <textarea name="requirements" class="form-control" rows="2"><?= htmlspecialchars($course['requirements']) ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Curriculum -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Materi Kursus</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Section
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sections)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-folder-plus fs-1"></i>
                            <p class="mt-2">Belum ada materi. Mulai dengan menambahkan section.</p>
                        </div>
                        <?php else: ?>
                        <div class="accordion" id="curriculumAccordion">
                            <?php foreach ($sections as $index => $section): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#section<?= $section['id'] ?>">
                                        <span class="fw-semibold"><?= htmlspecialchars($section['title']) ?></span>
                                        <span class="badge bg-secondary ms-2"><?= count($section['lessons']) ?> lessons</span>
                                    </button>
                                </h2>
                                <div id="section<?= $section['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>">
                                    <div class="accordion-body">
                                        <?php if (empty($section['lessons'])): ?>
                                        <p class="text-muted mb-3">Belum ada lesson di section ini.</p>
                                        <?php else: ?>
                                        <ul class="list-group list-group-flush mb-3">
                                            <?php foreach ($section['lessons'] as $lesson): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <i class="bi bi-play-circle me-2 text-primary"></i>
                                                    <?= htmlspecialchars($lesson['title']) ?>
                                                    <?php if ($lesson['is_preview']): ?>
                                                    <span class="badge bg-info ms-2">Preview</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <span class="text-muted me-3"><?= $lesson['duration_minutes'] ?> menit</span>
                                                    <button class="btn btn-sm btn-outline-secondary me-1" 
                                                            onclick="editLesson(<?= $lesson['id'] ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteLesson(<?= $lesson['id'] ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="openAddLessonModal(<?= $section['id'] ?>, '<?= htmlspecialchars($section['title']) ?>')">
                                            <i class="bi bi-plus-lg me-1"></i>Tambah Lesson
                                        </button>
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
                    <h5 class="modal-title">Tambah Section Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Section</label>
                        <input type="text" name="section_title" class="form-control" 
                               placeholder="Contoh: Pengenalan Web Development" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Section</button>
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
                    <h5 class="modal-title">Tambah Lesson - <span id="lessonSectionTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Lesson</label>
                        <input type="text" name="lesson_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="lesson_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Google Drive File ID</label>
                        <input type="text" name="drive_file_id" class="form-control" 
                               placeholder="Contoh: 1ABC123def456GHI" required>
                        <small class="text-muted">
                            Upload video ke Google Drive, lalu salin File ID dari URL. 
                            Pastikan sharing diset ke "Anyone with the link".
                        </small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="10" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preview Gratis?</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_preview" class="form-check-input" id="isPreview">
                                <label class="form-check-label" for="isPreview">Ya, jadikan preview gratis</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddLessonModal(sectionId, sectionTitle) {
    document.getElementById('lessonSectionId').value = sectionId;
    document.getElementById('lessonSectionTitle').textContent = sectionTitle;
    new bootstrap.Modal(document.getElementById('addLessonModal')).show();
}

async function deleteLesson(lessonId) {
    if (!confirm('Yakin ingin menghapus lesson ini?')) return;
    
    try {
        const response = await LGN.API.post('/api/tutor/delete_lesson.php', { lesson_id: lessonId });
        if (response.success) {
            LGN.Toast.success('Lesson berhasil dihapus');
            location.reload();
        }
    } catch (error) {
        LGN.Toast.error(error.message);
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>