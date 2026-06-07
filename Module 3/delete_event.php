<?php
require_once 'auth.php';
requireCommitteeOrAdmin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventID <= 0 || !canManageEvent($eventID)) {
    $_SESSION['errorMessage'] = "Invalid event selected.";
    header("Location: event_management.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM event WHERE eventID = ?");
$stmt->bind_param("i", $eventID);

if ($stmt->execute()) {
    $_SESSION['successMessage'] = "Event deleted successfully.";
} else {
    $_SESSION['errorMessage'] = "Failed to delete event.";
}

$stmt->close();

header("Location: event_management.php");
exit();
?>