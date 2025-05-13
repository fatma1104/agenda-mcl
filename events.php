<?php
require_once "includes/auth.php";
require_once "includes/db.php";

// Récupération des calendriers de l'utilisateur
$calendars = $pdo->prepare("SELECT * FROM calendars WHERE user_id = ?");
$calendars->execute([$_SESSION['user_id']]);
$calendars = $calendars->fetchAll();

// 1. Gestion des événements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter ou modifier un événement
    if (isset($_POST['save_event'])) {
        try {
            if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
                // Modification d'un événement existant
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET calendar_id = ?, title = ?, start = ?, end = ?, description = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $_POST['calendar_id'],
                    htmlspecialchars($_POST['title']),
                    $_POST['start'],
                    $_POST['end'],
                    htmlspecialchars($_POST['description']),
                    $_POST['event_id'],
                    $_SESSION['user_id']
                ]);
                $_SESSION['success'] = "Événement modifié avec succès";
            } else {
                // Ajout d'un nouvel événement
                $stmt = $pdo->prepare("
                    INSERT INTO events (calendar_id, user_id, title, start, end, description)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['calendar_id'],
                    $_SESSION['user_id'],
                    htmlspecialchars($_POST['title']),
                    $_POST['start'],
                    $_POST['end'],
                    htmlspecialchars($_POST['description'])
                ]);
                $_SESSION['success'] = "Événement ajouté avec succès";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
        header("Location: events.php");
        exit;
    }
    
    // Partager un événement
    if (isset($_POST['share_event'])) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO shared_events 
                (event_id, shared_with_user_id, shared_by_user_id, access_level)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['event_id'],
                $_POST['shared_with_user_id'],
                $_SESSION['user_id'],
                $_POST['access_level']
            ]);
            $_SESSION['success'] = "Événement partagé avec succès";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
        header("Location: events.php");
        exit;
    }
}

// 2. Suppression d'événement
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM events 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
        $_SESSION['success'] = "Événement supprimé";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
    header("Location: events.php");
    exit;
}

