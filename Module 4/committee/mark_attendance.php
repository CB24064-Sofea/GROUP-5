<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Student') die("Please login as student to mark attendance.");
$eventID = $_GET['event_id'];
$userID = $_SESSION['userID'];

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../topbar.php'; ?>
    <div class="app-container">
        <?php include '../sidebar.php'; ?>
        <div class="main-content">
            <h1 class="page-title">Mark Attendance</h1>
        <?php
        $reg = $conn->query("SELECT registrationID FROM event_registration WHERE eventID='$eventID' AND userID='$userID' AND registrationStatus='Success'");
        if ($reg->num_rows == 0) die("You are not registered for this event.");
        $regID = $reg->fetch_assoc()['registrationID'];

        $exists = $conn->query("SELECT attendanceID FROM attendance WHERE registrationID='$regID'");
        if ($exists->num_rows == 0) {
            $status = 'Present';
            $points = 10;
            $stmt = $conn->prepare("INSERT INTO attendance (registrationID, userID, attendanceStatus, points) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $regID, $userID, $status, $points);
            $stmt->execute();
            updateStudentPoints($conn, $userID);
            echo "<div class='alert alert-success'>Attendance marked successfully! Points added: $points</div>";
        } else {
            echo "<div class='alert alert-info'>Attendance already marked.</div>";
        }
        ?>
        <a href="../student/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>