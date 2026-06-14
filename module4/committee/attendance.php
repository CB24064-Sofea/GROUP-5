<?php
// Securely ensure active session context tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Strict Role Guard - Restrict operation access exclusively to active Committee accounts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Committee') { 
    header("Location: /GROUP%205/module1/login.php"); 
    exit(); 
}
requireCommitteeOrAdmin();
$page_title = "Committee Dashboard - Event Attendance Listing";
$userID = getUserID();

// Initialize variables
$clubID = 0;
$events = null;

// 1. Fetch assigned club tracking details safely using parameterized execution
$stmt = $conn->prepare("SELECT clubID FROM club_committee WHERE userID = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $clubResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$clubResult) {
        die("<div style='padding: 20px; font-family: sans-serif; color: #991b1b; background: #fee2e2; border-radius: 6px;'>System Exception: Account parameter configuration profile is not mapped to an active club workspace.</div>");
    }
    
    $clubID = (int)$clubResult['clubID'];

    // 2. Query target events matching the assigned club identifier context
    $stmt = $conn->prepare("SELECT eventID, eventName, eventDate, maxParticipants FROM event WHERE clubID = ? ORDER BY eventDate DESC");
    if ($stmt) {
        $stmt->bind_param("i", $clubID);
        $stmt->execute();
        $events = $stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>
    <?php include __DIR__ . '/../../topbar.php'; ?>
    
    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>
        
        <main class="main-content">
            <h1 class="page-title" style="margin-bottom: 5px;">Attendance Track Management</h1>
            <p style="color: #64748b; margin-bottom: 25px;">Review, structure, and modify your club's scheduled event operational registry lists.</p>
            
            <h2 style="font-size: 1.25rem; color: #0f172a; margin-bottom: 15px;">Assigned Workspace Events</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Scheduled Execution Date</th>
                        <th>Participant Cap Size</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($events && $events->num_rows > 0): ?>
                        <?php while($event = $events->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($event['eventName']) ?></td>
                                <td><?= htmlspecialchars($event['eventDate']) ?></td>
                                <td><?= htmlspecialchars($event['maxParticipants']) ?> users max</td>
                                <td style="text-align: center;">
                                    <a href="manage_attendance.php?event_id=<?= urlencode($event['eventID']) ?>" class="action-link" style="display: inline-block; padding: 6px 12px; background-color: #0284c7; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; font-size: 0.9rem;">Manage Attendance</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 30px 10px;">No registered club event data structures found in your active namespace.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php
                $conn->close();
                ?>
        </main>
    </div>
</body>
</html>