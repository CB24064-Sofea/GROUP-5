<?php
require_once 'auth.php';
requireLogin();
if (!isStudent()) { header("Location: index.php"); exit(); }

$eventID = $_GET['id'] ?? 0;
$userID = getUserID();

// Check if already registered
$check = $conn->prepare("SELECT registrationID FROM event_registration WHERE eventID=? AND userID=?");
$check->bind_param("is", $eventID, $userID);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo "<script>alert('You already registered for this event.'); window.location='events.php';</script>";
    exit();
}

// Get event details
$evt = $conn->prepare("SELECT maxParticipants, registrationDeadline, eventStatus FROM event WHERE eventID=?");
$evt->bind_param("i", $eventID);
$evt->execute();
$event = $evt->get_result()->fetch_assoc();
if (!$event || $event['eventStatus'] != 'Open' || strtotime($event['registrationDeadline']) < time()) {
    echo "<script>alert('Registration closed or event not open.'); window.location='events.php';</script>";
    exit();
}

// Count current registrations (status Success)
$regCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM event_registration WHERE eventID=? AND registrationStatus='Success'");
$regCount->bind_param("i", $eventID);
$regCount->execute();
$cnt = $regCount->get_result()->fetch_assoc()['cnt'];

if ($cnt < $event['maxParticipants']) {
    // Direct registration
    $reg = $conn->prepare("INSERT INTO event_registration (eventID, userID, registrationDate, registrationStatus, cancellationDate) VALUES (?, ?, NOW(), 'Success', '0000-00-00 00:00:00')");
    $reg->bind_param("is", $eventID, $userID);
    $reg->execute();
    echo "<div class='alert alert-success'>Registration successful!</div>";
} else {
    // Add to waiting list
    $pos = $conn->prepare("SELECT COALESCE(MAX(position),0)+1 AS pos FROM waitinglist WHERE eventID=?");
    $pos->bind_param("i", $eventID);
    $pos->execute();
    $position = $pos->get_result()->fetch_assoc()['pos'];
    $wl = $conn->prepare("INSERT INTO waitinglist (eventID, userID, position, registerAt) VALUES (?, ?, ?, NOW())");
    $wl->bind_param("isi", $eventID, $userID, $position);
    $wl->execute();
    echo "<div class='alert alert-warning'>Event full. Added to waiting list (position $position).</div>";
}
?>
<a href="events.php" class="btn btn-cancel">Back to Events</a>
<?php include 'footer.php'; ?>