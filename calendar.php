<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Gestion des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'un nouveau calendrier
    if (isset($_POST['add_calendar'])) {
        try {
            $name = trim(htmlspecialchars($_POST['name']));
            $color = $_POST['color'] ?? '#3498db';
            $description = isset($_POST['description']) ? trim(htmlspecialchars($_POST['description'])) : null;

            if (empty($name)) {
                throw new Exception("Le nom du calendrier est requis");
            }

            $stmt = $pdo->prepare("
                INSERT INTO calendars (user_id, name, description, color)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $name, $description, $color]);
            
            $_SESSION['success'] = "Calendrier créé avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    // Partage d'un calendrier
    if (isset($_POST['share_calendar'])) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO shared_calendars (calendar_id, shared_with_user_id, access_level)
                SELECT ?, id, ? FROM users WHERE email = ?
            ");
            $stmt->execute([
                $_POST['calendar_id'],
                $_POST['access_level'],
                $_POST['email']
            ]);
            
            $_SESSION['success'] = "Calendrier partagé avec succès";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
    }
    
    header("Location: calendar.php");
    exit;
}

// Suppression d'un calendrier
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM calendars 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
        $_SESSION['success'] = "Calendrier supprimé";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
    header("Location: calendar.php");
    exit;
}

// Récupération des données
$calendars = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM events WHERE calendar_id = c.id) as event_count
    FROM calendars c
    WHERE c.user_id = ?
");
$calendars->execute([$_SESSION['user_id']]);
$calendars = $calendars->fetchAll();

$shared_calendars = $pdo->prepare("
    SELECT c.*, u.email as owner_email
    FROM calendars c
    JOIN shared_calendars sc ON c.id = sc.calendar_id
    JOIN users u ON c.user_id = u.id
    WHERE sc.shared_with_user_id = ?
");
$shared_calendars->execute([$_SESSION['user_id']]);
$shared_calendars = $shared_calendars->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Calendriers</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-calendar-alt"></i> Mes Calendriers</h1>
            <a href="dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Retour</a>
            <button class="btn btn-primary" onclick="openModal('add-calendar-modal')">
                <i class="fas fa-plus"></i> Nouveau Calendrier
            </button>
        </header>

        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <section>
            <h2><i class="fas fa-user"></i> Mes Calendriers</h2>
            <div class="calendar-grid">
                <?php foreach ($calendars as $calendar): ?>
                    <div class="calendar-card">
                        <div class="calendar-color" style="background: <?= $calendar['color'] ?? '#3498db' ?>;"></div>
                        <h3><?= htmlspecialchars($calendar['name']) ?></h3>
                        <p><?= $calendar['event_count'] ?> événement(s)</p>
                        <?php if (!empty($calendar['description'])): ?>
                            <p><?= htmlspecialchars($calendar['description']) ?></p>
                        <?php endif; ?>
                        <div class="calendar-actions">
                            <a href="events.php?calendar_id=<?= $calendar['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <button class="btn" onclick="openShareModal(<?= $calendar['id'] ?>)">
                                <i class="fas fa-share-alt"></i> Partager
                            </button>
                            <a href="?delete=<?= $calendar['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce calendrier?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <h2><i class="fas fa-users"></i> Calendriers Partagés</h2>
            <?php if (empty($shared_calendars)): ?>
                <p>Aucun calendrier partagé avec vous</p>
            <?php else: ?>
                <div class="calendar-grid">
                    <?php foreach ($shared_calendars as $calendar): ?>
                        <div class="calendar-card">
                         <div class="calendar-color" style="background: <?= $calendar['color'] ?? '#3498db' ?>;"></div>
                            <h3><?= htmlspecialchars($calendar['name']) ?></h3>
                            <p>Propriétaire: <?= htmlspecialchars($calendar['owner_email']) ?></p>
                            <div class="calendar-actions">
                                <a href="events.php?calendar_id=<?= $calendar['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Modal Ajout Calendrier -->
    <div id="add-calendar-modal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-plus-circle"></i> Nouveau Calendrier</h2>
            <form method="post">
                <input type="hidden" name="add_calendar" value="1">
                <div class="form-group">
                    <label>Nom du calendrier</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="color" name="color" value="#3498db">
                </div>
                <div class="form-group">
                    <label>Description (optionnel)</label>
                    <textarea name="description"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="button" class="btn" onclick="closeModal('add-calendar-modal')">
                    Annuler
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Partage Calendrier -->
    <div id="share-calendar-modal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-share-alt"></i> Partager un Calendrier</h2>
            <form method="post">
                <input type="hidden" name="share_calendar" value="1">
                <input type="hidden" id="share-calendar-id" name="calendar_id">
                <div class="form-group">
                    <label>Email du destinataire</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Permission</label>
                    <select name="access_level" required>
                        <option value="lecture">Lecture seule</option>
                        <option value="edition">lecrure+ecriture </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Partager
                </button>
                <button type="button" class="btn" onclick="closeModal('share-calendar-modal')">
                    Annuler
                </button>
            </form>
        </div>
    </div>

</body>
<script src="assets/script.js" defer></script>
</html>
