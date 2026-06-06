<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Committee') exit;
$eventID = $_GET['event_id'];
$participants = $conn->query("
    SELECT u.name, u.userID, er.registrationID, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN user u ON er.userID = u.userID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.eventID = '$eventID' AND er.registrationStatus='Success'
");
?>
<h1 class="page-title">Mark Attendance - Event ID <?= $eventID ?></h1>
<table class="data-table">
    <thead><tr><th>Matric No.</th><th>Name</th><th>Status</th><th>Points</th><th>Action</th></tr></thead>
    <tbody><?php while($p = $participants->fetch_assoc()): ?>
        <tr>
            <td><?= $p['userID'] ?></td>
            <td><?= $p['name'] ?></td>
            <td><?= $p['attendanceStatus'] ?? 'Not marked' ?></td>
            <td><?= $p['points'] ?? '-' ?></td>
            <td><a href="edit_attendance.php?reg_id=<?= $p['registrationID'] ?>&event_id=<?= $eventID ?>" class="action-link">Edit</a></td>
        </tr>
    <?php endwhile; ?></tbody>
</table>
<a href="dashboard.php" class="btn btn-cancel">Back</a>
<?php require_once '../includes/footer.php'; ?>