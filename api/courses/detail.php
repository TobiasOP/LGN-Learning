<?php
// api/courses/detail.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
$id = intval($_GET['id'] ?? 0);

if (empty($slug) && $id === 0) {
    jsonResponse(['success' => false, 'message' => 'Slug atau ID kursus diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get course
    if ($slug) {
        $whereClause = "c.slug = ?";
        $param = $slug;
    } else {
        $whereClause = "c.id = ?";
        $param = $id;
    }
    
    $sql = "
        SELECT 
            c.*,
            cat.name as category_name,
            cat.slug as category_slug,
            u.id as tutor_id,
            u.name as tutor_name,
            u.avatar as tutor_avatar,
            u.bio as tutor_bio,
            (SELECT COUNT(*) FROM courses WHERE tutor_id = u.id AND is_published = 1) as tutor_courses,
            (SELECT COUNT(*) FROM enrollments e JOIN courses co ON e.course_id = co.id WHERE co.tutor_id = u.id) as tutor_students,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
            (SELECT AVG(rating) FROM reviews WHERE course_id = c.id AND is_approved = 1) as avg_rating,
            (SELECT COUNT(*) FROM reviews WHERE course_id = c.id AND is_approved = 1) as review_count
        FROM courses c
        JOIN categories cat ON c.category_id = cat.id
        JOIN users u ON c.tutor_id = u.id
        WHERE $whereClause
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$param]);
    $course = $stmt->fetch();
    
    if (!$course) {
        jsonResponse(['success' => false, 'message' => 'Kursus tidak ditemukan'], 404);
    }
    
    // Check if published (unless admin/tutor owner)
    if (!$course['is_published']) {
        if (!isLoggedIn() || 
            (!hasRole('admin') && $_SESSION['user_id'] != $course['tutor_id'])) {
            jsonResponse(['success' => false, 'message' => 'Kursus tidak ditemukan'], 404);
        }
    }
    
    // Parse JSON fields
    $course['what_you_learn'] = $course['what_you_learn'] ? explode('|', $course['what_you_learn']) : [];
    $course['requirements'] = $course['requirements'] ? explode('|', $course['requirements']) : [];
    
    // Get sections with lessons
    $sectionsSql = "
        SELECT * FROM course_sections 
        WHERE course_id = ? 
        ORDER BY order_number
    ";
    $stmt = $db->prepare($sectionsSql);
    $stmt->execute([$course['id']]);
    $sections = $stmt->fetchAll();
    
    $totalDuration = 0;
    $totalLessons = 0;
    
    foreach ($sections as &$section) {
        $lessonsSql = "
            SELECT id, title, description, content_type, duration_minutes, is_preview, order_number 
            FROM lessons 
            WHERE section_id = ? 
            ORDER BY order_number
        ";
        $stmt = $db->prepare($lessonsSql);
        $stmt->execute([$section['id']]);
        $section['lessons'] = $stmt->fetchAll();
        
        foreach ($section['lessons'] as $lesson) {
            $totalDuration += $lesson['duration_minutes'];
            $totalLessons++;
        }
    }
    
    $course['sections'] = $sections;
    $course['calculated_duration'] = $totalDuration;
    $course['calculated_lessons'] = $totalLessons;
    
    // Get reviews
    $reviewsSql = "
        SELECT r.*, u.name as user_name, u.avatar as user_avatar
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.course_id = ? AND r.is_approved = 1
        ORDER BY r.created_at DESC
        LIMIT 10
    ";
    $stmt = $db->prepare($reviewsSql);
    $stmt->execute([$course['id']]);
    $course['reviews'] = $stmt->fetchAll();
    
    // Rating breakdown
    $ratingBreakdown = [];
    for ($i = 5; $i >= 1; $i--) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE course_id = ? AND rating = ? AND is_approved = 1");
        $stmt->execute([$course['id'], $i]);
        $ratingBreakdown[$i] = intval($stmt->fetchColumn());
    }
    $course['rating_breakdown'] = $ratingBreakdown;
    
    // Check enrollment status
    $course['is_enrolled'] = false;
    $course['user_progress'] = 0;
    
    if (isLoggedIn()) {
        $course['is_enrolled'] = isEnrolled($_SESSION['user_id'], $course['id']);
        if ($course['is_enrolled']) {
            $course['user_progress'] = getCourseProgress($_SESSION['user_id'], $course['id']);
        }
    }
    
    // Format numeric values
    $course['price'] = floatval($course['price']);
    $course['discount_price'] = $course['discount_price'] ? floatval($course['discount_price']) : null;
    $course['avg_rating'] = $course['avg_rating'] ? round(floatval($course['avg_rating']), 1) : 0;
    $course['enrollment_count'] = intval($course['enrollment_count']);
    $course['review_count'] = intval($course['review_count']);
    
    // Calculate discount percentage
    if ($course['discount_price'] && $course['price'] > 0) {
        $course['discount_percentage'] = round((($course['price'] - $course['discount_price']) / $course['price']) * 100);
    }
    
    jsonResponse(['success' => true, 'data' => $course]);
    
} catch (PDOException $e) {
    error_log("Course detail error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}