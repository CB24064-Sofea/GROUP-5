<?php
// Securely ensure active session context tracking before evaluating variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Strict Role Guard - Restrict operation access exclusively to active Committee accounts
if (!isset($_SESSION['userID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Committee') {
    header("Location: ../../module1/login.php");
    exit();
}

$regID = isset($_GET['reg_id']) ? (int)$_GET['reg_id'] : 0;
$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($regID <= 0 || $eventID <= 0) {
    die("<div style='padding: 20px; font-family: sans-serif; color: #991b1b; background: #fee2e2; border-radius: 6px;'>System Exception: Invalid query identifier reference keys supplied.</div>");
}

// Fetch student and event details safely via type-safe parameterized execution statements
$sql = "
    SELECT u.name AS student_name, u.userID AS matric_no,
           e.eventName, e.eventDate,
           a.attendanceStatus, a.points, a.volunteer
    FROM event_registration er
    JOIN user u ON er.userID = u.userID
    JOIN event e ON er.eventID = e.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationID = ?
";

$info = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $regID);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();
}

if (!$info) {
    die("<div style='padding: 20px; font-family: sans-serif; color: #991b1b; background: #fee2e2; border-radius: 6px;'>System Exception: Targeted structural attendance mapping record could not be found.</div>");
}

$attStatus = $info['attendanceStatus'] ?? '';
$volunteer = $info['volunteer'] ?? 0;

// Process Form Submission Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Absent';
    
    // Whitelist validate the status value to prevent manual tamper insertion
    if (!in_array($status, ['Present', 'Late', 'Absent'])) {
        $status = 'Absent';
    }
    
    $volunteerChecked = isset($_POST['volunteer']) ? 1 : 0;
    
    // Compute point mechanics based on structural matrix values
    $basePoints = ($status === 'Present') ? 10 : (($status === 'Late') ? 5 : -10);
    $extraPoints = ($volunteerChecked === 1) ? 5 : 0;
    $points = $basePoints + $extraPoints;
    
    // Securely check for existing attendance rows via a parameterized placeholder statement
    $hasRecord = false;
    $checkSql = "SELECT attendanceID FROM attendance WHERE registrationID = ? LIMIT 1";
    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("i", $regID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult && $checkResult->num_rows > 0) {
            $hasRecord = true;
        }
        $checkStmt->close();
    }
    
    if ($hasRecord) {
        // Safe Parameterized Update Execution Object
        $updateSql = "UPDATE attendance SET attendanceStatus = ?, volunteer = ?, points = ? WHERE registrationID = ?";
        if ($stmt = $conn->prepare($updateSql)) {
            $stmt->bind_param("siii", $status, $volunteerChecked, $points, $regID);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Safe Parameterized Insert Execution Object
        $insertSql = "INSERT INTO attendance (registrationID, userID, attendanceQR, attendanceStatus, volunteer, points) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($insertSql)) {
            $matricNo = $info['matric_no'];
            $attendanceQR = 'QR_EVENT_' . $eventID;
            $stmt->bind_param("isssii", $regID, $matricNo, $attendanceQR, $status, $volunteerChecked, $points);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Re-tally cumulative student points balance cache table metric
    if (function_exists('updateStudentPoints')) {
        updateStudentPoints($conn, $info['matric_no']);
    }
    
    $conn->close();
    header("Location: manage_attendance.php?event_id=" . urlencode($eventID));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance Record - FK Management</title>
     <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>
    <?php include __DIR__ . '/../../topbar.php'; ?>
    
    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>
        
        <main class="main-content">
            <div class="form-card-container" style="max-width:600px; margin:40px auto; background:#ffffff; padding:30px; border-radius:8px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h1 class="page-title" style="font-size:1.5rem; color:#0f172a; margin-bottom:20px; border-bottom:2px solid #0284c7; padding-bottom:10px;">Modify Attendance Metrics</h1>
                
                <div style="margin-bottom:20px; padding:15px; background:#f8fafc; border-radius:6px; border:1px solid #e2e8f0;">
                    <h3 style="font-size:1rem; color:#334155; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">Student Metrics</h3>
                    <p style="margin-bottom:4px; color:#0f172a;"><strong>Full Name:</strong> <?= htmlspecialchars($info['student_name']) ?></p>
                    <p style="color:#0f172a;"><strong>Matriculation Index No.:</strong> <?= htmlspecialchars($info['matric_no']) ?></p>
                </div>
                
                <div style="margin-bottom:25px; padding:15px; background:#f8fafc; border-radius:6px; border:1px solid #e2e8f0;">
                    <h3 style="font-size:1rem; color:#334155; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">Target Activity Link</h3>
                    <p style="margin-bottom:4px; color:#0f172a;"><strong>Event Title:</strong> <?= htmlspecialchars($info['eventName']) ?></p>
                    <p style="color:#0f172a;"><strong>Calendar Execution Date:</strong> <?= htmlspecialchars($info['eventDate']) ?></p>
                </div>
                
                <form method="POST" action="edit_attendance.php?reg_id=<?= urlencode($regID); ?>&event_id=<?= urlencode($eventID); ?>">
                    <div class="form-group-row" style="margin-bottom:20px; display:flex; flex-direction:column; gap:6px;">
                        <label style="font-weight:600; color:#334155; font-size:0.9rem;">Evaluation Status State *</label>
                        <select name="status" class="input-control-select" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:1rem;" required>
                            <option value="Present" <?= $attStatus === 'Present' ? 'selected' : '' ?>>Present (+10 point metrics value Allocation)</option>
                            <option value="Late" <?= $attStatus === 'Late' ? 'selected' : '' ?>>Late (+5 point metrics value Allocation)</option>
                            <option value="Absent" <?= $attStatus === 'Absent' ? 'selected' : '' ?>>Absent (−10 point structural baseline Penalty)</option>
                        </select>
                    </div>
                    
                    <div class="form-group-row" style="margin-bottom:30px;">
                        <label style="display:flex; align-items:center; gap:8px; color:#0f172a; font-weight:500; cursor:pointer;">
                            <input type="checkbox" name="volunteer" value="1" <?= $volunteer ? 'checked' : '' ?> style="width:16px; height:16px; accent-color:#0284c7;">
                            Recognize as Assigned Volunteer Helper (+5 additional bonus merit points)
                        </label>
                    </div>
                    
                    <div class="form-actions-footer-bar" style="display:flex; justify-content:flex-end; gap:15px; border-top:1px solid #e2e8f0; padding-top:20px;">
                        <a href="manage_attendance.php?event_id=<?= urlencode($eventID) ?>" class="btn btn-cancel" style="padding:10px 20px; background:#64748b; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:0.95rem;">Cancel Changes</a>
                        <button type="submit" class="btn btn-submit" style="padding:10px 20px; background:#16a34a; color:#ffffff; border:none; border-radius:6px; font-weight:600; font-size:0.95rem; cursor:pointer;">Commit Record Save</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <?php 
    $conn->close();
    ?>
</body>
</html>