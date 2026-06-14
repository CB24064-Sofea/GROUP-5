<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($eventID <= 0) {
    die("Invalid session context parameters.");
}

// Fetch event details to populate the header titles dynamically
$stmt = $conn->prepare("SELECT eventName FROM event WHERE eventID = ? LIMIT 1");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    die("Target registration context not found.");
}

$message = "";
$messageClass = "";

// Process form submission when user enters their ID
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = trim($_POST['student_id']);
    
    if (empty($studentID)) {
        $message = "User ID cannot be blank.";
        $messageClass = "error";
    } else {
        // 1. Verify that this specific User ID is successfully registered for the event
        $stmt = $conn->prepare("SELECT registrationID FROM event_registration WHERE eventID = ? AND userID = ? AND registrationStatus = 'Success' LIMIT 1");
        $stmt->bind_param("is", $eventID, $studentID);
        $stmt->execute();
        $regResult = $stmt->get_result();
        
        if ($regResult->num_rows === 0) {
            $message = "You are not registered for this event, or your registration is not approved yet.";
            $messageClass = "error";
            $stmt->close();
        } else {
            $regData = $regResult->fetch_assoc();
            $regID = (int)$regData['registrationID'];
            $stmt->close();
            
            // 2. Prevent duplicate entries by checking if they are already marked present
            $stmt = $conn->prepare("SELECT attendanceID FROM attendance WHERE registrationID = ? LIMIT 1");
            $stmt->bind_param("i", $regID);
            $stmt->execute();
            $attendanceResult = $stmt->get_result();
            
            if ($attendanceResult->num_rows > 0) {
                $message = "Your attendance has already been recorded for this event.";
                $messageClass = "info";
                $stmt->close();
            } else {
                $stmt->close();
                
                // 3. Insert the new log signature cleanly into the table context
                $status = 'Present';
                $points = 10;
                $volunteer = 0;
                $attendanceQR = 'QR_EVENT_' . $eventID;
                
                $insertStmt = $conn->prepare("INSERT INTO attendance (registrationID, userID, attendanceQR, attendanceStatus, volunteer, points) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("isssii", $regID, $studentID, $attendanceQR, $status, $volunteer, $points);
                
                if ($insertStmt->execute()) {
                    // Update cached user score metrics tables if the helper function is linked
                    if (function_exists('updateStudentPoints')) {
                        updateStudentPoints($conn, $studentID);
                    }
                    $message = "Attendance marked successfully! Added: " . $points . " points.";
                    $messageClass = "success";
                } else {
                    $message = "System error writing records. Please try again.";
                    $messageClass = "error";
                }
                $insertStmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Check-in</title>
    <style>
        body { background-color: #f0ebf8; font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #202124; }
        .container { max-width: 600px; margin: 0 auto; }
        .card-header { background-color: #ffffff; border-top: 8px solid #673ab7; border-radius: 8px; padding: 24px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card-header h1 { margin: 0 0 10px 0; font-size: 28px; font-weight: 400; }
        .card-body { background-color: #ffffff; border-radius: 8px; padding: 24px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #dadce0; }
        .input-label { display: block; font-size: 16px; font-weight: 600; margin-bottom: 12px; }
        .input-field { width: 100%; max-width: 300px; border: none; border-bottom: 1px solid #70757a; font-size: 16px; padding: 6px 0; outline: none; }
        .input-field:focus { border-bottom: 2px solid #673ab7; }
        .btn-submit { background-color: #673ab7; color: white; border: none; border-radius: 4px; padding: 10px 24px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .btn-submit:hover { background-color: #512da8; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 16px; font-weight: 500; }
        .alert.success { background-color: #e6f4ea; color: #137333; }
        .alert.error { background-color: #fce8e6; color: #c5221f; }
        .alert.info { background-color: #e8f0fe; color: #1a73e8; }
    </style>
</head>
<body>

<div class="container">
    <div class="card-header">
        <h1>Event Check-in of <?= htmlspecialchars($event['eventName']) ?></h1>
        <p style="color: #70757a; margin: 0; font-size: 14px;">Please complete your individual tracking assignment status below.</p>
        <p style="color: #c5221f; font-size: 12px; margin: 10px 0 0 0;">* Required</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $messageClass ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($messageClass !== 'success'): ?>
        <form method="POST">
            <div class="card-body">
                <label class="input-label" for="student_id">userID <span style="color: #c5221f;">*</span></label>
                <input type="text" id="student_id" name="student_id" class="input-field" placeholder="Your answer" required autocomplete="off">
            </div>
            
            <button type="submit" class="btn-submit">Submit</button>
        </form>
    <?php else: ?>
        <div style="text-align: center; margin-top: 20px; color: #70757a;">
            <p>Your tracking checkpoint response has been compiled successfully.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>