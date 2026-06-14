<?php
// Ensure active session context tracking before evaluating variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

requireCommitteeOrAdmin();

$userID = $_SESSION['userID'];

// 1. Fetch assigned club tracking details safely using parameterized execution
$stmt = $conn->prepare("SELECT clubID FROM club_committee WHERE userID = ? LIMIT 1");
if (!$stmt) {
    die("Framework Initialization Error: Unable to compile statement.");
}
$stmt->bind_param("s", $userID);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$club) {
    die("System Exception: Account configuration profile is not mapped to an active club workspace.");
}
$clubID = (int)$club['clubID'];

// 2. Validate URL Event Query Identifier
$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($eventID <= 0) {
    die("Invalid Parameter Error: Missing tracking context identifier keys.");
}

// 3. Fetch event details while explicitly enforcing club ownership constraints
$stmt = $conn->prepare("SELECT eventName, eventDate, eventTime FROM event WHERE eventID = ? AND clubID = ?");
if (!$stmt) {
    die("Framework Initialization Error: Unable to compile event verification statement.");
}
$stmt->bind_param("ii", $eventID, $clubID);
$stmt->execute();
$eventInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$eventInfo) {
    die("Security Exception: Requested event record does not exist or does not belong to your assigned club workspace.");
}

$eventName = $eventInfo['eventName'];
$eventDate = $eventInfo['eventDate'];
$eventTime = (!empty($eventInfo['eventTime'])) ? date("h:i A", strtotime($eventInfo['eventTime'])) : 'N/A';

// 4. Fetch participants registered specifically to this authorized club event context
$stmt = $conn->prepare("
    SELECT u.name, u.userID, er.registrationID, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN user u ON er.userID = u.userID
    JOIN event e ON er.eventID = e.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.eventID = ? AND e.clubID = ? AND er.registrationStatus = 'Success'
    ORDER BY u.name ASC
");
if (!$stmt) {
    die("Framework Initialization Error: Unable to compile structural registry layout statement.");
}
$stmt->bind_param("ii", $eventID, $clubID);
$stmt->execute();
$participants = $stmt->get_result();

$page_title = "Mark Attendance Management";
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
            <h1 class="page-title">Manage Attendance</h1>
            
            <div style="margin-bottom: 25px; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h2 style="margin: 0 0 10px 0; font-size: 1.4rem; color: #0f172a;"><?= htmlspecialchars($eventName) ?></h2>
                <div style="color: #475569; font-size: 0.95rem;">
                    <strong>Scheduled Execution Date:</strong> <?= htmlspecialchars($eventDate) ?> &nbsp;&bull;&nbsp;
                    <strong>Target Start Time:</strong> <?= htmlspecialchars($eventTime) ?>
                </div>
            </div>

            <div class="qr-code-container" style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <a href="show_qr.php?event_id=<?= urlencode($eventID) ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; padding: 10px 18px; background-color: #0284c7; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.95rem; border: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    Display Tracking QR Code Matrix
                </a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matriculation No.</th>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                        <th>Merit Points Balance</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants && $participants->num_rows > 0): ?>
                        <?php while ($p = $participants->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.95rem; color: #334155;"><?= htmlspecialchars($p['userID']) ?></td>
                            <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($p['name']) ?></td>
                            <td>
                                <?php if (!empty($p['attendanceStatus'])): ?>
                                    <span class="status-badge status-<?= strtolower(htmlspecialchars($p['attendanceStatus'])) ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                        <?= htmlspecialchars($p['attendanceStatus']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-style: italic;">Pending Check-In</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #475569;"><?= isset($p['points']) ? htmlspecialchars($p['points']) : '-' ?></strong>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">

                                <?php if (empty($p['attendanceStatus'])): ?>
                                    <a href="mark_attendance.php?event_id=<?= urlencode($eventID) ?>&reg_id=<?= urlencode($p['registrationID']) ?>"
                                    style="
                                            display:inline-block;
                                            padding:6px 12px;
                                            background:#16a34a;
                                            color:white;
                                            text-decoration:none;
                                            border-radius:5px;
                                            font-size:13px;
                                            font-weight:600;
                                            margin-right:6px;
                                    ">
                                        Mark Attendance
                                    </a>
                                <?php endif; ?>

                                <a href="edit_attendance.php?reg_id=<?= urlencode($p['registrationID']) ?>&event_id=<?= urlencode($eventID) ?>"
                                style="
                                        display:inline-block;
                                        padding:6px 12px;
                                        background:#0284c7;
                                        color:white;
                                        text-decoration:none;
                                        border-radius:5px;
                                        font-size:13px;
                                        font-weight:600;
                                ">
                                    Edit
                                </a>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 40px 10px;">No students have successfully registered for this event yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <a href="attendance.php" class="btn btn-cancel" style="padding: 10px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">Return to Dashboard</a>
            </div>
        </main>
    </div>
    
    <?php 
    $stmt->close();
    $conn->close(); 
    ?>
</body>
</html>