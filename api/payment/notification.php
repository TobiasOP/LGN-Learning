<?php
// api/payment/notification.php
// Midtrans Payment Notification Handler (Webhook)

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/midtrans.php';

// Get notification data
$rawBody = file_get_contents('php://input');
$notification = json_decode($rawBody, true);

if (!$notification) {
    http_response_code(400);
    exit('Invalid notification');
}

// Log notification for debugging
error_log("Midtrans notification: " . $rawBody);

$orderId = $notification['order_id'] ?? '';
$transactionStatus = $notification['transaction_status'] ?? '';
$fraudStatus = $notification['fraud_status'] ?? '';
$paymentType = $notification['payment_type'] ?? '';
$signatureKey = $notification['signature_key'] ?? '';
$statusCode = $notification['status_code'] ?? '';
$grossAmount = $notification['gross_amount'] ?? '';
$transactionId = $notification['transaction_id'] ?? '';

// Verify signature
if (!MidtransConfig::verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
    error_log("Invalid signature for order: $orderId");
    http_response_code(403);
    exit('Invalid signature');
}

try {
    $db = getDB();
    
    // Get transaction
    $stmt = $db->prepare("SELECT * FROM transactions WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        error_log("Transaction not found: $orderId");
        http_response_code(404);
        exit('Transaction not found');
    }
    
    // Determine status
    $newStatus = 'pending';
    $shouldEnroll = false;
    
    if ($transactionStatus == 'capture') {
        if ($fraudStatus == 'accept') {
            $newStatus = 'success';
            $shouldEnroll = true;
        } elseif ($fraudStatus == 'challenge') {
            $newStatus = 'pending';
        }
    } elseif ($transactionStatus == 'settlement') {
        $newStatus = 'success';
        $shouldEnroll = true;
    } elseif ($transactionStatus == 'pending') {
        $newStatus = 'pending';
    } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel') {
        $newStatus = 'cancel';
    } elseif ($transactionStatus == 'expire') {
        $newStatus = 'expired';
    } elseif ($transactionStatus == 'refund') {
        $newStatus = 'refund';
    }
    
    // Update transaction
    $stmt = $db->prepare("
        UPDATE transactions SET
            transaction_status = ?,
            payment_type = ?,
            midtrans_transaction_id = ?,
            midtrans_status_code = ?,
            midtrans_status_message = ?,
            payment_response = ?,
            paid_at = CASE WHEN ? = 'success' THEN NOW() ELSE paid_at END,
            updated_at = NOW()
        WHERE order_id = ?
    ");
    $stmt->execute([
        $newStatus,
        $paymentType,
        $transactionId,
        $statusCode,
        $notification['status_message'] ?? '',
        $rawBody,
        $newStatus,
        $orderId
    ]);
    
    // Enroll user if payment successful
    if ($shouldEnroll && $transaction['transaction_status'] !== 'success') {
        // Check if not already enrolled
        if (!isEnrolled($transaction['user_id'], $transaction['course_id'])) {
            $stmt = $db->prepare("
                INSERT INTO enrollments (user_id, course_id, transaction_id, enrolled_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([
                $transaction['user_id'],
                $transaction['course_id'],
                $transaction['id']
            ]);
            
            // Get course info for notification
            $stmt = $db->prepare("SELECT title FROM courses WHERE id = ?");
            $stmt->execute([$transaction['course_id']]);
            $course = $stmt->fetch();
            
            // Create notification
            createNotification(
                $transaction['user_id'],
                'Pembayaran Berhasil! 🎉',
                "Selamat! Anda sekarang terdaftar di kursus \"{$course['title']}\". Mulai belajar sekarang!",
                'success',
                '/pages/learn.php?course=' . $transaction['course_id']
            );
            
            // Update coupon usage if applicable
            // ... (implement if needed)
        }
    }
    
    echo 'OK';
    
} catch (PDOException $e) {
    error_log("Notification handler error: " . $e->getMessage());
    http_response_code(500);
    exit('Server error');
}