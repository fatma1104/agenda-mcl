<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Ajouter un événement

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["calendar_id"], $_POST["title"])) {
    $stmt = $pdo->prepare("INSERT INTO events (calendar_id, user_id, title, description, start, end) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST["calendar_id"],
        $user_id, // L’utilisateur connecté
        $_POST["title"],
        $_POST["description"],
        $_POST["start"],
        $_POST["end"]
    ]);
}

// Supprimer un événement
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
}

// Lister les calendriers
$cals = $pdo->prepare("SELECT * FROM calendars WHERE user_id = ?");
$cals->execute([$_SESSION["user_id"]]);
$calendars = $cals->fetchAll();

// Lister tous les événements
$events = $pdo->query("SELECT events.*, calendars.name as calendar_name FROM events JOIN calendars ON events.calendar_id = calendars.id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>📌 Événements</h1>
    <a href="dashboard.php">⬅ Retour</a>

    <h2>Ajouter un événement</h2>
    <form method="POST">
        <select name="calendar_id" required>
            <option value="">-- Sélectionner un calendrier --</option>
            <?php foreach ($calendars as $cal) : ?>
                <option value="<?= $cal["id"] ?>"><?= htmlspecialchars($cal["name"]) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="title" placeholder="Titre" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="datetime-local" name="start">
        <input type="datetime-local" name="end">
        <button type="submit">Ajouter</button>
    </form>

    <h2>Liste des événements</h2>
    <ul>
        <?php foreach ($events as $event) : ?>
            <li>
                <strong><?= htmlspecialchars($event["title"]) ?></strong> (<?= htmlspecialchars($event["calendar_name"]) ?>)<br>
                <?= htmlspecialchars($event["description"]) ?><br>
                Du <?= $event["start"] ?> au <?= $event["end"] ?>
                <a href="?delete=<?= $event["id"] ?>" onclick="return confirm('Supprimer ?')">🗑</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
<script src="assets/script.js" defer></script>

</html>
