<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Committee') { header("Location: ../login.php"); exit(); }
$page_title = "Committee Dashboard";

$userID = $_SESSION['userID'];
$club = $conn->query("SELECT clubID FROM club_committee WHERE userID='$userID'")->fetch_assoc();
$clubID = $club['clubID'];
$events = $conn->query("SELECT eventID, eventName, eventDate, maxParticipants FROM event WHERE clubID='$clubID' ORDER BY eventDate DESC");
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
            <h1 class="page-title">Attendance</h1>
                <h2>Your Club Events</h2>
                    <table class="data-table">
                    <thead><tr><th>Event Name</th><th>Date</th><th>Max Participants</th><th>Actions</th></tr></thead>
                    <tbody><?php while($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?= $event['eventName'] ?></td>
                        <td><?= $event['eventDate'] ?></td>
                        <td><?= $event['maxParticipants'] ?></td>
                        <td>
                            <a href="manage_attendance.php?event_id=<?= $event['eventID'] ?>" class="action-link">Manage Attendance</a>
                        </td>
                    </tr>
                    <?php endwhile; ?></tbody>
                    </table>
            <?php require_once '../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>