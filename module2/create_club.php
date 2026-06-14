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

$message = '';
$messageClass = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $clubName    = trim($_POST['clubName']);
    $advisor     = trim($_POST['advisorName']);
    $email       = trim($_POST['email']);
    $description = trim($_POST['description']);
    $status      = trim($_POST['clubStatus']);

    if(
        empty($clubName) ||
        empty($advisor) ||
        empty($email) ||
        empty($description)
    ){
        $message = "Please complete all required fields.";
        $messageClass = "alert-error";
    }
    else{

        $check = $conn->prepare("
            SELECT clubID
            FROM club
            WHERE clubName = ?
            LIMIT 1
        ");

        $check->bind_param("s",$clubName);
        $check->execute();

        if($check->get_result()->num_rows > 0){

            $message = "Club already exists.";
            $messageClass = "alert-error";
        }
        else{

            $stmt = $conn->prepare("
                INSERT INTO club
                (
                    clubName,
                    description,
                    email,
                    status,
                    advisor
                )
                VALUES
                (
                    ?, ?, ?, ?, ?
                )
            ");

            $stmt->bind_param(
                "sssss",
                $clubName,
                $description,
                $email,
                $status,
                $advisor
            );

            if($stmt->execute()){

                $_SESSION['msg'] = "Club created successfully.";
                $_SESSION['msgClass'] = "alert-success";

                header("Location: manage_club.php");
                exit();
            }
            else{

                $message = "Database error: ".$stmt->error;
                $messageClass = "alert-error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Club</title>

<link rel="stylesheet" href="/GROUP%205/standard.css">

<style>
.form-container{
    max-width:700px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:8px;
    border:1px solid #ddd;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:10px;
    border-radius:5px;
    margin-bottom:15px;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
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
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
}
</style>
</head>
<body>

<?php include __DIR__.'/../topbar.php'; ?>

<div class="app-container">

<?php include __DIR__.'/../sidebar.php'; ?>

<main class="main-content">

<div class="form-container">

<h2>Create New Club</h2>

<?php if(!empty($message)): ?>
<div class="<?= $messageClass ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Club Name</label>
        <input type="text" name="clubName" required>
    </div>

    <div class="form-group">
        <label>Advisor Name</label>
        <input type="text" name="advisorName" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4" required></textarea>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="clubStatus">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <div style="display:flex;gap:10px;">
        <button type="submit" class="btn-save">
            Save Club
        </button>

        <a href="manage_club.php" class="btn-cancel">
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