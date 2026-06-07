<?php
require_once 'auth.php';
requireCommitteeOrAdmin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventID <= 0 || !canManageEvent($eventID)) {
    $_SESSION['errorMessage'] = "Invalid event selected.";
    header("Location: event_management.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM event WHERE eventID = ?");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION['errorMessage'] = "Event not found.";
    header("Location: event_management.php");
    exit();
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = trim($_POST['eventName']);
    $eventDescription = trim($_POST['eventDescription']);
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $venueLocation = trim($_POST['venueLocation']);
    $maxParticipants = (int)$_POST['maxParticipants'];
    $registrationDeadline = str_replace('T', ' ', $_POST['registrationDeadline']) . ':00';
    $eventStatus = $_POST['eventStatus'];

    $stmt = $conn->prepare("
        UPDATE event
        SET eventName = ?,
            eventDescription = ?,
            eventDate = ?,
            eventTime = ?,
            venueLocation = ?,
            maxParticipants = ?,
            registrationDeadline = ?,
            eventStatus = ?
        WHERE eventID = ?
    ");

    $stmt->bind_param(
        "sssssissi",
        $eventName,
        $eventDescription,
        $eventDate,
        $eventTime,
        $venueLocation,
        $maxParticipants,
        $registrationDeadline,
        $eventStatus,
        $eventID
    );

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Event updated successfully.";
        header("Location: event_management.php");
        exit();
    }

    $errorMessage = "Failed to update event.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Edit Event</h2>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="form-card-container">
                <form method="POST">
                    <div class="form-group-row">
                        <label>Event Name</label>
                        <input type="text" name="eventName" value="<?= htmlspecialchars($event['eventName']) ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Description</label>
                        <textarea name="eventDescription" rows="4" required><?= htmlspecialchars($event['eventDescription']) ?></textarea>
                    </div>

                    <div class="form-group-row">
                        <label>Date</label>
                        <input type="date" name="eventDate" value="<?= htmlspecialchars($event['eventDate']) ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Time</label>
                        <input type="time" name="eventTime" value="<?= htmlspecialchars(substr($event['eventTime'], 0, 5)) ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Venue</label>
                        <input type="text" name="venueLocation" value="<?= htmlspecialchars($event['venueLocation']) ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Maximum Participants</label>
                        <input type="number" name="maxParticipants" min="1" value="<?= (int)$event['maxParticipants'] ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Registration Deadline</label>
                        <input type="datetime-local" name="registrationDeadline" value="<?= date('Y-m-d\TH:i', strtotime($event['registrationDeadline'])) ?>" required>
                    </div>

                    <div class="form-group-row">
                        <label>Status</label>
                        <select name="eventStatus" required>
                            <?php foreach (['Open', 'Closed', 'Cancelled', 'Completed'] as $status): ?>
                                <option value="<?= $status ?>" <?= $event['eventStatus'] === $status ? 'selected' : '' ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Update Event</button>
                    <a href="event_management.php" class="btn btn-cancel">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>