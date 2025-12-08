<?php
// includes/navbar.php

// Get categories for dropdown
$navCategories = [];
try {
    $db = getDB();
    $navCategories = $db->query("SELECT name, slug, icon FROM categories WHERE is_active = 1 ORDER BY name LIMIT 8")->fetchAll();
} catch (Exception $e) {
    $navCategories = [];
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
            <div class="brand-logo me-2">
                <span class="text-primary fw-bold fs-4">L</span><span class="text-success fw-bold fs-4">G</span><span class="text-warning fw-bold fs-4">N</span>
            </div>
            <span class="brand-text d-none d-md-inline text-muted fw-medium">Learn GeNius</span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Left Navigation -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active fw-semibold' : '' ?>" href="/index.php">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Kategori
                    </a>
                    <ul class="dropdown-menu shadow-sm">
                        <?php if (!empty($navCategories)): ?>
                            <?php foreach ($navCategories as $cat): ?>
                            <li>
                                <a class="dropdown-item" href="/pages/courses.php?category=<?= htmlspecialchars($cat['slug']) ?>">
                                    <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-folder') ?> me-2"></i><?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" href="/pages/courses.php">
                                <i class="bi bi-collection me-2"></i>Semua Kursus
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pages/courses.php">
                        <i class="bi bi-book me-1"></i>Kursus
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/pages/my_learning.php">
                        <i class="bi bi-mortarboard me-1"></i>Pembelajaran Saya
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Search Form -->
            <form class="d-flex me-lg-3 my-2 my-lg-0" action="/pages/courses.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari kursus..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="min-width: 200px;">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Right Navigation -->
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <?php 
                            $userAvatarUrl = ($currentUser['avatar'] ?? null) && file_exists(__DIR__ . '/../' . $currentUser['avatar'])
                                ? '/' . $currentUser['avatar']
                                : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['name'] ?? 'User') . '&background=4f46e5&color=fff&size=32';
                            ?>
                            <img src="<?= $userAvatarUrl ?>" 
                                 alt="<?= htmlspecialchars($currentUser['name'] ?? 'User') ?>" 
                                 class="rounded-circle me-2" 
                                 width="32" height="32"
                                 style="object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff&size=32'">
                            <span class="d-none d-lg-inline"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $userAvatarUrl ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/pages/profile.php">
                                    <i class="bi bi-person me-2"></i>Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/pages/my_learning.php">
                                    <i class="bi bi-collection-play me-2"></i>Pembelajaran Saya
                                </a>
                            </li>
                            
                            <?php if (hasRole('tutor')): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase small text-muted">Tutor</li>
                            <li>
                                <a class="dropdown-item" href="/pages/tutor/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/pages/courses.php">
                                    <i class="bi bi-journal-code me-2"></i>Kelola Kursus
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasRole('admin')): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase small text-muted">Admin</li>
                            <li>
                                <a class="dropdown-item" href="/admin/index.php">
                                    <i class="bi bi-gear me-2"></i>Admin Panel
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="/api/auth/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">Masuk</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="/register.php">
                            <i class="bi bi-person-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>