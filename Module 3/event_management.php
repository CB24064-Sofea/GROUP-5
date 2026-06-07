<?php
require_once 'auth.php'; 
// Fetch messages if any (assuming you use sessions)
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';
unset($_SESSION['successMessage'], $_SESSION['errorMessage']);

// Database operations
try {
    // Counts based on group5.sql schema
    $totalEvents = $pdo->query("SELECT COUNT(*) FROM event")->fetchColumn();
    $upcomingEvents = $pdo->query("SELECT COUNT(*) FROM event WHERE eventDate > CURDATE()")->fetchColumn();
    $completedEvents = $pdo->query("SELECT COUNT(*) FROM event WHERE eventDate <= CURDATE()")->fetchColumn();
    $cancelledEvents = $pdo->query("SELECT COUNT(*) FROM event WHERE eventStatus = 'Cancelled'")->fetchColumn();

    $statusFilter = $_GET['status'] ?? 'All Statuses';
    
    $query = "SELECT * FROM event ORDER BY eventDate DESC";
    $events = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management - FK Student Club</title>
    <style>
        /* [INSERT THE CSS YOU PROVIDED IN THE PROMPT HERE] */
        /* Ensuring standard theme for all pages */
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-left">
            <div class="logo-placeholder">FK Logo</div>
            <h1>Event Management</h1>
        </div>
        <div class="header-right">
            <span class="admin-name"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
        </div>
    </header>

    <div class="app-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">Dashboard</a>
                <div class="submenu-container">
                    <div class="nav-item">Events</div>
                    <div class="submenu">
                        <a href="event_management.php" class="sub-nav-item active-sub">Manage Events</a>
                        <a href="create_event.php" class="sub-nav-item">Create New</a>
                    </div>
                </div>
            </nav>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </aside>

        <main class="main-content">
            <?php if ($successMessage): ?><div class="alert"><?php echo $successMessage; ?></div><?php endif; ?>
            
            <h2 class="page-title">Event Overview</h2>

            <div class="form-card-container" style="display: flex; justify-content: space-around; margin-bottom: 20px;">
                <div>Total: <?php echo $totalEvents; ?></div>
                <div>Upcoming: <?php echo $upcomingEvents; ?></div>
                <div>Completed: <?php echo $completedEvents; ?></div>
            </div>

            <div class="form-card-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #eee;">
                            <th>Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($event['eventTitle']); ?></td>
                            <td><?php echo $event['eventDate']; ?></td>
                            <td><?php echo htmlspecialchars($event['eventVenue']); ?></td>
                            <td><?php echo htmlspecialchars($event['eventStatus']); ?></td>
                            <td>
                                <a href="edit_event.php?id=<?php echo $event['Event_ID']; ?>">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>