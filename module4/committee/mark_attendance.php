<?php
// Securely ensure active session context tracking before evaluating variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

$page_title = "Mark Attendance";

requireCommitteeOrAdmin();

$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$regID   = isset($_GET['reg_id']) ? (int)$_GET['reg_id'] : 0;

if ($eventID <= 0 || $regID <= 0) {
    $_SESSION['errorMessage'] = "Invalid attendance request.";
    header("Location: attendance.php");
    exit();
}

// Get registration info
$stmt = $conn->prepare("
    SELECT registrationID,userID
    FROM event_registration
    WHERE registrationID = ?
    LIMIT 1
");

$stmt->bind_param("i", $regID);
$stmt->execute();
$registration = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$registration) {
    $_SESSION['errorMessage'] = "Registration record not found.";
    header("Location: manage_attendance.php?event_id=".$eventID);
    exit();
}

$userID = $registration['userID'];

// Check existing attendance
$stmt = $conn->prepare("
    SELECT attendanceID
    FROM attendance
    WHERE registrationID = ?
");

$stmt->bind_param("i", $regID);
$stmt->execute();
$attendanceExists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$attendanceExists) {

    $status = 'Present';
    $points = 10;
    $volunteer = 0;
    $attendanceQR = 'MANUAL_EVENT_'.$eventID;

    $stmt = $conn->prepare("
        INSERT INTO attendance
        (
            registrationID,
            userID,
            attendanceQR,
            attendanceStatus,
            volunteer,
            points
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "isssii",
        $regID,
        $userID,
        $attendanceQR,
        $status,
        $volunteer,
        $points
    );

    if ($stmt->execute()) {

        if (function_exists('updateStudentPoints')) {
            updateStudentPoints($conn, $userID);
        }

        $_SESSION['successMessage'] =
            "Attendance marked successfully for ".$userID;
    } else {
        $_SESSION['errorMessage'] =
            "Failed to mark attendance.";
    }

    $stmt->close();

} else {

    $_SESSION['errorMessage'] =
        "Attendance already exists for this participant.";
}

header("Location: manage_attendance.php?event_id=".$eventID);
exit();