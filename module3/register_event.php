<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

if (!isStudent() && !isCommittee()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userID = getUserID();

// Context-aware dynamic fallback routing
$fallbackPage = "/GROUP%205/module3/book_event.php";
$successPage = "/GROUP%205/module3/my_registration.php";

if ($eventID <= 0) {
    $_SESSION['errorMessage'] = "Invalid event selected.";
    header("Location: " . $fallbackPage);
    exit();
}

// Check if user is already registered or on the waiting list
$stmt = $conn->prepare("
    SELECT registrationID 
    FROM event_registration 
    WHERE eventID = ? 
    AND userID = ? 
    AND registrationStatus != 'Cancel'
");
$stmt->bind_param("is", $eventID, $userID);
$stmt->execute();
$alreadyRegistered = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($alreadyRegistered) {
    $_SESSION['errorMessage'] = "You are already registered or are on the waiting list for this event.";
    header("Location: " . $successPage);
    exit();
}

// Fetch event logistics parameters
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
    $_SESSION['errorMessage'] = "Registration is closed or the event is no longer open for entries.";
    header("Location: " . $fallbackPage);
    exit();
}

// Check current capacity status
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

// Option A: Active slot is open
if ($totalRegistered < (int)$event['maxParticipants']) {
    $stmt = $conn->prepare("
    INSERT INTO event_registration
        (eventID, userID, registrationDate, registrationStatus)
    VALUES
        (?, ?, NOW(), 'Success')
    ");
    $stmt->bind_param("is", $eventID, $userID);
    
    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Registration confirmed successfully!";
    } else {
        $_SESSION['errorMessage'] = "A system error occurred while compiling your reservation record.";
    }
    $stmt->close();

    header("Location: " . $successPage);
    exit();
}

// Option B: Full capacity - Route request into the waiting list table queue sequence
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

if ($stmt->execute()) {
    $_SESSION['successMessage'] = "The event is currently full. You have been added to the waiting list at position #" . $position . ".";
} else {
    $_SESSION['errorMessage'] = "Failed to place your account into the waiting queue.";
}
$stmt->close();

header("Location: " . $successPage);
exit();
?>