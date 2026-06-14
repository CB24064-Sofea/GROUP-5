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

$error_msg = '';
$committee_record = null;

/*
|--------------------------------------------------------------------------
| Load Existing Committee Record
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
            c.clubName
        FROM club_committee cc
        INNER JOIN club c
            ON cc.clubID = c.clubID
        WHERE cc.userID = ?
        AND cc.clubID = ?
        LIMIT 1
    ");

    $stmt->bind_param("si", $userID, $clubID);
    $stmt->execute();

    $committee_record = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$committee_record) {
        $error_msg = "Committee assignment not found.";
    }
}

/*
|--------------------------------------------------------------------------
| Update Committee Assignment
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old_uid = trim($_POST['old_uid']);
    $old_cid = (int)$_POST['old_cid'];

    $new_uid = trim($_POST['student_id']);
    $position = trim($_POST['position']);

    if (empty($new_uid) || empty($position)) {
        $error_msg = "Please complete all required fields.";
    } else {

        $conn->begin_transaction();

        try {

            /*
            -------------------------------------
            Check duplicate assignment
            -------------------------------------
            */
            $check = $conn->prepare("
                SELECT *
                FROM club_committee
                WHERE userID = ?
                AND clubID = ?
                AND NOT (userID = ? AND clubID = ?)
                LIMIT 1
            ");

            $check->bind_param(
                "sisi",
                $new_uid,
                $old_cid,
                $old_uid,
                $old_cid
            );

            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                throw new Exception(
                    "This student is already assigned to this club."
                );
            }

            $check->close();

            /*
            -------------------------------------
            Update Assignment
            -------------------------------------
            */
            $update = $conn->prepare("
                UPDATE club_committee
                SET userID = ?,
                    position = ?
                WHERE userID = ?
                AND clubID = ?
            ");

            $update->bind_param(
                "sssi",
                $new_uid,
                $position,
                $old_uid,
                $old_cid
            );

            if (!$update->execute()) {
                throw new Exception($update->error);
            }

            $update->close();

            $conn->commit();

            $_SESSION['msg'] = "Committee assignment updated successfully.";
            $_SESSION['msgClass'] = "alert-success";

            header("Location: manage_committee.php");
            exit();

        } catch (Exception $e) {

            $conn->rollback();

            $error_msg = $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Student List
|--------------------------------------------------------------------------
*/
$students = $conn->query("
    SELECT userID, name
    FROM user
    ORDER BY name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Committee</title>
<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>

.main-content{
    padding:30px;
}

.page-header{
    width:1000px;
    margin:0 auto 25px auto;
    background:#fff;
    border:1px solid #cbd5e1;
    border-radius:8px;
    text-align:center;
    padding:20px;
}

.page-header h2{
    margin:0;
    font-size:24px;
    color:#17365d;
    font-weight:700;
}

.form-wrapper{
    width:1000px;
    margin:0 auto;
    background:#fff;
    border:1px solid #cbd5e1;
    border-radius:8px;
    padding:40px;
}

.form-group{
    margin-bottom:28px;
}

.form-group label{
    display:block;
    margin-bottom:10px;
    font-size:14px;
    font-weight:600;
    color:#334155;
}

.form-control{
    width:100%;
    height:52px;
    border:1px solid #cbd5e1;
    border-radius:5px;
    padding:0 18px;
    font-size:16px;
    background:#fff;
}

.form-control[readonly]{
    background:#e2e8f0;
}

.button-group{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-top:40px;
}

.btn-update{
    background:#ffffff;
    border:1px solid #94a3b8;
    color:#17365d;
    padding:14px 35px;
    border-radius:5px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

.btn-cancel{
    background:#ffffff;
    border:1px solid #94a3b8;
    color:#17365d;
    padding:14px 35px;
    border-radius:5px;
    font-size:16px;
    font-weight:600;
    text-decoration:none;
}

.alert-error{
    width:1000px;
    margin:0 auto 20px auto;
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
    padding:15px;
    border-radius:6px;
}

</style>
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

   <main class="main-content">

    <div class="page-header">
        <h2>Update Committee</h2>
    </div>

    <?php if($error_msg): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <?php if($committee_record): ?>

    <div class="form-wrapper">

        <form method="POST">

            <input type="hidden"
                   name="old_uid"
                   value="<?= htmlspecialchars($committee_record['userID']) ?>">

            <input type="hidden"
                   name="old_cid"
                   value="<?= (int)$committee_record['clubID'] ?>">

            <div class="form-group">
                <label>Club Name</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($committee_record['clubName']) ?>"
                    readonly>
            </div>

            <div class="form-group">
                <label>Select Student</label>

                <select
                    name="student_id"
                    class="form-control"
                    required>

                    <option value="">
                        -- Choose Student Record --
                    </option>

                    <?php while($student = $students->fetch_assoc()): ?>

                        <option
                            value="<?= htmlspecialchars($student['userID']) ?>"
                            <?= ($student['userID']==$committee_record['userID']) ? 'selected' : '' ?>>

                            <?= htmlspecialchars($student['userID'].' - '.$student['name']) ?>

                        </option>

                    <?php endwhile; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Position</label>

                <select
                    name="position"
                    class="form-control"
                    required>

                    <option value="President"
                        <?= ($committee_record['position']=='President') ? 'selected' : '' ?>>
                        President
                    </option>

                    <option value="Vice President"
                        <?= ($committee_record['position']=='Vice President') ? 'selected' : '' ?>>
                        Vice President
                    </option>

                    <option value="Secretary"
                        <?= ($committee_record['position']=='Secretary') ? 'selected' : '' ?>>
                        Secretary
                    </option>

                    <option value="Treasurer"
                        <?= ($committee_record['position']=='Treasurer') ? 'selected' : '' ?>>
                        Treasurer
                    </option>

                    <option value="Committee Member"
                        <?= ($committee_record['position']=='Committee Member') ? 'selected' : '' ?>>
                        Committee Member
                    </option>

                </select>
            </div>

            <div class="button-group">

                <button type="submit" class="btn-update">
                    Update Committee
                </button>

                <a href="manage_committee.php" class="btn-cancel">
                    Cancel
                </a>

            </div>

        </form>

    </div>

    <?php endif; ?>

</main>

</div>

</body>
</html>

<?php
$conn->close();
?>