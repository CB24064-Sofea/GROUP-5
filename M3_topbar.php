<?php
// Set default mode
if (!isset($_SESSION['current_module'])) {
    $_SESSION['current_module'] = 'student';
}

$currentMode = $_SESSION['current_module'];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../STYLE/CSS/Module1/sidebar_CSS.css">

<nav id="sidebar">
    <ul class="list-unstyled components">

        <!-- ================================================= -->
        <!-- ================ COMMITTEE SIDEBAR =============== -->
        <!-- ================================================= -->
        <?php if ($currentMode === 'committee'): ?>

            <!-- Dashboard -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'committee_dashboard.php') ? 'active' : ''; ?>">
                <a href="../Module 1/committee_dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Event Management -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'event_management.php') ? 'active' : ''; ?>">
                <a href="../Module 3/event_management.php">
                    <i class="bi bi-calendar-event me-2"></i>
                    Event Management
                </a>
            </li>

            <!-- Event Participant List -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'event_participant_list.php') ? 'active' : ''; ?>">
                <a href="../Module 3/event_participant_list.php">
                    <i class="bi bi-people-fill me-2"></i>
                    Event Participant List
                </a>
            </li>

            <!-- Attendance Management -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'attendance_management.php') ? 'active' : ''; ?>">
                <a href="../Module 4/attendance_management.php">
                    <i class="bi bi-check2-square me-2"></i>
                    Attendance Management
                </a>
            </li>

                <!-- Attendance Monitoring -->
                <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'attendance_monitoring.php') ? 'active' : ''; ?>">
                    <a href="../Module 4/attendance_monitoring.php">
                        <i class="bi bi-check2-square me-2"></i>
                            Attendance Monitoring
                        </a>
                </li>

            <!-- Back to Student -->
            <li>
                <a href="../Module 1/student_dashboard.php">
                    <i class="bi bi-arrow-return-left me-2"></i>
                    Back to Student Mode
                </a>
            </li>

        <!-- ================================================= -->
        <!-- ================= STUDENT SIDEBAR =============== -->
        <!-- ================================================= -->
        <?php else: ?>

            <!-- Dashboard -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'student_dashboard.php') ? 'active' : ''; ?>">
                <a href="../Module 1/student_dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- My Membership -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'my_membership.php') ? 'active' : ''; ?>">
                <a href="../Module 2/my_membership.php">
                    <i class="bi bi-person-badge me-2"></i>
                    My Membership
                </a>
            </li>

            <!-- Club List -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'club_list.php') ? 'active' : ''; ?>">
                <a href="../Module 2/club_list.php">
                    <i class="bi bi-list-ul me-2"></i>
                    Club List
                </a>
            </li>

            <!-- Event List -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'event_list.php') ? 'active' : ''; ?>">
                <a href="../Module 3/event_list.php">
                    <i class="bi bi-calendar-event me-2"></i>
                    Event List
                </a>
            </li>

            <!-- My Event Registration -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'my_event_registration.php') ? 'active' : ''; ?>">
                <a href="../Module 3/my_event_registration.php">
                    <i class="bi bi-calendar-check me-2"></i>
                    My Event Registration
                </a>
            </li>

            <!-- Participation & Points -->
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Participation_and_Points.php') ? 'active' : ''; ?>">
                <a href="../Module 4/Participation_and_Points.php">
                    <i class="bi bi-award me-2"></i>
                    Participation & Points
                </a>
            </li>

        <?php endif; ?>

    </ul>
</nav>