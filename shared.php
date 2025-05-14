<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Récupérer les calendriers partagés avec l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT c.*, s.permission, u.name AS owner_name 
    FROM shared_calendars s
    JOIN calendars c ON s.calendar_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE s.shared_with_user_id = ?
");
$stmt->execute([$_SESSION["user_id"]]);
$sharedCalendars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Partagés avec moi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>📂 Calendriers partagés avec moi</h1>
    <a href="dashboard.php">⬅ Retour</a>

    <?php if (count($sharedCalendars) > 0): ?>
        <ul>
            <?php foreach ($sharedCalendars as $cal): ?>
                <li>
                    <strong><?= htmlspecialchars($cal["name"]) ?></strong> — par <?= htmlspecialchars($cal["owner_name"]) ?><br>
                    Description : <?= htmlspecialchars($cal["description"]) ?><br>
                    Droit : <em><?= $cal["permission"] ?></em>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun calendrier partagé avec vous.</p>
    <?php endif; ?>
</body>
<script src="assets/script.js" defer></script>

</html>
