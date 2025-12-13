<?php
// api/auth/forgot_password.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'request':
            handleRequest($db, $data);
            break;
            
        case 'verify':
            handleVerify($db, $data);
            break;
            
        case 'reset':
            handleReset($db, $data);
            break;
            
        default: 
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (PDOException $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}

/**
 * Handle password reset request - generate and send code
 */
function handleRequest($db, $data) {
    $email = sanitize($data['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid'], 400);
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        jsonResponse([
            'success' => true, 
            'message' => 'Jika email terdaftar, kode verifikasi akan dikirim'
        ]);
    }
    
    // Generate 6-digit code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Delete old reset codes for this user
    $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    
    // Store reset code
    $stmt = $db->prepare("
        INSERT INTO password_resets (user_id, email, code, expires_at, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user['id'], $email, password_hash($code, PASSWORD_DEFAULT), $expiresAt]);
    
    // Send email (using simple mail or your email service)
    $emailSent = sendResetEmail($user['email'], $user['name'], $code);
    
    if (!$emailSent) {
        // Log error but don't expose to user
        error_log("Failed to send reset email to: " . $email);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Kode verifikasi telah dikirim ke email Anda',
        // For development/testing only - remove in production! 
        // 'debug_code' => $code
    ]);
}

/**
 * Handle code verification
 */
function handleVerify($db, $data) {
    $email = sanitize($data['email'] ?? '');
    $code = sanitize($data['code'] ?? '');
    
    if (empty($email) || empty($code)) {
        jsonResponse(['success' => false, 'message' => 'Email dan kode wajib diisi'], 400);
    }
    
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        jsonResponse(['success' => false, 'message' => 'Format kode tidak valid'], 400);
    }
    
    // Get reset record
    $stmt = $db->prepare("
        SELECT pr.*, u.id as user_id 
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.email = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        jsonResponse(['success' => false, 'message' => 'Kode tidak valid atau sudah kadaluarsa'], 400);
    }
    
    // Verify code
    if (!password_verify($code, $reset['code'])) {
        // Increment attempts (optional: add attempts column and lock after too many)
        jsonResponse(['success' => false, 'message' => 'Kode verifikasi salah'], 400);
    }
    
    // Generate reset token for the next step
    $token = bin2hex(random_bytes(32));
    
    // Update record with token
    $stmt = $db->prepare("UPDATE password_resets SET token = ?, verified_at = NOW() WHERE id = ?");
    $stmt->execute([$token, $reset['id']]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Kode verifikasi valid',
        'data' => ['token' => $token]
    ]);
}

/**
 * Handle password reset
 */
function handleReset($db, $data) {
    $email = sanitize($data['email'] ?? '');
    $token = sanitize($data['token'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($token) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Data tidak lengkap'], 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
    }
    
    // Verify token
    $stmt = $db->prepare("
        SELECT pr.*, u.id as user_id 
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.email = ? AND pr.token = ? AND pr.used = 0 
              AND pr.verified_at IS NOT NULL AND pr.expires_at > NOW()
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        jsonResponse(['success' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'], 400);
    }
    
    // Update password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $reset['user_id']]);
    
    // Mark reset as used
    $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    $stmt->execute([$reset['id']]);
    
    // Create notification
    createNotification(
        $reset['user_id'],
        'Password Berhasil Direset 🔐',
        'Password akun Anda telah berhasil diperbarui. Jika Anda tidak melakukan ini, segera hubungi support.',
        'info',
        '/pages/profile.php'
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Password berhasil direset'
    ]);
}

/**
 * Send reset email
 */
function sendResetEmail($to, $name, $code) {
    $subject = 'Reset Password - LGN E-Learning';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #4f46e5; text-align: center; padding: 20px; background: white; border-radius: 10px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #64748b; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>LGN E-Learning</h1>
                <p style='margin:10px 0 0 0; opacity:0.9;'>Reset Password</p>
            </div>
            <div class='content'>
                <p>Halo <strong>{$name}</strong>,</p>
                <p>Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode verifikasi berikut:</p>
                <div class='code'>{$code}</div>
                <p>Kode ini akan kadaluarsa dalam <strong>15 menit</strong>.</p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini. Password Anda akan tetap aman.</p>
                <hr style='border:none;border-top:1px solid #e2e8f0;margin:20px 0;'>
                <p style='color:#64748b;font-size:14px;'>Jangan bagikan kode ini kepada siapapun termasuk pihak yang mengaku dari LGN.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " LGN E-Learning. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: LGN E-Learning <noreply@lgn.com>',
        'Reply-To: support@lgn.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Try to send email
    // In production, use PHPMailer or similar library
    return @mail($to, $subject, $message, implode("\r\n", $headers));
}
