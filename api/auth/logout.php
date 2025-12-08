<?php
// api/auth/logout.php

require_once __DIR__ . '/../../includes/functions.php';

// Clear session
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

session_destroy();

// Redirect based on request type
if (isAjaxRequest()) {
    jsonResponse(['success' => true, 'message' => 'Logout berhasil']);
} else {
    redirect('/login.php', 'Anda telah keluar', 'success');
}