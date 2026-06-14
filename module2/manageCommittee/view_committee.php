<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

/*
|--------------------------------------------------------------------------
| Admin Only
|--------------------------------------------------------------------------
*/
if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$error = '';
$committee = null;

/*
|--------------------------------------------------------------------------
| Load Committee Record
|--------------------------------------------------------------------------
*/
if (isset($_GET['uid']) && isset($_GET['cid'])) {

    $userID = trim($_GET['uid']);
    $clubID = (int)$_GET['cid'];

    $stmt = $conn->prepare("
        SELECT
            cc.userID,
            cc.clubID,
            cc.position,
            u.name,
            c.clubName
        FROM club_committee cc
        LEFT JOIN user u
            ON cc.userID = u.userID
        LEFT JOIN club c
            ON cc.clubID = c.clubID
        WHERE cc.userID = ?
        AND cc.clubID = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("si", $userID, $clubID);
    $stmt->execute();

    $committee = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$committee) {
        $error = "Committee record not found.";
    }

} else {
    $error = "Missing User ID or Club ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Committee Detail</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>

.main-content{
    padding:30px;
}

.page-header{
    width:650px;
    margin:0 auto 20px auto;
    background:#ffffff;
    border:1px solid #b8c4ce;
    border-radius:4px;
    text-align:center;
    padding:12px;
}

.page-header h2{
    margin:0;
    color:#17365d;
    font-size:20px;
    font-weight:700;
}

.detail-card{
    width:650px;
    margin:0 auto;
    background:#ffffff;
    border:1px solid #b8c4ce;
    border-radius:4px;
    padding:25px;
}

.form-group{
    margin-bottom:14px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:12px;
    color:#7a8793;
    font-weight:700;
    letter-spacing:1px;
    text-transform:uppercase;
}

.form-control{
    width:100%;
    padding:10px 14px;
    border:1px solid #d5dbe1;
    border-radius:4px;
    background:#f8fafc;
    color:#17365d;
    font-size:15px;
    box-sizing:border-box;
}

.error-box{
    width:650px;
    margin:0 auto 20px auto;
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
    border-radius:4px;
    padding:12px;
    text-align:center;
}

.button-area{
    width:650px;
    margin:20px auto;
    text-align:right;
}

.btn-back{
    background:#95a5a6;
    color:white;
    text-decoration:none;
    padding:10px 18px;
    border-radius:4px;
    font-size:14px;
    font-weight:600;
}

.btn-back:hover{
    opacity:0.9;
}

</style>
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <h2>Committee Detail</h2>
        </div>

        <?php if ($error): ?>

            <div class="error-box">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <?php if ($committee): ?>

        <div class="detail-card">

            <div class="form-group">
                <label>Committee ID</label>
                <div class="form-control">
                    CMTE-<?= htmlspecialchars($committee['clubID']) ?>-<?= htmlspecialchars($committee['userID']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Club Name</label>
                <div class="form-control">
                    <?= htmlspecialchars($committee['clubName']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <div class="form-control">
                    <?= htmlspecialchars($committee['userID']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Student Name</label>
                <div class="form-control">
                    <?= htmlspecialchars($committee['name']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Club</label>
                <div class="form-control">
                    ID: <?= htmlspecialchars($committee['clubID']) ?>
                    — <?= htmlspecialchars($committee['clubName']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Position</label>
                <div class="form-control">
                    <?= htmlspecialchars($committee['position']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Assigned Date</label>
                <div class="form-control">
                    <?= !empty($committee['assignedDate'])
                        ? htmlspecialchars(date('Y-m-d', strtotime($committee['assignedDate'])))
                        : 'N/A'; ?>
                </div>
            </div>

        </div>

        <div class="button-area">
            <a href="manage_committee.php" class="btn-back">
                Back
            </a>
        </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>

<?php
$conn->close();
?>