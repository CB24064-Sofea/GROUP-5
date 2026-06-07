<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];
$page_title = "My Participation History";

$history = $conn->query("SELECT e.eventName, c.clubName, e.eventDate, a.attendanceStatus, a.points
                         FROM event_registration er
                         JOIN event e ON er.eventID = e.eventID
                         JOIN club c ON e.clubID = c.clubID
                         LEFT JOIN attendance a ON er.registrationID = a.registrationID
                         WHERE er.userID='$userID' AND er.registrationStatus='Success'
                         ORDER BY e.eventDate DESC");
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
            <h1 class="page-title">My Participation History</h1>
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
                        <td><?= $row['attendanceStatus'] ?></td>
                        <td><?= $row['points'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>