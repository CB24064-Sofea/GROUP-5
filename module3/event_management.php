<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

if (!isCommittee() && !isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);
$clubID = null;

if (isCommittee()) {
    $clubID = getCommitteeClubID();
}
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
            IFNULL(SUM(eventDate > CURDATE()), 0) AS upcomingEvents,
            IFNULL(SUM(eventStatus = 'Completed'), 0) AS completedEvents,
            IFNULL(SUM(eventStatus = 'Cancelled'), 0) AS cancelledEvents
        FROM event
        WHERE clubID = ?
    ");
    $statsStmt->bind_param("i", $clubID);
} else {
    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS totalEvents,
            IFNULL(SUM(eventDate > CURDATE()), 0) AS upcomingEvents,
            IFNULL(SUM(eventStatus = 'Completed'), 0) AS completedEvents,
            IFNULL(SUM(eventStatus = 'Cancelled'), 0) AS cancelledEvents
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../topbar.php';  ?>

    <div class="app-container">
        
        <?php include __DIR__ . '/../sidebar.php'; ?>
        
        <main class="main-content">
            <div class="workspace-stack">
                
                <h2 class="page-title">Event Management Overview</h2>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Events</h3>
                        <div class="number"><?= (int)$stats['totalEvents'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Upcoming</h3>
                        <div class="number" style="color: #3498db;"><?= (int)$stats['upcomingEvents'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Completed</h3>
                        <div class="number" style="color: #2ecc71;"><?= (int)$stats['completedEvents'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Cancelled</h3>
                        <div class="number" style="color: #e74c3c;"><?= (int)$stats['cancelledEvents'] ?></div>
                    </div>
                </div>

                <div class="form-card-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: #2c3e50; margin: 0;">Club Managed Events Logs</h3>
                        <a href="create_event.php" class="btn-primary" style="text-decoration: none; padding: 10px 18px; font-size: 0.9rem;">➕ Create New Event</a>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Organizing Club</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue Location</th>
                                <th>Participants Status</th>
                                <th>Event Status</th>
                                <th>Control Operations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No registered club event data profiles found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($event['eventName']) ?></strong></td>
                                        <td><?= htmlspecialchars($event['clubName'] ?? '-') ?></td>
                                        <td style="text-align: center; white-space: nowrap;"><?= htmlspecialchars($event['eventDate']) ?></td>
                                        <td style="text-align: center;"><?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?></td>
                                        <td><?= htmlspecialchars($event['venueLocation']) ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge">
                                                <?= (int)$event['registeredCount'] ?> / <?= (int)$event['maxParticipants'] ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="status-badge"><?= htmlspecialchars($event['eventStatus']) ?></span>
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            <a href="view_event.php?id=<?= $event['eventID'] ?>" class="btn-edit" style="text-decoration: none; margin-right: 4px;">View</a>
                                            <a href="edit_event.php?id=<?= $event['eventID'] ?>" class="btn-secondary" style="text-decoration: none; margin-right: 4px; padding: 8px 12px; min-width: auto; font-size: 0.85rem;">Edit</a>
                                            <a href="delete_event.php?id=<?= $event['eventID'] ?>"
                                               class="btn-delete"
                                               style="text-decoration: none; padding: 8px 12px; min-width: auto; font-size: 0.85rem;"
                                               onclick="return confirm('Are you sure you want to completely delete this event execution profile?');">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>