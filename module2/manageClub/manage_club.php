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

/* ==========================================
   LOAD CLUBS FOR FILTER DROPDOWN
========================================== */
$clubsListResult = $conn->query("
    SELECT clubID, clubName
    FROM club
    ORDER BY clubName ASC
");

$filterClubID = isset($_GET['filterClubID'])
    ? (int)$_GET['filterClubID']
    : 0;

/* ==========================================
   LOAD DATA
========================================== */
if ($filterClubID > 0) {

    $stmt = $conn->prepare("
        SELECT
            c.clubID,
            c.clubName,
            c.advisor,
            u.userID AS studentID,
            u.name AS studentName,
            u.email AS studentEmail
        FROM membership m
        INNER JOIN club c
            ON m.clubID = c.clubID
        INNER JOIN user u
            ON m.userID = u.userID
        WHERE m.clubID = ?
        AND m.status = 'Active'
        ORDER BY u.name ASC
    ");

    $stmt->bind_param("i", $filterClubID);
    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT
            clubID,
            clubName,
            advisor,
            email
        FROM club
        ORDER BY clubName ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Club</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>

.workspace-stack{
    width:100%;
}

.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.filter-form{
    display:flex;
    align-items:center;
    gap:10px;
}

.filter-select{
    padding:10px;
    min-width:250px;
}

.btn{
    text-decoration:none;
    padding:8px 14px;
    border-radius:6px;
    color:white;
    font-weight:600;
    display:inline-block;
}

.btn-add{
    background:#16a34a;
}

.btn-view{
    background:#0284c7;
}

.btn-edit{
    background:#eab308;
    color:black;
}

.btn-delete{
    background:#dc2626;
}

.action-cell{
    display:flex;
    gap:8px;
    justify-content:center;
}

.alert{
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
}

</style>
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <div class="workspace-stack">

            <h2 class="page-title">Manage Clubs</h2>

            <?php if(isset($_SESSION['msg'])): ?>

                <div class="alert <?= htmlspecialchars($_SESSION['msgClass'] ?? 'alert-success') ?>">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                </div>

                <?php
                unset($_SESSION['msg']);
                unset($_SESSION['msgClass']);
                ?>

            <?php endif; ?>

            <div class="filter-bar">

                <form method="GET" class="filter-form">

                    <label><strong>Filter Club:</strong></label>

                    <select
                        name="filterClubID"
                        class="filter-select"
                        onchange="this.form.submit()">

                        <option value="">
                            -- View All Clubs --
                        </option>

                        <?php while($club = $clubsListResult->fetch_assoc()): ?>

                            <option
                                value="<?= $club['clubID'] ?>"
                                <?= ($filterClubID == $club['clubID']) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($club['clubName']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </form>

                <a href="create_club.php" class="btn btn-add">
                    + Add New Club
                </a>

            </div>

            <div class="form-card-container">

                <table class="data-table">

                    <thead>

                    <tr>

                        <th>Club ID</th>
                        <th>Club Name</th>

                        <?php if($filterClubID > 0): ?>
                            <th>Student ID</th>
                            <th>Student Name</th>
                        <?php endif; ?>

                        <th>Advisor</th>
                        <th>Email</th>
                        <th>Actions</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php if($result && $result->num_rows > 0): ?>

                        <?php while($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td><?= htmlspecialchars($row['clubID']) ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['clubName']) ?>
                                    </strong>
                                </td>

                                <?php if($filterClubID > 0): ?>

                                    <td>
                                        <?= htmlspecialchars($row['studentID']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['studentName']) ?>
                                    </td>

                                <?php endif; ?>

                                <td>
                                    <?= htmlspecialchars($row['advisor']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $filterClubID > 0
                                        ? $row['studentEmail']
                                        : $row['email']
                                    ) ?>
                                </td>

                                <td>

                                    <div class="action-cell">

                                        <a
                                            href="view_club.php?id=<?= urlencode($row['clubID']) ?>"
                                            class="btn btn-view">

                                            View

                                        </a>

                                        <a
                                            href="edit_club.php?id=<?= urlencode($row['clubID']) ?>"
                                            class="btn btn-edit">

                                            Edit

                                        </a>

                                        <a
                                            href="delete_club.php?id=<?= urlencode($row['clubID']) ?>"
                                            class="btn btn-delete"
                                            onclick="return confirm('Delete this club?')">

                                            Delete

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="<?= ($filterClubID > 0 ? 7 : 5) ?>"
                                style="text-align:center">

                                No records found.

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

if(isset($stmt)){
    $stmt->close();
}

$conn->close();

?>