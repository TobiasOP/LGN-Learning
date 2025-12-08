// assets/js/main.js

/**
 * LGN E-Learning - Main JavaScript
 */

// =====================================================
// TOAST NOTIFICATION SYSTEM
// =====================================================
const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.getElementById('toastContainer');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toastContainer';
                this.container.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(this.container);
            }
        }
    },
    
    show(message, type = 'success', duration = 5000) {
        this.init();
        
        const icons = {
            success: 'bi-check-circle-fill',
            danger: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${icons[type] || icons.info}"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        this.container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const bsToast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        
        bsToast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    },
    
    success(message) {
        this.show(message, 'success');
    },
    
    error(message) {
        this.show(message, 'danger');
    },
    
    warning(message) {
        this.show(message, 'warning');
    },
    
    info(message) {
        this.show(message, 'info');
    }
};

// =====================================================
// LOADING OVERLAY
// =====================================================
const Loading = {
    overlay: null,
    
    show(message = 'Memproses...') {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'spinner-overlay';
            this.overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-custom mb-3"></div>
                    <p class="text-muted loading-message">${message}</p>
                </div>
            `;
        } else {
            this.overlay.querySelector('.loading-message').textContent = message;
        }
        document.body.appendChild(this.overlay);
        document.body.style.overflow = 'hidden';
    },
    
    hide() {
        if (this.overlay && this.overlay.parentNode) {
            this.overlay.parentNode.removeChild(this.overlay);
            document.body.style.overflow = '';
        }
    }
};

// =====================================================
// API HELPER
// =====================================================
const API = {
    baseUrl: '',
    
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        try {
            const response = await fetch(this.baseUrl + url, mergedOptions);
            const contentType = response.headers.get('content-type');
            
            let data;
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }
            
            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan pada server');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    async get(url) {
        return this.request(url, { method: 'GET' });
    },
    
    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    },
    
    async upload(url, formData) {
        return this.request(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
    }
};

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Format currency to Indonesian Rupiah
 */
function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

/**
 * Format date to Indonesian format
 */
function formatDate(dateString, options = {}) {
    const defaultOptions = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', { ...defaultOptions, ...options });
}

/**
 * Format duration in minutes to readable format
 */
function formatDuration(minutes) {
    if (minutes < 60) {
        return minutes + ' menit';
    }
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (mins === 0) {
        return hours + ' jam';
    }
    return hours + ' jam ' + mins + ' menit';
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Copy text to clipboard
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        Toast.success('Berhasil disalin!');
        return true;
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            Toast.success('Berhasil disalin!');
            return true;
        } catch (e) {
            Toast.error('Gagal menyalin');
            return false;
        } finally {
            document.body.removeChild(textArea);
        }
    }
}

/**
 * Generate slug from text
 */
function generateSlug(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
}

// =====================================================
// FORM VALIDATION
// =====================================================
const FormValidator = {
    rules: {
        required: (value) => value.trim() !== '' || 'Field ini wajib diisi',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || 'Format email tidak valid',
        minLength: (value, min) => value.length >= min || `Minimal ${min} karakter`,
        maxLength: (value, max) => value.length <= max || `Maksimal ${max} karakter`,
        match: (value, targetId) => {
            const target = document.getElementById(targetId);
            return value === target.value || 'Nilai tidak cocok';
        },
        phone: (value) => /^[0-9+\-\s()]{10,}$/.test(value) || 'Format nomor telepon tidak valid',
        url: (value) => {
            if (!value) return true;
            try {
                new URL(value);
                return true;
            } catch {
                return 'Format URL tidak valid';
            }
        },
        number: (value) => !isNaN(value) || 'Harus berupa angka',
        min: (value, min) => parseFloat(value) >= min || `Minimal ${min}`,
        max: (value, max) => parseFloat(value) <= max || `Maksimal ${max}`
    },
    
    validate(form, validations) {
        let isValid = true;
        
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        for (const [fieldName, rules] of Object.entries(validations)) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;
            
            const value = field.value;
            
            for (const rule of rules) {
                let error;
                
                if (typeof rule === 'string') {
                    error = this.rules[rule](value);
                } else if (typeof rule === 'object') {
                    const [ruleName, param] = Object.entries(rule)[0];
                    error = this.rules[ruleName](value, param);
                }
                
                if (error !== true) {
                    isValid = false;
                    this.showError(field, error);
                    break;
                }
            }
        }
        
        return isValid;
    },
    
    showError(field, message) {
        field.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        
        if (field.parentNode.classList.contains('input-group')) {
            field.parentNode.parentNode.appendChild(feedback);
        } else {
            field.parentNode.appendChild(feedback);
        }
    }
};

// =====================================================
// IMAGE PREVIEW
// =====================================================
function setupImagePreview(inputId, previewId, placeholderSrc = '/assets/images/placeholder.png') {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (!input || !preview) return;
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                Toast.error('File harus berupa gambar');
                input.value = '';
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Toast.error('Ukuran file maksimal 5MB');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = placeholderSrc;
        }
    });
}

// =====================================================
// NAVBAR SCROLL EFFECT
// =====================================================
const navbar = document.querySelector('.navbar');
if (navbar) {
    window.addEventListener('scroll', throttle(() => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }, 100));
}

// =====================================================
// INITIALIZE BOOTSTRAP COMPONENTS
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});

// =====================================================
// CONFIRM DIALOG
// =====================================================
function confirmDialog(message, callback) {
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Konfirmasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmBtn">Ya, Lanjutkan</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    
    document.getElementById('confirmBtn').addEventListener('click', function() {
        modal.hide();
        callback();
    });
    
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// =====================================================
// SEARCH FUNCTIONALITY
// =====================================================
const searchInput = document.querySelector('.search-input');
if (searchInput) {
    const searchSuggestions = debounce(async function(query) {
        if (query.length < 2) return;
        
        try {
            const data = await API.get(`/api/courses/search.php?q=${encodeURIComponent(query)}&limit=5`);
            // Display suggestions...
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300);
    
    searchInput.addEventListener('input', function(e) {
        searchSuggestions(e.target.value);
    });
}

// =====================================================
// GLOBAL ERROR HANDLER
// =====================================================
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    Toast.error('Terjadi kesalahan. Silakan coba lagi.');
});

// =====================================================
// EXPORT FOR USE IN OTHER FILES
// =====================================================
window.LGN = {
    Toast,
    Loading,
    API,
    FormValidator,
    formatCurrency,
    formatDate,
    formatDuration,
    debounce,
    throttle,
    copyToClipboard,
    generateSlug,
    setupImagePreview,
    confirmDialog
};