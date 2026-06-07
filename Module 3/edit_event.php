<?php

require_once 'auth.php'; 

// Fetch the event ID from the request
$eventID = isset($_GET['eventID']) ? intval($_GET['eventID']) : 0;

// Fetch current event data
$query = "SELECT * FROM event WHERE eventID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

// Handle form submission
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventName = $_POST['eventName'];
    $eventDescription = $_POST['eventDescription'];
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $venueLocation = $_POST['venueLocation'];
    $maxParticipants = intval($_POST['maxParticipants']);
    $registrationDeadline = $_POST['registrationDeadline'];
    $eventStatus = $_POST['eventStatus'];

    $updateQuery = "UPDATE event SET eventName=?, eventDescription=?, eventDate=?, eventTime=?, venueLocation=?, maxParticipants=?, registrationDeadline=?, eventStatus=? WHERE eventID=?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssissi", $eventName, $eventDescription, $eventDate, $eventTime, $venueLocation, $maxParticipants, $registrationDeadline, $eventStatus, $eventID);

    if ($updateStmt->execute()) {
        header("Location: event_list.php?success=1");
        exit;
    } else {
        $error = "Failed to update event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f6fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .main-header { background-color: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 2px solid #e0e0e0; height: 70px; }
        .app-container { display: flex; flex: 1; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .workspace-stack { width: 100%; max-width: 750px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }
        .page-title { text-align: center; font-size: 1.6rem; color: #2c3e50; border: 1px solid #b2bec3; background-color: #ffffff; padding: 12px; border-radius: 5px; }
        .form-card-container { background-color: #ffffff; border: 1px solid #b2bec3; border-radius: 6px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .form-group-row { display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px; }
        .form-group-row label { font-weight: 600; color: #2c3e50; font-size: 0.95rem; }
        .input-control-select, .input-control-date { width: 100%; padding: 12px 14px; font-size: 0.95rem; border: 1px solid #b2bec3; border-radius: 5px; background-color: #ffffff; outline: none; color: #333; transition: border-color 0.2s; }
        .form-actions-footer-bar { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 10px; }
        .btn { padding: 12px 24px; font-size: 0.95rem; font-weight: 600; border-radius: 5px; cursor: pointer; border: none; text-decoration: none; display: inline-block; text-align: center; transition: all 0.2s; min-width: 160px; }
        .btn-submit { background-color: #2ecc71; color: white; border: 1px solid #27ae60; }
        .btn-cancel { background-color: #ffffff; color: #333; border: 1px solid #b2bec3; }
        .alert { padding: 12px 18px; border-radius: 5px; font-weight: 600; font-size: 0.95rem; text-align: center; border: 1px solid transparent; margin-bottom: 5px; }
        .alert-error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>

<header class="main-header"><h1>Edit Event</h1></header>

<div class="app-container">
    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Edit Event Details</h2>
            <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
            
            <div class="form-card-container">
                <form method="POST">
                    <div class="form-group-row">
                        <label>Event Name</label>
                        <input type="text" name="eventName" class="input-control-select" value="<?php echo htmlspecialchars($event['eventName']); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Description</label>
                        <textarea name="eventDescription" class="input-control-select" required><?php echo htmlspecialchars($event['eventDescription']); ?></textarea>
                    </div>
                    <div class="form-group-row">
                        <label>Date</label>
                        <input type="date" name="eventDate" class="input-control-date" value="<?php echo $event['eventDate']; ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Time</label>
                        <input type="time" name="eventTime" class="input-control-date" value="<?php echo date("H:i", strtotime($event['eventTime'])); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Venue</label>
                        <input type="text" name="venueLocation" class="input-control-select" value="<?php echo htmlspecialchars($event['venueLocation']); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Max Participants</label>
                        <input type="number" name="maxParticipants" class="input-control-select" value="<?php echo $event['maxParticipants']; ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Registration Deadline</label>
                        <input type="datetime-local" name="registrationDeadline" class="input-control-date" value="<?php echo date("Y-m-d\TH:i", strtotime($event['registrationDeadline'])); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Status</label>
                        <select name="eventStatus" class="input-control-select">
                            <option value="Open" <?php if($event['eventStatus']=='Open') echo 'selected'; ?>>Open</option>
                            <option value="Closed" <?php if($event['eventStatus']=='Closed') echo 'selected'; ?>>Closed</option>
                            <option value="Cancelled" <?php if($event['eventStatus']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                            <option value="Completed" <?php if($event['eventStatus']=='Completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-actions-footer-bar">
                        <button type="submit" class="btn btn-submit">Update Event</button>
                        <a href="event_list.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>