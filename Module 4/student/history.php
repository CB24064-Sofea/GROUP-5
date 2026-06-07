<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];
$page_title = "My Participation History";

// Get student stats
$student = $conn->query("SELECT totalPoints, recognitionLevel FROM student WHERE userID='$userID'")->fetch_assoc();
$eventCount = $conn->query("SELECT COUNT(*) as cnt FROM event_registration er 
                            JOIN attendance a ON er.registrationID = a.registrationID 
                            WHERE er.userID='$userID' AND a.attendanceStatus IN ('Present','Late')")->fetch_assoc()['cnt'];

// Participation history (without participantRole)
$history = $conn->query("
    SELECT e.eventName, c.clubName, e.eventDate, 
           a.attendanceStatus, a.points
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    JOIN club c ON e.clubID = c.clubID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.userID='$userID' AND er.registrationStatus='Success'
    ORDER BY e.eventDate DESC
");

// Ranking (top 10 students)
$ranking = $conn->query("
    SELECT u.name, s.totalPoints 
    FROM student s 
    JOIN user u ON s.userID = u.userID 
    ORDER BY s.totalPoints DESC 
    LIMIT 10
");
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
            <h1 class="page-title">My Participation & Points</h1>

            <!-- Stats Cards -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Points</h3>
                    <div class="number"><?= $student['totalPoints'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Recognition</h3>
                    <div class="number" style="font-size:1rem;"><?= htmlspecialchars($student['recognitionLevel']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Events Attended</h3>
                    <div class="number"><?= $eventCount ?></div>
                </div>
            </div>

            <!-- Participation History Table (Role column removed) -->
            <h2>Participation History</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Club</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Points</th>
                    </tr>
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

            <!-- Ranking Table -->
            <h2>Ranking</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; while($row = $ranking->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['totalPoints'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>