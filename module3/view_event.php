<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

requireLogin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventID <= 0) {
    header("Location: " . (isStudent() ? "dashboard.php" : "event_management.php"));
    exit();
}

// Fetch Master Event Details
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
    header("Location: " . (isStudent() ? "dashboard.php" : "event_management.php"));
    exit();
}

// Gather Aggregate Success Registrations
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event Details - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../topbar.php'; ?>

    <div class="app-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">Event Detail Information</h2>

                <div class="form-card-container">
                    <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px;">
                        <span class="status-badge" style="float: right; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                            <?= htmlspecialchars($event['eventStatus']) ?>
                        </span>
                        <h3 style="color: #0a2540; margin: 0; font-size: 1.5rem;"><?= htmlspecialchars($event['eventName']) ?></h3>
                        <p style="margin: 5px 0 0 0; color: #64748b;">Hosted by: <strong><?= htmlspecialchars($event['clubName'] ?? '-') ?></strong></p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #475569; margin-bottom: 8px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.3px;">Description Logistical Briefing</h4>
                        <div style="line-height: 1.6; color: #334155; background-color: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <?= nl2br(htmlspecialchars($event['eventDescription'])) ?>
                        </div>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px;">
                        <div style="flex: 1; min-width: 200px; background-color: #f8fafc; padding: 12px; border-radius: 6px;">
                            <span style="display: block; font-size: 0.8rem; text-transform: uppercase; color: #64748b;">📅 Scheduled Date</span>
                            <strong style="color: #0a2540; font-size: 1.05rem;"><?= htmlspecialchars($event['eventDate']) ?></strong>
                        </div>
                        <div style="flex: 1; min-width: 200px; background-color: #f8fafc; padding: 12px; border-radius: 6px;">
                            <span style="display: block; font-size: 0.8rem; text-transform: uppercase; color: #64748b;">⏰ Start Time</span>
                            <strong style="color: #0a2540; font-size: 1.05rem;"><?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?></strong>
                        </div>
                        <div style="flex: 1; min-width: 200px; background-color: #f8fafc; padding: 12px; border-radius: 6px;">
                            <span style="display: block; font-size: 0.8rem; text-transform: uppercase; color: #64748b;">📍 Venue Location</span>
                            <strong style="color: #0a2540; font-size: 1.05rem;"><?= htmlspecialchars($event['venueLocation']) ?></strong>
                        </div>
                    </div>

                    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 6px; margin-bottom: 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.9rem; color: #475569;"><strong>Registration Progress:</strong> <?= $totalRegistered ?> / <?= $capacity ?> Occupied Slots</span>
                            <span style="font-size: 0.85rem; font-weight: bold; color: #0284c7;"><?= $percentage ?>% Full</span>
                        </div>
                        <div style="background:#cbd5e1; height:12px; border-radius:20px; overflow:hidden;">
                            <div style="background:#0284c7; height:100%; width:<?= $percentage ?>%; border-radius:20px; transition: width 0.3s ease;"></div>
                        </div>
                        <p style="margin: 10px 0 0 0; font-size: 0.8rem; color: #64748b;">⚠️ Closing Deadline: <?= htmlspecialchars($event['registrationDeadline']) ?></p>
                    </div>

                    <div style="display: flex; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <?php if (isStudent() && $event['eventStatus'] === 'Open'): ?>
                            <a href="register_event.php?id=<?= $event['eventID'] ?>" class="btn-submit" style="text-decoration: none; text-align: center; line-height: 1.5;">Register for Event</a>
                        <?php endif; ?>

                        <?php if (isCommittee() || isAdmin()): ?>
                            <a href="edit_event.php?id=<?= $event['eventID'] ?>" class="btn-secondary">
                              ✏️ Edit Details
                            </a>
                        <?php endif; ?>

                        <a href="<?= isStudent() ? 'book_event.php' : 'event_management.php' ?>" class="btn-secondary" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 6px; background-color: #e2e8f0; color: #334155; line-height: 1.5;">Back to Directory</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>