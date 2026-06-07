<?php

include("../includes/auth.php");
include("../config/database.php");

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM user WHERE userID='$id'"
);

header("Location: users.php");
exit();
