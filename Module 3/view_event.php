<?php
// Include your authentication and configuration files
require_once 'auth.php'; 
require_once 'config.php';

// Check if user is logged in (Assuming auth.php manages this)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Module 1/index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: event_management.php");
    exit();
}

// Adjusted SQL to match group5.sql schema (column names)
$stmt = $pdo->prepare("
    SELECT 
        e.*,
        c.clubName
    FROM event e
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE e.eventID = ?
");

$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: event_management.php");
    exit();
}

// Count registrations
$regStmt = $pdo->prepare("SELECT COUNT(*) FROM event_registration WHERE eventID = ?");
$regStmt->execute([$id]);
$totalRegistered = $regStmt->fetchColumn();

// Calculate progress percentage
$capacity = (int)$event['maxParticipants'];
$percentage = $capacity > 0 ? min(100, round(($totalRegistered / $capacity) * 100)) : 0;

// Determine status
$eventTimestamp = strtotime($event['eventDate']);
$currentTimestamp = strtotime(date('Y-m-d'));

if ($event['eventStatus'] == 'Completed') {
    $statusBadge = '<span class="badge bg-secondary">Completed</span>';
} elseif ($eventTimestamp > $currentTimestamp) {
    $statusBadge = '<span class="badge bg-primary">Upcoming</span>';
} else {
    $statusBadge = '<span class="badge bg-success">Ongoing</span>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event</title>
    <style>
        /* Insert the exact CSS provided in your prompt here */
    </style>
</head>
<body>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>
    
    <div class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title"><?php echo htmlspecialchars($event['eventName']); ?></h2>
            
            <div class="form-card-container">
                <div class="form-group-row">
                    <label>Event Description</label>
                    <p><?php echo nl2br(htmlspecialchars($event['eventDescription'])); ?></p>
                </div>

                <div class="form-group-row">
                    <label>Event Date & Time</label>
                    <div><?php echo date('d M Y', strtotime($event['eventDate'])); ?> | <?php echo date('h:i A', strtotime($event['eventTime'])); ?></div>
                </div>

                <div class="form-group-row">
                    <label>Venue</label>
                    <div><?php echo htmlspecialchars($event['venueLocation']); ?></div>
                </div>

                <div class="form-group-row">
                    <label>Participation</label>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <small><?php echo $totalRegistered; ?> / <?php echo $event['maxParticipants']; ?> registered</small>
                </div>

                <div class="form-actions-footer-bar">
                    <a href="event_management.php" class="btn btn-cancel">Back</a>
                    <a href="edit_event.php?id=<?php echo $event['eventID']; ?>" class="btn btn-submit">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>