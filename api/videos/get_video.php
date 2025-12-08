<?php
// api/videos/get_video.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/google_drive.php';

$lessonId = intval($_GET['lesson_id'] ?? 0);

if (!$lessonId) {
    jsonResponse(['success' => false, 'message' => 'ID lesson diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get lesson with course info
    $sql = "
        SELECT 
            l.*,
            cs.course_id,
            cs.title as section_title,
            c.title as course_title,
            c.tutor_id
        FROM lessons l
        JOIN course_sections cs ON l.section_id = cs.id
        JOIN courses c ON cs.course_id = c.id
        WHERE l.id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        jsonResponse(['success' => false, 'message' => 'Lesson tidak ditemukan'], 404);
    }
    
    // Check access
    $hasAccess = false;
    
    // Preview lessons are public
    if ($lesson['is_preview']) {
        $hasAccess = true;
    }
    // Check if user is enrolled
    elseif (isLoggedIn()) {
        if (isEnrolled($_SESSION['user_id'], $lesson['course_id'])) {
            $hasAccess = true;
        }
        // Course owner/admin always has access
        if (hasRole('admin') || $_SESSION['user_id'] == $lesson['tutor_id']) {
            $hasAccess = true;
        }
    }
    
    if (!$hasAccess) {
        jsonResponse([
            'success' => false, 
            'message' => 'Anda tidak memiliki akses ke video ini. Silakan beli kursus terlebih dahulu.',
            'require_purchase' => true
        ], 403);
    }
    
    // Get video URL from Google Drive
    $googleDrive = new GoogleDriveAPI();
    $embedUrl = $googleDrive->getEmbedUrl($lesson['google_drive_file_id']);
    
    // Get user progress if logged in
    $progress = null;
    if (isLoggedIn()) {
        $stmt = $db->prepare("
            SELECT * FROM lesson_progress 
            WHERE user_id = ? AND lesson_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $lessonId]);
        $progress = $stmt->fetch();
        
        // Create progress record if not exists
        if (!$progress) {
            $stmt = $db->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id) VALUES (?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $lessonId]);
            $progress = [
                'is_completed' => false,
                'watch_time_seconds' => 0,
                'last_position_seconds' => 0
            ];
        }
        
        // Update last accessed
        $stmt = $db->prepare("
            UPDATE enrollments SET last_accessed_at = NOW() 
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $lesson['course_id']]);
    }
    
    // Get next/previous lesson
    $navSql = "
        SELECT id, title FROM lessons 
        WHERE section_id = ? AND order_number = ?
    ";
    
    $stmt = $db->prepare($navSql);
    $stmt->execute([$lesson['section_id'], $lesson['order_number'] + 1]);
    $nextLesson = $stmt->fetch();
    
    $stmt = $db->prepare($navSql);
    $stmt->execute([$lesson['section_id'], $lesson['order_number'] - 1]);
    $prevLesson = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'data' => [
            'lesson' => [
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'description' => $lesson['description'],
                'duration_minutes' => $lesson['duration_minutes'],
                'content_type' => $lesson['content_type'],
                'resources' => $lesson['resources'],
                'section_title' => $lesson['section_title'],
                'course_title' => $lesson['course_title']
            ],
            'video_url' => $embedUrl,
            'progress' => $progress,
            'navigation' => [
                'next' => $nextLesson,
                'previous' => $prevLesson
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Get video error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}