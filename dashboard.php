<?php
require_once "includes/auth.php";
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
