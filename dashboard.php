<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Vérifier si la colonne color existe, sinon utiliser une valeur par défaut
$column_exists = false;
try {
    $test = $pdo->query("SELECT color FROM calendars LIMIT 1");
    $column_exists = true;
} catch (PDOException $e) {
    $column_exists = false;
}

// Récupérer les statistiques
$event_count = $pdo->prepare("SELECT COUNT(*) FROM events WHERE user_id = ?");
$event_count->execute([$_SESSION['user_id']]);
$event_count = $event_count->fetchColumn();

$shared_count = $pdo->prepare("SELECT COUNT(*) FROM shared_events WHERE shared_with_user_id = ?");
$shared_count->execute([$_SESSION['user_id']]);
$shared_count = $shared_count->fetchColumn();

$calendar_count = $pdo->prepare("SELECT COUNT(*) FROM calendars WHERE user_id = ?");
$calendar_count->execute([$_SESSION['user_id']]);
$calendar_count = $calendar_count->fetchColumn();

// Récupérer les prochains événements avec gestion de la colonne color
if ($column_exists) {
    $upcoming_events = $pdo->prepare("
        SELECT e.*, c.name as calendar_name, c.color as calendar_color
        FROM events e
        JOIN calendars c ON e.calendar_id = c.id
        WHERE e.user_id = ? AND e.start >= NOW()
        ORDER BY e.start ASC
        LIMIT 5
    ");
} else {
    $upcoming_events = $pdo->prepare("
        SELECT e.*, c.name as calendar_name, '#4361ee' as calendar_color
        FROM events e
        JOIN calendars c ON e.calendar_id = c.id
        WHERE e.user_id = ? AND e.start >= NOW()
        ORDER BY e.start ASC
        LIMIT 5
    ");
}
$upcoming_events->execute([$_SESSION['user_id']]);
$upcoming_events = $upcoming_events->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION["name"]) ?> !</h1>
    <nav>
        <a href="calendar.php">📅 Calendriers</a> |
        <a href="events.php">📌 Événements</a> |
        <a href="profile.php">👤 Profil</a> |
        <a href="logout.php">🚪 Déconnexion</a>
    </nav>
</body>
<script src="assets/script.js" defer></script>

</html>
