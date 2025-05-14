<?php
require_once "includes/auth.php";
require_once "includes/db.php";

$calendar_id = $_GET['calendar_id'] ?? null;

// Vérification des permissions
$calendar_stmt = $pdo->prepare("
    SELECT c.* 
    FROM calendars c
    LEFT JOIN shared_calendars sc ON c.id = sc.calendar_id
    WHERE c.id = ? AND (c.user_id = ? OR sc.shared_with_user_id = ?)
");
$calendar_stmt->execute([$calendar_id, $_SESSION['user_id'], $_SESSION['user_id']]);
$calendar = $calendar_stmt->fetch();

if (!$calendar) {
    $_SESSION['error'] = "Calendrier introuvable ou accès refusé";
    header("Location: calendar.php");
    exit;
}

// Récupération des événements
$events = $pdo->prepare("
    SELECT id, title, description, start, end 
    FROM events 
    WHERE calendar_id = ?
");
$events->execute([$calendar_id]);
$events_data = $events->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier: <?= htmlspecialchars($calendar['name']) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>
    <div class="container">
        <header class="calendar-header">
            <h1 style="color: <?= $calendar['color'] ?>">
                <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($calendar['name']) ?>
            </h1>
            <a href="calendar.php" class="btn">
                <i class="fas fa-arrow-left"></i> Retour aux calendriers
            </a>
        </header>

        <div id="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?= json_encode(array_map(function($event) use ($calendar) {
                    return [
                        'id' => $event['id'],
                        'title' => htmlspecialchars($event['title']),
                        'start' => $event['start'],
                        'end' => $event['end'],
                        'color' => $calendar['color'],
                        'description' => htmlspecialchars($event['description'])
                    ];
                }, $events_data)) ?>,
                eventClick: function(info) {
                    alert(
                        'Événement: ' + info.event.title + '\n\n' +
                        'Description: ' + (info.event.extendedProps.description || 'Aucune') + '\n\n' +
                        'Du: ' + info.event.start.toLocaleString('fr-FR') + '\n' +
                        'Au: ' + (info.event.end ? info.event.end.toLocaleString('fr-FR') : '')
                    );
                },
                editable: true,
                eventDrop: function(info) {
                    fetch('api/update_event.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            event_id: info.event.id,
                            start: info.event.start.toISOString(),
                            end: info.event.end?.toISOString()
                        })
                    });
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
