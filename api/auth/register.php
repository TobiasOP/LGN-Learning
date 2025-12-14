<?php
// api/auth/register.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/mailer.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'send_verification';

try {
    $db = getDB();
    
    switch ($action) {
        case 'send_verification':
            handleSendVerification($db, $data);
            break;
            
        case 'verify_and_register':
            handleVerifyAndRegister($db, $data);
            break;
            
        default:
            // Legacy: direct registration (untuk backward compatibility)
            handleDirectRegistration($db, $data);
    }
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}

/**
 * Step 1: Send verification code to email
 */
function handleSendVerification($db, $data) {
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    $role = in_array($data['role'] ?? '', ['student', 'tutor']) ? $data['role'] : 'student';
    
    // Validations
    if (empty($name) || strlen($name) < 3) {
        jsonResponse(['success' => false, 'message' => 'Nama minimal 3 karakter'], 400);
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid'], 400);
    }
    
    if (empty($password) || strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
    }
    
    if ($password !== $confirmPassword) {
        jsonResponse(['success' => false, 'message' => 'Konfirmasi password tidak cocok'], 400);
    }
    
    // Check if email already registered
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar. Silakan login atau gunakan email lain.'], 400);
    }
    
    // Generate 6-digit verification code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $hashedCode = password_hash($code, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Delete old pending registration for this email
    $stmt = $db->prepare("DELETE FROM pending_registrations WHERE email = ?");
    $stmt->execute([$email]);
    
    // Store pending registration
    $stmt = $db->prepare("
        INSERT INTO pending_registrations (name, email, password, role, verification_code, expires_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $hashedPassword, $role, $hashedCode, $expiresAt]);

    $emailSent = sendVerificationEmail($email, $name, $code);
    
    if (!$emailSent) {
        error_log("Failed to send verification email to: " . $email);
        // Still return success - don't expose email sending issues
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Kode verifikasi telah dikirim ke email Anda',
        // For development only - remove in production! 
        // 'debug_code' => $code
    ]);
}

/**
 * Step 2: Verify code and complete registration
 */
function handleVerifyAndRegister($db, $data) {
    $email = sanitize($data['email'] ?? '');
    $code = sanitize($data['code'] ?? '');
    
    if (empty($email) || empty($code)) {
        jsonResponse(['success' => false, 'message' => 'Email dan kode wajib diisi'], 400);
    }
    
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        jsonResponse(['success' => false, 'message' => 'Format kode tidak valid'], 400);
    }
    
    // Get pending registration
    $stmt = $db->prepare("
        SELECT * FROM pending_registrations 
        WHERE email = ? AND expires_at > NOW()
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $pending = $stmt->fetch();
    
    if (!$pending) {
        jsonResponse(['success' => false, 'message' => 'Kode sudah kadaluarsa. Silakan daftar ulang.'], 400);
    }
    
    // Verify code
    if (!password_verify($code, $pending['verification_code'])) {
        jsonResponse(['success' => false, 'message' => 'Kode verifikasi salah'], 400);
    }
    
    // Check again if email already registered (race condition prevention)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // Delete pending registration
        $db->prepare("DELETE FROM pending_registrations WHERE email = ?")->execute([$email]);
        jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar. Silakan login.'], 400);
    }
    
    // Create user account
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role, email_verified_at, created_at) 
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $pending['name'],
        $pending['email'],
        $pending['password'],
        $pending['role']
    ]);
    
    $userId = $db->lastInsertId();
    
    // Delete pending registration
    $stmt = $db->prepare("DELETE FROM pending_registrations WHERE email = ?");
    $stmt->execute([$email]);
    
    // Set session (auto login)
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $pending['role'];
    $_SESSION['user_name'] = $pending['name'];
    $_SESSION['user_email'] = $pending['email'];
    
    // Create welcome notification
    createNotification(
        $userId,
        'Selamat Datang di LGN! 🎉',
        'Terima kasih telah bergabung. Email Anda telah terverifikasi. Mulai jelajahi ribuan kursus berkualitas untuk mengembangkan skill Anda.',
        'success',
        '/pages/courses.php'
    );
    
    // Determine redirect based on role
    $redirect = $pending['role'] === 'tutor' ? '/pages/tutor/dashboard.php' : '/pages/courses.php';
    
    jsonResponse([
        'success' => true,
        'message' => 'Registrasi berhasil! Selamat datang di LGN.',
        'data' => [
            'user' => [
                'id' => $userId,
                'name' => $pending['name'],
                'email' => $pending['email'],
                'role' => $pending['role']
            ],
            'redirect' => $redirect
        ]
    ]);
}

/**
 * Legacy: Direct registration without email verification
 * Keep for backward compatibility or API usage
 */
function handleDirectRegistration($db, $data) {
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    $role = in_array($data['role'] ?? '', ['student', 'tutor']) ? $data['role'] : 'student';
    
    // Validations
    if (empty($name) || strlen($name) < 3) {
        jsonResponse(['success' => false, 'message' => 'Nama minimal 3 karakter'], 400);
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid'], 400);
    }
    
    if (empty($password) || strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
    }
    
    if ($password !== $confirmPassword) {
        jsonResponse(['success' => false, 'message' => 'Konfirmasi password tidak cocok'], 400);
    }
    
    // Check existing email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar'], 400);
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$name, $email, $hashedPassword, $role]);
    
    $userId = $db->lastInsertId();
    
    // Set session
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    // Create welcome notification
    createNotification(
        $userId,
        'Selamat Datang di LGN! 🎉',
        'Terima kasih telah bergabung. Mulai jelajahi ribuan kursus berkualitas untuk mengembangkan skill Anda.',
        'success',
        '/pages/courses.php'
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'data' => [
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ]
        ]
    ]);
}


