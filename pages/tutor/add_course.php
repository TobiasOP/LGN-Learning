<?php
// pages/tutor/add_course.php

$pageTitle = 'Buat Kursus Baru';

require_once __DIR__ . '/../../includes/functions.php';

requireRole('tutor');

$db = getDB();

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $shortDescription = sanitize($_POST['short_description'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = floatval($_POST['discount_price'] ?? 0) ?: null;
    $level = sanitize($_POST['level'] ?? 'beginner');
    $whatYouLearn = sanitize($_POST['what_you_learn'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    
    $errors = [];
    
    if (empty($title) || strlen($title) < 10) {
        $errors[] = 'Judul minimal 10 karakter';
    }
    
    if ($categoryId <= 0) {
        $errors[] = 'Pilih kategori';
    }
    
    if (empty($shortDescription)) {
        $errors[] = 'Deskripsi singkat wajib diisi';
    }
    
    if ($price < 0) {
        $errors[] = 'Harga tidak valid';
    }
    
    if (empty($errors)) {
        $slug = generateSlug($title, 'courses');
        
        // Handle thumbnail upload
        $thumbnail = null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['thumbnail'], 'thumbnails');
            if ($upload['success']) {
                $thumbnail = $upload['path'];
            } else {
                $errors[] = $upload['message'];
            }
        }
        
        if (empty($errors)) {
            $stmt = $db->prepare("
                INSERT INTO courses (
                    tutor_id, category_id, title, slug, short_description, description,
                    thumbnail, price, discount_price, level, what_you_learn, requirements,
                    is_published, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $categoryId,
                $title,
                $slug,
                $shortDescription,
                $description,
                $thumbnail,
                $price,
                $discountPrice,
                $level,
                $whatYouLearn,
                $requirements
            ]);
            
            $courseId = $db->lastInsertId();
            
            redirect("/pages/tutor/edit_course.php?id=$courseId", 'Kursus berhasil dibuat! Sekarang tambahkan materi.', 'success');
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Buat Kursus Baru
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Basic Info -->
                            <h5 class="fw-semibold mb-3">Informasi Dasar</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Judul Kursus <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                       placeholder="Contoh: Complete Web Development Bootcamp 2024" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Level</label>
                                    <select name="level" class="form-select">
                                        <option value="beginner" <?= ($_POST['level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Pemula</option>
                                        <option value="intermediate" <?= ($_POST['level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Menengah</option>
                                        <option value="advanced" <?= ($_POST['level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Lanjutan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                                <textarea name="short_description" class="form-control" rows="2" 
                                          placeholder="Ringkasan singkat tentang kursus ini (maks. 200 karakter)"
                                          maxlength="200" required><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Lengkap</label>
                                <textarea name="description" class="form-control" rows="6" 
                                          placeholder="Jelaskan secara detail tentang kursus ini..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Thumbnail Kursus</label>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                <small class="text-muted">Ukuran yang disarankan: 750x422 pixels (16:9)</small>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Pricing -->
                            <h5 class="fw-semibold mb-3">Harga</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Normal (Rp)</label>
                                    <input type="number" name="price" class="form-control" 
                                           value="<?= $_POST['price'] ?? '0' ?>" min="0" step="1000">
                                    <small class="text-muted">Isi 0 untuk kursus gratis</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Diskon (Rp)</label>
                                    <input type="number" name="discount_price" class="form-control" 
                                           value="<?= $_POST['discount_price'] ?? '' ?>" min="0" step="1000">
                                    <small class="text-muted">Kosongkan jika tidak ada diskon</small>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Learning Outcomes -->
                            <h5 class="fw-semibold mb-3">Detail Kursus</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Yang Akan Dipelajari</label>
                                <textarea name="what_you_learn" class="form-control" rows="4" 
                                          placeholder="Pisahkan setiap poin dengan baris baru atau tanda |"><?= htmlspecialchars($_POST['what_you_learn'] ?? '') ?></textarea>
                                <small class="text-muted">Contoh: Membangun website dari nol|Menguasai HTML, CSS, JavaScript</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Persyaratan</label>
                                <textarea name="requirements" class="form-control" rows="3" 
                                          placeholder="Pisahkan setiap poin dengan baris baru atau tanda |"><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                                <small class="text-muted">Contoh: Komputer/Laptop|Koneksi internet</small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan & Lanjutkan
                                </button>
                                <a href="/pages/courses.php" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>