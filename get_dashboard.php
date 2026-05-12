<?php
require_once 'db.php';
header('Content-Type: application/json');

$today = date('Y-m-d');

// Today's KPI summary
$kpi = $pdo->query("
    SELECT 
        COUNT(*) AS total_inserts,
        COALESCE(SUM(weight_kg), 0) AS total_weight,
        COALESCE(SUM(coins_issued), 0) AS total_coins,
        COALESCE(SUM(payout), 0) AS total_payouts
    FROM transactions
    WHERE DATE(created_at) = '$today'
")->fetch(PDO::FETCH_ASSOC);

// Latest water reading
$water = $pdo->query("
    SELECT * FROM water_logs ORDER BY created_at DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

// Active alerts
$alerts = $pdo->query("
    SELECT message FROM alerts 
    WHERE is_cleared = 0 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_COLUMN);

// Recent activity
$activity = $pdo->query("
    SELECT description FROM activity_log 
    ORDER BY created_at DESC 
    LIMIT 6
")->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'kpi'      => $kpi,
    'water'    => $water,
    'alerts'   => $alerts,
    'activity' => $activity
]);
?>