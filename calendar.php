<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Créer un calendrier
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"])) {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $stmt = $pdo->prepare("INSERT INTO calendars (user_id, name, description) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION["user_id"], $name, $description]);
}

// Supprimer un calendrier
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $stmt = $pdo->prepare("DELETE FROM calendars WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION["user_id"]]);
}

// Récupérer les calendriers de l’utilisateur
$stmt = $pdo->prepare("SELECT * FROM calendars WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$calendars = $stmt->fetchAll();


// Modifier un calendrier
if (isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("UPDATE calendars SET name = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['edit_name'], $_POST['edit_description'], $_POST['edit_id'], $_SESSION['user_id']]);
}

// Partager un calendrier
if (isset($_POST['share_id'])) {
    $shareWith = $_POST['shared_email'];
    $permission = $_POST['permission'];

    // Trouver l'ID de l'utilisateur à partager
    $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->execute([$shareWith]);
    $sharedUser = $userStmt->fetch();

    if ($sharedUser) {
        $stmt = $pdo->prepare("INSERT INTO shared_calendars (calendar_id, shared_with_user_id, permission) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['share_id'], $sharedUser['id'], $permission]);
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes calendriers</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>📅 Mes calendriers</h1>
    <a href="dashboard.php">⬅ Retour</a>

    <h2>Créer un nouveau calendrier</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Nom du calendrier" required>
        <textarea name="description" placeholder="Description (optionnel)"></textarea>
        <button type="submit">Créer</button>
    </form>

    <h2>Liste de mes calendriers</h2>
    <ul>
        <?php foreach ($calendars as $cal) : ?>
            <li>
                <strong><?= htmlspecialchars($cal["name"]) ?></strong><br>
                <?= nl2br(htmlspecialchars($cal["description"])) ?><br>
                <a href="?delete=<?= $cal["id"] ?>" onclick="return confirm('Supprimer ce calendrier ?')">🗑 Supprimer</a>
                <!-- Liens futurs : Modifier, Partager, Ajouter événement -->
            </li>
        <?php endforeach; ?>
    </ul>
    <!-- Boutons d'action -->
<form method="POST" style="margin-top: 10px;">
    <input type="hidden" name="edit_id" value="<?= $cal['id'] ?>">
    <input type="text" name="edit_name" value="<?= htmlspecialchars($cal['name']) ?>" required>
    <textarea name="edit_description"><?= htmlspecialchars($cal['description']) ?></textarea>
    <button type="submit">✏️ Modifier</button>
</form>

<form method="POST" style="margin-top: 10px;">
    <input type="hidden" name="share_id" value="<?= $cal['id'] ?>">
    <input type="email" name="shared_email" placeholder="Email utilisateur" required>
    <select name="permission">
        <option value="read">Lecture seule</option>
        <option value="write">Lecture + écriture</option>
    </select>
    <button type="submit">🔗 Partager</button>
</form>

</body>
<script src="assets/script.js" defer></script>

</html>
