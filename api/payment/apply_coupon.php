<?php
// api/payment/apply_coupon.php

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
$couponCode = strtoupper(sanitize($data['coupon_code'] ?? ''));

if (!$courseId || empty($couponCode)) {
    jsonResponse(['success' => false, 'message' => 'Data tidak lengkap'], 400);
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
    
    $originalPrice = $course['discount_price'] ?: $course['price'];
    
    // Get coupon
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = 1
        AND (start_date IS NULL OR start_date <= NOW())
        AND (end_date IS NULL OR end_date >= NOW())
        AND (usage_limit IS NULL OR used_count < usage_limit)
        AND (course_id IS NULL OR course_id = ?)
    ");
    $stmt->execute([$couponCode, $courseId]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        jsonResponse(['success' => false, 'message' => 'Kode kupon tidak valid atau sudah kadaluarsa'], 400);
    }
    
    // Check minimum purchase
    if ($originalPrice < $coupon['min_purchase']) {
        jsonResponse([
            'success' => false, 
            'message' => 'Minimal pembelian ' . formatCurrency($coupon['min_purchase']) . ' untuk menggunakan kupon ini'
        ], 400);
    }
    
    // Calculate discount
    if ($coupon['discount_type'] === 'percentage') {
        $discountAmount = ($originalPrice * $coupon['discount_value']) / 100;
        if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
            $discountAmount = $coupon['max_discount'];
        }
    } else {
        $discountAmount = $coupon['discount_value'];
    }
    
    $finalPrice = max(0, $originalPrice - $discountAmount);
    
    jsonResponse([
        'success' => true,
        'message' => 'Kupon berhasil diterapkan',
        'data' => [
            'coupon_code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => floatval($coupon['discount_value']),
            'discount' => floatval($discountAmount),
            'original_price' => floatval($originalPrice),
            'final_price' => floatval($finalPrice)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Apply coupon error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}