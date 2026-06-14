<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';


if (!isset($_SESSION['userID']) || (!isStudent() && !isCommittee())) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$userID = $_SESSION['userID'];
$page_title = "My Participation History";

$studentQuery = $conn->prepare("
    SELECT
        COALESCE(totalPoints,0) AS totalPoints,
        COALESCE(recognitionLevel,'No Level') AS recognitionLevel
    FROM student
    WHERE userID = ?
    LIMIT 1
");
$studentQuery->bind_param("s", $userID);
$studentQuery->execute();
$student = $studentQuery->get_result()->fetch_assoc();
$studentQuery->close();

if (!$student) {
    $student = ['totalPoints' => 0, 'recognitionLevel' => 'No Level'];
}

$attendanceQuery = $conn->prepare("
    SELECT COUNT(*) as cnt 
    FROM event_registration er 
    JOIN attendance a ON er.registrationID = a.registrationID 
    WHERE er.userID = ? AND a.attendanceStatus IN ('Present', 'Late')
");
$attendanceQuery->bind_param("s", $userID);
$attendanceQuery->execute();
$eventCount = $attendanceQuery->get_result()->fetch_assoc()['cnt'] ?? 0;
$attendanceQuery->close();

$historyQuery = $conn->prepare("
    SELECT e.eventName, c.clubName, e.eventDate, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    JOIN club c ON e.clubID = c.clubID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.userID = ? AND er.registrationStatus = 'Success'
    ORDER BY e.eventDate DESC
");
$historyQuery->bind_param("s", $userID);
$historyQuery->execute();
$history = $historyQuery->get_result();
$historyQuery->close();

$ranking = $conn->query("
    SELECT u.name, s.totalPoints
    FROM student s
    JOIN user u ON s.userID = u.userID
    ORDER BY s.totalPoints DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../../topbar.php'; ?>

    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">My Participation & Points Log</h2>

                <div class="stats">
                    <div class="stat-card" style="background-color: #ffffff; border: 1px solid #b2bec3;">
                        <h3>Total Accumulated Points</h3>
                        <div class="number" style="color: #27ae60;"><?= (int)$student['totalPoints'] ?></div>
                    </div>
                    <div class="stat-card" style="background-color: #ffffff; border: 1px solid #b2bec3;">
                        <h3>Recognition Tier Level</h3>
                        <div class="number" style="font-size: 1.4rem; color: #3498db;"><?= htmlspecialchars($student['recognitionLevel']) ?></div>
                    </div>
                    <div class="stat-card" style="background-color: #ffffff; border: 1px solid #b2bec3;">
                        <h3>Verified Event Attendances</h3>
                        <div class="number"><?= (int)$eventCount ?></div>
                    </div>
                </div>

                <div class="form-card-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">Participation History Records</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Club</th>
                                <th>Date</th>
                                <th>Attendance Status</th>
                                <th>Points Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($history->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No completed history data files found.</td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php while($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['eventName']) ?></strong></td>
                                <td><?= htmlspecialchars($row['clubName']) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($row['eventDate']) ?></td>
                                <td style="text-align: center;"><span class="status-badge"><?= htmlspecialchars($row['attendanceStatus'] ?? 'Not marked') ?></span></td>
                                <td style="text-align: center; font-weight: bold; color: #27ae60;"><?= $row['points'] !== null ? "+".(int)$row['points'] : '-' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-card-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">Top 10 Performance Standings</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank Standing</th>
                                <th>Student Full Name</th>
                                <th>Total Points Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; while($row = $ranking->fetch_assoc()): ?>
                            <tr>
                                <td style="text-align: center;"><strong>#<?= $rank++ ?></strong></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td style="text-align: center; font-weight: 700; color: #2c3e50;"><?= (int)$row['totalPoints'] ?> pts</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>