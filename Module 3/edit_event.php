<?php
require_once 'auth.php';
requireLogin();
if (!isAdmin() && !isCommittee()) { header("Location: index.php"); exit(); }

$eventID = $_GET['id'] ?? 0;
requireEventOwnership($eventID);

$stmt = $conn->prepare("SELECT * FROM event WHERE eventID = ?");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) die("Event not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $conn->prepare("UPDATE event SET eventName=?, eventDescription=?, eventDate=?, eventTime=?, venueLocation=?, maxParticipants=?, registrationDeadline=?, eventStatus=?, eventGeoLocationLat=?, eventGeoLocationLog=? WHERE eventID=?");
    $update->bind_param("sssssisssdi", $_POST['eventName'], $_POST['eventDescription'], $_POST['eventDate'], $_POST['eventTime'], $_POST['venueLocation'], $_POST['maxParticipants'], $_POST['registrationDeadline'], $_POST['eventStatus'], $_POST['eventGeoLocationLat'], $_POST['eventGeoLocationLog'], $eventID);
    if ($update->execute()) {
        echo '<div class="alert alert-success">Event updated.</div>';
        header("Refresh:1; url=events.php");
    } else echo '<div class="alert alert-error">Error.</div>';
}
include 'header.php';
include 'sidebar.php';
?>
<div class="workspace-stack">
    <div class="page-title">Edit Event</div>
    <div class="form-card-container">
        <form method="post">
            <div class="form-group-row"><label>Event Name</label><input type="text" name="eventName" value="<?php echo htmlspecialchars($event['eventName']); ?>" required></div>
            <div class="form-group-row"><label>Description</label><textarea name="eventDescription" rows="3"><?php echo htmlspecialchars($event['eventDescription']); ?></textarea></div>
            <div class="form-group-row"><label>Date</label><input type="date" name="eventDate" value="<?php echo $event['eventDate']; ?>" required></div>
            <div class="form-group-row"><label>Time</label><input type="time" name="eventTime" value="<?php echo $event['eventTime']; ?>" required></div>
            <div class="form-group-row"><label>Venue</label><input type="text" name="venueLocation" value="<?php echo htmlspecialchars($event['venueLocation']); ?>" required></div>
            <div class="form-group-row"><label>Max Participants</label><input type="number" name="maxParticipants" value="<?php echo $event['maxParticipants']; ?>" required></div>
            <div class="form-group-row"><label>Registration Deadline</label><input type="datetime-local" name="registrationDeadline" value="<?php echo date('Y-m-d\TH:i', strtotime($event['registrationDeadline'])); ?>" required></div>
            <div class="form-group-row"><label>Status</label><select name="eventStatus"><option <?php echo $event['eventStatus']=='Open'?'selected':'';?>>Open</option><option <?php echo $event['eventStatus']=='Closed'?'selected':'';?>>Closed</option><option <?php echo $event['eventStatus']=='Cancelled'?'selected':'';?>>Cancelled</option></select></div>
            <div class="form-group-row"><label>Latitude</label><input type="text" name="eventGeoLocationLat" value="<?php echo $event['eventGeoLocationLat']; ?>"></div>
            <div class="form-group-row"><label>Longitude</label><input type="text" name="eventGeoLocationLog" value="<?php echo $event['eventGeoLocationLog']; ?>"></div>
            <div class="form-actions-footer-bar"><button type="submit" class="btn btn-submit">Update Event</button><a href="events.php" class="btn btn-cancel">Cancel</a></div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>