// 3. Récupération des données
// Mes événements
$events = $pdo->prepare("
    SELECT e.*, c.name as calendar_name 
    FROM events e
    JOIN calendars c ON e.calendar_id = c.id
    WHERE e.user_id = ?
");
$events->execute([$_SESSION['user_id']]);
$my_events = $events->fetchAll();

// Récupération des événements partagés avec moi
try {
    $stmt_shared_with_me = $pdo->prepare("
        SELECT e.*, u.email as sender_email, se.access_level, c.name as calendar_name
        FROM events e
        JOIN shared_events se ON e.id = se.event_id
        JOIN users u ON se.shared_by_user_id = u.id
        JOIN calendars c ON e.calendar_id = c.id
        WHERE se.shared_with_user_id = ?
    ");
    $stmt_shared_with_me->execute([$_SESSION['user_id']]);
    $shared_with_me = $stmt_shared_with_me->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des événements partagés: " . $e->getMessage();
    error_log("Erreur SQL (événements partagés): " . $e->getMessage());
    $shared_with_me = [];
}

// Récupération des utilisateurs pour le partage (sauf l'utilisateur courant)
$users = $pdo->prepare("SELECT id, email FROM users WHERE id != ?");
$users->execute([$_SESSION['user_id']]);
$users = $users->fetchAll();

// Récupération d'un événement à éditer
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $edit_event = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Événements</title>
    <link rel="stylesheet" href="assets/style.css">
    
</head>
<body>
    <h1>Gestion des Événements</h1>
    <a href="dashboard.php" class="btn">← Retour</a>
    <!-- Bouton pour créer un événement -->
        <button id="createEventBtn" class="btn">Créer un événement</button>

    <!-- Messages d'alerte -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Onglets -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('my-events')">Mes Événements</div>
        <div class="tab" onclick="showTab('shared-with-me')">Partagés avec moi</div>
        <div class="tab" onclick="showTab('share-form')">Partager un événement</div>
    </div>

    <!-- Contenu des onglets -->
    <div id="my-events" class="tab-content active">
        <h2>Mes Événements</h2>
        
        

        <!-- Modal pour le formulaire d'ajout/modification -->
        <div id="eventModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3><?= $edit_event ? 'Modifier' : 'Créer' ?> un événement</h3>
                <form method="post">
                    <input type="hidden" name="save_event" value="1">
                    <?php if ($edit_event): ?>
                        <input type="hidden" name="event_id" value="<?= $edit_event['id'] ?>">
                    <?php endif; ?>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Calendrier:</label>
                        <select name="calendar_id" required style="width: 100%; padding: 8px;">
                            <?php foreach ($calendars as $cal): ?>
                                <option value="<?= $cal['id'] ?>" 
                                    <?= ($edit_event && $edit_event['calendar_id'] == $cal['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cal['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Titre:</label>
                        <input type="text" name="title" value="<?= $edit_event ? htmlspecialchars($edit_event['title']) : '' ?>" 
                               required style="width: 100%; padding: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Description:</label>
                        <textarea name="description" style="width: 100%; padding: 8px; height: 100px;"><?= $edit_event ? htmlspecialchars($edit_event['description']) : '' ?></textarea>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Début:</label>
                        <input type="datetime-local" name="start" 
                            value="<?= $edit_event ? date('Y-m-d\TH:i', strtotime($edit_event['start'])) : '' ?>" 
                            required style="width: 100%; padding: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Fin:</label>
                        <input type="datetime-local" name="end" 
                            value="<?= $edit_event ? date('Y-m-d\TH:i', strtotime($edit_event['end'])) : '' ?>" 
                            required style="width: 100%; padding: 8px;">
                    </div>
                    
                    <button type="submit" class="btn"><?= $edit_event ? 'Modifier' : 'Créer' ?></button>
                </form>
            </div>
        </div>

        <!-- Liste des événements -->
        <h3>Liste de mes événements</h3>
        <?php if (empty($my_events)): ?>
            <p>Aucun événement à afficher.</p>
        <?php else: ?>
            <?php foreach ($my_events as $event): ?>
                <div class="event-card">
                    <h4><?= htmlspecialchars($event['title']) ?></h4>
                    <p><strong>Calendrier:</strong> <?= htmlspecialchars($event['calendar_name']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
                    <p><strong>Début:</strong> <?= date('d/m/Y H:i', strtotime($event['start'])) ?></p>
                    <p><strong>Fin:</strong> <?= date('d/m/Y H:i', strtotime($event['end'])) ?></p>
                    <div class="event-actions">
                        <a href="?edit=<?= $event['id'] ?>" class="btn">Modifier</a>
                        <a href="?delete=<?= $event['id'] ?>" onclick="return confirm('Supprimer cet événement?')" class="btn danger">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="shared-with-me" class="tab-content">
        <h2>Événements partagés avec moi</h2>
        <?php if (empty($shared_with_me)): ?>
            <p>Aucun événement partagé avec vous.</p>
        <?php else: ?>
            <?php foreach ($shared_with_me as $event): ?>
                <div class="event-card">
                    <h4><?= htmlspecialchars($event['title']) ?></h4>
                    <p><strong>Calendrier:</strong> <?= htmlspecialchars($event['calendar_name']) ?></p>
                    <p><strong>Partagé par:</strong> <?= htmlspecialchars($event['sender_email']) ?></p>
                    <p><strong>Permission:</strong> <?= $event['access_level'] == 'lecture' ? 'Lecture seule' : 'Édition' ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
                    <p><strong>Début:</strong> <?= date('d/m/Y H:i', strtotime($event['start'])) ?></p>
                    <p><strong>Fin:</strong> <?= date('d/m/Y H:i', strtotime($event['end'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="share-form" class="tab-content">
        <h2>Partager un événement</h2>
        <form method="post">
            <input type="hidden" name="share_event" value="1">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Événement à partager:</label>
                <select name="event_id" required style="width: 100%; padding: 8px;">
                    <?php foreach ($my_events as $event): ?>
                        <option value="<?= $event['id'] ?>">
                            <?= htmlspecialchars($event['title']) ?> (<?= date('d/m/Y', strtotime($event['start'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Utilisateur avec qui partager:</label>
                <select name="shared_with_user_id" required style="width: 100%; padding: 8px;">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Permission:</label>
                <select name="access_level" required style="width: 100%; padding: 8px;">
                    <option value="lecture">Lecture seule</option>
                    <option value="edition">Édition</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Partager</button>
        </form>
    </div>

    <script>
        // Gestion des onglets
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
        
        // Gestion du modal
        const modal = document.getElementById("eventModal");
        const btn = document.getElementById("createEventBtn");
        const span = document.getElementsByClassName("close")[0];
        
        btn.onclick = function() {
            modal.style.display = "block";
        }
        
        span.onclick = function() {
            modal.style.display = "none";
            <?php if (isset($_GET['edit'])): ?>
                window.location.href = 'events.php';
            <?php endif; ?>
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                <?php if (isset($_GET['edit'])): ?>
                    window.location.href = 'events.php';
                <?php endif; ?>
            }
        }
        
        // Si on est en mode édition, ouvrir automatiquement le modal
        <?php if (isset($_GET['edit'])): ?>
            window.onload = function() {
                modal.style.display = "block";
            }
        <?php endif; ?>
    </script>
</body>
</html>

