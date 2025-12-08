<?php
// includes/header.php

require_once __DIR__ . '/functions.php';
// Load Midtrans config so we can output correct Snap JS URL and client key
require_once __DIR__ . '/../config/midtrans.php';

$currentUser = isLoggedIn() ? getCurrentUser() : null;
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'LGN - Learn GeNius, Platform e-learning terbaik untuk mengembangkan skill dan karir Anda' ?>">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - LGN' : 'LGN - Learn GeNius' ?></title>
    
    <!-- Favicon - menggunakan emoji sebagai fallback -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($additionalCss) && is_array($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Midtrans Snap JS -->
    <script src="<?= htmlspecialchars(MidtransConfig::getSnapJsUrl()) ?>" data-client-key="<?= htmlspecialchars(MidtransConfig::CLIENT_KEY) ?>"></script>
    
    <style>
        /* Inline critical CSS */
        body {
            font-family: 'Poppins', sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .hero-section {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 80px 0 100px;
            color: white;
        }
        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .hero-title span {
            color: #fbbf24;
        }
        @media (min-width: 992px) {
            .hero-title {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <!-- Flash Message -->
    <?php if ($flashMessage): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : htmlspecialchars($flashMessage['type']) ?> alert-dismissible fade show" role="alert">
            <?php if ($flashMessage['type'] === 'success'): ?>
                <i class="bi bi-check-circle me-2"></i>
            <?php elseif ($flashMessage['type'] === 'error' || $flashMessage['type'] === 'danger'): ?>
                <i class="bi bi-x-circle me-2"></i>
            <?php else: ?>
                <i class="bi bi-info-circle me-2"></i>
            <?php endif; ?>
            <?= htmlspecialchars($flashMessage['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>