<?php
// api/payment/check_status.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/midtrans.php';

$orderId = sanitize($_GET['order_id'] ?? '');

if (empty($orderId)) {
    jsonResponse(['success' => false, 'message' => 'Order ID diperlukan'], 400);
}

try {
    $db = getDB();
    
    // Get transaction from database
    $stmt = $db->prepare("
        SELECT t.*, c.title as course_title, c.slug as course_slug
        FROM transactions t
        JOIN courses c ON t.course_id = c.id
        WHERE t.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        jsonResponse(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
    }
    
    // Check ownership
    if (isLoggedIn() && $transaction['user_id'] != $_SESSION['user_id'] && !hasRole('admin')) {
        jsonResponse(['success' => false, 'message' => 'Akses ditolak'], 403);
    }
    
    // If pending, check with Midtrans
    if ($transaction['transaction_status'] === 'pending') {
        $midtransStatus = MidtransConfig::getTransactionStatus($orderId);
        
        if ($midtransStatus['http_code'] === 200) {
            $mtData = $midtransStatus['data'];
            $newStatus = $transaction['transaction_status'];
            
            if (in_array($mtData['transaction_status'], ['settlement', 'capture'])) {
                $newStatus = 'success';
            } elseif ($mtData['transaction_status'] === 'expire') {
                $newStatus = 'expired';
            } elseif (in_array($mtData['transaction_status'], ['deny', 'cancel'])) {
                $newStatus = 'cancel';
            }
            
            if ($newStatus !== $transaction['transaction_status']) {
                $stmt = $db->prepare("UPDATE transactions SET transaction_status = ?, updated_at = NOW() WHERE order_id = ?");
                $stmt->execute([$newStatus, $orderId]);
                $transaction['transaction_status'] = $newStatus;
                
                // Enroll if successful
                if ($newStatus === 'success') {
                    if (!isEnrolled($transaction['user_id'], $transaction['course_id'])) {
                        $stmt = $db->prepare("INSERT INTO enrollments (user_id, course_id, transaction_id, enrolled_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$transaction['user_id'], $transaction['course_id'], $transaction['id']]);
                    }
                }
            }
        }
    }
    
    jsonResponse([
        'success' => true,
        'data' => [
            'order_id' => $transaction['order_id'],
            'status' => $transaction['transaction_status'],
            'amount' => floatval($transaction['final_amount']),
            'payment_type' => $transaction['payment_type'],
            'course_title' => $transaction['course_title'],
            'course_slug' => $transaction['course_slug'],
            'created_at' => $transaction['created_at'],
            'paid_at' => $transaction['paid_at']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Check status error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}