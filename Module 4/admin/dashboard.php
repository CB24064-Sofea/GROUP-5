<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Admin') { header("Location: ../login.php"); exit(); }
require_once '../includes/header.php';

$totalEvents = $conn->query("SELECT COUNT(*) as total FROM event")->fetch_assoc()['total'];
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM student")->fetch_assoc()['total'];
$totalParticipation = $conn->query("SELECT COUNT(*) as total FROM event_registration WHERE registrationStatus='Success'")->fetch_assoc()['total'];

// Monthly participation trend (last 6 months)
$months = []; $counts = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = $month;
    $cnt = $conn->query("SELECT COUNT(*) as cnt FROM event_registration er 
                         JOIN event e ON er.eventID = e.eventID 
                         WHERE DATE_FORMAT(e.eventDate, '%Y-%m') = '$month' AND er.registrationStatus='Success'")->fetch_assoc()['cnt'];
    $counts[] = $cnt;
}

$topStudents = $conn->query("SELECT s.userID, u.name, s.totalPoints FROM student s JOIN user u ON s.userID = u.userID ORDER BY s.totalPoints DESC LIMIT 5");

// Club attendance rates
$clubData = $conn->query("SELECT c.clubName,
       ROUND(SUM(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END) * 100.0 / COUNT(a.attendanceID), 2) as rate
FROM club c
JOIN event e ON c.clubID = e.clubID
JOIN event_registration er ON e.eventID = er.eventID
JOIN attendance a ON er.registrationID = a.registrationID
GROUP BY c.clubID");
$clubLabels = []; $clubRates = [];
while($row = $clubData->fetch_assoc()) { $clubLabels[] = $row['clubName']; $clubRates[] = $row['rate']; }

$topClubs = $conn->query("SELECT c.clubName, COUNT(e.eventID) as eventCount FROM club c JOIN event e ON c.clubID = e.clubID GROUP BY c.clubID ORDER BY eventCount DESC LIMIT 5");
?>
<div>
    <h1 class="page-title">Admin Dashboard</h1>
    <div class="stats">
        <div class="card"><h3>Total Events</h3><div class="number"><?= $totalEvents ?></div></div>
        <div class="card"><h3>Total Students</h3><div class="number"><?= $totalStudents ?></div></div>
        <div class="card"><h3>Total Participation</h3><div class="number"><?= $totalParticipation ?></div></div>
    </div>

    <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
    </div>

    <h2>Top Active Students</h2>
    <table class="data-table">
        <thead><tr><th>Rank</th><th>Name</th><th>Matric No.</th><th>Points</th></tr></thead>
        <tbody><?php $rank=1; while($row = $topStudents->fetch_assoc()): ?>
            <tr><td><?= $rank++ ?></td><td><?= $row['name'] ?></td><td><?= $row['userID'] ?></td><td><?= $row['totalPoints'] ?></td></tr>
        <?php endwhile; ?></tbody>
    </table>

    <div class="chart-container">
        <canvas id="clubRateChart"></canvas>
    </div>

    <h2>Top Active Clubs (by Events)</h2>
    <table class="data-table">
        <thead><tr><th>Rank</th><th>Club Name</th><th>Number of Events</th></tr></thead>
        <tbody><?php $rank=1; while($row = $topClubs->fetch_assoc()): ?>
            <tr><td><?= $rank++ ?></td><td><?= $row['clubName'] ?></td><td><?= $row['eventCount'] ?></td></tr>
        <?php endwhile; ?></tbody>
    </table>
</div>
<script>
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar', data: { labels: <?= json_encode($months) ?>, datasets: [{ label: 'Participants', data: <?= json_encode($counts) ?> }] }
    });
    new Chart(document.getElementById('clubRateChart'), {
        type: 'pie', data: { labels: <?= json_encode($clubLabels) ?>, datasets: [{ data: <?= json_encode($clubRates) ?> }] }
    });
</script>
<?php require_once '../includes/footer.php'; ?>