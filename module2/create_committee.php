```php
<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../auth.php';

if(!isAdmin()){
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$error_msg = '';

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_committee'])){

    $clubID   = (int)($_POST['clubID'] ?? 0);
    $userID   = trim($_POST['userID'] ?? '');
    $position = trim($_POST['position'] ?? '');

    if(empty($clubID) || empty($userID) || empty($position)){
        $error_msg = "Please complete all required fields.";
    }
    else{

        try{

            $conn->begin_transaction();

            /*
            ----------------------------------------------------
            Verify Club Exists
            ----------------------------------------------------
            */
            $clubCheck = $conn->prepare("
                SELECT clubID
                FROM club
                WHERE clubID = ?
                LIMIT 1
            ");

            $clubCheck->bind_param("i",$clubID);
            $clubCheck->execute();

            if($clubCheck->get_result()->num_rows === 0){
                throw new Exception("Selected club does not exist.");
            }

            $clubCheck->close();

            /*
            ----------------------------------------------------
            Verify Student Exists
            ----------------------------------------------------
            */
            $studentCheck = $conn->prepare("
                SELECT userID
                FROM student
                WHERE userID = ?
                LIMIT 1
            ");

            $studentCheck->bind_param("s",$userID);
            $studentCheck->execute();

            if($studentCheck->get_result()->num_rows === 0){
                throw new Exception("Selected student does not exist.");
            }

            $studentCheck->close();

            /*
            ----------------------------------------------------
            Prevent Duplicate Assignment
            ----------------------------------------------------
            */
            $duplicate = $conn->prepare("
                SELECT *
                FROM club_committee
                WHERE userID = ?
                AND clubID = ?
                LIMIT 1
            ");

            $duplicate->bind_param("si",$userID,$clubID);
            $duplicate->execute();

            if($duplicate->get_result()->num_rows > 0){
                throw new Exception(
                    "This student is already assigned to this club."
                );
            }

            $duplicate->close();

            /*
            ----------------------------------------------------
            Insert Assignment
            ----------------------------------------------------
            */
            $insert = $conn->prepare("
                INSERT INTO club_committee
                (
                    userID,
                    clubID,
                    position
                )
                VALUES
                (
                    ?, ?, ?
                )
            ");

            $insert->bind_param(
                "sis",
                $userID,
                $clubID,
                $position
            );

            if(!$insert->execute()){
                throw new Exception($insert->error);
            }

            $insert->close();

            $conn->commit();

            $_SESSION['msg'] = "Committee assignment created successfully.";
            $_SESSION['msgClass'] = "alert-success";

            header("Location: manage_committee.php");
            exit();

        }
        catch(Exception $e){

            $conn->rollback();

            $error_msg = $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Load Clubs
|--------------------------------------------------------------------------
*/
$clubsResult = $conn->query("
    SELECT clubID, clubName
    FROM club
    ORDER BY clubName ASC
");

/*
|--------------------------------------------------------------------------
| Load Students
|--------------------------------------------------------------------------
*/
$studentsResult = $conn->query("
    SELECT
        s.userID,
        u.name
    FROM student s
    INNER JOIN user u
        ON s.userID = u.userID
    ORDER BY u.name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Committee Assignment</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>
.workspace-stack{
    max-width:750px;
    margin:auto;
}

.form-card-container{
    background:#fff;
    padding:25px;
    border-radius:8px;
    border:1px solid #ddd;
}

.form-group-row{
    margin-bottom:20px;
}

.form-group-row label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}

.input-control-select{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:5px;
    margin-bottom:15px;
}

.btn-save{
    background:#16a34a;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    cursor:pointer;
}

.btn-cancel{
    background:#64748b;
    color:white;
    text-decoration:none;
    padding:10px 20px;
    border-radius:5px;
}
</style>
</head>

<body>

<?php include __DIR__.'/../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__.'/../sidebar.php'; ?>

<main class="main-content">

<div class="workspace-stack">

<h2 class="page-title">
    Create Committee Assignment
</h2>

<?php if(!empty($error_msg)): ?>
<div class="alert-error">
    <?= htmlspecialchars($error_msg) ?>
</div>
<?php endif; ?>

<form method="POST" class="form-card-container">

    <div class="form-group-row">
        <label>Club</label>

        <select name="clubID"
                class="input-control-select"
                required>

            <option value="">
                Select Club
            </option>

            <?php while($club = $clubsResult->fetch_assoc()): ?>

                <option value="<?= $club['clubID'] ?>">
                    <?= htmlspecialchars($club['clubName']) ?>
                </option>

            <?php endwhile; ?>

        </select>
    </div>

    <div class="form-group-row">
        <label>Student</label>

        <select name="userID"
                class="input-control-select"
                required>

            <option value="">
                Select Student
            </option>

            <?php while($student = $studentsResult->fetch_assoc()): ?>

                <option value="<?= htmlspecialchars($student['userID']) ?>">
                    <?= htmlspecialchars(
                        $student['userID'].' - '.$student['name']
                    ) ?>
                </option>

            <?php endwhile; ?>

        </select>
    </div>

    <div class="form-group-row">
        <label>Position</label>

        <select name="position"
                class="input-control-select"
                required>

            <option value="">Select Position</option>
            <option value="President">President</option>
            <option value="Vice President">Vice President</option>
            <option value="Secretary">Secretary</option>
            <option value="Treasurer">Treasurer</option>
            <option value="Committee Member">Committee Member</option>

        </select>
    </div>

    <div style="display:flex;gap:10px;">

        <button type="submit"
                name="assign_committee"
                class="btn-save">
            Assign Committee
        </button>

        <a href="manage_committee.php"
           class="btn-cancel">
           Cancel
        </a>

    </div>

</form>

</div>

</main>
</div>

</body>
</html>

<?php
$conn->close();
?>
