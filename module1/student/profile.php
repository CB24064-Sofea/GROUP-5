<?php

include("../includes/auth.php");
include("../config/database.php");

$userID = $_SESSION['userID'];

if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    mysqli_query(
        $conn,
        "UPDATE user
        SET
        name='$name',
        email='$email',
        phoneNumber='$phone'
        WHERE userID='$userID'"
    );

    header("Location: profile.php");
    exit();
}

$query = "
SELECT *
FROM user
WHERE userID='$userID'
";

$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$clubs = mysqli_query(
    $conn,
    "
SELECT c.clubName
FROM membership m
JOIN club c
ON m.clubID = c.clubID
WHERE m.userID='$userID'
AND m.status='Active'
"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Profile</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>

    <div class="main-header">

        <div class="header-left">
            <h1>Club Management System</h1>
        </div>

        <div class="header-right">

            <span class="admin-name">
                <?= $_SESSION['name']; ?>
            </span>

        </div>

    </div>

    <div class="app-container">

        <div class="sidebar">

            <div class="sidebar-nav">

                <a href="dashboard.php" class="nav-item">
                    Dashboard
                </a>

                <a href="profile.php" class="nav-item">
                    My Profile
                </a>

            </div>

            <a href="../logout.php" class="btn-logout">
                Logout
            </a>

        </div>

        <div class="main-content">

            <div class="workspace-stack">

                <div class="page-title">
                    My Profile
                </div>

                <div class="form-card-container">

                    <form method="POST">

                        <div class="form-group-row">

                            <label>Name</label>

                            <input
                                type="text"
                                name="name"
                                value="<?= $user['name']; ?>"
                                class="input-control-select">

                        </div>

                        <div class="form-group-row">

                            <label>Email</label>

                            <input
                                type="email"
                                name="email"
                                value="<?= $user['email']; ?>"
                                class="input-control-select">

                        </div>

                        <div class="form-group-row">

                            <label>Phone Number</label>

                            <input
                                type="text"
                                name="phone"
                                value="<?= $user['phoneNumber']; ?>"
                                class="input-control-select">

                        </div>

                        <div class="form-actions-footer-bar">

                            <button
                                type="submit"
                                name="update"
                                class="btn btn-submit">

                                Update Profile

                            </button>

                        </div>

                    </form>

                </div>

                <div class="form-card-container">

                    <h3>My Club Membership</h3>

                    <br>

                    <?php while ($club = mysqli_fetch_assoc($clubs)) { ?>

                        <p>
                            • <?= $club['clubName']; ?>
                        </p>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

</body>

</html>