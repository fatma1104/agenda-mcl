<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Récupération des données utilisateur
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

// Statistiques
$stats = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM calendars WHERE user_id = ?) as calendar_count,
        (SELECT COUNT(*) FROM events WHERE user_id = ?) as event_count,
        (SELECT COUNT(*) FROM shared_calendars WHERE shared_with_user_id = ?) as shared_count
");
$stats->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$stats = $stats->fetch();

// Mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $name = htmlspecialchars($_POST['name']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        $stmt = $pdo->prepare("UPDATE users SET email = ?, name = ? WHERE id = ?");
        $stmt->execute([$email, $name, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Profil mis à jour avec succès";
        header("Location: profile.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            throw new Exception("Les mots de passe ne correspondent pas");
        }
        
        // Vérification du mot de passe actuel
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $db_password = $stmt->fetchColumn();
        
        if (!password_verify($current_password, $db_password)) {
            throw new Exception("Mot de passe actuel incorrect");
        }
        
        // Mise à jour du mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Mot de passe changé avec succès";
        header("Location: profile.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Mon Profil</h1>
            <a href="dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Retour</a>
        </header>

         <!-- Messages d'alerte -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

        <div class="profile-tabs">
            <!-- Onglets -->
            <div class="tabs">
                <div class="tab active" data-tab="personal-info">
                    <i class="fas fa-user"></i> Informations
                </div>
                <div class="tab" data-tab="statistics">
                    <i class="fas fa-chart-bar"></i> Statistiques
                </div>
                <div class="tab" data-tab="security">
                    <i class="fas fa-lock"></i> Sécurité
                </div>
            </div>

            <!-- Contenu des onglets -->
            <div class="tab-content active" id="personal-info">
                <form method="post" class="profile-form">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
            </div>

           <div class="tab-content" id="statistics">
    <h3 class="stats-title"><i class="fas fa-chart-pie"></i> Mes Activités</h3>
    
    <div class="modern-stats-grid">
        <!-- Carte Calendriers -->
        <div class="stat-card calendar-stat">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-text">
                    <div class="stat-number"><?= $stats['calendar_count'] ?? 0 ?></div>
                    <div class="stat-label">Calendriers créés</div>
                </div>
            </div>
            <div class="stat-footer">
                <a href="calendar.php" class="stat-link">Voir mes calendriers <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Carte Événements -->
        <div class="stat-card event-stat">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-text">
                    <div class="stat-number"><?= $stats['event_count'] ?? 0 ?></div>
                    <div class="stat-label">Événements planifiés</div>
                </div>
            </div>
            <div class="stat-footer">
                <a href="events.php" class="stat-link">Voir mes événements <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Carte Partages -->
        <div class="stat-card share-stat">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-share-alt"></i>
                </div>
                <div class="stat-text">
                    <div class="stat-number"><?= $stats['shared_count'] ?? 0 ?></div>
                    <div class="stat-label">Calendriers partagés</div>
                </div>
            </div>
            <div class="stat-footer">
                <a href="calendar.php" class="stat-link">Voir les partages <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

            <div class="tab-content" id="security">
                <form method="post" class="security-form">
                    <div class="form-group">
                        <label>Mot de passe actuel</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion des onglets
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Désactiver tous les onglets
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Activer l'onglet cliqué
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
