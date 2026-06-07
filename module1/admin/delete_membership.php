<?php

include("../includes/auth.php");
include("../config/database.php");

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM membership
WHERE membershipID='$id'"
);

header("Location: memberships.php");
exit();
