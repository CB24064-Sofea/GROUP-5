<?php

include("../includes/auth.php");
include("../config/database.php");

if ($_SESSION['role'] != "Student") {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

$query = "
SELECT
u.name,
u.email,
s.studyLevel,
s.program,
s.yearOfStudy,
s.semester,
s.totalPoints,
s.recognitionLevel
FROM user u
LEFT JOIN student s
ON u.userID = s.userID
WHERE u.userID='$userID'
";

$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Dashboard</title>

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
                (Student)
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
                    Student Dashboard
                </div>

                <div class="form-card-container">

                    <h3>Name</h3>
                    <p><?= $student['name']; ?></p>

                    <hr><br>

                    <h3>Study Level</h3>
                    <p><?= $student['studyLevel']; ?></p>

                    <hr><br>

                    <h3>Program</h3>
                    <p><?= $student['program']; ?></p>

                    <hr><br>

                    <h3>Year Of Study</h3>
                    <p><?= $student['yearOfStudy']; ?></p>

                    <hr><br>

                    <h3>Semester</h3>
                    <p><?= $student['semester']; ?></p>

                    <hr><br>

                    <h3>Total Points</h3>
                    <p><?= $student['totalPoints']; ?></p>

                    <hr><br>

                    <h3>Recognition Level</h3>
                    <p><?= $student['recognitionLevel']; ?></p>

                </div>

            </div>

        </div>

    </div>

</body>

</html>