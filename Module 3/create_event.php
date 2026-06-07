<?php

require_once 'auth.php'; 
require_once 'config.php'; 

try {
    $clubs = $pdo->query("SELECT clubID, clubName FROM club ORDER BY clubName ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clubs = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventTitle = trim($_POST['eventTitle']);
    $eventDescription = trim($_POST['eventDescription']);
    $eventDate = $_POST['eventDate'];
    $eventStartTime = $_POST['eventStartTime'];
    $eventEndTime = $_POST['eventEndTime'];
    $eventVenue = trim($_POST['eventVenue']);
    $eventMaxParticipant = $_POST['eventMaxParticipant'];
    $clubID = $_POST['clubID'];

    try {
       
        $stmt = $pdo->prepare("
            INSERT INTO event 
            (eventName, eventDescription, eventDate, eventStartTime, eventEndTime, eventVenue, eventMaxParticipant, clubID)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $eventTitle,
            $eventDescription,
            $eventDate,
            $eventStartTime,
            $eventEndTime,
            $eventVenue,
            $eventMaxParticipant,
            $clubID
        ]);

        $_SESSION['successMessage'] = "Event created successfully.";
        header("Location: event_management.php");
        exit();
    } catch (PDOException $e) {
        $errorMessage = "Failed to create event: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>
    <style>
       
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-left"><h1>Create Event</h1></div>
        <div class="header-right"><span class="admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span></div>
    </header>

    <div class="app-container">
        <nav class="sidebar">
            <div class="sidebar-nav">
                <a href="event_management.php" class="nav-item">Event Management</a>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </nav>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">New Club Event</h2>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-error"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <div class="form-card-container">
                    <form method="POST">
                        <div class="form-group-row">
                            <label>Event Title</label>
                            <input type="text" name="eventTitle" class="input-control-select" required>
                        </div>
                        <div class="form-group-row">
                            <label>Description</label>
                            <textarea name="eventDescription" class="input-control-select" rows="4" required></textarea>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-group-row">
                                <label>Date</label>
                                <input type="date" name="eventDate" class="input-control-date" required>
                            </div>
                            <div class="form-group-row">
                                <label>Start</label>
                                <input type="time" name="eventStartTime" class="input-control-date" required>
                            </div>
                            <div class="form-group-row">
                                <label>End</label>
                                <input type="time" name="eventEndTime" class="input-control-date" required>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <label>Venue</label>
                            <input type="text" name="eventVenue" class="input-control-select" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group-row">
                                <label>Max Participants</label>
                                <input type="number" name="eventMaxParticipant" class="input-control-select" required>
                            </div>
                            <div class="form-group-row">
                                <label>Club</label>
                                <select name="clubID" class="input-control-select" required>
                                    <option value="">Select Club</option>
                                    <?php foreach ($clubs as $club): ?>
                                        <option value="<?php echo $club['clubID']; ?>">
                                            <?php echo htmlspecialchars($club['clubName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions-footer-bar">
                            <button type="submit" class="btn btn-submit">Create Event</button>
                            <a href="event_management.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>