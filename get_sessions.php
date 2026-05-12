<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

$result = $conn->query("
    SELECT u.id, u.name, u.email, s.logged_in_at
    FROM active_session s
    JOIN users u ON u.id = s.user_id
    ORDER BY s.logged_in_at DESC
    LIMIT 1
");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["ok" => true, "user" => $user]);
} else {
    echo json_encode(["ok" => true, "user" => null]);
}
?>