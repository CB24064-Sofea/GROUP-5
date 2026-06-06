<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Student') { header("Location: ../login.php"); exit(); }
require_once '../includes/header.php';
$userID = $_SESSION['userID'];

$student = $conn->query("SELECT totalPoints, recognitionLevel FROM student WHERE userID='$userID'")->fetch_assoc();
$eventCount = $conn->query("SELECT COUNT(*) as cnt FROM event_registration er 
                            JOIN attendance a ON er.registrationID = a.registrationID 
                            WHERE er.userID='$userID' AND a.attendanceStatus IN ('Present','Late')")->fetch_assoc()['cnt'];

$history = $conn->query("SELECT e.eventName, c.clubName, e.eventDate, a.attendanceStatus, a.points
                         FROM event_registration er
                         JOIN event e ON er.eventID = e.eventID
                         JOIN club c ON e.clubID = c.clubID
                         LEFT JOIN attendance a ON er.registrationID = a.registrationID
                         WHERE er.userID='$userID' AND er.registrationStatus='Success'
                         ORDER BY e.eventDate DESC");

$ranking = $conn->query("SELECT u.name, s.totalPoints FROM student s JOIN user u ON s.userID = u.userID ORDER BY s.totalPoints DESC LIMIT 10");
?>
<h1 class="page-title">Student Dashboard</h1>
<div class="stats">
    <div class="card"><h3>Total Points</h3><div class="number"><?= $student['totalPoints'] ?></div></div>
    <div class="card"><h3>Recognition</h3><div class="number" style="font-size:1rem;"><?= $student['recognitionLevel'] ?></div></div>
    <div class="card"><h3>Events Attended</h3><div class="number"><?= $eventCount ?></div></div>
</div>

<h2>My Participation History</h2>
<table class="data-table">
    <thead><tr><th>Event</th><th>Club</th><th>Date</th><th>Status</th><th>Points</th></tr></thead>
    <tbody><?php while($row = $history->fetch_assoc()): ?>
        <tr>
            <td><?= $row['eventName'] ?></td>
            <td><?= $row['clubName'] ?></td>
            <td><?= $row['eventDate'] ?></td>
            <td><?= $row['attendanceStatus'] ?></td>
            <td><?= $row['points'] ?></td>
        </tr>
    <?php endwhile; ?></tbody>
</table>

<h2>Top 10 Ranking</h2>
<table class="data-table">
    <thead><tr><th>Rank</th><th>Name</th><th>Total Points</th></tr></thead>
    <tbody><?php $rank=1; while($row = $ranking->fetch_assoc()): ?>
        <tr><td><?= $rank++ ?></td><td><?= $row['name'] ?></td><td><?= $row['totalPoints'] ?></td></tr>
    <?php endwhile; ?></tbody>
</table>
<?php require_once '../includes/footer.php'; ?>