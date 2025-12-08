<?php
// api/videos/update_progress.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$lessonId = intval($data['lesson_id'] ?? 0);
$watchTimeSeconds = intval($data['watch_time_seconds'] ?? 0);
$lastPositionSeconds = intval($data['last_position_seconds'] ?? 0);
$isCompleted = isset($data['is_completed']) && $data['is_completed'] === true;

if (!$lessonId) {
    jsonResponse(['success' => false, 'message' => 'ID lesson diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get lesson and course info
    $stmt = $db->prepare("
        SELECT l.*, cs.course_id 
        FROM lessons l
        JOIN course_sections cs ON l.section_id = cs.id
        WHERE l.id = ?
    ");
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        jsonResponse(['success' => false, 'message' => 'Lesson tidak ditemukan'], 404);
    }
    
    // Check enrollment
    if (!isEnrolled($_SESSION['user_id'], $lesson['course_id'])) {
        jsonResponse(['success' => false, 'message' => 'Anda tidak terdaftar di kursus ini'], 403);
    }
    
    // Update or insert progress
    $stmt = $db->prepare("
        INSERT INTO lesson_progress 
            (user_id, lesson_id, watch_time_seconds, last_position_seconds, is_completed, completed_at, updated_at)
        VALUES 
            (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            watch_time_seconds = GREATEST(watch_time_seconds, VALUES(watch_time_seconds)),
            last_position_seconds = VALUES(last_position_seconds),
            is_completed = CASE 
                WHEN is_completed = 1 THEN 1 
                ELSE VALUES(is_completed) 
            END,
            completed_at = CASE 
                WHEN is_completed = 0 AND VALUES(is_completed) = 1 THEN NOW()
                ELSE completed_at
            END,
            updated_at = NOW()
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $lessonId,
        $watchTimeSeconds,
        $lastPositionSeconds,
        $isCompleted ? 1 : 0,
        $isCompleted ? date('Y-m-d H:i:s') : null
    ]);
    
    // Update course progress
    $courseProgress = updateCourseProgress($_SESSION['user_id'], $lesson['course_id']);
    
    $response = [
        'success' => true,
        'message' => 'Progress disimpan',
        'course_progress' => $courseProgress
    ];
    
    // Check if course completed
    if ($courseProgress >= 100) {
        $response['course_completed'] = true;
        $response['message'] = 'Selamat! Anda telah menyelesaikan kursus ini!';
    }
    
    jsonResponse($response);
    
} catch (PDOException $e) {
    error_log("Update progress error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}