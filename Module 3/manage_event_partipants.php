<?php
require_once 'auth.php';
require_once 'config.php';

/** @var PDO $pdo */

// Ensure user is logged in and is a Student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../Module 1/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// --- LOGIC: Cancel Registration and Auto-Promote from Waiting List ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_registration_id'])) {
    $reg_id = $_POST['cancel_registration_id'];

    // 1. Get the Event ID for the registration being cancelled
    $stmt = $pdo->prepare("SELECT Event_ID FROM event_registration WHERE EventRegistration_ID = ? AND User_ID = ?");
    $stmt->execute([$reg_id, $user_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        $event_id = $event['Event_ID'];

        // 2. Mark current registration as Cancelled
        $update = $pdo->prepare("UPDATE event_registration SET eventRegistrationStatus = 'Cancelled' WHERE EventRegistration_ID = ?");
        $update->execute([$reg_id]);

        // 3. Check for the next person in line
        $next = $pdo->prepare("SELECT * FROM waitinglist WHERE eventID = ? ORDER BY timestamp ASC LIMIT 1");
        $next->execute([$event_id]);
        $waiter = $next->fetch(PDO::FETCH_ASSOC);

        if ($waiter) {
            // Promote them: Insert into registration
            $promote = $pdo->prepare("INSERT INTO event_registration (Event_ID, User_ID, eventRegistrationDate, eventRegistrationStatus) VALUES (?, ?, NOW(), 'Approved')");
            $promote->execute([$event_id, $waiter['userID']]);

            // Remove from waiting list
            $remove = $pdo->prepare("DELETE FROM waitinglist WHERE waitingID = ?");
            $remove->execute([$waiter['waitingID']]);
            
            $message = "Registration cancelled successfully. The next student in the waiting list has been automatically registered.";
        } else {
            $message = "Registration cancelled successfully.";
        }
        $messageType = "success";
    }
}

// Fetch Registrations
$stmt = $pdo->prepare("SELECT er.*, e.eventTitle, e.eventDate, e.eventStartTime, e.eventEndTime, c.clubName 
                       FROM event_registration er 
                       JOIN event e ON er.Event_ID = e.Event_ID 
                       LEFT JOIN club c ON e.Club_ID = c.Club_ID 
                       WHERE er.User_ID = ? ORDER BY e.eventDate DESC");
$stmt->execute([$user_id]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Event Registrations</title>
    <style>
        /* [Insert the Standard CSS you provided here] */
        /* ... (Copy your entire provided CSS block here) ... */
    </style>
</head>
<body>

<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h1 class="page-title">My Event Registrations</h1>

            <?php if($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-card-container">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e0e0e0;">
                            <th style="padding:15px; text-align:left;">Event</th>
                            <th style="padding:15px; text-align:left;">Date</th>
                            <th style="padding:15px; text-align:left;">Status</th>
                            <th style="padding:15px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($registrations as $row): ?>
                        <tr style="border-bottom: 1px solid #f1f2f6;">
                            <td style="padding:15px;">
                                <strong><?php echo htmlspecialchars($row['eventTitle']); ?></strong><br>
                                <small style="color:#64748b;"><?php echo htmlspecialchars($row['clubName']); ?></small>
                            </td>
                            <td style="padding:15px;">
                                <?php echo date("M d, Y", strtotime($row['eventDate'])); ?>
                            </td>
                            <td style="padding:15px;">
                                <span class="status-badge <?php echo strtolower($row['eventRegistrationStatus']); ?>">
                                    <?php echo htmlspecialchars($row['eventRegistrationStatus']); ?>
                                </span>
                            </td>
                            <td style="padding:15px; text-align:center;">
                                <?php if($row['eventRegistrationStatus'] !== 'Cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Cancel this registration?');">
                                        <input type="hidden" name="cancel_registration_id" value="<?php echo $row['EventRegistration_ID']; ?>">
                                        <button type="submit" class="btn btn-cancel">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>