<?php
require_once 'auth.php';
requireLogin();
if (!isAdmin() && !isCommittee()) { header("Location: index.php"); exit(); }

include 'header.php';
include 'sidebar.php';

$clubID = '';
if (isCommittee()) {
    $clubID = getCommitteeClubID(getUserID());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['eventName'];
    $desc = $_POST['eventDescription'];
    $date = $_POST['eventDate'];
    $time = $_POST['eventTime'];
    $venue = $_POST['venueLocation'];
    $max = $_POST['maxParticipants'];
    $deadline = $_POST['registrationDeadline'];
    $status = $_POST['eventStatus'];
    $lat = $_POST['eventGeoLocationLat'];
    $lng = $_POST['eventGeoLocationLog'];
    $clubID = $_POST['clubID'];

    $stmt = $conn->prepare("INSERT INTO event (clubID, userID, eventName, eventDescription, eventDate, eventTime, venueLocation, maxParticipants, registrationDeadline, eventStatus, eventGeoLocationLat, eventGeoLocationLog, createAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssssisssd", $clubID, $_SESSION['userID'], $eventName, $desc, $date, $time, $venue, $max, $deadline, $status, $lat, $lng);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Event created successfully.</div>';
    } else {
        echo '<div class="alert alert-error">Error: ' . $conn->error . '</div>';
    }
}

// Clubs for admin dropdown
$clubs = [];
if (isAdmin()) {
    $res = $conn->query("SELECT clubID, clubName FROM club");
    while ($r = $res->fetch_assoc()) $clubs[] = $r;
}
?>
<div class="workspace-stack">
    <div class="page-title">Create New Event</div>
    <div class="form-card-container">
        <form method="post">
            <?php if (isAdmin()): ?>
            <div class="form-group-row">
                <label>Club</label>
                <select name="clubID" required>
                    <?php foreach ($clubs as $c): ?>
                        <option value="<?php echo $c['clubID']; ?>"><?php echo htmlspecialchars($c['clubName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="clubID" value="<?php echo $clubID; ?>">
            <?php endif; ?>
            <div class="form-group-row"><label>Event Name</label><input type="text" name="eventName" required></div>
            <div class="form-group-row"><label>Description</label><textarea name="eventDescription" rows="3"></textarea></div>
            <div class="form-group-row"><label>Date</label><input type="date" name="eventDate" required></div>
            <div class="form-group-row"><label>Time</label><input type="time" name="eventTime" required></div>
            <div class="form-group-row"><label>Venue</label><input type="text" name="venueLocation" required></div>
            <div class="form-group-row"><label>Max Participants</label><input type="number" name="maxParticipants" required></div>
            <div class="form-group-row"><label>Registration Deadline</label><input type="datetime-local" name="registrationDeadline" required></div>
            <div class="form-group-row"><label>Status</label>
                <select name="eventStatus"><option>Open</option><option>Closed</option><option>Cancelled</option></select>
            </div>
            <div class="form-group-row"><label>Latitude</label><input type="text" name="eventGeoLocationLat" value="3.12345"></div>
            <div class="form-group-row"><label>Longitude</label><input type="text" name="eventGeoLocationLog" value="101.679"></div>
            <div class="form-actions-footer-bar"><button type="submit" class="btn btn-submit">Create Event</button><a href="events.php" class="btn btn-cancel">Cancel</a></div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>