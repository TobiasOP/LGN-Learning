<?php
// api/tutor/delete_lesson.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/functions.php';

requireRole('tutor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$lessonId = intval($data['lesson_id'] ?? 0);

if (!$lessonId) {
    jsonResponse(['success' => false, 'message' => 'ID lesson diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Verify ownership
    $stmt = $db->prepare("
        SELECT l.*, cs.course_id 
        FROM lessons l
        JOIN course_sections cs ON l.section_id = cs.id
        JOIN courses c ON cs.course_id = c.id
        WHERE l.id = ? AND c.tutor_id = ?
    ");
    $stmt->execute([$lessonId, $_SESSION['user_id']]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        jsonResponse(['success' => false, 'message' => 'Lesson tidak ditemukan'], 404);
    }
    
    // Delete lesson
    $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->execute([$lessonId]);
    
    // Update course totals
    $stmt = $db->prepare("
        UPDATE courses c SET 
            total_lessons = (SELECT COUNT(*) FROM lessons l JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id),
            duration_hours = CEIL((SELECT COALESCE(SUM(duration_minutes), 0) FROM lessons l JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id) / 60)
        WHERE c.id = ?
    ");
    $stmt->execute([$lesson['course_id']]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Lesson berhasil dihapus'
    ]);
    
} catch (PDOException $e) {
    error_log("Delete lesson error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}