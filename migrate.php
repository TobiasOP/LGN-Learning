<?php
// migrate.php - Migration untuk Email Verification System
// PENTING: Hapus file ini setelah migration selesai!

require_once 'includes/functions.php';

// Pastikan hanya admin yang bisa menjalankan
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    die('Unauthorized');
}

// Secret key untuk keamanan (ganti dengan string random Anda)
$secretKey = 'LGN_mig_2024_a8f3h2k9x7m4';
if (($_GET['key'] ?? '') !== $secretKey) {
    die('Invalid key. Usage: migrate.php?key=your-secret-migration-key-2024');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { 
            background: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8fafc;
            border-left: 3px solid #4f46e5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Database Migration</h1>
        <p>Email Verification System - LGN E-Learning</p>
        <hr>

<?php

try {
    $db = getDB();
    $results = [];
    
    // =============================================
    // 1. Tabel pending_registrations
    // =============================================
    echo "<div class='step'>";
    echo "<h3>Step 1: Creating pending_registrations table...</h3>";
    
    $sql1 = "
    CREATE TABLE IF NOT EXISTS `pending_registrations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('student','tutor') DEFAULT 'student',
      `verification_code` varchar(255) NOT NULL,
      `expires_at` timestamp NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql1);
    echo "<p class='success'>✅ Table <code>pending_registrations</code> created successfully!</p>";
    $results[] = 'pending_registrations: OK';
    echo "</div>";
    
    // =============================================
    // 2. Tabel password_resets
    // =============================================
    echo "<div class='step'>";
    echo "<h3>Step 2: Creating password_resets table...</h3>";
    
    $sql2 = "
    CREATE TABLE IF NOT EXISTS `password_resets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `email` varchar(100) NOT NULL,
      `code` varchar(255) NOT NULL,
      `token` varchar(64) DEFAULT NULL,
      `used` tinyint(1) DEFAULT 0,
      `verified_at` timestamp NULL DEFAULT NULL,
      `expires_at` timestamp NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `email` (`email`),
      KEY `expires_at` (`expires_at`),
      CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql2);
    echo "<p class='success'>✅ Table <code>password_resets</code> created successfully!</p>";
    $results[] = 'password_resets: OK';
    echo "</div>";
    
    // =============================================
    // 3. Kolom email_verified_at di tabel users
    // =============================================
    echo "<div class='step'>";
    echo "<h3>Step 3: Adding email_verified_at column to users table...</h3>";
    
    // Check if column exists
    $checkColumn = $db->query("SHOW COLUMNS FROM `users` LIKE 'email_verified_at'")->fetch();
    
    if (!$checkColumn) {
        $sql3 = "ALTER TABLE `users` ADD COLUMN `email_verified_at` timestamp NULL DEFAULT NULL AFTER `is_active`";
        $db->exec($sql3);
        echo "<p class='success'>✅ Column <code>email_verified_at</code> added to <code>users</code> table!</p>";
        $results[] = 'email_verified_at column: ADDED';
    } else {
        echo "<p style='color: #f59e0b;'>⚠️ Column <code>email_verified_at</code> already exists. Skipped.</p>";
        $results[] = 'email_verified_at column: ALREADY EXISTS';
    }
    echo "</div>";
    
    // =============================================
    // 4. Verifikasi
    // =============================================
    echo "<div class='step'>";
    echo "<h3>Step 4: Verification...</h3>";
    
    $tables = $db->query("SHOW TABLES LIKE '%registration%' OR SHOW TABLES LIKE '%reset%'")->fetchAll();
    echo "<p>Tables created:</p>";
    echo "<ul>";
    
    $verifyTables = ['pending_registrations', 'password_resets'];
    foreach ($verifyTables as $table) {
        $exists = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($exists) {
            echo "<li class='success'>✅ <code>$table</code></li>";
        } else {
            echo "<li class='error'>❌ <code>$table</code> NOT FOUND</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
    
    // =============================================
    // Summary
    // =============================================
    echo "<hr>";
    echo "<h2 class='success'>🎉 Migration Completed Successfully!</h2>";
    echo "<div class='warning'>";
    echo "<strong>⚠️ PENTING - Post-Migration Steps:</strong>";
    echo "<ol>";
    echo "<li>Hapus file <code>migrate.php</code> dari server untuk keamanan</li>";
    echo "<li>Test fitur registrasi dengan email verification</li>";
    echo "<li>Test fitur forgot password</li>";
    echo "<li>Setup cron job untuk cleanup (opsional):</li>";
    echo "</ol>";
    echo "<pre style='background: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 5px;'>";
    echo "# Cleanup expired records (run daily)\n";
    echo "0 0 * * * /usr/bin/php /path/to/cleanup.php\n";
    echo "</pre>";
    echo "</div>";
    
    echo "<h3>Migration Summary:</h3>";
    echo "<ul>";
    foreach ($results as $result) {
        echo "<li>$result</li>";
    }
    echo "</ul>";
    
    echo "<a href='/admin/index.php' class='btn'>Kembali ke Dashboard</a>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>❌ Migration Failed!</h2>";
    echo "<div class='warning'>";
    echo "<strong>Error Details:</strong><br>";
    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
    echo "</div>";
    echo "<p>Silakan periksa:</p>";
    echo "<ul>";
    echo "<li>Database connection</li>";
    echo "<li>User permissions (CREATE TABLE, ALTER TABLE)</li>";
    echo "<li>Foreign key constraints</li>";
    echo "</ul>";
}

?>

    </div>
</body>
</html>

<?php
// =============================================
// Cleanup Script (Opsional)
// Buat file terpisah: cleanup.php
// =============================================
/*
<?php
// cleanup.php - Jalankan via cron job untuk membersihkan data expired

require_once 'includes/functions.php';

try {
    $db = getDB();
    
    // Delete expired pending registrations
    $stmt1 = $db->prepare("DELETE FROM pending_registrations WHERE expires_at < NOW()");
    $stmt1->execute();
    $deleted1 = $stmt1->rowCount();
    
    // Delete expired or used password resets
    $stmt2 = $db->prepare("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");
    $stmt2->execute();
    $deleted2 = $stmt2->rowCount();
    
    error_log("Cleanup completed: $deleted1 pending registrations, $deleted2 password resets deleted");
    
} catch (PDOException $e) {
    error_log("Cleanup error: " . $e->getMessage());
}
?>
*/
?>
