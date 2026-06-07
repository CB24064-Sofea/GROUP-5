<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Committee') {
    header("Location: ../login.php");
    exit();
}

$regID = isset($_GET['reg_id']) ? (int)$_GET['reg_id'] : 0;
$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($regID <= 0 || $eventID <= 0) {
    die("Invalid request.");
}

// Fetch student and event details
$sql = "
    SELECT u.name AS student_name, u.userID AS matric_no,
           e.eventName, e.eventDate,
           a.attendanceStatus, a.points, a.volunteer
    FROM event_registration er
    JOIN user u ON er.userID = u.userID
    JOIN event e ON er.eventID = e.eventID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.registrationID = $regID
";
$info = $conn->query($sql)->fetch_assoc();

if (!$info) {
    die("Attendance record not found.");
}

$attStatus = $info['attendanceStatus'] ?? '';
$volunteer = $info['volunteer'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $volunteerChecked = isset($_POST['volunteer']) ? 1 : 0;
    $points = ($status == 'Present' ? 10 : ($status == 'Late' ? 5 : -10)) + ($volunteerChecked ? 5 : 0);
    
    $check = $conn->query("SELECT attendanceID FROM attendance WHERE registrationID = $regID");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE attendance SET attendanceStatus=?, volunteer=?, points=? WHERE registrationID=?");
        $stmt->bind_param("siii", $status, $volunteerChecked, $points, $regID);
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance (registrationID, userID, attendanceStatus, volunteer, points) VALUES (?, ?, ?, ?, ?)");
        $userID = $info['matric_no'];
        $stmt->bind_param("issii", $regID, $userID, $status, $volunteerChecked, $points);
    }
    $stmt->execute();
    
    updateStudentPoints($conn, $info['matric_no']);
    
    header("Location: manage_attendance.php?event_id=$eventID");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Attendance Record</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../topbar.php'; ?>
    <div class="app-container">
        <?php include '../sidebar.php'; ?>
        <div class="form-card-container" style="max-width:600px; margin:40px auto;">
            <h1 class="page-title">Edit Attendance Record</h1>
            
            <!-- Student Info -->
            <div style="margin-bottom:20px;">
                <h3>Student Info</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($info['student_name']) ?></p>
                <p><strong>Matric No.:</strong> <?= htmlspecialchars($info['matric_no']) ?></p>
            </div>
            
            <!-- Event Info -->
            <div style="margin-bottom:20px;">
                <h3>Event</h3>
                <p><strong>Event Name:</strong> <?= htmlspecialchars($info['eventName']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($info['eventDate']) ?></p>
            </div>
            
            <form method="post">
                <div class="form-group-row">
                    <label>Attendance Status *</label>
                    <select name="status" class="input-control-select" required>
                        <option value="Present" <?= $attStatus=='Present'?'selected':'' ?>>Present (+10 points)</option>
                        <option value="Late" <?= $attStatus=='Late'?'selected':'' ?>>Late (+5 points)</option>
                        <option value="Absent" <?= $attStatus=='Absent'?'selected':'' ?>>Absent (−10 points)</option>
                    </select>
                </div>
                
                <div class="form-group-row">
                    <label>
                        <input type="checkbox" name="volunteer" value="1" <?= $volunteer ? 'checked' : '' ?>>
                        Volunteer / Helper (+5 extra points)
                    </label>
                </div>
                
                <div class="form-actions-footer-bar">
                    <button type="submit" class="btn btn-submit">Save Attendance</button>
                    <a href="manage_attendance.php?event_id=<?= $eventID ?>" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
