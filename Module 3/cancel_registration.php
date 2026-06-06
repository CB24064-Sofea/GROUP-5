<?php
require_once 'auth.php';
requireLogin();
if (!isStudent()) { header("Location: index.php"); exit(); }

$regID = $_GET['regID'] ?? 0;
$userID = getUserID();

// Verify ownership
$check = $conn->prepare("SELECT registrationID, eventID FROM event_registration WHERE registrationID=? AND userID=?");
$check->bind_param("is", $regID, $userID);
$check->execute();
$reg = $check->get_result()->fetch_assoc();
if (!$reg) die("Invalid registration.");

$eventID = $reg['eventID'];

// Delete registration
$del = $conn->prepare("DELETE FROM event_registration WHERE registrationID=?");
$del->bind_param("i", $regID);
$del->execute();

// Promote first waiting list member if any
$wl = $conn->prepare("SELECT waitingID, userID FROM waitinglist WHERE eventID=? ORDER BY position LIMIT 1");
$wl->bind_param("i", $eventID);
$wl->execute();
$waiting = $wl->get_result()->fetch_assoc();
if ($waiting) {
    // Insert new registration as Success
    $newReg = $conn->prepare("INSERT INTO event_registration (eventID, userID, registrationDate, registrationStatus, cancellationDate) VALUES (?, ?, NOW(), 'Success', '0000-00-00 00:00:00')");
    $newReg->bind_param("is", $eventID, $waiting['userID']);
    $newReg->execute();
    // Remove from waitinglist
    $delWl = $conn->prepare("DELETE FROM waitinglist WHERE waitingID=?");
    $delWl->bind_param("i", $waiting['waitingID']);
    $delWl->execute();
}

echo "<div class='alert alert-success'>Registration cancelled. Waiting list promoted if any.</div>";
header("Refresh:2; url=my_registrations.php");
?>