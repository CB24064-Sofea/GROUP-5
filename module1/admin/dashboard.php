<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../includes/auth.php");
include("../config/database.php");

if ($_SESSION['role'] != "Admin") {
    header("Location: ../../login.php");
    exit();
}

/* TOTAL STUDENTS */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM student"
);

$row = mysqli_fetch_assoc($result);
$totalStudents = $row['total'];

/* TOTAL ACTIVE CLUBS */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM club
     WHERE status='Active'"
);

$row = mysqli_fetch_assoc($result);
$totalClubs = $row['total'];

/* TOTAL EVENTS */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM event"
);

$row = mysqli_fetch_assoc($result);
$totalEvents = $row['total'];

/* RECENT USERS */
$recentUsers = mysqli_query(
    $conn,
    "SELECT *
     FROM user
     ORDER BY userID DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../assets/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <div class="main-header">

        <div class="header-left">
            <h1>Club Management System</h1>
        </div>

        <div class="header-right">

            <span class="admin-name">

                <?= $_SESSION['name']; ?>

                (<?= $_SESSION['role']; ?>)

            </span>

        </div>

    </div>

    <div class="app-container">

        <!-- SIDEBAR -->

        <div class="sidebar">

            <div class="sidebar-nav">

                <a href="dashboard.php"
                    class="nav-item">

                    Dashboard

                </a>

                <a href="users.php"
                    class="nav-item">

                    Manage Users

                </a>

                <a href="memberships.php"
                    class="nav-item">

                    Manage Membership

                </a>

            </div>

            <a href="../logout.php" class="btn-logout">

                Logout

            </a>

        </div>

        <!-- CONTENT -->

        <div class="main-content">

            <div class="workspace-stack">

                <div class="page-title">

                    Administrator Dashboard

                </div>

                <div class="form-card-container">

                    <h3>Total Registered Students</h3>
                    <p><?= $totalStudents ?></p>

                    <br>

                    <h3>Total Active Clubs</h3>
                    <p><?= $totalClubs ?></p>

                    <br>

                    <h3>Total Events</h3>
                    <p><?= $totalEvents ?></p>

                </div>

                <div class="form-card-container">

                    <canvas id="dashboardChart"></canvas>

                </div>

                <div class="form-card-container">

                    <h3>Recent User Registrations</h3>

                    <br>

                    <table
                        width="100%"
                        border="1"
                        cellpadding="10">

                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                        </tr>

                        <?php while ($user = mysqli_fetch_assoc($recentUsers)) { ?>

                            <tr>

                                <td><?= $user['userID']; ?></td>

                                <td><?= $user['name']; ?></td>

                                <td><?= $user['role']; ?></td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <script>
        const ctx =
            document.getElementById('dashboardChart');

        new Chart(ctx, {

            type: 'pie',

            data: {

                labels: [
                    'Students',
                    'Clubs',
                    'Events'
                ],

                datasets: [{

                    data: [
                        <?= $totalStudents ?>,
                        <?= $totalClubs ?>,
                        <?= $totalEvents ?>
                    ]

                }]

            }

        });
    </script>

</body>

</html>