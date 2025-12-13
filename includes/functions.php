<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// =====================================================
// DATABASE FUNCTIONS
// =====================================================

function getDB() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}

// =====================================================
// AUTHENTICATION FUNCTIONS
// =====================================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    
    return $_SESSION['user_role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
        }
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'message' => 'Akses tidak diizinkan'], 403);
        }
        header('Location: /index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, role, avatar, bio, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, role, avatar, bio, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// =====================================================
// INPUT HANDLING
// =====================================================

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function getInput($key, $default = null) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input[$key])) {
        return sanitize($input[$key]);
    }
    
    if (isset($_POST[$key])) {
        return sanitize($_POST[$key]);
    }
    
    if (isset($_GET[$key])) {
        return sanitize($_GET[$key]);
    }
    
    return $default;
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// =====================================================
// RESPONSE FUNCTIONS
// =====================================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// =====================================================
// STRING HELPERS
// =====================================================

function generateSlug($text, $table = null, $column = 'slug') {
    // Convert to lowercase and replace spaces with dashes
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Ensure unique slug if table is provided
    if ($table) {
        $db = getDB();
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$slug]);
            
            if ($stmt->fetchColumn() == 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }
    
    return $slug;
}

function generateOrderId() {
    return 'LGN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
}

function generateCertificateNumber() {
    return 'CERT-LGN-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 10));
}

// =====================================================
// FORMATTING FUNCTIONS
// =====================================================

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date, $format = 'd M Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function formatDateTime($date) {
    if (!$date) return '-';
    return date('d M Y H:i', strtotime($date));
}

function formatDuration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' menit';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($mins == 0) {
        return $hours . ' jam';
    }
    
    return $hours . ' jam ' . $mins . ' menit';
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'jt';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'rb';
    }
    return number_format($number);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam lalu';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' hari lalu';
    } elseif ($diff < 2592000) {
        return floor($diff / 604800) . ' minggu lalu';
    } else {
        return formatDate($datetime);
    }
}

// =====================================================
// COURSE FUNCTIONS
// =====================================================

function isEnrolled($userId, $courseId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch() !== false;
}

function getCourseProgress($userId, $courseId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT progress_percentage FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $result = $stmt->fetch();
    return $result ? floatval($result['progress_percentage']) : 0;
}

function updateCourseProgress($userId, $courseId) {
    $db = getDB();
    
    // Get total lessons
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM lessons l 
        JOIN course_sections cs ON l.section_id = cs.id 
        WHERE cs.course_id = ?
    ");
    $stmt->execute([$courseId]);
    $total = $stmt->fetch()['total'];
    
    if ($total == 0) return 0;
    
    // Get completed lessons
    $stmt = $db->prepare("
        SELECT COUNT(*) as completed 
        FROM lesson_progress lp 
        JOIN lessons l ON lp.lesson_id = l.id 
        JOIN course_sections cs ON l.section_id = cs.id 
        WHERE lp.user_id = ? AND cs.course_id = ? AND lp.is_completed = 1
    ");
    $stmt->execute([$userId, $courseId]);
    $completed = $stmt->fetch()['completed'];
    
    $progress = round(($completed / $total) * 100, 2);
    
    // Update enrollment
    $stmt = $db->prepare("
        UPDATE enrollments 
        SET progress_percentage = ?,
            completed_at = CASE WHEN ? >= 100 THEN NOW() ELSE completed_at END,
            last_accessed_at = NOW()
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->execute([$progress, $progress, $userId, $courseId]);
    
    // Issue certificate if completed
    if ($progress >= 100) {
        issueCertificate($userId, $courseId);
    }
    
    return $progress;
}

function issueCertificate($userId, $courseId) {
    $db = getDB();
    
    // Check if certificate already exists
    $stmt = $db->prepare("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    
    if ($stmt->fetch()) {
        return false; // Already issued
    }
    
    // Get enrollment ID
    $stmt = $db->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) return false;
    
    // Create certificate
    $certificateNumber = generateCertificateNumber();
    
    $stmt = $db->prepare("
        INSERT INTO certificates (user_id, course_id, enrollment_id, certificate_number) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $courseId, $enrollment['id'], $certificateNumber]);
    
    // Update enrollment
    $stmt = $db->prepare("UPDATE enrollments SET certificate_issued = 1 WHERE id = ?");
    $stmt->execute([$enrollment['id']]);
    
    return $certificateNumber;
}

function getCourseRating($courseId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as total_reviews
        FROM reviews 
        WHERE course_id = ? AND is_approved = 1
    ");
    $stmt->execute([$courseId]);
    $result = $stmt->fetch();
    
    return [
        'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'count' => (int) $result['total_reviews']
    ];
}

function getEnrollmentCount($courseId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

// =====================================================
// FILE UPLOAD
// =====================================================

function uploadFile($file, $directory = 'uploads', $allowedTypes = null, $maxSize = 5242880) {
    if ($allowedTypes === null) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }
    
    $targetDir = __DIR__ . '/../assets/' . $directory . '/';
    
    // Create directory if not exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max: ' . ($maxSize / 1048576) . 'MB)'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => 'assets/' . $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal mengupload file'];
}

function deleteFile($path) {
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

// =====================================================
// NOTIFICATION FUNCTIONS
// =====================================================

function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, title, message, type, link) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $title, $message, $type, $link]);
}

