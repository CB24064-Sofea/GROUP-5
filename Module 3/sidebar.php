<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active-sub' : ''; ?>">Dashboard</a>
        <?php if (isAdmin() || isCommittee()): ?>
        <div class="submenu-container">
            <div class="nav-item">📅 Event Management</div>
            <div class="submenu">
                <a href="events.php" class="sub-nav-item">All Events</a>
                <a href="create_event.php" class="sub-nav-item">Create Event</a>
                <a href="event_dashboard.php" class="sub-nav-item">Event Dashboard</a>
                <a href="event_reports.php" class="sub-nav-item">Event Reports</a>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isStudent()): ?>
        <div class="submenu-container">
            <div class="nav-item">🎟️ My Participation</div>
            <div class="submenu">
                <a href="events.php" class="sub-nav-item">Browse Events</a>
                <a href="my_registrations.php" class="sub-nav-item">My Registrations</a>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isCommittee()): ?>
        <a href="manage_event_participants.php" class="nav-item">📋 Manage Participants</a>
        <?php endif; ?>
    </div>
    <a href="logout.php" class="btn-logout">🚪 Logout</a>
</div>
<div class="main-content">