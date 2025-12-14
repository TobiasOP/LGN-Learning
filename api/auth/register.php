<?php
// api/auth/register.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

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

try {
    $db = getDB();
    
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
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}
