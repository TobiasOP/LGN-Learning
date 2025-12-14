<?php
// register.php

$pageTitle = 'Daftar';

require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$defaultRole = sanitize($_GET['role'] ?? 'student');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - LGN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="/index.php">
                    <span class="brand-logo">
                        <span class="text-primary fw-bold">L</span><span class="text-success fw-bold">G</span><span class="text-warning fw-bold">N</span>
                    </span>
                </a>
            </div>
            
            <h4 class="auth-title">Buat Akun Baru</h4>
            <p class="auth-subtitle">Mulai perjalanan belajar Anda bersama LGN</p>
            
            <form id="registerForm" novalidate>
                <!-- Role Selection -->
                <div class="mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="role" id="roleStudent" value="student" <?= $defaultRole === 'student' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary w-100 py-3" for="roleStudent">
                                <i class="bi bi-mortarboard d-block fs-4 mb-1"></i>
                                <span>Siswa</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="role" id="roleTutor" value="tutor" <?= $defaultRole === 'tutor' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary w-100 py-3" for="roleTutor">
                                <i class="bi bi-person-video3 d-block fs-4 mb-1"></i>
                                <span>Tutor</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="mt-2"></div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                    </div>
                </div>
                
                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="agree_terms" required>
                    <label class="form-check-label" for="agree_terms">
                        Saya menyetujui <a href="/pages/terms.php" target="_blank">Syarat & Ketentuan</a> dan <a href="/pages/privacy.php" target="_blank">Kebijakan Privasi</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                </button>
            </form>
            
            <!-- <div class="auth-divider">atau daftar dengan</div>
            
            <div class="social-login">
                <button type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-google"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-facebook"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-github"></i>
                </button>
            </div> -->
            
            <p class="text-center mt-4 mb-0">
                Sudah punya akun? <a href="/login.php" class="fw-semibold">Masuk di sini</a>
            </p>
        </div>
    </div>
    
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>
</html>
