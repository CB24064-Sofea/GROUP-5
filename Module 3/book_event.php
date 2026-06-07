<?php

require_once 'auth.php'; 
require_once 'config.php'; 

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
    <title>Book Event</title>
    <style>
        
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-left">
            <h1>Club Event System</h1>
        </div>
        <div class="header-right">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
        </div>
    </header>

    <div class="app-container">
        <nav class="sidebar">
            <div class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">Dashboard</a>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </nav>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">Register for Event</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="form-card-container">
                    <form action="" method="POST">
                        <div class="form-group-row">
                            <label>Choose an Available Event</label>
                            <select name="event_id" class="input-control-select" required>
                                <option value="">-- Select Event --</option>
                                <?php foreach ($events as $ev): ?>
                                    <option value="<?php echo $ev['eventID']; ?>">
                                        <?php echo htmlspecialchars($ev['eventName']); ?> (<?php echo htmlspecialchars($ev['eventDate']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-actions-footer-bar">
                            <button type="submit" class="btn btn-submit">Submit Registration</button>
                            <a href="my_registrations.php" class="btn btn-cancel">View My Bookings</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>