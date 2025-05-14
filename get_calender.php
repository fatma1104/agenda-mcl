<?php
require_once "includes/auth.php";
require_once "includes/db.php";

header('Content-Type: application/json');

$stmt = $pdo->prepare("
    SELECT * FROM calendars 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$calendar = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($calendar);
?>