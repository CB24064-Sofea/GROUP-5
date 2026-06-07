<?php
require_once 'auth.php';
requireCommitteeOrAdmin();

$clubID = getCommitteeClubID();
$eventID = isset($_GET['eventID']) ? (int)$_GET['eventID'] : 0;

if (isCommittee()) {
    $stmt = $conn->prepare("
        SELECT eventID, eventName
        FROM event
        WHERE clubID = ?
        ORDER BY eventDate DESC
    ");
    $stmt->bind_param("i", $clubID);
} else {
    $stmt = $conn->prepare("
        SELECT eventID, eventName
        FROM event
        ORDER BY eventDate DESC
    ");
}

$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($eventID === 0 && $events) {
    $eventID = (int)$events[0]['eventID'];
}

$participants = [];

if ($eventID > 0 && canManageEvent($eventID)) {
    $stmt = $conn->prepare("
        SELECT
            er.registrationID,
            er.registrationStatus,
            er.registrationDate,
            u.userID,
            u.name,
            u.email,
            s.program
        FROM event_registration er
        JOIN user u ON er.userID = u.userID
        LEFT JOIN student s ON u.userID = s.userID
        WHERE er.eventID = ?
        ORDER BY u.name ASC
    ");
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$successMessage = flash('successMessage');
$errorMessage = flash('errorMessage');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Participant List</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Event Participant List</h2>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="form-card-container">
                <form method="GET" class="form-group-row">
                    <label>Select Event</label>
                    <select name="eventID" onchange="this.form.submit()">
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['eventID'] ?>" <?= $eventID === (int)$event['eventID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['eventName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Programme</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Registered At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$participants): ?>
                            <tr>
                                <td colspan="7">No participants found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($participants as $participant): ?>
                            <tr>
                                <td><?= htmlspecialchars($participant['userID']) ?></td>
                                <td><?= htmlspecialchars($participant['name']) ?></td>
                                <td><?= htmlspecialchars($participant['program'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($participant['email']) ?></td>
                                <td>
                                    <span class="status-badge">
                                        <?= htmlspecialchars($participant['registrationStatus']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($participant['registrationDate']) ?></td>
                                <td>
                                    <?php if ($participant['registrationStatus'] === 'Success'): ?>
                                        <a href="manage_event_participant.php?action=remove&registrationID=<?= $participant['registrationID'] ?>&eventID=<?= $eventID ?>"
                                           class="btn btn-danger"
                                           onclick="return confirm('Remove this participant?');">
                                            Remove
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
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