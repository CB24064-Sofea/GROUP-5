<?php
if (!isset($_SESSION['userID'])) return;
$userRole = $_SESSION['role'];
?>
<div class="sidebar">
    <div class="sidebar-nav">
        <?php if ($userRole == 'Admin'): ?>
            <a href="../admin/dashboard.php" class="nav-item">Dashboard</a>
            <a href="../admin/reports.php" class="nav-item">Reports</a>
            <!-- Add any other admin links as per project instruction -->
        
        <?php elseif ($userRole == 'Committee'): ?>
            <a href="../committee/dashboard.php" class="nav-item">Dashboard</a>
            <a href="../committee/dashboard.php" class="nav-item">Events</a>
            <a href="../committee/dashboard.php" class="nav-item">Attendance</a>
            <!-- Committee can manage their club events and attendance -->
        
        <?php else: // Student ?>
            <a href="../student/dashboard.php" class="nav-item">Dashboard</a>
            <a href="../student/dashboard.php" class="nav-item">My Points</a>
            <a href="../student/dashboard.php" class="nav-item">History</a>
            <a href="../student/dashboard.php" class="nav-item">Ranking</a>
        <?php endif; ?>
    </div>
    <a href="../logout.php" class="btn-logout">Logout</a>
</div>