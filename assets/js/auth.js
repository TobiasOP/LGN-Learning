// assets/js/auth.js

/**
 * LGN E-Learning - Authentication JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // LOGIN FORM
    // =====================================================
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember')?.checked || false;
            
            // Validate
            if (!email || !password) {
                LGN.Toast.error('Email dan password wajib diisi');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                
                const response = await LGN.API.post('/api/auth/login.php', {
                    email,
                    password,
                    remember
                });
                
                if (response.success) {
                    LGN.Toast.success('Login berhasil! Mengalihkan...');
                    
                    // Redirect
                    setTimeout(() => {
                        const redirect = new URLSearchParams(window.location.search).get('redirect');
                        window.location.href = redirect || '/index.php';
                    }, 1000);
                } else {
                    LGN.Toast.error(response.message || 'Login gagal');
                }
            } catch (error) {
                LGN.Toast.error(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // =====================================================
    // REGISTER FORM
    // =====================================================
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const role = document.querySelector('input[name="role"]:checked')?.value || 'student';
            const agreeTerms = document.getElementById('agree_terms')?.checked || false;
            
            // Validate
            const validations = {
                name: ['required', { minLength: 3 }],
                email: ['required', 'email'],
                password: ['required', { minLength: 6 }],
                confirm_password: ['required', { match: 'password' }]
            };
            
            if (!LGN.FormValidator.validate(this, validations)) {
                return;
            }
            
            if (!agreeTerms) {
                LGN.Toast.error('Anda harus menyetujui syarat dan ketentuan');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendaftar...';
                
                const response = await LGN.API.post('/api/auth/register.php', {
                    name,
                    email,
                    password,
                    confirm_password: confirmPassword,
                    role
                });
                
                if (response.success) {
                    LGN.Toast.success('Registrasi berhasil! Mengalihkan...');
                    
                    setTimeout(() => {
                        window.location.href = '/index.php';
                    }, 1000);
                } else {
                    LGN.Toast.error(response.message || 'Registrasi gagal');
                }
            } catch (error) {
                LGN.Toast.error(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // =====================================================
    // PASSWORD VISIBILITY TOGGLE
    // =====================================================
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
    
    // =====================================================
    // PASSWORD STRENGTH METER
    // =====================================================
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('passwordStrength');
    
    if (passwordInput && strengthMeter) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            strengthMeter.innerHTML = `
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-${strength.color}" style="width: ${strength.percent}%"></div>
                </div>
                <small class="text-${strength.color}">${strength.label}</small>
            `;
        });
    }
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        const levels = [
            { min: 0, max: 2, label: 'Lemah', color: 'danger', percent: 25 },
            { min: 2, max: 4, label: 'Sedang', color: 'warning', percent: 50 },
            { min: 4, max: 5, label: 'Kuat', color: 'info', percent: 75 },
            { min: 5, max: 7, label: 'Sangat Kuat', color: 'success', percent: 100 }
        ];
        
        return levels.find(l => score >= l.min && score < l.max) || levels[levels.length - 1];
    }
});