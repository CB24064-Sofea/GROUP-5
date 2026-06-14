<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Strict Role Security Verification Check
if (!isAdmin()) { 
    header("Location: ../login.php"); 
    exit(); 
}

$page_title = "Admin Dashboard - FK Portal";

// -------------------------------------------------------------
// CORE QUERIES FOR METRIC HEADER COUNTERS (Requirements 1-5)
// -------------------------------------------------------------

// 1. Total number of registered students 
$totalStudents = (int)$conn->query("
    SELECT COUNT(*) as total
    FROM user
    WHERE role IN ('Student', 'Committee')
")->fetch_assoc()['total'];

// 2. Total number of clubs
$totalClubs = (int)$conn->query("SELECT COUNT(*) as total FROM club")->fetch_assoc()['total'];

// 3. Total number of active clubs 
$totalActiveClubs = (int)$conn->query("SELECT COUNT(*) as total FROM club WHERE status = 'Active'")->fetch_assoc()['total'];

// 4. Total Number of upcoming events 
$totalUpcomingEvents = (int)$conn->query("SELECT COUNT(*) as total FROM event WHERE eventDate >= CURDATE() AND eventStatus = 'Open'")->fetch_assoc()['total'];

// 5. Total number of membership in our club (Total active system enrollment records)
$totalMemberships = (int)$conn->query("SELECT COUNT(*) as total FROM membership WHERE status = 'Active'")->fetch_assoc()['total'];


// -------------------------------------------------------------
// ANALYTICAL WORKSPACE COMPONENT DATA QUERIES (Requirements 6-10)
// -------------------------------------------------------------

// 6. Event Popularity Data (Attendance rate per event)
$popularityQuery = $conn->query("
    SELECT e.eventName,
           COUNT(er.registrationID) AS total_reg,
           ROUND(
               100 * SUM(
                   CASE
                       WHEN a.attendanceStatus IN ('Present','Late')
                       THEN 1 ELSE 0
                   END
               ) / COUNT(er.registrationID),
           2) AS rate
    FROM event e
    JOIN event_registration er
        ON e.eventID = er.eventID
    LEFT JOIN attendance a
        ON er.registrationID = a.registrationID
    WHERE er.registrationStatus = 'Success'
    GROUP BY e.eventID
    HAVING COUNT(er.registrationID) > 0
    ORDER BY rate DESC
");
$popularityData = [];
if ($popularityQuery) {
    $popularityData = $popularityQuery->fetch_all(MYSQLI_ASSOC);
}

// 7. Club Status Distribution Chart
$chartCategories = [];
$chartCategoryCounts = [];

$clubDistQuery = $conn->query("
    SELECT status, COUNT(*) AS category_count
    FROM club
    GROUP BY status
");

if ($clubDistQuery) {
    while ($row = $clubDistQuery->fetch_assoc()) {
        $chartCategories[] = $row['status'];
        $chartCategoryCounts[] = (int)$row['category_count'];
    }
}

// 8 & 9. Display & Graph details for the Most Active Club (Highest host count)
$activeClubsQuery = $conn->query("
    SELECT c.clubName, COUNT(e.eventID) AS event_count
    FROM club c
    LEFT JOIN event e ON c.clubID = e.clubID
    GROUP BY c.clubID
    ORDER BY event_count DESC
");

$graphClubNames = [];
$graphClubCounts = [];
$mostActiveClubName = "None";
$mostActiveClubCount = 0;

$rank = 0;
while($row = $activeClubsQuery->fetch_assoc()) {
    if ($rank === 0) {
        $mostActiveClubName = $row['clubName'];
        $mostActiveClubCount = (int)$row['event_count'];
    }
    // Limit to top 6 clubs in the trend layout chart to keep view scannable
    if ($rank < 6) {
        $graphClubNames[] = $row['clubName'];
        $graphClubCounts[] = (int)$row['event_count'];
    }
    $rank++;
}

// 10. Table of Recent user registrations timeline logic
$recentRegistrations = [];
$recentQuery = $conn->query("
    SELECT u.name, c.clubName, m.joinDate 
    FROM membership m
    JOIN user u ON m.userID = u.userID
    JOIN club c ON m.clubID = c.clubID
    ORDER BY m.joinDate DESC, m.membershipID DESC
    LIMIT 3
");
if ($recentQuery) {
    $recentRegistrations = $recentQuery->fetch_all(MYSQLI_ASSOC);
}

//11. Montthly Event Acticity Trend
$monthlyTrend = $conn->query("
    SELECT
        DATE_FORMAT(eventDate, '%b') AS monthName,
        COUNT(*) AS totalEvents
    FROM event
    GROUP BY MONTH(eventDate)
    ORDER BY MONTH(eventDate)
");

$trendMonths = [];
$trendCounts = [];

while($row = $monthlyTrend->fetch_assoc()){
    $trendMonths[] = $row['monthName'];
    $trendCounts[] = (int)$row['totalEvents'];
}
// -------------------------------------------------------------
// MAIN TABULAR REPOSITORIES FILTERS (Requirement 10)
// -------------------------------------------------------------
$selectedClub = isset($_GET['club']) ? trim($_GET['club']) : '';
$selectedEvent = isset($_GET['event']) ? trim($_GET['event']) : '';

$eventDetailsQuery = "
    SELECT e.eventName, c.clubName, 
           COUNT(er.registrationID) AS registered,
           SUM(CASE WHEN a.attendanceStatus IN ('Present', 'Late') THEN 1 ELSE 0 END) AS present,
           SUM(CASE WHEN a.attendanceStatus = 'Absent' THEN 1 ELSE 0 END) AS absent,
           ROUND(100 * SUM(CASE WHEN a.attendanceStatus IN ('Present','Late') THEN 1 ELSE 0 END) / COUNT(er.registrationID), 2) AS rate
    FROM event e
    JOIN club c ON e.clubID = c.clubID
    JOIN event_registration er ON e.eventID = er.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationStatus = 'Success'
";

if (!empty($selectedClub)) {
    $eventDetailsQuery .= " AND c.clubID = " . intval($selectedClub);
}
if (!empty($selectedEvent)) {
    $eventDetailsQuery .= " AND e.eventID = " . intval($selectedEvent);
}

$eventDetailsQuery .= " GROUP BY e.eventID ORDER BY e.eventDate DESC";
$eventDetails = $conn->query($eventDetailsQuery);

$clubsOptions = $conn->query("SELECT clubID, clubName FROM club ORDER BY clubName ASC");
$eventsOptions = $conn->query("SELECT eventID, eventName FROM event ORDER BY eventName ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(5, 1-fr); gap: 15px; margin-bottom: 25px; }
        .metric-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); position: relative; }
        .metric-title { font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; margin: 0 0 8px 0; letter-spacing: 0.3px; }
        .metric-value { font-size: 1.8rem; font-weight: 700; color: #0a2540; margin: 0; }
        .metric-badge { position: absolute; bottom: 18px; right: 18px; font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .row-container { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .analytics-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; flex-direction: column; }
        .card-heading { font-size: 0.95rem; color: #0a2540; font-weight: 700; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 0.2px; }
        .progress-item { margin-bottom: 12px; }
        .progress-labels { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px; }
        .progress-track { background: #f1f5f9; height: 8px; border-radius: 4px; overflow: hidden; }
        .progress-fill { background: #0a2540; height: 100%; border-radius: 4px; }
        .recent-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f8fafc; }
        .avatar-box { background: #eff6ff; color: #1e40af; font-weight: 700; font-size: 0.85rem; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body style="background-color: #f8fafc;">

    <?php include __DIR__ . '/../../topbar.php'; ?>

    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack" style="gap: 0;">
                
                <h2 class="page-title" style="margin-bottom: 20px;">System Administrative Control Center</h2>

                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; width: 100%;">
                    <div class="metric-card" style="flex: 1; min-width: 180px;">
                        <p class="metric-title">Total Registered Students</p>
                        <h3 class="metric-value"><?= number_format($totalStudents) ?></h3>
                        <span class="metric-badge" style="background: #eafaf1; color: #2ecc71;">+12%</span>
                    </div>
                    <div class="metric-card" style="flex: 1; min-width: 180px;">
                        <p class="metric-title">Total Clubs</p>
                        <h3 class="metric-value"><?= $totalClubs ?></h3>
                        <span class="metric-badge" style="background: #f8fafc; color: #64748b;">Active</span>
                    </div>
                    <div class="metric-card" style="flex: 1; min-width: 180px;">
                        <p class="metric-title">Active Clubs Registry</p>
                        <h3 class="metric-value"><?= $totalActiveClubs ?></h3>
                        <div style="background: #cbd5e1; height: 4px; border-radius: 2px; margin-top: 12px; overflow:hidden;">
                            <div style="background: #0284c7; width: <?= $totalClubs > 0 ? ($totalActiveClubs/$totalClubs)*100 : 0 ?>%; height: 100%;"></div>
                        </div>
                    </div>
                    <div class="metric-card" style="flex: 1; min-width: 180px;">
                        <p class="metric-title">Upcoming Events</p>
                        <h3 class="metric-value"><?= $totalUpcomingEvents ?></h3>
                        <span class="metric-badge" style="right: 15px; top: 18px; color: #0284c7; font-size: 1.1rem;">📅</span>
                    </div>
                    <div class="metric-card" style="flex: 1; min-width: 180px;">
                        <p class="metric-title">Total Memberships</p>
                        <h3 class="metric-value"><?= number_format($totalMemberships) ?></h3>
                        <span class="metric-badge" style="background: #eafaf1; color: #2ecc71;">+5%</span>
                    </div>
                </div>

                <div class="row-container" style="display: flex; flex-wrap: wrap; width: 100%;">
                    
                    <div class="analytics-card" style="flex: 1.2; min-width: 320px;">
                        <h4 class="card-heading">Event Popularity <span style="font-weight: normal; font-size: 11px; text-transform: none; color: #64748b; float: right;">Attendance Rate</span></h4>
                        <div style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
                            <?php if(empty($popularityData)): ?>
                                <p style="text-align: center; color: #94a3b8; font-size: 0.9rem;">No attendance matrices logged.</p>
                            <?php else: ?>
                                <?php foreach($popularityData as $pop): ?>
                                    <div class="progress-item">
                                        <div class="progress-labels">
                                            <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($pop['eventName']) ?></span>
                                            <span style="font-weight: bold; color: #0a2540;"><?= $pop['rate'] ?>%</span>
                                        </div>
                                        <div class="progress-track">
                                            <div class="progress-fill" style="width: <?= $pop['rate'] ?>%; background: #0a2540;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="analytics-card" style="flex: 1; min-width: 260px;">
                        <h4 class="card-heading">Club Category Mix Ratio</h4>
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 180px; position: relative;">
                            <canvas id="clubDistPieChart"></canvas>
                        </div>
                    </div>

                    <div class="analytics-card" style="flex: 1; min-width: 260px; background: #0a2540; color: #ffffff; border: none; justify-content: space-between;">
                        <div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; font-weight: bold;">Most Active Club Context</span>
                            <h3 style="margin: 5px 0 15px 0; font-size: 1.4rem; font-weight: 700; color: #ffffff;"><?= htmlspecialchars($mostActiveClubName) ?></h3>
                            
                            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 6px; flex: 1;">
                                    <span style="font-size: 11px; display: block; opacity: 0.8;">Hosted Metrics</span>
                                    <strong style="font-size: 1.2rem;"><?= $mostActiveClubCount ?> Events</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 6px;">
                            <span style="font-size: 0.75rem; display: block; margin-bottom: 5px; opacity: 0.8; font-weight: 600;">Event Roster Frequency Scale</span>
                            <canvas id="activeClubTrendChart" style="max-height: 100px;"></canvas>
                        </div>

                        <a href="membership_list.php" style="background: #ffffff; color: #0a2540; text-align: center; padding: 10px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; margin-top: 15px; display: block;">View Club Details</a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; width:100%;">
                    <div class="analytics-card">
                        <h4 class="card-heading">
                            Monthly Event Activity Trend
                        </h4>

                        <div style="height:300px;">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="analytics-card">
                        <h4 class="card-heading">Recent Club Registrations</h4>
                        <div style="display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                            <?php if (empty($recentRegistrations)): ?>
                                <p style="text-align: center; color: #94a3b8; font-size: 0.85rem; padding: 20px 0;">No structural enrollment logs mapped.</p>
                            <?php else: ?>
                                <?php foreach ($recentRegistrations as $reg): 
                                    $initials = '';
                                    $parts = explode(' ', $reg['name']);
                                    $initials .= isset($parts[0][0]) ? $parts[0][0] : 'U';
                                    $initials .= isset($parts[1][0]) ? $parts[1][0] : '';
                                ?>
                                    <div class="recent-item">
                                        <div class="avatar-box"><?= strtoupper(htmlspecialchars($initials)) ?></div>
                                        <div style="flex: 1; min-width: 0;">
                                            <strong style="display: block; font-size: 0.85rem; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($reg['name']) ?></strong>
                                            <span style="font-size: 0.75rem; color: #64748b; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($reg['clubName']) ?></span>
                                        </div>
                                        <span style="font-size: 11px; color: #94a3b8; white-space: nowrap;">Recent</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <a href="membership_list.php" style="text-align: center; color: #0284c7; font-size: 0.8rem; font-weight: bold; text-decoration: none; margin-top: 10px; display: block;">View All Members</a>
                        </div>
                    </div>
                </div>

                <div class="form-card-container" style="width: 100%; box-sizing: border-box;">
                    <h3 style="color: #0a2540; margin-top: 0; margin-bottom: 15px; font-size: 1.15rem;">Event Analytical Breakdown Details</h3>
                    
                    <form method="GET" action="dashboard.php" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <select name="club" style="margin: 0; width: 220px; padding: 8px 12px;">
                            <option value="">All Hosting Clubs</option>
                            <?php while($c = $clubsOptions->fetch_assoc()): ?>
                                <option value="<?= $c['clubID'] ?>" <?= $selectedClub == $c['clubID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['clubName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <select name="event" style="margin: 0; width: 220px; padding: 8px 12px;">
                            <option value="">All Tracked Events</option>
                            <?php while($e = $eventsOptions->fetch_assoc()): ?>
                                <option value="<?= $e['eventID'] ?>" <?= $selectedEvent == $e['eventID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['eventName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <button type="submit" class="btn-primary" style="min-width: auto; padding: 0 22px;">Filter Metrics</button>
                        <a href="dashboard.php" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center; background: #e2e8f0; color: #475569; padding: 0 18px; border-radius: 6px;">Reset</a>
                    </form>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Event Scope Title</th>
                                <th style="width: 25%;">Hosting Organizational Entity</th>
                                <th style="text-align: center;">Registered</th>
                                <th style="text-align: center;">Present</th>
                                <th style="text-align: center;">Absent</th>
                                <th style="text-align: center; width: 18%;">Attendance Evaluation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($eventDetails && $eventDetails->num_rows > 0): ?>
                                <?php while($row = $eventDetails->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['eventName']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['clubName']) ?></td>
                                    <td style="text-align: center;"><?= $row['registered'] ?></td>
                                    <td style="text-align: center; color: #2ecc71; font-weight: 600;"><?= $row['present'] ?></td>
                                    <td style="text-align: center; color: #e74c3c;"><?= $row['absent'] ?></td>
                                    <td style="text-align: center; font-weight: bold; color: #0a2540;">
                                        <?= $row['rate'] ?>%
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 25px; color: #64748b;">No analytics logged for current context configurations.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Club Mix Proportional Category Breakdown Pie Chart Render
        new Chart(document.getElementById('clubDistPieChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartCategories) ?>,
                datasets: [{
                    data: <?= json_encode($chartCategoryCounts) ?>,
                    backgroundColor: ['#0a2540', '#0284c7', '#38bdf8', '#94a3b8', '#cbd5e1'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });

        // Most Active Hosting Club Analytics Bar Graph Render Overlay
        new Chart(document.getElementById('activeClubTrendChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($graphClubNames) ?>,
                datasets: [{
                    data:  <?= json_encode($graphClubCounts) ?>,
                    backgroundColor: 'rgba(56, 189, 248, 0.85)',
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                plugins: { legend: { display: false } }
            }
        });
        new Chart(document.getElementById('monthlyTrendChart'), {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($trendMonths) ?>,
                        datasets: [{
                            label: 'Events Hosted',
                            data: <?= json_encode($trendCounts) ?>,
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2,132,199,0.15)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins:{
                            legend:{
                                display:true
                            }
                        },
                        scales:{
                            y:{
                                beginAtZero:true
                            }
                        }
                    }
                });
    </script>
</body>
</html>