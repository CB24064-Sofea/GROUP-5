<?php

require_once 'auth.php'; 


$message = '';
$messageType = '';


try {
    $events = $pdo->query("SELECT eventID, eventName, eventDate FROM event")->fetchAll();
} catch (Exception $e) {
    $events = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    $reg_status = 'Pending';
    $reg_date = date('Y-m-d H:i:s');

    try {
        
        $stmt = $pdo->prepare("INSERT INTO event_registration (eventRegistrationStatus, eventRegistrationDate, userID, eventID) VALUES (?, ?, ?, ?)");
        $stmt->execute([$reg_status, $reg_date, $user_id, $event_id]);
        
        $message = "Successfully registered for the event!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: Could not register for the event.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Event - FK Student Club & Event Management</title>
    <link rel="stylesheet" href="../styles/standard.css">
</head>
<body>

    <header class="main-header">
        <div class="header-left">
            <div class="logo-placeholder">
                <img src="https://via.placeholder.com/45?text=UMPSA" alt="Logo">
            </div>
            <h1>FK Student Club & Event Management</h1>
        </div>
        <div class="header-right">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            <div class="profile-container">
                <div class="profile-fallback">👤</div>
            </div>
        </div>
    </header>

    <div class="app-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="event_management.php" class="nav-item">Dashboard</a>
                <a href="events.php" class="nav-item">View Events</a>
                <a href="register_event.php" class="nav-item">Register Event</a>
                <a href="my_registration.php" class="nav-item">My Registrations</a>
            </nav>
            <a href="logout.php" class="btn-logout">Logout</a>
        </aside>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">Book Event</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-card-container">
                    <form action="" method="POST">
                        <div class="form-group-row">
                            <label>Choose an Available Event</label>
                            <select name="event_id" class="input-control-select" required>
                                <option value="">-- Select Event --</option>
                                <?php foreach ($events as $ev): ?>
                                    <option value="<?php echo htmlspecialchars($ev['eventID']); ?>">
                                        <?php echo htmlspecialchars($ev['eventName']); ?> (<?php echo htmlspecialchars($ev['eventDate']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-actions-footer-bar">
                            <button type="submit" class="btn btn-submit">Submit Registration</button>
                            <a href="my_registration.php" class="btn btn-cancel">View My Bookings</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>