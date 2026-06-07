<?php
if (!isset($_SESSION['userID'])) return;
$userRole = $_SESSION['role'];

// Determine the base path dynamically based on current module
$currentDir = basename(dirname(__FILE__));
$isModuleDir = (strpos($currentDir, 'Module') === 0) || in_array($currentDir, ['admin', 'committee', 'student']);
$basePath = $isModuleDir ? '..' : '.';
?>
<div class="sidebar">
    <div class="sidebar-nav">
        <?php if ($userRole == 'Admin'): ?>
            <a href="<?php echo $basePath; ?>/admin/dashboard.php" class="nav-item">Dashboard</a>
            <a href="<?php echo $basePath; ?>/admin/reports.php" class="nav-item">Reports</a>
        
        <?php elseif ($userRole == 'Committee'): ?>
            <a href="<?php echo $basePath; ?>/Module 3/index.php" class="nav-item">Dashboard</a>
            <a href="<?php echo $basePath; ?>/Module 3/event_management.php" class="nav-item">Events</a>
            <a href="<?php echo $basePath; ?>/Module 3/event_participant_list.php" class="nav-item">Attendance</a>
            <a href="<?php echo $basePath; ?>/committee/members.php" class="nav-item">Members</a>
            <a href="<?php echo $basePath; ?>/committee/reports.php" class="nav-item">Reports</a>
        
        <?php else: // Student ?>
            <a href="<?php echo $basePath; ?>/Module 3/index.php" class="nav-item">Dashboard</a>
            <a href="<?php echo $basePath; ?>/student/profile.php" class="nav-item">Profile</a>
            <a href="<?php echo $basePath; ?>/Module 3/register_event.php" class="nav-item">Events</a>
            <a href="<?php echo $basePath; ?>/student/points.php" class="nav-item">My Points</a>
            <a href="<?php echo $basePath; ?>/Module 3/view_event.php" class="nav-item">History</a>
        <?php endif; ?>
    </div>
    <a href="<?php echo $basePath; ?>/logout.php" class="btn-logout">Logout</a>
</div>