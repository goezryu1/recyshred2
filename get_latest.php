<?php
require_once 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT * FROM transactions 
    ORDER BY created_at DESC 
    LIMIT 50
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
?>