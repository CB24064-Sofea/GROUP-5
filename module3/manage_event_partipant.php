<?php
require_once 'auth.php';
requireCommitteeOrAdmin();
require_once 'waitinglist.php';

$action = $_GET['action'] ?? '';
$registrationID = isset($_GET['registrationID']) ? (int)$_GET['registrationID'] : 0;
$eventID = isset($_GET['eventID']) ? (int)$_GET['eventID'] : 0;

if ($action !== 'remove' || $registrationID <= 0 || $eventID <= 0 || !canManageEvent($eventID)) {
    $_SESSION['errorMessage'] = "Invalid participant action.";
    header("Location: event_participant_list.php");
    exit();
}

$stmt = $conn->prepare("
    UPDATE event_registration
    SET registrationStatus = 'Cancel',
        cancellationDate = NOW()
    WHERE registrationID = ?
    AND eventID = ?
");
$stmt->bind_param("ii", $registrationID, $eventID);
$stmt->execute();
$stmt->close();

$promoted = promoteNextFromWaitingList($conn, $eventID);

if ($promoted) {
    $_SESSION['successMessage'] = "Participant removed. The next student in the waiting list has been promoted.";
} else {
    $_SESSION['successMessage'] = "Participant removed successfully.";
}

header("Location: event_participant_list.php?eventID=" . $eventID);
exit();
?>