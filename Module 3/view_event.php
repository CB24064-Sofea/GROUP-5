<?php
require_once 'auth.php';
requireLogin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventID <= 0) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT e.*, c.clubName
    FROM event e
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE e.eventID = ?
");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM event_registration
    WHERE eventID = ? AND registrationStatus = 'Success'
");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$totalRegistered = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$capacity = (int)$event['maxParticipants'];
$percentage = $capacity > 0 ? min(100, round(($totalRegistered / $capacity) * 100)) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Event</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title"><?= htmlspecialchars($event['eventName']) ?></h2>

            <div class="form-card-container">
                <p><strong>Club:</strong> <?= htmlspecialchars($event['clubName'] ?? '-') ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($event['eventDescription'])) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($event['eventDate']) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?></p>
                <p><strong>Venue:</strong> <?= htmlspecialchars($event['venueLocation']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($event['eventStatus']) ?></p>
                <p><strong>Registration Deadline:</strong> <?= htmlspecialchars($event['registrationDeadline']) ?></p>
                <p><strong>Participants:</strong> <?= $totalRegistered ?> / <?= $capacity ?></p>

                <div style="background:#e2e8f0; height:12px; border-radius:20px; overflow:hidden; margin:10px 0;">
                    <div style="background:#1f6feb; height:100%; width:<?= $percentage ?>%;"></div>
                </div>

                <?php if (isStudent() && $event['eventStatus'] === 'Open'): ?>
                    <a href="register_event.php?id=<?= $event['eventID'] ?>" class="btn btn-submit">Register</a>
                <?php endif; ?>

                <?php if ((isCommittee() || isAdmin()) && canManageEvent($eventID)): ?>
                    <a href="edit_event.php?id=<?= $event['eventID'] ?>" class="btn">Edit</a>
                <?php endif; ?>

                <a href="<?= isStudent() ? 'book_event.php' : 'event_management.php' ?>" class="btn btn-cancel">Back</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>