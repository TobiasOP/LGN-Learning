<?php
// api/auth/change_password.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Silakan login'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$currentPassword = $data['current_password'] ?? '';
$newPassword = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    jsonResponse(['success' => false, 'message' => 'Semua field wajib diisi'], 400);
}

if (strlen($newPassword) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password baru minimal 6 karakter'], 400);
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'Konfirmasi password tidak cocok'], 400);
}

try {
    $db = getDB();
    
    // Get user
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Password saat ini salah'], 400);
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Password berhasil diubah'
    ]);
    
} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}