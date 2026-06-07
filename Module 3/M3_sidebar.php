<?php
require_once 'auth.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);

function navActive($page, $currentPage) {
    return $page === $currentPage ? 'nav-item active' : 'nav-item';
}
?>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="index.php" class="<?= navActive('index.php', $currentPage) ?>">Dashboard</a>

        <?php if (isCommittee() || isAdmin()): ?>
            <a href="event_management.php" class="<?= navActive('event_management.php', $currentPage) ?>">Manage Events</a>
            <a href="create_event.php" class="<?= navActive('create_event.php', $currentPage) ?>">Create Event</a>
            <a href="event_participant_list.php" class="<?= navActive('event_participant_list.php', $currentPage) ?>">Participants</a>
        <?php endif; ?>

        <?php if (isStudent()): ?>
            <a href="book_event.php" class="<?= navActive('book_event.php', $currentPage) ?>">Browse Events</a>
            <a href="my_registration.php" class="<?= navActive('my_registration.php', $currentPage) ?>">My Registrations</a>
        <?php endif; ?>
    </nav>

    <a href="../logout.php" class="btn-logout">Logout</a>
</aside>