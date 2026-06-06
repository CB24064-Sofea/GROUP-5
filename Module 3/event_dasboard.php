<?php
require_once 'auth.php';
requireLogin();
include 'header.php';
include 'sidebar.php';

// Events per club
$clubData = $conn->query("SELECT c.clubName, COUNT(e.eventID) as total FROM club c LEFT JOIN event e ON c.clubID=e.clubID GROUP BY c.clubID");
$clubNames = []; $eventCounts = [];
while ($row = $clubData->fetch_assoc()) {
    $clubNames[] = $row['clubName'];
    $eventCounts[] = $row['total'];
}

// Monthly trends (last 6 months)
$trend = $conn->query("SELECT DATE_FORMAT(eventDate, '%Y-%m') as month, COUNT(*) as cnt FROM event WHERE eventDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
$months = []; $trendCounts = [];
while ($t = $trend->fetch_assoc()) {
    $months[] = $t['month'];
    $trendCounts[] = $t['cnt'];
}

// Popular events (top 5 by registrations)
$popular = $conn->query("SELECT e.eventName, COUNT(er.registrationID) as regs FROM event e LEFT JOIN event_registration er ON e.eventID=er.eventID GROUP BY e.eventID ORDER BY regs DESC LIMIT 5");
$popNames = []; $popRegs = [];
while ($p = $popular->fetch_assoc()) {
    $popNames[] = $p['eventName'];
    $popRegs[] = $p['regs'];
}
?>
<div class="workspace-stack">
    <div class="page-title">Event Dashboard</div>
    <div class="form-card-container">
        <canvas id="clubChart" width="400" height="200"></canvas>
        <canvas id="trendChart" width="400" height="200"></canvas>
        <canvas id="popularChart" width="400" height="200"></canvas>
    </div>
</div>
<script>
    new Chart(document.getElementById('clubChart'), { type: 'bar', data: { labels: <?php echo json_encode($clubNames); ?>, datasets: [{ label: 'Events per Club', data: <?php echo json_encode($eventCounts); ?> }] } });
    new Chart(document.getElementById('trendChart'), { type: 'line', data: { labels: <?php echo json_encode($months); ?>, datasets: [{ label: 'Events per Month', data: <?php echo json_encode($trendCounts); ?> }] } });
    new Chart(document.getElementById('popularChart'), { type: 'pie', data: { labels: <?php echo json_encode($popNames); ?>, datasets: [{ data: <?php echo json_encode($popRegs); ?> }] } });
</script>
<?php include 'footer.php'; ?>