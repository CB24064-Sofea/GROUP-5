<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Admin') { header("Location: ../login.php"); exit(); }
require_once '../includes/header.php';

$singleReport = $conn->query("SELECT userID, totalPoints, recognitionLevel FROM student ORDER BY totalPoints DESC LIMIT 10");
$joinReport = $conn->query("
    SELECT e.eventName, 
           COUNT(er.registrationID) as registered,
           SUM(CASE WHEN a.attendanceStatus = 'Present' THEN 1 ELSE 0 END) as present,
           SUM(CASE WHEN a.attendanceStatus = 'Late' THEN 1 ELSE 0 END) as late,
           SUM(CASE WHEN a.attendanceStatus = 'Absent' THEN 1 ELSE 0 END) as absent,
           ROUND(AVG(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END)*100,2) as attendance_rate
    FROM event e
    JOIN event_registration er ON e.eventID = er.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationStatus = 'Success'
    GROUP BY e.eventID
");
?>
<h1 class="page-title">Reports</h1>
<h2>Top Students (Single Table)</h2>
<table class="data-table">
    <thead><tr><th>User ID</th><th>Total Points</th><th>Recognition Level</th></tr></thead>
    <tbody><?php while($row = $singleReport->fetch_assoc()): ?>
        <tr><td><?= $row['userID'] ?></td><td><?= $row['totalPoints'] ?></td><td><?= $row['recognitionLevel'] ?></td></tr>
    <?php endwhile; ?></tbody>
</table>

<h2>Event Attendance Summary (Join Table)</h2>
<table class="data-table">
    <thead><tr><th>Event Name</th><th>Registered</th><th>Present</th><th>Late</th><th>Absent</th><th>Attendance Rate (%)</th></tr></thead>
    <tbody><?php while($row = $joinReport->fetch_assoc()): ?>
        <tr>
            <td><?= $row['eventName'] ?></td>
            <td><?= $row['registered'] ?></td>
            <td><?= $row['present'] ?></td>
            <td><?= $row['late'] ?></td>
            <td><?= $row['absent'] ?></td>
            <td><?= $row['attendance_rate'] ?></td>
        </tr>
    <?php endwhile; ?></tbody>
</table>
<?php require_once '../includes/footer.php'; ?>