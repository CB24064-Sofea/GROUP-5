<?php

include("../includes/auth.php");
include("../config/database.php");

if ($_SESSION['role'] != "Admin") {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query(
    $conn,
    "SELECT * FROM user ORDER BY userID ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Manage Users</title>

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

            </div>

            <a href="../logout.php" class="btn-logout">
                Logout
            </a>

        </div>

        <div class="main-content">

            <div class="workspace-stack">

                <div class="page-title">
                    Manage Users
                </div>

                <div class="form-card-container">

                    <a href="add_user.php"
                        class="btn btn-submit">

                        Add User

                    </a>

                    <br><br>

                    <table width="100%"
                        border="1"
                        cellpadding="10">

                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td><?= $row['userID']; ?></td>

                                <td><?= $row['name']; ?></td>

                                <td><?= $row['username']; ?></td>

                                <td><?= $row['email']; ?></td>

                                <td><?= $row['phoneNumber']; ?></td>

                                <td><?= $row['role']; ?></td>

                                <td>

                                    <a href="edit_user.php?id=<?= $row['userID']; ?>">
                                        Edit
                                    </a>

                                    |

                                    <a
                                        href="delete_user.php?id=<?= $row['userID']; ?>"
                                        onclick="return confirm('Delete User?')">

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