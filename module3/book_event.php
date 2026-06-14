<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

if (!isStudent() && !isCommittee()) {
     header("Location: /GROUP%205/module1/login.php");
    exit();
}

$userID = getUserID();
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);

$result = $conn->query("
    SELECT e.*, c.clubName,
        (SELECT COUNT(*) FROM event_registration er WHERE er.eventID = e.eventID AND er.registrationStatus = 'Success') AS registeredCount
    FROM event e
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE e.eventStatus = 'Open' AND e.registrationDeadline >= NOW()
    ORDER BY e.eventDate ASC, e.eventTime ASC
");

$events = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Events - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../topbar.php'; ?>
    <div class="app-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">Browse Available Events</h2>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <div class="form-card-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Organizing Club</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue Location</th>
                                <th>Capacity Status</th>
                                <th>Available Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$events): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No open events available right now.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($event['eventName']) ?></strong></td>
                                    <td><?= htmlspecialchars($event['clubName'] ?? '-') ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars($event['eventDate']) ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?></td>
                                    <td><?= htmlspecialchars($event['venueLocation']) ?></td>
                                    <td style="text-align: center;">
                                        <span class="status-badge">
                                            <?= (int)$event['registeredCount'] ?> / <?= (int)$event['maxParticipants'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="view_event.php?id=<?= $event['eventID'] ?>" class="btn-edit" style="text-decoration:none; margin-right:5px;">View</a>
                                        <a href="register_event.php?id=<?= $event['eventID'] ?>" 
                                           class="btn-primary" 
                                           style="text-decoration:none; padding: 8px 12px; font-size:0.85rem; min-width:auto;"
                                           onclick="return confirm('Confirm event registration request?');">
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