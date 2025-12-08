// assets/js/courses.js

/**
 * LGN E-Learning - Courses JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // COURSE FILTERS
    // =====================================================
    const filterForm = document.getElementById('courseFilterForm');
    if (filterForm) {
        // Category filter
        const categorySelect = document.getElementById('categoryFilter');
        const levelSelect = document.getElementById('levelFilter');
        const sortSelect = document.getElementById('sortFilter');
        const priceRange = document.getElementById('priceRange');
        
        [categorySelect, levelSelect, sortSelect].forEach(select => {
            if (select) {
                select.addEventListener('change', () => {
                    filterForm.submit();
                });
            }
        });
        
        // Price range filter
        if (priceRange) {
            const priceLabel = document.getElementById('priceLabel');
            priceRange.addEventListener('input', function() {
                priceLabel.textContent = LGN.formatCurrency(this.value);
            });
            
            priceRange.addEventListener('change', LGN.debounce(() => {
                filterForm.submit();
            }, 500));
        }
    }
    
    // =====================================================
    // COURSE SEARCH
    // =====================================================
    const searchInput = document.getElementById('courseSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', LGN.debounce(function() {
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchCourses(query);
            } else if (query.length === 0) {
                // Reset to show all
                loadCourses();
            }
        }, 300));
    }
    
    async function searchCourses(query) {
        const coursesGrid = document.getElementById('coursesGrid');
        if (!coursesGrid) return;
        
        try {
            coursesGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            const response = await LGN.API.get(`/api/courses/list.php?search=${encodeURIComponent(query)}`);
            
            if (response.success) {
                renderCourses(response.data, coursesGrid);
            }
        } catch (error) {
            coursesGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <p class="text-danger">Gagal memuat kursus</p>
                </div>
            `;
        }
    }
    
    async function loadCourses(page = 1) {
        const coursesGrid = document.getElementById('coursesGrid');
        if (!coursesGrid) return;
        
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', page);
        
        try {
            const response = await LGN.API.get(`/api/courses/list.php?${urlParams.toString()}`);
            
            if (response.success) {
                renderCourses(response.data, coursesGrid);
                renderPagination(response.pagination);
            }
        } catch (error) {
            console.error('Error loading courses:', error);
        }
    }
    
    function renderCourses(courses, container) {
        if (courses.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">Tidak ada kursus ditemukan</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = courses.map(course => `
            <div class="col-lg-4 col-md-6">
                <div class="card course-card h-100">
                    <div class="card-img-wrapper">
                        <img src="${course.thumbnail || '/assets/images/course-placeholder.jpg'}" 
                             class="card-img-top" alt="${course.title}">
                        <span class="card-badge badge badge-level ${course.level}">${course.level}</span>
                        <div class="card-wishlist" data-course-id="${course.id}">
                            <i class="bi bi-heart"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <span class="course-category">${course.category_name}</span>
                        <h5 class="course-title">
                            <a href="/pages/course_detail.php?slug=${course.slug}">${course.title}</a>
                        </h5>
                        <div class="tutor-info">
                            <img src="${course.tutor_avatar || '/assets/images/default-avatar.png'}" 
                                 alt="${course.tutor_name}" class="tutor-avatar">
                            <span class="tutor-name">${course.tutor_name}</span>
                        </div>
                        <div class="course-meta">
                            <span class="course-rating">
                                <span class="stars"><i class="bi bi-star-fill"></i></span>
                                <span>${course.avg_rating || '0'}</span>
                                <span class="text-muted">(${course.review_count || 0})</span>
                            </span>
                            <span><i class="bi bi-people"></i> ${course.enrollment_count || 0}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div>
                            ${course.discount_price ? `
                                <span class="course-price">${LGN.formatCurrency(course.discount_price)}</span>
                                <span class="course-price-original">${LGN.formatCurrency(course.price)}</span>
                            ` : `
                                <span class="course-price ${course.price == 0 ? 'free' : ''}">${course.price == 0 ? 'Gratis' : LGN.formatCurrency(course.price)}</span>
                            `}
                        </div>
                        <a href="/pages/course_detail.php?slug=${course.slug}" class="btn btn-sm btn-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function renderPagination(pagination) {
        const container = document.getElementById('coursePagination');
        if (!container || pagination.total_pages <= 1) {
            if (container) container.innerHTML = '';
            return;
        }
        
        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        html += `
            <li class="page-item ${pagination.page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="?page=${pagination.page - 1}" data-page="${pagination.page - 1}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (
                i === 1 ||
                i === pagination.total_pages ||
                (i >= pagination.page - 2 && i <= pagination.page + 2)
            ) {
                html += `
                    <li class="page-item ${i === pagination.page ? 'active' : ''}">
                        <a class="page-link" href="?page=${i}" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next button
        html += `
            <li class="page-item ${pagination.page === pagination.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="?page=${pagination.page + 1}" data-page="${pagination.page + 1}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        html += '</ul></nav>';
        container.innerHTML = html;
    }
    
    // =====================================================
    // WISHLIST TOGGLE
    // =====================================================
    document.addEventListener('click', async function(e) {
        const wishlistBtn = e.target.closest('.card-wishlist');
        if (!wishlistBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const courseId = wishlistBtn.dataset.courseId;
        const icon = wishlistBtn.querySelector('i');
        const isWishlisted = icon.classList.contains('bi-heart-fill');
        
        try {
            const response = await LGN.API.post('/api/courses/wishlist.php', {
                course_id: courseId,
                action: isWishlisted ? 'remove' : 'add'
            });
            
            if (response.success) {
                if (isWishlisted) {
                    icon.classList.remove('bi-heart-fill', 'text-danger');
                    icon.classList.add('bi-heart');
                    LGN.Toast.success('Dihapus dari wishlist');
                } else {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill', 'text-danger');
                    LGN.Toast.success('Ditambahkan ke wishlist');
                }
            }
        } catch (error) {
            if (error.message.includes('login')) {
                LGN.Toast.warning('Silakan login untuk menambahkan ke wishlist');
            } else {
                LGN.Toast.error(error.message);
            }
        }
    });
    
    // =====================================================
    // LOAD MORE COURSES
    // =====================================================
    const loadMoreBtn = document.getElementById('loadMoreCourses');
    if (loadMoreBtn) {
        let currentPage = 1;
        
        loadMoreBtn.addEventListener('click', async function() {
            currentPage++;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memuat...';
            
            try {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('page', currentPage);
                
                const response = await LGN.API.get(`/api/courses/list.php?${urlParams.toString()}`);
                
                if (response.success && response.data.length > 0) {
                    const coursesGrid = document.getElementById('coursesGrid');
                    const tempContainer = document.createElement('div');
                    tempContainer.innerHTML = '';
                    
                    response.data.forEach(course => {
                        coursesGrid.insertAdjacentHTML('beforeend', createCourseCard(course));
                    });
                    
                    if (currentPage >= response.pagination.total_pages) {
                        this.style.display = 'none';
                    }
                } else {
                    this.style.display = 'none';
                }
            } catch (error) {
                currentPage--;
                LGN.Toast.error('Gagal memuat kursus');
            } finally {
                this.disabled = false;
                this.innerHTML = 'Muat Lebih Banyak';
            }
        });
    }
    
    function createCourseCard(course) {
        return `
            <div class="col-lg-4 col-md-6">
                <div class="card course-card h-100">
                    <div class="card-img-wrapper">
                        <img src="${course.thumbnail || '/assets/images/course-placeholder.jpg'}" 
                             class="card-img-top" alt="${course.title}">
                        <span class="card-badge badge badge-level ${course.level}">${course.level}</span>
                    </div>
                    <div class="card-body">
                        <span class="course-category">${course.category_name}</span>
                        <h5 class="course-title">
                            <a href="/pages/course_detail.php?slug=${course.slug}">${course.title}</a>
                        </h5>
                        <div class="course-meta">
                            <span class="course-rating">
                                <i class="bi bi-star-fill text-warning"></i>
                                ${course.avg_rating || '0'} (${course.review_count || 0})
                            </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="course-price">${course.price == 0 ? 'Gratis' : LGN.formatCurrency(course.discount_price || course.price)}</span>
                    </div>
                </div>
            </div>
        `;
    }
});