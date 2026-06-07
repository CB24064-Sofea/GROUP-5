<?php
require_once 'auth.php';
requireCommitteeOrAdmin();

$successMessage = flash('successMessage');
$errorMessage = flash('errorMessage');

$clubID = getCommitteeClubID();

if (isCommittee()) {
    $stmt = $conn->prepare("
        SELECT 
            e.*,
            c.clubName,
            COUNT(er.registrationID) AS registeredCount
        FROM event e
        LEFT JOIN club c ON e.clubID = c.clubID
        LEFT JOIN event_registration er
            ON e.eventID = er.eventID
            AND er.registrationStatus = 'Success'
        WHERE e.clubID = ?
        GROUP BY e.eventID
        ORDER BY e.eventDate DESC, e.eventTime DESC
    ");
    $stmt->bind_param("i", $clubID);
} else {
    $stmt = $conn->prepare("
        SELECT 
            e.*,
            c.clubName,
            COUNT(er.registrationID) AS registeredCount
        FROM event e
        LEFT JOIN club c ON e.clubID = c.clubID
        LEFT JOIN event_registration er
            ON e.eventID = er.eventID
            AND er.registrationStatus = 'Success'
        GROUP BY e.eventID
        ORDER BY e.eventDate DESC, e.eventTime DESC
    ");
}

$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (isCommittee()) {
    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS totalEvents,
            SUM(eventDate > CURDATE()) AS upcomingEvents,
            SUM(eventStatus = 'Completed') AS completedEvents,
            SUM(eventStatus = 'Cancelled') AS cancelledEvents
        FROM event
        WHERE clubID = ?
    ");
    $statsStmt->bind_param("i", $clubID);
} else {
    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS totalEvents,
            SUM(eventDate > CURDATE()) AS upcomingEvents,
            SUM(eventStatus = 'Completed') AS completedEvents,
            SUM(eventStatus = 'Cancelled') AS cancelledEvents
        FROM event
    ");
}

$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <h2 class="page-title">Event Overview</h2>

            <div class="form-card-container">
                <p><strong>Total Events:</strong> <?= (int)$stats['totalEvents'] ?></p>
                <p><strong>Upcoming Events:</strong> <?= (int)$stats['upcomingEvents'] ?></p>
                <p><strong>Completed Events:</strong> <?= (int)$stats['completedEvents'] ?></p>
                <p><strong>Cancelled Events:</strong> <?= (int)$stats['cancelledEvents'] ?></p>
            </div>

            <div class="form-card-container">
                <p style="margin-bottom: 15px;">
                    <a href="create_event.php" class="btn btn-submit">Create New Event</a>
                </p>

                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$events): ?>
                            <tr>
                                <td colspan="8">No events found.</td>
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
                                    <span class="status-badge">
                                        <?= htmlspecialchars($event['eventStatus']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_event.php?id=<?= $event['eventID'] ?>" class="btn">View</a>
                                    <a href="edit_event.php?id=<?= $event['eventID'] ?>" class="btn">Edit</a>
                                    <a href="delete_event.php?id=<?= $event['eventID'] ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('Delete this event?');">
                                        Delete
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