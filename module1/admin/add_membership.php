<?php

include("../includes/auth.php");
include("../config/database.php");

$students = mysqli_query(
    $conn,
    "SELECT userID,name
FROM user
WHERE role='Student'"
);

$clubs = mysqli_query(
    $conn,
    "SELECT clubID,clubName
FROM club"
);

if (isset($_POST['submit'])) {

    $userID = $_POST['userID'];
    $clubID = $_POST['clubID'];
    $status = $_POST['status'];

    mysqli_query(
        $conn,
        "INSERT INTO membership
(userID,clubID,joinDate,status)
VALUES
('$userID',
'$clubID',
CURDATE(),
'$status')"
    );

    header("Location: memberships.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Membership</title>

    <link rel="stylesheet"
        href="../assets/style.css">

</head>

<body>

    <div class="main-content">

        <div class="workspace-stack">

            <div class="page-title">
                Add Membership
            </div>

            <div class="form-card-container">

                <form method="POST">

                    <div class="form-group-row">

                        <label>Student</label>

                        <select
                            name="userID"
                            class="input-control-select">

                            <?php while ($student = mysqli_fetch_assoc($students)) { ?>

                                <option value="<?= $student['userID']; ?>">

                                    <?= $student['name']; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="form-group-row">

                        <label>Club</label>

                        <select
                            name="clubID"
                            class="input-control-select">

                            <?php while ($club = mysqli_fetch_assoc($clubs)) { ?>

                                <option value="<?= $club['clubID']; ?>">

                                    <?= $club['clubName']; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="form-group-row">

                        <label>Status</label>

                        <select
                            name="status"
                            class="input-control-select">

                            <option value="Active">
                                Active
                            </option>

                            <option value="Pending">
                                Pending
                            </option>

                        </select>

                    </div>

                    <div class="form-actions-footer-bar">

                        <button
                            type="submit"
                            name="submit"
                            class="btn btn-submit">

                            Save Membership

                        </button>

                        <a href="memberships.php"
                            class="btn btn-cancel">

                            Back

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>