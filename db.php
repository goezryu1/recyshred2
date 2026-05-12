<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "recyshred_db";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Database connection failed");
}
?>