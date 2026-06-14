<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        cc.userID,
        cc.clubID,
        cc.position,
        c.clubName
    FROM club_committee cc
    INNER JOIN club c
        ON cc.clubID = c.clubID
";

if (!empty($search)) {
    $sql .= "
        WHERE
            cc.userID LIKE ?
            OR c.clubName LIKE ?
            OR cc.position LIKE ?
    ";
}

$sql .= " ORDER BY c.clubName ASC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $keyword = "%{$search}%";
    $stmt->bind_param(
        "sss",
        $keyword,
        $keyword,
        $keyword
    );
}

$stmt->execute();
$result = $stmt->get_result();

$msg = $_SESSION['msg'] ?? '';
$msgClass = $_SESSION['msgClass'] ?? '';

unset($_SESSION['msg']);
unset($_SESSION['msgClass']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Committee</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>

.search-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.search-form{
    display:flex;
    gap:10px;
    flex:1;
}

.search-form input{
    flex:1;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:6px;
}

.btn-search{
    background:#3498db;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
}

.btn-add{
    background:#2ecc71;
    color:white;
    text-decoration:none;
    padding:12px 20px;
    border-radius:6px;
    font-weight:bold;
}

.btn-edit{
    background:#f1c40f;
    color:black;
    text-decoration:none;
    padding:8px 14px;
    border-radius:5px;
    font-weight:bold;
}

.btn-delete{
    background:#e74c3c;
    color:white;
    text-decoration:none;
    padding:8px 14px;
    border-radius:5px;
    font-weight:bold;
}

.btn-view{
    background:#3498db;
    color:white;
    text-decoration:none;
    padding:8px 14px;
    border-radius:5px;
    font-weight:bold;
}

.action-buttons{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
}

.club-id{
    color:#64748b;
    font-size:0.9rem;
}

</style>
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__ . '/../../sidebar.php'; ?>

<main class="main-content">

<div class="workspace-stack">

<h2 class="page-title">
    Manage Committee
</h2>

<?php if(!empty($msg)): ?>
<div class="<?= htmlspecialchars($msgClass) ?>">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="form-card-container">

<div class="search-bar">

<form method="GET" class="search-form">

<input
    type="text"
    name="search"
    placeholder="Search Clubs..."
    value="<?= htmlspecialchars($search) ?>"
>

<button type="submit" class="btn-search">
    Search
</button>

</form>

<a href="create_committee.php" class="btn-add">
    Add New Committee
</a>

</div>

</div>

<div class="form-card-container">

<table class="data-table">

<thead>
<tr>
    <th>Student ID</th>
    <th>Club Name</th>
    <th>Position</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
    <strong><?= htmlspecialchars($row['userID']) ?></strong>
</td>

<td>
    <?= htmlspecialchars($row['clubName']) ?>
    <br>
    <span class="club-id">
        Club ID: <?= htmlspecialchars($row['clubID']) ?>
    </span>
</td>

<td>
    <?= htmlspecialchars($row['position']) ?>
</td>

<td>

<div class="action-buttons">

<a href="edit_committee.php?uid=<?= urlencode($row['userID']) ?>&cid=<?= $row['clubID'] ?>"
   class="btn-edit">
    Edit
</a>

<a
    href="delete_committee.php?uid=<?= urlencode($row['userID']) ?>&cid=<?= urlencode($row['clubID']) ?>"
    class="btn-delete"
    onclick="return confirm('Delete this committee assignment?');">
    Delete
</a>

<a href="view_committee.php?uid=<?= urlencode($row['userID']) ?>&cid=<?= $row['clubID'] ?>"
   class="btn-view">
    View
</a>

</div>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4" style="text-align:center;">
    No committee assignments found.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</main>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>