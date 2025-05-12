

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Connexion</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
    <p>Pas de compte ? <a href="register.php">Inscription</a></p>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
<script src="assets/script.js" defer></script>

</html>
