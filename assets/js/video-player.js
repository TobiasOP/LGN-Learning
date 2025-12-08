// assets/js/video-player.js

/**
 * LGN E-Learning - Video Player JavaScript
 */

const VideoPlayer = {
    currentLessonId: null,
    watchInterval: null,
    watchTime: 0,
    
    /**
     * Initialize video player
     */
    init(lessonId) {
        this.currentLessonId = lessonId;
        this.watchTime = 0;
        this.startWatchTimer();
        this.setupEventListeners();
    },
    
    /**
     * Load lesson video
     */
    async loadLesson(lessonId) {
        try {
            LGN.Loading.show('Memuat video...');
            
            const response = await LGN.API.get(`/api/videos/get_video.php?lesson_id=${lessonId}`);
            
            LGN.Loading.hide();
            
            if (response.success) {
                this.currentLessonId = lessonId;
                this.displayVideo(response.data);
                this.updateLessonUI(lessonId);
                this.watchTime = response.data.progress?.last_position_seconds || 0;
                this.startWatchTimer();
            } else {
                LGN.Toast.error(response.message || 'Gagal memuat video');
            }
        } catch (error) {
            LGN.Loading.hide();
            LGN.Toast.error(error.message || 'Terjadi kesalahan');
        }
    },
    
    /**
     * Display video in player
     */
    displayVideo(data) {
        const container = document.getElementById('videoContainer');
        if (!container) return;
        
        container.innerHTML = `
            <div class="video-player-container">
                <iframe 
                    src="${data.video_url}"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                ></iframe>
            </div>
        `;
        
        // Update lesson title
        const titleEl = document.getElementById('currentLessonTitle');
        if (titleEl) {
            titleEl.textContent = data.lesson.title;
        }
        
        // Update lesson description
        const descEl = document.getElementById('lessonDescription');
        if (descEl) {
            descEl.innerHTML = data.lesson.description || '';
        }
    },
    
    /**
     * Update lesson UI (sidebar)
     */
    updateLessonUI(lessonId) {
        // Remove active class from all lessons
        document.querySelectorAll('.lesson-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to current lesson
        const currentLesson = document.querySelector(`.lesson-item[data-lesson-id="${lessonId}"]`);
        if (currentLesson) {
            currentLesson.classList.add('active');
            
            // Scroll into view
            currentLesson.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    },
    
    /**
     * Start watch timer
     */
    startWatchTimer() {
        this.stopWatchTimer();
        
        this.watchInterval = setInterval(() => {
            this.watchTime++;
            
            // Save progress every 30 seconds
            if (this.watchTime % 30 === 0) {
                this.saveProgress(false);
            }
        }, 1000);
    },
    
    /**
     * Stop watch timer
     */
    stopWatchTimer() {
        if (this.watchInterval) {
            clearInterval(this.watchInterval);
            this.watchInterval = null;
        }
    },
    
    /**
     * Save progress to server
     */
    async saveProgress(isCompleted = false) {
        if (!this.currentLessonId) return;
        
        try {
            await LGN.API.post('/api/videos/update_progress.php', {
                lesson_id: this.currentLessonId,
                watch_time_seconds: this.watchTime,
                is_completed: isCompleted
            });
            
            if (isCompleted) {
                this.markLessonComplete(this.currentLessonId);
            }
        } catch (error) {
            console.error('Error saving progress:', error);
        }
    },
    
    /**
     * Mark lesson as complete
     */
    async markComplete() {
        if (!this.currentLessonId) return;
        
        try {
            const response = await LGN.API.post('/api/videos/update_progress.php', {
                lesson_id: this.currentLessonId,
                watch_time_seconds: this.watchTime,
                is_completed: true
            });
            
            if (response.success) {
                this.markLessonComplete(this.currentLessonId);
                LGN.Toast.success('Lesson selesai!');
                
                // Update course progress
                if (response.course_progress !== undefined) {
                    this.updateCourseProgress(response.course_progress);
                }
                
                // Auto-load next lesson
                this.loadNextLesson();
            }
        } catch (error) {
            LGN.Toast.error('Gagal menyimpan progress');
        }
    },
    
    /**
     * Mark lesson as complete in UI
     */
    markLessonComplete(lessonId) {
        const lessonItem = document.querySelector(`.lesson-item[data-lesson-id="${lessonId}"]`);
        if (lessonItem) {
            lessonItem.classList.add('completed');
            const checkbox = lessonItem.querySelector('.lesson-checkbox');
            if (checkbox) {
                checkbox.innerHTML = '<i class="bi bi-check"></i>';
            }
        }
    },
    
    /**
     * Update course progress display
     */
    updateCourseProgress(progress) {
        const progressBar = document.querySelector('.course-progress-bar');
        const progressText = document.querySelector('.course-progress-text');
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        if (progressText) {
            progressText.textContent = Math.round(progress) + '% selesai';
        }
        
        // Check if course completed
        if (progress >= 100) {
            this.showCertificateModal();
        }
    },
    
    /**
     * Load next lesson
     */
    loadNextLesson() {
        const currentLesson = document.querySelector('.lesson-item.active');
        if (!currentLesson) return;
        
        const nextLesson = currentLesson.nextElementSibling;
        if (nextLesson && nextLesson.classList.contains('lesson-item')) {
            const nextLessonId = nextLesson.dataset.lessonId;
            this.loadLesson(nextLessonId);
        } else {
            // Check next section
            const currentSection = currentLesson.closest('.section-content');
            const nextSection = currentSection?.parentElement?.nextElementSibling;
            
            if (nextSection) {
                const firstLesson = nextSection.querySelector('.lesson-item');
                if (firstLesson) {
                    this.loadLesson(firstLesson.dataset.lessonId);
                }
            }
        }
    },
    
    /**
     * Load previous lesson
     */
    loadPreviousLesson() {
        const currentLesson = document.querySelector('.lesson-item.active');
        if (!currentLesson) return;
        
        const prevLesson = currentLesson.previousElementSibling;
        if (prevLesson && prevLesson.classList.contains('lesson-item')) {
            const prevLessonId = prevLesson.dataset.lessonId;
            this.loadLesson(prevLessonId);
        }
    },
    
    /**
     * Show certificate modal
     */
    showCertificateModal() {
        const modalHtml = `
            <div class="modal fade" id="certificateModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-trophy-fill text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="mb-3">Selamat! 🎉</h3>
                            <p class="text-muted mb-4">Anda telah menyelesaikan kursus ini. Sertifikat Anda sudah siap!</p>
                            <a href="/pages/certificate.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-award me-2"></i>Lihat Sertifikat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('certificateModal'));
        modal.show();
        
        document.getElementById('certificateModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    },
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Lesson item click
        document.querySelectorAll('.lesson-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const lessonId = item.dataset.lessonId;
                const isLocked = item.classList.contains('locked');
                
                if (isLocked) {
                    LGN.Toast.warning('Selesaikan lesson sebelumnya terlebih dahulu');
                    return;
                }
                
                // Save current progress before switching
                if (this.currentLessonId) {
                    this.saveProgress(false);
                }
                
                this.loadLesson(lessonId);
            });
        });
        
        // Complete button
        const completeBtn = document.getElementById('markCompleteBtn');
        if (completeBtn) {
            completeBtn.addEventListener('click', () => {
                this.markComplete();
            });
        }
        
        // Previous button
        const prevBtn = document.getElementById('prevLessonBtn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.loadPreviousLesson();
            });
        }
        
        // Next button
        const nextBtn = document.getElementById('nextLessonBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.loadNextLesson();
            });
        }
        
        // Section toggle
        document.querySelectorAll('.section-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const icon = header.querySelector('.section-toggle');
                
                header.classList.toggle('collapsed');
                
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                } else {
                    content.style.display = 'none';
                }
            });
        });
        
        // Save progress before leaving page
        window.addEventListener('beforeunload', () => {
            this.saveProgress(false);
            this.stopWatchTimer();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            switch(e.key) {
                case 'ArrowRight':
                    if (e.shiftKey) this.loadNextLesson();
                    break;
                case 'ArrowLeft':
                    if (e.shiftKey) this.loadPreviousLesson();
                    break;
                case 'c':
                case 'C':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        this.markComplete();
                    }
                    break;
            }
        });
    }
};

// Export
window.VideoPlayer = VideoPlayer;