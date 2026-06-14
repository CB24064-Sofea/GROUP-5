<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

requireLogin();

$query = "
SELECT
    w.waitingID,
    w.eventID,
    e.eventName,
    e.eventDescription,
    w.userID,
    u.name,
    w.position,
    w.registerAt
FROM waitinglist w
LEFT JOIN event e
    ON w.eventID = e.eventID
LEFT JOIN user u
    ON w.userID = u.userID
ORDER BY w.eventID ASC, w.position ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$totalWaiting = mysqli_num_rows($result);

mysqli_data_seek($result, 0);

$firstRow = mysqli_fetch_assoc($result);

$eventID = $firstRow['eventID'] ?? '-';
$eventName = $firstRow['eventName'] ?? '-';
$eventDescription = $firstRow['eventDescription'] ?? '';

mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Waiting List</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>
.breadcrumb {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 15px;
    text-transform: uppercase;
    font-weight: 600;
}

.breadcrumb span {
    color: #243b53;
}

.toolbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.export-btn{
    background:#163a70;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
}

.export-btn:hover{
    background:#0f2b55;
}

.waiting-footer{
    padding:15px;
    border-top:1px solid #e5e7eb;
    background:#fafafa;
    color:#64748b;
    font-size:14px;
}
</style>

</head>
<body>

<?php include __DIR__ . '/../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__ . '/../sidebar.php'; ?>

<main class="main-content">

<div class="workspace-stack">

<div class="breadcrumb">
EVENTS MANAGEMENT
&nbsp; › &nbsp;
<span>WAITING LIST</span>
</div>

<div class="toolbar">

<h1 style="font-size:26px;color:#243b53;">
Event Waiting List
</h1>

<button class="export-btn" onclick="window.print()">
Export CSV
</button>

</div>

<div class="stats-row">

<div class="info-card">
<div class="info-card-icon">🆔</div>

<div class="info-card-content">
<small>Event ID</small>
<strong>#<?= htmlspecialchars($eventID) ?></strong>
</div>
</div>

<div class="info-card">
<div class="info-card-icon">🎉</div>

<div class="info-card-content">
<small>Active Event</small>
<strong><?= htmlspecialchars($eventName) ?></strong>
</div>
</div>

<div class="info-card">
<div class="info-card-icon">👥</div>

<div class="info-card-content">
<small>Waitlist Count</small>
<strong><?= $totalWaiting ?> Students</strong>
</div>
</div>

</div>

<div class="waiting-wrapper">

<div class="waiting-header">

<input
type="text"
class="waiting-search"
placeholder="Filter by name or user ID..."
id="searchInput">

</div>

<table class="waiting-table" id="waitingTable">

<thead>
<tr>
<th>Waiting ID</th>
<th>Event ID</th>
<th>Event Name</th>
<th>User ID</th>
<th>Student Name</th>
<th>Position</th>
<th>Registered At</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)): ?>

<tr>

<td>
<?= htmlspecialchars($row['waitingID']) ?>
</td>

<td>
<?= htmlspecialchars($row['eventID']) ?>
</td>

<td>
<?= htmlspecialchars($row['eventName']) ?>
</td>

<td>
<?= htmlspecialchars($row['userID']) ?>
</td>

<td>

<div class="student-info">

<div class="student-avatar">
<?= strtoupper(substr($row['name'],0,1)) ?>
</div>

<?= htmlspecialchars($row['name']) ?>

</div>

</td>

<td>

<span class="position-badge">
#<?= htmlspecialchars($row['position']) ?>
</span>

</td>

<td>
<?= date('d M Y H:i', strtotime($row['registerAt'])) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<div class="waiting-footer">
Showing <?= $totalWaiting ?> waiting list record(s)
</div>

</div>

<div class="hero-banner">

<img src="/GROUP%205/images.jpg" alt="Event Banner">

<div class="hero-overlay">

<h2>Event Capacity Reached</h2>

<p>
<?= htmlspecialchars($eventDescription) ?>
</p>

</div>

</div>

</div>

</main>

</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {

let value = this.value.toLowerCase();

let rows =
document.querySelectorAll('#waitingTable tbody tr');

rows.forEach(function(row){

let text =
row.textContent.toLowerCase();

row.style.display =
text.includes(value) ? '' : 'none';

});

});
</script>

</body>
</html>

<?php
mysqli_free_result($result);
mysqli_close($conn);
?>