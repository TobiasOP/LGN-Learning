<?php
// db_update.php - HAPUS SETELAH SELESAI!

require_once 'includes/functions.php';

// Security key
$key = 'Halo123333'; // Ganti dengan string random
if (($_GET['key'] ?? '') !== $key) {
    die('Unauthorized');
}

try {
    $db = getDB();
    
    // SQL dari artifact di atas
    $sql = file_get_contents('migration.sql'); // atau paste langsung
    
    $db->exec($sql);
    
    echo "✅ Database updated successfully!";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
