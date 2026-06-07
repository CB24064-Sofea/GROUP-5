<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$studentID = $_GET['id'] ?? '';
if (!$studentID) {
    die("Student ID missing.");
}

// Get student details
$studentInfo = $conn->query("SELECT u.name, s.totalPoints, s.recognitionLevel 
                             FROM user u JOIN student s ON u.userID = s.userID 
                             WHERE u.userID = '$studentID'")->fetch_assoc();
if (!$studentInfo) {
    die("Student not found.");
}

// Count events attended
$eventCount = $conn->query("SELECT COUNT(*) as cnt FROM event_registration er 
                            JOIN attendance a ON er.registrationID = a.registrationID 
                            WHERE er.userID='$studentID' AND a.attendanceStatus IN ('Present','Late')")->fetch_assoc()['cnt'];

// Participation history
$history = $conn->query("
    SELECT e.eventName, c.clubName, e.eventDate, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    JOIN club c ON e.clubID = c.clubID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.userID='$studentID' AND er.registrationStatus='Success'
    ORDER BY e.eventDate DESC
");

// Ranking position
$rankQuery = $conn->query("SELECT COUNT(*) + 1 as rank FROM student WHERE totalPoints > (SELECT totalPoints FROM student WHERE userID='$studentID')");
$rank = $rankQuery->fetch_assoc()['rank'];

$page_title = "Student History - " . htmlspecialchars($studentInfo['name']);
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
            <h1 class="page-title">Student History: <?= htmlspecialchars($studentInfo['name']) ?> (<?= $studentID ?>)</h1>

            <div class="stats" style="margin-bottom:20px;">
                <div class="stat-card">
                    <h3>Total Points</h3>
                    <div class="number"><?= $studentInfo['totalPoints'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Recognition</h3>
                    <div class="number" style="font-size:1rem;"><?= htmlspecialchars($studentInfo['recognitionLevel']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Events Attended</h3>
                    <div class="number"><?= $eventCount ?></div>
                </div>
                <div class="stat-card">
                    <h3>Current Rank</h3>
                    <div class="number">#<?= $rank ?></div>
                </div>
            </div>

            <h2>Participation History</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Event</th><th>Club</th><th>Date</th><th>Status</th><th>Points</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $history->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['eventName']) ?></td>
                        <td><?= htmlspecialchars($row['clubName']) ?></td>
                        <td><?= $row['eventDate'] ?></td>
                        <td><?= $row['attendanceStatus'] ?? 'Not marked' ?></td>
                        <td><?= $row['points'] ?? '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="students.php" class="btn btn-cancel">Back to Student List</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>