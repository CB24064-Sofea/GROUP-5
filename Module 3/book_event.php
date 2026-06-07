<?php
require_once 'auth.php';
requireStudent();

$successMessage = flash('successMessage');
$errorMessage = flash('errorMessage');

$result = $conn->query("
    SELECT 
        e.*,
        c.clubName,
        (
            SELECT COUNT(*)
            FROM event_registration er
            WHERE er.eventID = e.eventID
            AND er.registrationStatus = 'Success'
        ) AS registeredCount
    FROM event e
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE e.eventStatus = 'Open'
    AND e.registrationDeadline >= NOW()
    ORDER BY e.eventDate ASC, e.eventTime ASC
");

$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Events</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Browse Events</h2>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="form-card-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$events): ?>
                            <tr>
                                <td colspan="7">No open events available.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['eventName']) ?></td>
                                <td><?= htmlspecialchars($event['clubName'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($event['eventDate']) ?></td>
                                <td><?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars($event['venueLocation']) ?></td>
                                <td><?= (int)$event['registeredCount'] ?> / <?= (int)$event['maxParticipants'] ?></td>
                                <td>
                                    <a href="view_event.php?id=<?= $event['eventID'] ?>" class="btn">View</a>
                                    <a href="register_event.php?id=<?= $event['eventID'] ?>"
                                       class="btn btn-submit"
                                       onclick="return confirm('Register for this event?');">
                                        Register
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>