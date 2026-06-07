<?php

include("../includes/auth.php");
include("../config/database.php");

$query = "
SELECT
m.membershipID,
u.name,
c.clubName,
m.joinDate,
m.status
FROM membership m
JOIN user u ON m.userID = u.userID
JOIN club c ON m.clubID = c.clubID
ORDER BY m.membershipID DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Membership</title>

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

                <a href="users.php" class="nav-item">
                    Manage Users
                </a>

                <a href="memberships.php" class="nav-item">
                    Manage Membership
                </a>

            </div>

            <a href="../logout.php" class="btn-logout">
                Logout
            </a>

        </div>

        <div class="main-content">

            <div class="workspace-stack">

                <div class="page-title">
                    Manage Membership
                </div>

                <div class="form-card-container">

                    <a href="add_membership.php"
                        class="btn btn-submit">

                        Add Membership

                    </a>

                    <br><br>

                    <table width="100%"
                        border="1"
                        cellpadding="10">

                        <tr>

                            <th>ID</th>
                            <th>Student</th>
                            <th>Club</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td><?= $row['membershipID']; ?></td>

                                <td><?= $row['name']; ?></td>

                                <td><?= $row['clubName']; ?></td>

                                <td><?= $row['joinDate']; ?></td>

                                <td><?= $row['status']; ?></td>

                                <td>

                                    <a
                                        href="delete_membership.php?id=<?= $row['membershipID']; ?>"
                                        onclick="return confirm('Delete Membership?')">

                                        Delete

                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

        </div>

    </div>

</body>

</html>