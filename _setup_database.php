<?php
// LGN E-Learning - Database Import untuk Railway
// Jalankan SEKALI:  https://your-app. railway.app/_setup_database.php

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$flagFile = '. db_imported';

// Sudah pernah dijalankan? 
if (file_exists($flagFile)) {
    ?>
    <! DOCTYPE html>
    <html>
    <head>
        <title>Setup Done</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow text-center p-5">
                        <h1 class="text-success">✅ Database Already Imported</h1>
                        <p class="text-muted">Setup sudah selesai sebelumnya. </p>
                        <a href="/" class="btn btn-primary btn-lg mt-3">Buka LGN E-Learning</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// File SQL tidak ada? 
if (! file_exists('database.sql')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>File Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow text-center p-5 border-danger">
                        <h1 class="text-danger">❌ database.sql Not Found</h1>
                        <p class="text-muted">Pastikan file database.sql ada di root folder. </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Baca file SQL
$sql = file_get_contents('database.sql');

// Bersihkan SQL
$sql = preg_replace('/--.*\n/', "\n", $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
$sql = preg_replace('/USE\s+`? [\w]+`?\s*;/i', '', $sql);

$queries = array_filter(array_map('trim', explode(';', $sql)));
$total = count($queries);
$success = 0;
$errors = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import - LGN E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .card { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .log-area { background: #1a1a1a; color: #0f0; font-family: monospace; padding: 15px; 
                    max-height: 350px; overflow-y: auto; border-radius: 8px; font-size: 13px; }
        .log-success { color: #0f0; }
        . log-error { color: #f44; }
        .progress { height: 25px; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header text-center text-white py-3" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    <h3 class="mb-0">🚀 LGN Database Import</h3>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3">📊 Total queries: <strong><?php echo $total; ?></strong></p>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="bar" style="width: 0%">0%</div>
                    </div>
                    <div class="log-area" id="log">
                    <? php
                    foreach ($queries as $i => $query) {
                        if (empty($query)) continue;
                        
                        $num = $i + 1;
                        $pct = round(($num / $total) * 100);
                        
                        echo "<script>document.getElementById('bar').style.width='" . $pct . "%';document.getElementById('bar').innerText='" . $pct . "%';</script>";
                        flush();
                        @ob_flush();
                        
                        try {
                            $db->exec($query);
                            $success++;
                            $preview = htmlspecialchars(substr($query, 0, 60));
                            echo "<div class='log-success'>✓ #" . $num .  ": " . $preview . "...</div>";
                        } catch (PDOException $e) {
                            $errors[] = "#" . $num .  ": " . $e->getMessage();
                            echo "<div class='log-error'>✗ #" .  $num . ":  " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                        
                        usleep(5000);
                    }
                    ?>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5>📋 Summary</h5>
                        <p class="text-success mb-1">✅ Success: <strong><?php echo $success; ?></strong></p>
                        <p class="text-danger mb-0">❌ Failed: <strong><?php echo count($errors); ?></strong></p>
                    </div>
                    
                    <? php if ($success > 0 && count($errors) === 0): ?>
                        <? php file_put_contents($flagFile, date('Y-m-d H:i: s')); ?>
                        <div class="alert alert-success mt-4 text-center">
                            <h4>🎉 Import Berhasil!</h4>
                            <p>Database sudah siap digunakan. </p>
                            <a href="/" class="btn btn-primary btn-lg">Buka LGN E-Learning</a>
                        </div>
                    <?php elseif (count($errors) > 0): ?>
                        <div class="alert alert-warning mt-3">
                            <strong>Beberapa error:</strong>
                            <ul class="mb-0 small">
                            <?php foreach (array_slice($errors, 0, 5) as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>document.getElementById('log').scrollTop = document.getElementById('log').scrollHeight;</script>
</body>
</html>
