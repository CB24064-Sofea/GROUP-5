<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

// Strict Role Access Guard
requireCommitteeOrAdmin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Access control layer checking permission constraints before execution
if ($eventID <= 0 || !canManageEvent($eventID)) {
    $_SESSION['errorMessage'] = "Invalid event selected or you do not have permission to delete it.";
    header("Location: event_management.php");
    exit();
}

// Proceed with parameterized deletion
$stmt = $conn->prepare("DELETE FROM event WHERE eventID = ?");
$stmt->bind_param("i", $eventID);

if ($stmt->execute()) {
    $_SESSION['successMessage'] = "Event execution profile has been removed from records.";
} else {
    $_SESSION['errorMessage'] = "System database constraint error occurred while deleting event.";
}

$stmt->close();

header("Location: event_management.php");
exit();
?>