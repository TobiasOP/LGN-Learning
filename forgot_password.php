<?php
// forgot_password.php

$pageTitle = 'Lupa Password';

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
            
            <!-- Step 1: Request Reset -->
            <div id="stepRequest">
                <h4 class="auth-title">Lupa Password?</h4>
                <p class="auth-subtitle">Masukkan email Anda dan kami akan mengirimkan kode verifikasi untuk reset password.</p>
                
                <div id="alertContainer"></div>
                
                <form id="forgotPasswordForm" novalidate>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3" id="btnSubmit">
                        <i class="bi bi-send me-2"></i>Kirim Kode Verifikasi
                    </button>
                </form>
            </div>
            
            <!-- Step 2: Verify Code -->
            <div id="stepVerify" style="display: none;">
                <h4 class="auth-title">Verifikasi Kode</h4>
                <p class="auth-subtitle">Masukkan kode 6 digit yang telah dikirim ke email <strong id="emailDisplay"></strong></p>
                
                <div id="alertContainerVerify"></div>
                
                <form id="verifyCodeForm" novalidate>
                    <input type="hidden" id="verifyEmail" name="email">
                    
                    <div class="mb-4">
                        <label for="code" class="form-label">Kode Verifikasi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="text" class="form-control text-center fs-4 letter-spacing-2" id="code" name="code" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                                   style="letter-spacing: 0.5em;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3" id="btnVerify">
                        <i class="bi bi-check-circle me-2"></i>Verifikasi
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted">Tidak menerima kode?</span>
                        <button type="button" class="btn btn-link p-0" id="btnResend" disabled>
                            Kirim ulang <span id="resendTimer"></span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Step 3: Reset Password -->
            <div id="stepReset" style="display: none;">
                <h4 class="auth-title">Buat Password Baru</h4>
                <p class="auth-subtitle">Masukkan password baru Anda</p>
                
                <div id="alertContainerReset"></div>
                
                <form id="resetPasswordForm" novalidate>
                    <input type="hidden" id="resetEmail" name="email">
                    <input type="hidden" id="resetToken" name="token">
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="newPassword" name="password" 
                                   placeholder="Minimal 6 karakter" minlength="6" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                   placeholder="Ulangi password baru" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3" id="btnReset">
                        <i class="bi bi-check-lg me-2"></i>Reset Password
                    </button>
                </form>
            </div>
            
            <!-- Step 4: Success -->
            <div id="stepSuccess" style="display: none;">
                <div class="text-center py-4">
                    <div class="mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg text-success fs-1"></i>
                        </div>
                    </div>
                    <h4 class="auth-title">Password Berhasil Direset!</h4>
                    <p class="auth-subtitle">Password Anda telah berhasil diperbarui. Silakan login dengan password baru.</p>
                    <a href="/login.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang
                    </a>
                </div>
            </div>
            
            <p class="text-center mt-4 mb-0">
                <a href="/login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke halaman login
                </a>
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
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
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
        
        // Step 1: Request password reset
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnSubmit');
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                showAlert('alertContainer', 'Email wajib diisi');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            
            try {
                const response = await fetch('/api/auth/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'request', email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show step 2
                    document.getElementById('stepRequest').style.display = 'none';
                    document.getElementById('stepVerify').style.display = 'block';
                    document.getElementById('emailDisplay').textContent = email;
                    document.getElementById('verifyEmail').value = email;
                    document.getElementById('code').focus();
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
        
        // Step 2: Verify code
        document.getElementById('verifyCodeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnVerify');
            const email = document.getElementById('verifyEmail').value;
            const code = document.getElementById('code').value.trim();
            
            if (!code || code.length !== 6) {
                showAlert('alertContainerVerify', 'Masukkan kode 6 digit');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memverifikasi...';
            
            try {
                const response = await fetch('/api/auth/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'verify', email, code })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show step 3
                    document.getElementById('stepVerify').style.display = 'none';
                    document.getElementById('stepReset').style.display = 'block';
                    document.getElementById('resetEmail').value = email;
                    document.getElementById('resetToken').value = data.data.token;
                    document.getElementById('newPassword').focus();
                    
                    if (resendInterval) clearInterval(resendInterval);
                } else {
                    showAlert('alertContainerVerify', data.message);
                }
            } catch (error) {
                showAlert('alertContainerVerify', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verifikasi';
            }
        });
        
        // Resend code
        document.getElementById('btnResend').addEventListener('click', async function() {
            const email = document.getElementById('verifyEmail').value;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch('/api/auth/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'request', email })
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
        
        // Step 3: Reset password
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnReset');
            const email = document.getElementById('resetEmail').value;
            const token = document.getElementById('resetToken').value;
            const password = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password.length < 6) {
                showAlert('alertContainerReset', 'Password minimal 6 karakter');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('alertContainerReset', 'Konfirmasi password tidak cocok');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            
            try {
                const response = await fetch('/api/auth/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reset', email, token, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success
                    document.getElementById('stepReset').style.display = 'none';
                    document.getElementById('stepSuccess').style.display = 'block';
                } else {
                    showAlert('alertContainerReset', data.message);
                }
            } catch (error) {
                showAlert('alertContainerReset', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Reset Password';
            }
        });
        
        // Auto-format code input (numbers only)
        document.getElementById('code').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    });
    </script>
</body>
</html>
