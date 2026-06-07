<?php
require_once 'auth.php';
requireStudent();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userID = getUserID();

if ($eventID <= 0) {
    $_SESSION['errorMessage'] = "Invalid event selected.";
    header("Location: book_event.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT registrationID
    FROM event_registration
    WHERE eventID = ?
    AND userID = ?
    AND registrationStatus != 'Cancel'
");
$stmt->bind_param("is", $eventID, $userID);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    $_SESSION['errorMessage'] = "You already registered or are already waiting for this event.";
    header("Location: my_registration.php");
    exit();
}

$stmt->close();

$stmt = $conn->prepare("
    SELECT maxParticipants, registrationDeadline, eventStatus
    FROM event
    WHERE eventID = ?
");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event || $event['eventStatus'] !== 'Open' || strtotime($event['registrationDeadline']) < time()) {
    $_SESSION['errorMessage'] = "Registration closed or event is not open.";
    header("Location: book_event.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM event_registration
    WHERE eventID = ?
    AND registrationStatus = 'Success'
");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$totalRegistered = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if ($totalRegistered < (int)$event['maxParticipants']) {
    $stmt = $conn->prepare("
        INSERT INTO event_registration
            (eventID, userID, registrationDate, registrationStatus, cancellationDate)
        VALUES
            (?, ?, NOW(), 'Success', ?)
    ");
    $stmt->bind_param("iss", $eventID, $userID, $event['registrationDeadline']);
    $stmt->execute();
    $stmt->close();

    $_SESSION['successMessage'] = "Registration successful.";
    header("Location: my_registration.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT COALESCE(MAX(position), 0) + 1 AS nextPosition
    FROM waitinglist
    WHERE eventID = ?
");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$position = (int)$stmt->get_result()->fetch_assoc()['nextPosition'];
$stmt->close();

$stmt = $conn->prepare("
    INSERT INTO waitinglist
        (eventID, userID, position, registerAt)
    VALUES
        (?, ?, ?, NOW())
");
$stmt->bind_param("isi", $eventID, $userID, $position);
$stmt->execute();
$stmt->close();

$_SESSION['successMessage'] = "Event is full. You have been added to the waiting list at position " . $position . ".";
header("Location: my_registration.php");
exit();
?>