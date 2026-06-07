<?php
require_once 'auth.php';
requireCommitteeOrAdmin();

$errorMessage = '';
$committeeClubID = getCommitteeClubID();
$clubs = [];

if (isAdmin()) {
    $result = $conn->query("SELECT clubID, clubName FROM club ORDER BY clubName ASC");
    $clubs = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT clubID, clubName FROM club WHERE clubID = ?");
    $stmt->bind_param("i", $committeeClubID);
    $stmt->execute();
    $clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clubID = isAdmin() ? (int)$_POST['clubID'] : (int)$committeeClubID;
    $userID = getUserID();

    $eventName = trim($_POST['eventName']);
    $eventDescription = trim($_POST['eventDescription']);
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $venueLocation = trim($_POST['venueLocation']);
    $maxParticipants = (int)$_POST['maxParticipants'];
    $registrationDeadline = str_replace('T', ' ', $_POST['registrationDeadline']) . ':00';
    $eventStatus = $_POST['eventStatus'];
    $lat = 0;
    $lng = 0;

    if (!$clubID || !$eventName || !$eventDescription || !$eventDate || !$eventTime || !$venueLocation || $maxParticipants < 1) {
        $errorMessage = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO event
            (
                clubID, userID, eventName, eventDescription, eventDate, eventTime,
                venueLocation, maxParticipants, registrationDeadline, eventStatus,
                eventGeoLocationLat, eventGeoLocationLog, createAt
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "issssssissdd",
            $clubID,
            $userID,
            $eventName,
            $eventDescription,
            $eventDate,
            $eventTime,
            $venueLocation,
            $maxParticipants,
            $registrationDeadline,
            $eventStatus,
            $lat,
            $lng
        );

        if ($stmt->execute()) {
            $_SESSION['successMessage'] = "Event created successfully.";
            header("Location: event_management.php");
            exit();
        }

        $errorMessage = "Failed to create event.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Create Event</h2>

            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="form-card-container">
                <form method="POST">
                    <div class="form-group-row">
                        <label>Event Name</label>
                        <input type="text" name="eventName" required>
                    </div>

                    <div class="form-group-row">
                        <label>Description</label>
                        <textarea name="eventDescription" rows="4" required></textarea>
                    </div>

                    <div class="form-group-row">
                        <label>Date</label>
                        <input type="date" name="eventDate" required>
                    </div>

                    <div class="form-group-row">
                        <label>Time</label>
                        <input type="time" name="eventTime" required>
                    </div>

                    <div class="form-group-row">
                        <label>Venue</label>
                        <input type="text" name="venueLocation" required>
                    </div>

                    <div class="form-group-row">
                        <label>Maximum Participants</label>
                        <input type="number" name="maxParticipants" min="1" required>
                    </div>

                    <div class="form-group-row">
                        <label>Registration Deadline</label>
                        <input type="datetime-local" name="registrationDeadline" required>
                    </div>

                    <div class="form-group-row">
                        <label>Status</label>
                        <select name="eventStatus" required>
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="form-group-row">
                        <label>Club</label>
                        <select name="clubID" <?= isCommittee() ? 'disabled' : '' ?> required>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?= $club['clubID'] ?>">
                                    <?= htmlspecialchars($club['clubName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Create Event</button>
                    <a href="event_management.php" class="btn btn-cancel">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>