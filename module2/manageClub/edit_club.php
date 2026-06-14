<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$message = "";
$messageClass = "";

$clubID = 0;
$clubName = "";
$advisor = "";
$email = "";
$description = "";
$status = "Active";

/* ======================================
   LOAD CLUB DATA
====================================== */
if (isset($_GET['id'])) {

    $clubID = (int)$_GET['id'];

    if ($clubID > 0) {

        $stmt = $conn->prepare("
            SELECT
                clubID,
                clubName,
                advisor,
                email,
                description,
                status
            FROM club
            WHERE clubID = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $clubID);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $club = $result->fetch_assoc();

            $clubID      = $club['clubID'];
            $clubName    = $club['clubName'];
            $advisor     = $club['advisor'];
            $email       = $club['email'];
            $description = $club['description'];
            $status      = $club['status'];

        } else {

            $message = "Club not found.";
            $messageClass = "alert-error";
        }

        $stmt->close();

    } else {

        $message = "Invalid Club ID.";
        $messageClass = "alert-error";
    }

} else {

    $message = "Missing Club ID.";
    $messageClass = "alert-error";
}

/* ======================================
   UPDATE CLUB
====================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $clubID      = (int)$_POST['clubId'];
    $clubName    = trim($_POST['clubName']);
    $advisor     = trim($_POST['advisorName']);
    $email       = trim($_POST['email']);
    $description = trim($_POST['description']);
    $status      = trim($_POST['clubStatus']);

    if (
        empty($clubName) ||
        empty($advisor) ||
        empty($email) ||
        empty($description)
    ) {

        $message = "All fields are required.";
        $messageClass = "alert-error";

    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Invalid email format.";
        $messageClass = "alert-error";

    }
    else {

        $stmt = $conn->prepare("
            UPDATE club
            SET
                clubName = ?,
                advisor = ?,
                email = ?,
                description = ?,
                status = ?
            WHERE clubID = ?
        ");

        $stmt->bind_param(
            "sssssi",
            $clubName,
            $advisor,
            $email,
            $description,
            $status,
            $clubID
        );

        if ($stmt->execute()) {

            $_SESSION['msg'] = "Club updated successfully.";
            $_SESSION['msgClass'] = "alert-success";

            header("Location: manage_club.php");
            exit();

        } else {

            $message = "Failed to update club.";
            $messageClass = "alert-error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Club</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>
.form-container{
    background:#fff;
    max-width:800px;
    margin:auto;
    padding:30px;
    border-radius:8px;
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}

.form-title{
    margin-bottom:25px;
    color:#0f172a;
    border-bottom:2px solid #0284c7;
    padding-bottom:10px;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
}

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:10px;
    border:1px solid #cbd5e1;
    border-radius:6px;
}

.form-group input[readonly]{
    background:#f1f5f9;
}

.form-actions{
    margin-top:25px;
    display:flex;
    gap:10px;
}

.btn-save{
    background:#16a34a;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:6px;
    cursor:pointer;
}

.btn-cancel{
    background:#64748b;
    color:white;
    text-decoration:none;
    padding:10px 20px;
    border-radius:6px;
}

.alert{
    padding:12px;
    margin-bottom:20px;
    border-radius:6px;
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

        <div class="form-container">

            <h2 class="form-title">
                Edit Club
            </h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $messageClass ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Club ID</label>
                    <input
                        type="text"
                        name="clubId"
                        value="<?= htmlspecialchars($clubID) ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Club Name</label>
                    <input
                        type="text"
                        name="clubName"
                        value="<?= htmlspecialchars($clubName) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Advisor Name</label>
                    <input
                        type="text"
                        name="advisorName"
                        value="<?= htmlspecialchars($advisor) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea
                        name="description"
                        rows="4"
                        required><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="clubStatus">
                        <option value="Active"
                            <?= $status === 'Active' ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="Inactive"
                            <?= $status === 'Inactive' ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-save">
                        Save Changes
                    </button>

                    <a
                        href="manage_club.php"
                        class="btn-cancel">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </main>
</div>

<?php $conn->close(); ?>

</body>
</html>