<?php

session_start();

include("config/database.php");

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$role     = trim($_POST['role']);

$sql = "
SELECT *
FROM user
WHERE username = ?
AND password = ?
AND role = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $username,
    $password,
    $role
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    $user = mysqli_fetch_assoc($result);

    $_SESSION['userID'] = $user['userID'];
    $_SESSION['name']   = $user['name'];
    $_SESSION['role']   = $user['role'];

    if ($user['role'] == "Admin") {

        header("Location: admin/dashboard.php");
        exit();
    } elseif ($user['role'] == "Student") {

        header("Location: student/dashboard.php");
        exit();
    }
} else {

    echo "
    <script>
        alert('Invalid Username, Password or Role');
        window.location='login.php';
    </script>
    ";
}
