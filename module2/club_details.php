<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$clubID = "";
$clubName = "";
$advisor = "";
$email = "";
$description = "";
$status = "";
$errorMessage = "";

if (isset($_GET['id'])) {

    $viewID = (int)$_GET['id'];

    $stmt = $conn->prepare("
        SELECT *
        FROM club
        WHERE clubID = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $viewID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $clubID      = $row['clubID'];
        $clubName    = $row['clubName'];
        $advisor     = $row['advisor'] ?? '';
        $email       = $row['email'] ?? '';
        $description = $row['description'] ?? '';
        $status      = $row['status'] ?? '';

    } else {

        $errorMessage = "Club not found.";
    }

    $stmt->close();

} else {

    $errorMessage = "Missing Club ID.";
}

$page_title = !empty($clubName)
    ? $clubName . " Profile"
    : "Club Profile";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($page_title) ?></title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>

.wireframe-stack{
    width:100%;
    max-width:1000px;
    margin:auto;
    display:flex;
    flex-direction:column;
    gap:25px;
}

.section-block{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:24px;
}

.section-title{
    font-size:1.15rem;
    font-weight:600;
    color:#0f172a;
    margin-bottom:15px;
    border-bottom:2px solid #0284c7;
    padding-bottom:8px;
}

.detail-row{
    display:flex;
    margin-bottom:10px;
    border:1px solid #e2e8f0;
    border-radius:6px;
    overflow:hidden;
}

.detail-label{
    width:200px;
    background:#f8fafc;
    padding:12px;
    font-weight:600;
    border-right:1px solid #e2e8f0;
}

.detail-value{
    flex:1;
    padding:12px;
}

.data-table-alt{
    width:100%;
    border-collapse:collapse;
}

.data-table-alt th,
.data-table-alt td{
    border:1px solid #e2e8f0;
    padding:12px;
}

.data-table-alt th{
    background:#f8fafc;
}

</style>
</head>

<body>

<?php include __DIR__ . '/../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__ . '/../sidebar.php'; ?>

<main class="main-content">

<div class="wireframe-stack">

<div style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h2 class="page-title">Club Profile</h2>
        <p style="color:#64748b;">
            View club details, committee members and events.
        </p>
    </div>

    <a href="manage_club.php"
       class="btn btn-secondary">
        Back
    </a>
</div>

<?php if(!empty($errorMessage)): ?>

<div class="alert alert-error">
    <?= htmlspecialchars($errorMessage) ?>
</div>

<?php else: ?>

<!-- CLUB DETAILS -->

<section class="section-block">

<h3 class="section-title">Club Information</h3>

<div class="detail-row">
    <div class="detail-label">Club ID</div>
    <div class="detail-value">
        <?= htmlspecialchars($clubID) ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Club Name</div>
    <div class="detail-value">
        <?= htmlspecialchars($clubName) ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Advisor</div>
    <div class="detail-value">
        <?= htmlspecialchars($advisor) ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Email</div>
    <div class="detail-value">
        <?= htmlspecialchars($email) ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Description</div>
    <div class="detail-value">
        <?= nl2br(htmlspecialchars($description)) ?>
    </div>
</div>

<div class="detail-row">
    <div class="detail-label">Status</div>
    <div class="detail-value">

        <?php if($status === 'Active'): ?>

            <span class="status-badge">
                Active
            </span>

        <?php else: ?>

            <span class="status-badge">
                Inactive
            </span>

        <?php endif; ?>

    </div>
</div>

</section>

<!-- COMMITTEE -->

<section class="section-block">

<h3 class="section-title">Committee Members</h3>

<table class="data-table-alt">

<thead>
<tr>
    <th>Position</th>
    <th>User ID</th>
    <th>Name</th>
</tr>
</thead>

<tbody>

<?php

$commStmt = $conn->prepare("
    SELECT
        cc.position,
        cc.userID,
        u.name
    FROM club_committee cc
    INNER JOIN user u
        ON cc.userID = u.userID
    WHERE cc.clubID = ?
    ORDER BY cc.position ASC
");

$commStmt->bind_param("i", $clubID);
$commStmt->execute();

$commResult = $commStmt->get_result();

if($commResult->num_rows > 0):

while($commRow = $commResult->fetch_assoc()):
?>

<tr>
    <td><?= htmlspecialchars($commRow['position']) ?></td>
    <td><?= htmlspecialchars($commRow['userID']) ?></td>
    <td><?= htmlspecialchars($commRow['name']) ?></td>
</tr>

<?php
endwhile;

else:
?>

<tr>
    <td colspan="3" style="text-align:center;">
        No committee members assigned.
    </td>
</tr>

<?php
endif;

$commStmt->close();
?>

</tbody>
</table>

</section>

<!-- EVENTS -->

<section class="section-block">

<h3 class="section-title">Club Events</h3>

<table class="data-table-alt">

<thead>
<tr>
    <th>Event Name</th>
    <th>Date</th>
    <th>Status</th>
</tr>
</thead>

<tbody>

<?php

$evStmt = $conn->prepare("
    SELECT
        eventName,
        eventDate
    FROM event
    WHERE clubID = ?
    ORDER BY eventDate DESC
");

$evStmt->bind_param("i", $clubID);
$evStmt->execute();

$evResult = $evStmt->get_result();

if($evResult->num_rows > 0):

while($evRow = $evResult->fetch_assoc()):

$isUpcoming =
    strtotime($evRow['eventDate']) >= strtotime(date('Y-m-d'));
?>

<tr>

<td>
    <?= htmlspecialchars($evRow['eventName']) ?>
</td>

<td>
    <?= htmlspecialchars($evRow['eventDate']) ?>
</td>

<td>

<?php if($isUpcoming): ?>

<span style="color:green;font-weight:bold;">
    Upcoming
</span>

<?php else: ?>

<span style="color:#64748b;font-weight:bold;">
    Completed
</span>

<?php endif; ?>

</td>

</tr>

<?php
endwhile;

else:
?>

<tr>
<td colspan="3" style="text-align:center;">
No events found.
</td>
</tr>

<?php
endif;

$evStmt->close();
?>

</tbody>
</table>

</section>

<?php endif; ?>

</div>

</main>

</div>

</body>
</html>

<?php
$conn->close();
?>