function getUnreadNotificationCount($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

// =====================================================
// RATING STARS HTML
// =====================================================

function renderStars($rating, $showNumber = true) {
    $html = '<div class="rating-stars d-inline-flex align-items-center">';
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($i == $fullStars + 1 && $hasHalf) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    
    if ($showNumber) {
        $html .= '<span class="ms-1 text-muted">(' . number_format($rating, 1) . ')</span>';
    }
    
    $html .= '</div>';
    return $html;
}

// =====================================================
// SETTINGS
// =====================================================

function getSetting($key, $default = null) {
    $db = getDB();
    $stmt = $db->prepare("SELECT value FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['value'] : $default;
}

function setSetting($key, $value) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO settings (key_name, value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");
    return $stmt->execute([$key, $value]);

}
/**
 * Get course thumbnail URL with consistent fallback
 */
function getCourseImage($course) {
    $thumbnail = is_array($course) ? ($course['thumbnail'] ?? '') : $course;
    $title = is_array($course) ? ($course['title'] ?? 'Course') : 'Course';
    
    // If thumbnail path exists and not empty
    if (!empty($thumbnail)) {
        // Remove leading slash
        $cleanPath = ltrim($thumbnail, '/');
        
        // Check if it's external URL
        if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
            return $thumbnail;
        }
        
        // Check if file exists on server
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $cleanPath;
        if (file_exists($fullPath)) {
            return '/' . $cleanPath;
        }
    }
    
    // Fallback to placeholder with course title
    $placeholderText = urlencode(substr($title, 0, 30));
    return "https://via.placeholder.com/750x422/4f46e5/ffffff?text={$placeholderText}";
}

/**
 * Get user avatar URL with consistent fallback
 */
function getUserAvatar($user, $size = 128) {
    $avatar = is_array($user) ? ($user['avatar'] ?? $user['tutor_avatar'] ?? '') : $user;
    $name = is_array($user) ? ($user['name'] ?? $user['tutor_name'] ?? 'User') : 'User';
    
    if (!empty($avatar)) {
        $cleanPath = ltrim($avatar, '/');
        
        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }
        
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $cleanPath;
        if (file_exists($fullPath)) {
            return '/' . $cleanPath;
        }
    }
    
    $initials = urlencode($name);
    return "https://ui-avatars.com/api/?name={$initials}&background=4f46e5&color=fff&size={$size}";
}

/**
 * Get category icon with fallback
 * @param array $category Category data
 * @return string Icon class
 */
function getCategoryIcon($category) {
    return $category['icon'] ?? 'bi-folder';
}

/**
 * Get category color with fallback
 * @param array $category Category data
 * @return string Color hex code
 */
function getCategoryColor($category) {
    return $category['color'] ?? '#4f46e5';
}

