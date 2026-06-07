<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

$page_title = "Reports";
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
            <h1 class="page-title">Reports</h1>

            <h2>Most Active Clubs</h2>
            <?php
            $clubReport = $conn->query("
                SELECT c.clubName, COUNT(e.eventID) AS total_events
                FROM club c
                JOIN event e ON c.clubID = e.clubID
                GROUP BY c.clubID
                ORDER BY total_events DESC
                LIMIT 5
            ");
            ?>
            <table class="data-table">
                <thead><tr><th>Club Name</th><th>Total Events Organized</th></tr></thead>
                <tbody>
                    <?php while($row = $clubReport->fetch_assoc()): ?>
                    <tr><td><?= htmlspecialchars($row['clubName']) ?></td><td><?= $row['total_events'] ?></td></tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2>Event Attendance Summary</h2>
            <?php
            $joinReport = $conn->query("
                SELECT 
                    e.eventName,
                    COUNT(er.registrationID) AS registered,
                    SUM(CASE WHEN a.attendanceStatus = 'Present' THEN 1 ELSE 0 END) AS present,
                    SUM(CASE WHEN a.attendanceStatus = 'Late' THEN 1 ELSE 0 END) AS late,
                    SUM(CASE WHEN a.attendanceStatus = 'Absent' THEN 1 ELSE 0 END) AS absent,
                    ROUND(
                        (SUM(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END) * 100.0) 
                        / COUNT(er.registrationID), 2
                    ) AS attendance_rate
                FROM event e
                JOIN event_registration er ON e.eventID = er.eventID
                LEFT JOIN attendance a ON er.registrationID = a.registrationID
                WHERE er.registrationStatus = 'Success'
                GROUP BY e.eventID
                ORDER BY e.eventDate DESC
            ");
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Registered</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Attendance Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $joinReport->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['eventName']) ?></td>
                        <td><?= $row['registered'] ?></td>
                        <td><?= $row['present'] ?></td>
                        <td><?= $row['late'] ?></td>
                        <td><?= $row['absent'] ?></td>
                        <td><?= $row['attendance_rate'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2>Points per Student per Event</h2>
            <?php
            $avgPointsReport = $conn->query("
                SELECT u.name AS student_name, 
                       COUNT(DISTINCT er.eventID) AS events_attended,
                       SUM(a.points) AS total_points,
                       ROUND(SUM(a.points) / COUNT(DISTINCT er.eventID), 2) AS avg_points_per_event
                FROM attendance a
                JOIN event_registration er ON a.registrationID = er.registrationID
                JOIN user u ON er.userID = u.userID
                WHERE er.registrationStatus = 'Success'
                GROUP BY er.userID
                ORDER BY avg_points_per_event DESC
            ");
            ?>
            <table class="data-table">
                <thead>
                    <tr><th>Student Name</th><th>Events Attended</th><th>Total Points</th><th>Avg Points per Event</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $avgPointsReport->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= $row['events_attended'] ?></td>
                        <td><?= $row['total_points'] ?></td>
                        <td><?= $row['avg_points_per_event'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2>Student Points (Overall Semester & Top Students)</h2>
            <?php
            $allStudents = $conn->query("
                SELECT s.userID, u.name, s.totalPoints, s.recognitionLevel 
                FROM student s
                JOIN user u ON s.userID = u.userID
                ORDER BY s.totalPoints DESC
            ");
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Total Points (Semester)</th>
                        <th>Recognition Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; while($row = $allStudents->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($row['userID']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['totalPoints'] ?></td>
                        <td><?= htmlspecialchars($row['recognitionLevel']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>