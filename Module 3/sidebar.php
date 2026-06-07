<?php
if (!isset($_SESSION['userID'])) return;
$userRole = $_SESSION['role'];
?>
<div class="sidebar">
    <div class="sidebar-nav">
        <?php if ($userRole == 'Admin'): ?>
            <a href="../Module 3/index.php" class="nav-item">Dashboard</a>
            <a href="../admin/reports.php" class="nav-item">Reports</a>
            <!-- Add any other admin links as per project instruction -->
        
        <?php elseif ($userRole == 'Committee'): ?>
            <a href="../Module 3/index.php" class="nav-item">Dashboard</a>
            <a href="../Module 3/event_management.php" class="nav-item">Events</a>
            <a href="../Module 3/event_participant_list.php" class="nav-item">Attendance</a>
            <a href="../committee/members.php" class="nav-item">Members</a>
            <a href="../committee/reports.php" class="nav-item">Reports</a>
            
            <!-- Committee can manage their club events and attendance -->
        
        <?php else: // Student ?>
            <a href="../Module 3/index.php" class="nav-item">Dashboard</a>
            <a href="../Module 3/register_event.php" class="nav-item">Events</a>
            <a href="../Module 3/book_event.php" class="nav-item">My Bookings</a>
            <a href="../Module 3/view_event.php" class="nav-item">History</a>
        <?php endif; ?>
    </div>
    <a href="../logout.php" class="btn-logout">Logout</a>
</div>
