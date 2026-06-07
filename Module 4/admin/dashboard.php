<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

$page_title = "Admin Dashboard";

// ----- Stats -----
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM student")->fetch_assoc()['total'];
$totalClubs = $conn->query("SELECT COUNT(*) as total FROM club")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(*) as total FROM event")->fetch_assoc()['total'];

// ----- Event Popularity (attendance rate per event) -----
$popularity = $conn->query("
    SELECT e.eventName, 
           ROUND(100 * SUM(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END) / COUNT(er.registrationID), 2) AS rate
    FROM event e
    JOIN event_registration er ON e.eventID = er.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationStatus = 'Success'
    GROUP BY e.eventID
    ORDER BY rate DESC
");

// ----- Monthly Event Activity Trend (based on actual event dates in DB) -----
$monthlyTrend = $conn->query("
    SELECT DATE_FORMAT(e.eventDate, '%Y-%m') AS month, 
           COUNT(er.registrationID) AS participants
    FROM event e
    JOIN event_registration er ON e.eventID = er.eventID
    WHERE er.registrationStatus = 'Success'
    GROUP BY DATE_FORMAT(e.eventDate, '%Y-%m')
    ORDER BY month ASC
");

$months = [];
$counts = [];
while($row = $monthlyTrend->fetch_assoc()) {
    $months[] = $row['month'];
    $counts[] = $row['participants'];
}

// ----- Club Distribution by Category (number of members per club) -----
$clubDist = $conn->query("
    SELECT c.clubName, COUNT(m.membershipID) AS member_count
    FROM club c
    LEFT JOIN membership m ON c.clubID = m.clubID AND m.status = 'Active'
    GROUP BY c.clubID
");
$clubNames = []; $memberCounts = [];
while($row = $clubDist->fetch_assoc()) {
    $clubNames[] = $row['clubName'];
    $memberCounts[] = $row['member_count'];
}

// ----- Most Active Club (most events organized) -----
$mostActiveClub = $conn->query("
    SELECT c.clubName, COUNT(e.eventID) AS event_count
    FROM club c
    JOIN event e ON c.clubID = e.clubID
    GROUP BY c.clubID
    ORDER BY event_count DESC
    LIMIT 1
")->fetch_assoc();

// ----- Filters for Event Details table -----
$selectedClub = $_GET['club'] ?? '';
$selectedEvent = $_GET['event'] ?? '';

$eventDetailsQuery = "
    SELECT e.eventName, c.clubName, 
           COUNT(er.registrationID) AS registered,
           SUM(CASE WHEN a.attendanceStatus = 'Present' THEN 1 ELSE 0 END) AS present,
           SUM(CASE WHEN a.attendanceStatus = 'Late' THEN 1 ELSE 0 END) AS late,
           SUM(CASE WHEN a.attendanceStatus = 'Absent' THEN 1 ELSE 0 END) AS absent,
           ROUND(100 * SUM(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END) / COUNT(er.registrationID), 2) AS rate
    FROM event e
    JOIN club c ON e.clubID = c.clubID
    JOIN event_registration er ON e.eventID = er.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationStatus = 'Success'
";

if ($selectedClub) {
    $eventDetailsQuery .= " AND c.clubID = " . intval($selectedClub);
}
if ($selectedEvent) {
    $eventDetailsQuery .= " AND e.eventID = " . intval($selectedEvent);
}

$eventDetailsQuery .= " GROUP BY e.eventID ORDER BY e.eventDate DESC";
$eventDetails = $conn->query($eventDetailsQuery);

// ----- Dropdown options -----
$clubsOptions = $conn->query("SELECT clubID, clubName FROM club ORDER BY clubName");
$eventsOptions = $conn->query("SELECT eventID, eventName FROM event ORDER BY eventName");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   
</head>
<body>
    <?php include '../topbar.php'; ?>
    <div class="app-container">
        <?php include '../sidebar.php'; ?>
        <div class="main-content">
            <h1 class="page-title">Dashboard</h1>

            <!-- Stats Cards -->
            <div class="stats">
                <div class="stat-card"><h3>Total Student</h3><div class="number"><?= $totalStudents ?></div></div>
                <div class="stat-card"><h3>Total Clubs</h3><div class="number"><?= $totalClubs ?></div></div>
                <div class="stat-card"><h3>Total Events</h3><div class="number"><?= $totalEvents ?></div></div>
            </div>

            <!-- Event Popularity -->
            <h2>Event Popularity (Attendance rate per event)</h2>
            <table class="data-table">
                <thead><tr><th>Event Name</th><th>Attendance Rate (%)</th></tr></thead>
                <tbody>
                    <?php while($row = $popularity->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['eventName']) ?></td>
                        <td><?= $row['rate'] ?>%</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Two columns: Monthly Trend (left) and Club Distribution (right) -->
            <div class="charts-row">
                <!-- Monthly Event Activity Trend -->
                <div class="chart-box">
                    <h3>Monthly Event Activity Trend</h3>
                    <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
                </div>

                <!-- Club Distribution by Category -->
                <div class="chart-box">
                    <h3>Club Distribution by Category</h3>
                    <canvas id="clubDistChart" style="max-height: 300px;"></canvas>
                    <!-- Most Active Club placed directly below the pie chart -->
                    <div style="margin-top: 20px;">
                        <div class="stat-card most-active-card">
                            <h4>Most Active Club</h4>
                            <h3><?= htmlspecialchars($mostActiveClub['clubName']) ?></h3>
                            <p><?= $mostActiveClub['event_count'] ?> events organized</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Details Table with Filters -->
            <h2>Event Details</h2>
            <form method="get" style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <select name="club" class="input-control-select" style="width:200px;">
                    <option value="">All Clubs</option>
                    <?php while($c = $clubsOptions->fetch_assoc()): ?>
                        <option value="<?= $c['clubID'] ?>" <?= $selectedClub == $c['clubID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['clubName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="event" class="input-control-select" style="width:200px;">
                    <option value="">All Events</option>
                    <?php while($e = $eventsOptions->fetch_assoc()): ?>
                        <option value="<?= $e['eventID'] ?>" <?= $selectedEvent == $e['eventID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['eventName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="dashboard.php" class="btn btn-cancel">Reset</a>
            </form>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Club</th>
                        <th>Registered</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Attendance Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $eventDetails->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['eventName']) ?></td>
                        <td><?= htmlspecialchars($row['clubName']) ?></td>
                        <td><?= $row['registered'] ?></td>
                        <td><?= $row['present'] ?></td>
                        <td><?= $row['absent'] ?></td>
                        <td><?= $row['rate'] ?>%</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>

<script>
    // Monthly trend chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{ label: 'Participants', data: <?= json_encode($counts) ?> }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });

    // Club distribution pie chart
    new Chart(document.getElementById('clubDistChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($clubNames) ?>,
            datasets: [{ data: <?= json_encode($memberCounts) ?> }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
</script>