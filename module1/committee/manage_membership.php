<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

if (!isCommittee() && !isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

/* ------------------------------
   DETERMINE CLUB ID
--------------------------------*/
$clubID = null;

if (isCommittee()) {

    $stmt = $conn->prepare("
        SELECT clubID
        FROM club_committee
        WHERE userID = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $_SESSION['userID']);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    $clubID = $result['clubID'] ?? null;

    $stmt->close();

} elseif (isAdmin()) {

    $clubID = isset($_GET['club_id'])
        ? (int)$_GET['club_id']
        : (isset($_POST['club_id'])
            ? (int)$_POST['club_id']
            : 0);
}

if (!$clubID) {
    die("No club assigned.");
}

$redirectSuffix = isAdmin()
    ? "?club_id=" . $clubID
    : "";

/* ------------------------------
   DELETE MEMBER
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action_type'])
    && $_POST['action_type'] === 'delete') {

    $membershipID = (int)$_POST['membership_id'];

    $stmt = $conn->prepare("
        DELETE FROM membership
        WHERE membershipID = ?
        AND clubID = ?
    ");

    $stmt->bind_param("ii", $membershipID, $clubID);

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Member removed successfully.";
    } else {
        $_SESSION['errorMessage'] = "Unable to remove member.";
    }

    $stmt->close();

    header("Location: manage_membership.php" . $redirectSuffix);
    exit();
}

/* ------------------------------
   ADD MEMBER
--------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action_type'])
    && $_POST['action_type'] === 'add') {

    $userID = trim($_POST['student_user_id']);

    $check = $conn->prepare("
        SELECT membershipID
        FROM membership
        WHERE userID = ?
        AND clubID = ?
        LIMIT 1
    ");

    $check->bind_param("si", $userID, $clubID);
    $check->execute();

    $exists = $check->get_result()->num_rows > 0;

    $check->close();

    if ($exists) {

        $_SESSION['errorMessage'] =
            "Student already belongs to this club.";

    } else {

        $insert = $conn->prepare("
            INSERT INTO membership
            (userID, clubID, joinDate, status)
            VALUES (?, ?, NOW(), 'Active')
        ");

        $insert->bind_param("si", $userID, $clubID);

        if ($insert->execute()) {
            $_SESSION['successMessage'] =
                "Student added successfully.";
        } else {
            $_SESSION['errorMessage'] =
                "Failed to add student.";
        }

        $insert->close();
    }

    header("Location: manage_membership.php" . $redirectSuffix);
    exit();
}

/* ------------------------------
   FLASH MESSAGES
--------------------------------*/
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);

/* ------------------------------
   CLUB INFO
--------------------------------*/
$clubName = "Club";

$stmt = $conn->prepare("
    SELECT clubName
    FROM club
    WHERE clubID = ?
");

$stmt->bind_param("i", $clubID);
$stmt->execute();

$club = $stmt->get_result()->fetch_assoc();

if ($club) {
    $clubName = $club['clubName'];
}

$stmt->close();

/* ------------------------------
   SEARCH USERS
--------------------------------*/
$searchKeyword = trim($_GET['search'] ?? '');
$searchResults = [];

if (!empty($searchKeyword)) {

    $search = "%" . $searchKeyword . "%";

    $stmt = $conn->prepare("
        SELECT userID, name, email
        FROM user
        WHERE (userID LIKE ? OR name LIKE ?)
        AND role IN ('Student','Committee')
        ORDER BY name
        LIMIT 20
    ");

    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();

    $searchResults =
        $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

/* ------------------------------
   CURRENT MEMBERS
--------------------------------*/
$stmt = $conn->prepare("
    SELECT
        m.membershipID,
        u.userID,
        u.name,
        u.email
    FROM membership m
    JOIN user u
        ON m.userID = u.userID
    WHERE m.clubID = ?
    AND m.status = 'Active'
    ORDER BY u.name
");

$stmt->bind_param("i", $clubID);
$stmt->execute();

$currentMembers =
    $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Membership</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__ . '/../../sidebar.php'; ?>

<main class="main-content">

<h1 class="page-title">
    Manage Membership - <?= htmlspecialchars($clubName) ?>
</h1>

<?php if ($successMessage): ?>
<div class="alert alert-success">
    <?= htmlspecialchars($successMessage) ?>
</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
<div class="alert alert-error">
    <?= htmlspecialchars($errorMessage) ?>
</div>
<?php endif; ?>

<div class="form-card-container">

<h3>Search Student</h3>

<form method="GET">

<?php if (isAdmin()): ?>
<input type="hidden"
       name="club_id"
       value="<?= $clubID ?>">
<?php endif; ?>

<input type="text"
       name="search"
       placeholder="Search student name or ID"
       value="<?= htmlspecialchars($searchKeyword) ?>">

<button type="submit">
    Search
</button>

</form>

<?php if (!empty($searchKeyword)): ?>

<table class="data-table">

<tr>
    <th>User ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Action</th>
</tr>

<?php foreach ($searchResults as $student): ?>

<tr>

<td><?= htmlspecialchars($student['userID']) ?></td>

<td><?= htmlspecialchars($student['name']) ?></td>

<td><?= htmlspecialchars($student['email']) ?></td>

<td>

<form method="POST">

<input type="hidden"
       name="action_type"
       value="add">

<input type="hidden"
       name="student_user_id"
       value="<?= htmlspecialchars($student['userID']) ?>">

<?php if (isAdmin()): ?>
<input type="hidden"
       name="club_id"
       value="<?= $clubID ?>">
<?php endif; ?>

<button type="submit">
    Add Member
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

</div>

<br>

<div class="form-card-container">

<h3>Current Members</h3>

<table class="data-table">

<tr>
    <th>User ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Action</th>
</tr>

<?php foreach ($currentMembers as $member): ?>

<tr>

<td><?= htmlspecialchars($member['userID']) ?></td>

<td><?= htmlspecialchars($member['name']) ?></td>

<td><?= htmlspecialchars($member['email']) ?></td>

<td>

<form method="POST"
      onsubmit="return confirm('Remove this member?');">

<input type="hidden"
       name="action_type"
       value="delete">

<input type="hidden"
       name="membership_id"
       value="<?= $member['membershipID'] ?>">

<?php if (isAdmin()): ?>
<input type="hidden"
       name="club_id"
       value="<?= $clubID ?>">
<?php endif; ?>

<button type="submit">
    Remove
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</main>
</div>

</body>
</html>