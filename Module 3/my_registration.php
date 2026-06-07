<?php
require_once 'auth.php';
requireStudent();
require_once 'waitinglist.php';

$successMessage = flash('successMessage');
$errorMessage = flash('errorMessage');
$userID = getUserID();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_registration_id'])) {
    $registrationID = (int)$_POST['cancel_registration_id'];

    $stmt = $conn->prepare("
        SELECT eventID
        FROM event_registration
        WHERE registrationID = ?
        AND userID = ?
        AND registrationStatus = 'Success'
    ");
    $stmt->bind_param("is", $registrationID, $userID);
    $stmt->execute();
    $registration = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($registration) {
        $stmt = $conn->prepare("
            UPDATE event_registration
            SET registrationStatus = 'Cancel',
                cancellationDate = NOW()
            WHERE registrationID = ?
        ");
        $stmt->bind_param("i", $registrationID);
        $stmt->execute();
        $stmt->close();

        $promoted = promoteNextFromWaitingList($conn, (int)$registration['eventID']);

        if ($promoted) {
            $successMessage = "Registration cancelled. The next student in the waiting list has been promoted.";
        } else {
            $successMessage = "Registration cancelled successfully.";
        }
    } else {
        $errorMessage = "Registration could not be cancelled.";
    }
}

$stmt = $conn->prepare("
    SELECT 
        er.*,
        e.eventName,
        e.eventDate,
        e.eventTime,
        e.venueLocation,
        c.clubName
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE er.userID = ?
    ORDER BY e.eventDate DESC, e.eventTime DESC
");
$stmt->bind_param("s", $userID);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
    SELECT 
        w.*,
        e.eventName,
        e.eventDate,
        e.eventTime,
        e.venueLocation,
        c.clubName
    FROM waitinglist w
    JOIN event e ON w.eventID = e.eventID
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE w.userID = ?
    ORDER BY w.registerAt DESC
");
$stmt->bind_param("s", $userID);
$stmt->execute();
$waitingList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Registrations</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">My Event Registrations</h2>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="form-card-container">
                <h3>Registered Events</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$registrations): ?>
                            <tr>
                                <td colspan="6">No registrations found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($registrations as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['eventName']) ?></strong><br>
                                    <small><?= htmlspecialchars($row['clubName'] ?? '-') ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['eventDate']) ?></td>
                                <td><?= htmlspecialchars(substr($row['eventTime'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars($row['venueLocation']) ?></td>
                                <td>
                                    <span class="status-badge">
                                        <?= htmlspecialchars($row['registrationStatus']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['registrationStatus'] === 'Success'): ?>
                                        <form method="POST" onsubmit="return confirm('Cancel this registration?');">
                                            <input type="hidden" name="cancel_registration_id" value="<?= $row['registrationID'] ?>">
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-card-container">
                <h3>Waiting List</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Position</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$waitingList): ?>
                            <tr>
                                <td colspan="6">You are not in any waiting list.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($waitingList as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['eventName']) ?></strong><br>
                                    <small><?= htmlspecialchars($row['clubName'] ?? '-') ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['eventDate']) ?></td>
                                <td><?= htmlspecialchars(substr($row['eventTime'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars($row['venueLocation']) ?></td>
                                <td><?= (int)$row['position'] ?></td>
                                <td><?= htmlspecialchars($row['registerAt']) ?></td>
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