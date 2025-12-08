<?php
// api/tutor/delete_section.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/functions.php';

requireRole('tutor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$sectionId = intval($data['section_id'] ?? 0);

if (!$sectionId) {
    jsonResponse(['success' => false, 'message' => 'ID section diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Verify ownership
    $stmt = $db->prepare("
        SELECT cs.*, c.id as course_id
        FROM course_sections cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.id = ? AND c.tutor_id = ?
    ");
    $stmt->execute([$sectionId, $_SESSION['user_id']]);
    $section = $stmt->fetch();
    
    if (!$section) {
        jsonResponse(['success' => false, 'message' => 'Section tidak ditemukan'], 404);
    }
    
    // Delete section (lessons will be deleted via CASCADE)
    $stmt = $db->prepare("DELETE FROM course_sections WHERE id = ?");
    $stmt->execute([$sectionId]);
    
    // Update course totals
    $stmt = $db->prepare("
        UPDATE courses c SET 
            total_lessons = (SELECT COUNT(*) FROM lessons l JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id),
            duration_hours = CEIL((SELECT COALESCE(SUM(duration_minutes), 0) FROM lessons l JOIN course_sections cs ON l.section_id = cs.id WHERE cs.course_id = c.id) / 60)
        WHERE c.id = ?
    ");
    $stmt->execute([$section['course_id']]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Section berhasil dihapus'
    ]);
    
} catch (PDOException $e) {
    error_log("Delete section error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}