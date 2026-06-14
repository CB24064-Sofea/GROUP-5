<?php
require_once 'auth.php';
requireLogin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="/GROUP%205/index.php" class="nav-item">Dashboard</a>

        <?php if (isAdmin()): ?>
            <div class="nav-group">
                <div class="nav-item has-sub">Student History</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module4/admin/student_list.php" class="sub-nav-item">Student List</a>
                    <a href="/GROUP%205/module1/admin/manage_users.php" class="sub-nav-item">Manage Users</a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Membership</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module1/committee/membership_list.php" class="sub-nav-item">Membership List</a>
                </div>
            </div>
                
            <div class="nav-group">
                <div class="nav-item has-sub">Committee</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module2/manageCommittee/manage_committee.php" class="sub-nav-item">Manage Committee</a>
                    <a href="/GROUP%205/module2/create_committee.php" class="sub-nav-item">Create Committee</a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Clubs</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module2/manageClub/manage_club.php" class="sub-nav-item">Manage Club</a>
                    <a href="/GROUP%205/module2/create_club.php" class="sub-nav-item">Create Club</a>
                </div>
            </div>

            <div class="nav-group">
                <a href="/GROUP%205/module4/admin/reports.php" class="nav-item">Report</a>
            </div>

        <?php elseif (isCommittee()): ?>
            <div class="nav-group">
                <a href="/GROUP%205/module1/student/profile.php" class="nav-item">My Profile</a>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Membership</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module1/committee/membership_list.php" class="sub-nav-item">Membership List</a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Events</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module3/my_registration.php" class="sub-nav-item">My Registrations</a>
                    <a href="/GROUP%205/module3/book_event.php" class="sub-nav-item">Browse Events</a>
                    <a href="/GROUP%205/module3/event_management.php" class="sub-nav-item">Manage Events</a>
                    <a href="/GROUP%205/module3/create_event.php" class="sub-nav-item">Create Event</a>
                    <a href="/GROUP%205/module3/event_participant_list.php" class="sub-nav-item">Participants</a>
                    <a href="/GROUP%205/module3/waitinglist.php" class="sub-nav-item">Waiting List</a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Attendance</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module4/committee/attendance.php" class="sub-nav-item">Attendance Dashboard</a>
                </div>
            </div>

            <div class="nav-group">
                <a href="/GROUP%205/module4/student/history.php" class="nav-item">History</a>
            </div>

        <?php elseif (isStudent()): ?>
            <div class="nav-group">
                <a href="/GROUP%205/module1/student/profile.php" class="nav-item">My Profile</a>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Clubs</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module1/student/club_register.php" class="sub-nav-item">Register Club</a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-item has-sub">Events</div>
                <div class="sub-nav">
                    <a href="/GROUP%205/module3/my_registration.php" class="sub-nav-item">My Registrations</a>
                    <a href="/GROUP%205/module3/book_event.php" class="sub-nav-item">Browse Events</a>
                </div>
            </div>

            <div class="nav-group">
                <a href="/GROUP%205/module4/student/history.php" class="nav-item">History</a>
            </div>
        <?php endif; ?>
    </nav>
    <a href="/GROUP%205/module1/logout.php" class="btn-logout">Logout</a>
</aside>
