<?php
// api/auth/login.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$remember = $data['remember'] ?? false;

if (empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Email dan password wajib diisi'], 400);
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT id, name, email, password, role, avatar, is_active 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
    }
    
    if (!$user['is_active']) {
        jsonResponse(['success' => false, 'message' => 'Akun Anda telah dinonaktifkan'], 403);
    }
    
    if (!password_verify($password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Email atau password salah'], 401);
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    
    // Set remember me cookie
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        
        // Store token in database (you'd need to create a remember_tokens table)
    }
    
    unset($user['password']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => ['user' => $user]
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}