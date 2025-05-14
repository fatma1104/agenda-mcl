<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Charger les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();

// Mettre à jour les infos
if (isset($_POST["name"], $_POST["email"])) {
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST["name"], $_POST["email"], $_SESSION["user_id"]]);
    $_SESSION["user_name"] = $_POST["name"];
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>👤 Mon Profil</h1>
    <a href="dashboard.php">⬅ Retour</a>

    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user["name"]) ?>" required>

        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>" required>

        <button type="submit">Mettre à jour</button>
    </form>
</body>
<script src="assets/script.js" defer></script>

</html>
