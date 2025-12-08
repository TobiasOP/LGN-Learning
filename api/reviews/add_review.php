<?php
// api/reviews/add_review.php

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
$rating = intval($data['rating'] ?? 0);
$comment = sanitize($data['comment'] ?? '');

if (!$courseId) {
    jsonResponse(['success' => false, 'message' => 'ID kursus diperlukan'], 400);
}

if ($rating < 1 || $rating > 5) {
    jsonResponse(['success' => false, 'message' => 'Rating harus antara 1-5'], 400);
}

try {
    $db = getDB();
    
    // Check enrollment
    if (!isEnrolled($_SESSION['user_id'], $courseId)) {
        jsonResponse(['success' => false, 'message' => 'Anda harus terdaftar di kursus ini untuk memberikan review'], 403);
    }
    
    // Check existing review
    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $existingReview = $stmt->fetch();
    
    if ($existingReview) {
        // Update existing review
        $stmt = $db->prepare("UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$rating, $comment, $existingReview['id']]);
        $message = 'Review berhasil diperbarui';
    } else {
        // Create new review
        $stmt = $db->prepare("INSERT INTO reviews (user_id, course_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $courseId, $rating, $comment]);
        $message = 'Terima kasih atas review Anda!';
    }
    
    jsonResponse([
        'success' => true,
        'message' => $message
    ]);
    
} catch (PDOException $e) {
    error_log("Add review error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}