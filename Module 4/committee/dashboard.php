<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Committee') { header("Location: ../login.php"); exit(); }
require_once '../includes/header.php';

$userID = $_SESSION['userID'];
$club = $conn->query("SELECT clubID FROM club_committee WHERE userID='$userID'")->fetch_assoc();
$clubID = $club['clubID'];
$events = $conn->query("SELECT eventID, eventName, eventDate, maxParticipants FROM event WHERE clubID='$clubID' ORDER BY eventDate DESC");
?>
<h1 class="page-title">Committee Dashboard</h1>
<h2>Your Club Events</h2>
<table class="data-table">
    <thead><tr><th>Event Name</th><th>Date</th><th>Max Participants</th><th>Actions</th></tr></thead>
    <tbody><?php while($event = $events->fetch_assoc()): ?>
        <tr>
            <td><?= $event['eventName'] ?></td>
            <td><?= $event['eventDate'] ?></td>
            <td><?= $event['maxParticipants'] ?></td>
            <td>
                <a href="manage_attendance.php?event_id=<?= $event['eventID'] ?>" class="action-link">Manage Attendance</a> |
                <a href="show_qr.php?event_id=<?= $event['eventID'] ?>" class="action-link">Show QR</a>
            </td>
        </tr>
    <?php endwhile; ?></tbody>
</table>
<?php require_once '../includes/footer.php'; ?>