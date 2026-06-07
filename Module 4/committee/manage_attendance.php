<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Committee') {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get the club ID of this committee member
$club = $conn->query("SELECT clubID FROM club_committee WHERE userID='$userID'")->fetch_assoc();
if (!$club) {
    die("You are not assigned to any club.");
}
$clubID = $club['clubID'];

// Get the event ID from URL
$eventID = $_GET['event_id'] ?? 0;
if (!$eventID) {
    die("Event ID missing.");
}

// Fetch event details (name, date, time)
$eventInfo = $conn->query("SELECT eventName, eventDate, eventTime FROM event WHERE eventID = '$eventID'")->fetch_assoc();
if (!$eventInfo) {
    die("Event not found.");
}
$eventName = $eventInfo['eventName'];
$eventDate = $eventInfo['eventDate'];
$eventTime = date("h:i A", strtotime($eventInfo['eventTime'])); // format time nicely

// Fetch participants for this event
$participants = $conn->query("
    SELECT u.name, u.userID, er.registrationID, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN user u ON er.userID = u.userID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.eventID = '$eventID' AND er.registrationStatus='Success'
");

$page_title = "Mark Attendance";
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
            <h1 class="page-title">Mark Attendance - Event: <?= htmlspecialchars($eventName) ?></h1>
            
            <div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <strong>Event Date:</strong> <?= htmlspecialchars($eventDate) ?> &nbsp;|&nbsp;
                <strong>Event Time:</strong> <?= htmlspecialchars($eventTime) ?>
            </div>

            <div class="qr-code-container">
                <a href="show_qr.php?event_id=<?= $eventID ?>" class="btn btn-primary">Show QR Code</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>Matric No.</th><th>Name</th><th>Status</th><th>Points</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while ($p = $participants->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['userID']) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['attendanceStatus'] ?? 'Not marked' ?></td>
                        <td><?= $p['points'] ?? '-' ?></td>
                        <td><a href="edit_attendance.php?reg_id=<?= $p['registrationID'] ?>&event_id=<?= $eventID ?>" class="action-link">Edit</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="dashboard.php" class="btn btn-cancel">Back</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
