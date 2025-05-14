<?php
require_once "includes/auth.php";
require_once "includes/db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("
        UPDATE events 
        SET start = ?, end = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $data['start'],
        $data['end'],
        $data['event_id'],
        $_SESSION['user_id']
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}