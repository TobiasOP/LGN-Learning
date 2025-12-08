<?php
// api/courses/enroll_free.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$courseId = intval($data['course_id'] ?? 0);

if (!$courseId) {
    jsonResponse(['success' => false, 'message' => 'ID kursus diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get course
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ? AND is_published = 1");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        jsonResponse(['success' => false, 'message' => 'Kursus tidak ditemukan'], 404);
    }
    
    // Check if free
    $price = $course['discount_price'] ?: $course['price'];
    if ($price > 0) {
        jsonResponse(['success' => false, 'message' => 'Kursus ini tidak gratis'], 400);
    }
    
    // Check if already enrolled
    if (isEnrolled($_SESSION['user_id'], $courseId)) {
        jsonResponse(['success' => false, 'message' => 'Anda sudah terdaftar'], 400);
    }
    
    // Enroll
    $stmt = $db->prepare("INSERT INTO enrollments (user_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    
    // Create notification
    createNotification(
        $_SESSION['user_id'],
        'Pendaftaran Berhasil! 🎉',
        "Anda berhasil mendaftar di kursus \"{$course['title']}\". Selamat belajar!",
        'success',
        '/pages/learn.php?course=' . $courseId
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Berhasil mendaftar kursus'
    ]);
    
} catch (PDOException $e) {
    error_log("Enroll free error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}