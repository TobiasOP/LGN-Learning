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
            
            <!-- Step 1: Registration Form -->
            <div id="stepRegister">
                <h4 class="auth-title">Buat Akun Baru</h4>
                <p class="auth-subtitle">Mulai perjalanan belajar Anda bersama LGN</p>
                
                <div id="alertContainer"></div>
                
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
                        <small class="text-muted">Kami akan mengirim kode verifikasi ke email ini</small>
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
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Ulangi password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                Saya setuju dengan <a href="/pages/terms.php" target="_blank">Syarat & Ketentuan</a> dan <a href="/pages/privacy.php" target="_blank">Kebijakan Privasi</a>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3" id="btnRegister">
                        <i class="bi bi-send me-2"></i>Kirim Kode Verifikasi
                    </button>
                </form>
            </div>
            
            <!-- Step 2: Email Verification -->
            <div id="stepVerify" style="display: none;">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-envelope-check text-primary fs-1"></i>
                    </div>
                </div>
                
                <h4 class="auth-title">Verifikasi Email</h4>
                <p class="auth-subtitle">
                    Masukkan kode 6 digit yang telah dikirim ke<br>
                    <strong id="emailDisplay" class="text-primary"></strong>
                </p>
                
                <div id="alertContainerVerify"></div>
                
                <form id="verifyForm" novalidate>
                    <input type="hidden" id="verifyEmail" name="email">
                    
                    <div class="mb-4">
                        <label for="verificationCode" class="form-label">Kode Verifikasi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="text" class="form-control text-center fs-4" id="verificationCode" name="code" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                                   style="letter-spacing: 0.5em;" autocomplete="off">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3" id="btnVerify">
                        <i class="bi bi-check-circle me-2"></i>Verifikasi & Daftar
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted">Tidak menerima kode?</span>
                        <button type="button" class="btn btn-link p-0" id="btnResend" disabled>
                            Kirim ulang <span id="resendTimer"></span>
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnChangeEmail">
                            <i class="bi bi-arrow-left me-1"></i>Ubah email
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Step 3: Success -->
            <div id="stepSuccess" style="display: none;">
                <div class="text-center py-4">
                    <div class="mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px;">
                            <i class="bi bi-check-lg text-success" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h4 class="auth-title">Pendaftaran Berhasil! 🎉</h4>
                    <p class="auth-subtitle">
                        Selamat datang di LGN E-Learning!<br>
                        Akun Anda telah aktif dan siap digunakan.
                    </p>
                    <a href="/index.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-rocket-takeoff me-2"></i>Mulai Belajar
                    </a>
                </div>
            </div>
            
            <p class="text-center mt-4 mb-0" id="loginLink">
                Sudah punya akun? <a href="/login.php" class="fw-semibold">Masuk</a>
            </p>
        </div>
    </div>
    
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let resendCountdown = 0;
        let resendInterval = null;
        let registrationData = {};
        
        // Password toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
        
        // Show alert helper
        function showAlert(containerId, message, type = 'danger') {
            const container = document.getElementById(containerId);
            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        
        // Start resend countdown
        function startResendCountdown() {
            resendCountdown = 60;
            const btnResend = document.getElementById('btnResend');
            const resendTimer = document.getElementById('resendTimer');
            
            btnResend.disabled = true;
            resendTimer.textContent = `(${resendCountdown}s)`;
            
            resendInterval = setInterval(() => {
                resendCountdown--;
                if (resendCountdown <= 0) {
                    clearInterval(resendInterval);
                    btnResend.disabled = false;
                    resendTimer.textContent = '';
                } else {
                    resendTimer.textContent = `(${resendCountdown}s)`;
                }
            }, 1000);
        }
        
        // Step 1: Submit registration form
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnRegister');
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const role = document.querySelector('input[name="role"]:checked')?.value || 'student';
            const agreeTerms = document.getElementById('agreeTerms').checked;
            
            // Validations
            if (!name || name.length < 3) {
                showAlert('alertContainer', 'Nama minimal 3 karakter');
                return;
            }
            
            if (!email || !email.includes('@')) {
                showAlert('alertContainer', 'Format email tidak valid');
                return;
            }
            
            if (!password || password.length < 6) {
                showAlert('alertContainer', 'Password minimal 6 karakter');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('alertContainer', 'Konfirmasi password tidak cocok');
                return;
            }
            
            if (!agreeTerms) {
                showAlert('alertContainer', 'Anda harus menyetujui Syarat & Ketentuan');
                return;
            }
            
            // Store data for later
            registrationData = { name, email, password, confirm_password: confirmPassword, role };
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            
            try {
                const response = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send_verification', ...registrationData })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show step 2
                    document.getElementById('stepRegister').style.display = 'none';
                    document.getElementById('stepVerify').style.display = 'block';
                    document.getElementById('emailDisplay').textContent = email;
                    document.getElementById('verifyEmail').value = email;
                    document.getElementById('loginLink').style.display = 'none';
                    document.getElementById('verificationCode').focus();
                    startResendCountdown();
                } else {
                    showAlert('alertContainer', data.message);
                }
            } catch (error) {
                showAlert('alertContainer', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Kode Verifikasi';
            }
        });
        
        // Step 2: Verify code and complete registration
        document.getElementById('verifyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnVerify');
            const code = document.getElementById('verificationCode').value.trim();
            
            if (!code || code.length !== 6) {
                showAlert('alertContainerVerify', 'Masukkan kode 6 digit');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memverifikasi...';
            
            try {
                const response = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'verify_and_register', 
                        email: registrationData.email,
                        code: code 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success
                    document.getElementById('stepVerify').style.display = 'none';
                    document.getElementById('stepSuccess').style.display = 'block';
                    document.getElementById('loginLink').style.display = 'none';
                    
                    if (resendInterval) clearInterval(resendInterval);
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = data.data?.redirect || '/index.php';
                    }, 3000);
                } else {
                    showAlert('alertContainerVerify', data.message);
                }
            } catch (error) {
                showAlert('alertContainerVerify', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verifikasi & Daftar';
            }
        });
        
        // Resend code
        document.getElementById('btnResend').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send_verification', ...registrationData })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('alertContainerVerify', 'Kode verifikasi baru telah dikirim', 'success');
                    startResendCountdown();
                } else {
                    showAlert('alertContainerVerify', data.message);
                    this.disabled = false;
                }
            } catch (error) {
                showAlert('alertContainerVerify', 'Gagal mengirim ulang kode');
                this.disabled = false;
            }
            
            this.innerHTML = 'Kirim ulang <span id="resendTimer"></span>';
        });
        
        // Change email button
        document.getElementById('btnChangeEmail').addEventListener('click', function() {
            document.getElementById('stepVerify').style.display = 'none';
            document.getElementById('stepRegister').style.display = 'block';
            document.getElementById('loginLink').style.display = 'block';
            document.getElementById('alertContainer').innerHTML = '';
            
            if (resendInterval) clearInterval(resendInterval);
        });
        
        // Auto-format code input (numbers only)
        document.getElementById('verificationCode').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    });
    </script>
</body>
</html>
