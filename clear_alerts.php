<?php
require_once 'db.php';
$pdo->exec("UPDATE alerts SET is_cleared = 1 WHERE is_cleared = 0");
echo json_encode(['success' => true]);
?>