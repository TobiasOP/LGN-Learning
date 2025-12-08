<?php
// login.php

$pageTitle = 'Masuk';

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/index.php');
}
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
            
            <h4 class="auth-title">Selamat Datang Kembali!</h4>
            <p class="auth-subtitle">Masuk untuk melanjutkan pembelajaran Anda</p>
            
            <div id="alertContainer"></div>
            
            <form id="loginForm" novalidate>
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
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <a href="/forgot_password.php" class="text-decoration-none">Lupa password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>
            
            <!-- <div class="auth-divider">atau masuk dengan</div>
            
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
                Belum punya akun? <a href="/register.php" class="fw-semibold">Daftar sekarang</a>
            </p>
        </div>
    </div>
    
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>
</html>