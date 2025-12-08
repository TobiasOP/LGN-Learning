<?php
// api/payment/create_transaction.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/midtrans.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$courseId = intval($data['course_id'] ?? 0);
$couponCode = sanitize($data['coupon_code'] ?? '');

if (!$courseId) {
    jsonResponse(['success' => false, 'message' => 'ID kursus diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get course
    $stmt = $db->prepare("
        SELECT c.*, u.name as tutor_name 
        FROM courses c
        JOIN users u ON c.tutor_id = u.id
        WHERE c.id = ? AND c.is_published = 1
    ");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        jsonResponse(['success' => false, 'message' => 'Kursus tidak ditemukan'], 404);
    }
    
    // Check if already enrolled
    if (isEnrolled($_SESSION['user_id'], $courseId)) {
        jsonResponse(['success' => false, 'message' => 'Anda sudah terdaftar di kursus ini'], 400);
    }
    
    // Check for existing pending transaction
    $stmt = $db->prepare("
        SELECT * FROM transactions 
        WHERE user_id = ? AND course_id = ? AND transaction_status = 'pending'
        AND expired_at > NOW()
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $pendingTx = $stmt->fetch();
    
    if ($pendingTx && $pendingTx['snap_token']) {
        jsonResponse([
            'success' => true,
            'message' => 'Menggunakan transaksi yang sudah ada',
            'data' => [
                'order_id' => $pendingTx['order_id'],
                'snap_token' => $pendingTx['snap_token'],
                'amount' => floatval($pendingTx['final_amount'])
            ]
        ]);
    }
    
    // Calculate price
    $originalPrice = $course['discount_price'] ?: $course['price'];
    $discountAmount = 0;
    
    // Apply coupon if provided
    if ($couponCode) {
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
        
        if ($coupon) {
            if ($originalPrice >= $coupon['min_purchase']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discountAmount = ($originalPrice * $coupon['discount_value']) / 100;
                    if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
                        $discountAmount = $coupon['max_discount'];
                    }
                } else {
                    $discountAmount = $coupon['discount_value'];
                }
            }
        }
    }
    
    $finalAmount = max(0, $originalPrice - $discountAmount);
    
    // Free course - enroll directly
    if ($finalAmount == 0) {
        $stmt = $db->prepare("
            INSERT INTO enrollments (user_id, course_id, enrolled_at) VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $courseId]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Berhasil mendaftar kursus gratis!',
            'data' => ['free_enrollment' => true]
        ]);
    }
    
    // Get user data
    $user = getCurrentUser();
    
    // Generate order ID
    $orderId = generateOrderId();
    
    // Build Midtrans params
    $params = MidtransConfig::buildTransactionParams(
        $orderId,
        $finalAmount,
        $user,
        $course
    );
    
    // Create Snap token
    $snapResponse = MidtransConfig::createSnapToken($params);
    
    if (!$snapResponse['success']) {
        error_log("Midtrans error: " . json_encode($snapResponse));
        jsonResponse(['success' => false, 'message' => 'Gagal membuat transaksi pembayaran'], 500);
    }
    
    $snapToken = $snapResponse['data']['token'];
    $redirectUrl = $snapResponse['data']['redirect_url'] ?? null;
    
    // Save transaction
    $stmt = $db->prepare("
        INSERT INTO transactions 
        (user_id, course_id, order_id, amount, discount_amount, final_amount, snap_token, snap_redirect_url, expired_at, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $courseId,
        $orderId,
        $originalPrice,
        $discountAmount,
        $finalAmount,
        $snapToken,
        $redirectUrl
    ]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Transaksi berhasil dibuat',
        'data' => [
            'order_id' => $orderId,
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'amount' => $finalAmount
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Create transaction error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}