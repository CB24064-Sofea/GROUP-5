<?php
require_once '../includes/config.php';
if ($_SESSION['role'] != 'Committee') {
    header("Location: ../login.php");
    exit();
}
$page_title = "Student List";
$students = $conn->query("
    SELECT u.userID, u.name, s.totalPoints, s.recognitionLevel 
    FROM user u 
    JOIN student s ON u.userID = s.userID 
    ORDER BY u.name
");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../topbar.php'; ?>
    <div class="app-container">
        <?php include '../sidebar.php'; ?>
        <div class="main-content">
            <h1 class="page-title">Student List</h1>
            <table class="data-table">
                <thead>
                    <tr><th>Matric No.</th><th>Name</th><th>Total Points</th><th>Recognition</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['userID']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['totalPoints'] ?></td>
                        <td><?= htmlspecialchars($row['recognitionLevel']) ?></td>
                        <td><a href="view_student.php?id=<?= $row['userID'] ?>" class="action-link">View History</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>