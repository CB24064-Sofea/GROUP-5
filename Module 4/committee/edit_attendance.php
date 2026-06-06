<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if ($_SESSION['role'] != 'Committee') exit;
$regID = $_GET['reg_id'];
$eventID = $_GET['event_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $volunteer = isset($_POST['volunteer']) ? 1 : 0;
    $points = ($status == 'Present' ? 10 : ($status == 'Late' ? 5 : -10)) + ($volunteer ? 5 : 0);
    $stmt = $conn->prepare("UPDATE attendance SET attendanceStatus=?, volunteer=?, points=? WHERE registrationID=?");
    $stmt->bind_param("siii", $status, $volunteer, $points, $regID);
    $stmt->execute();
    $userID = $conn->query("SELECT userID FROM event_registration WHERE registrationID='$regID'")->fetch_assoc()['userID'];
    updateStudentPoints($conn, $userID);
    header("Location: manage_attendance.php?event_id=$eventID");
    exit();
}

$att = $conn->query("SELECT * FROM attendance WHERE registrationID='$regID'")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Attendance</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="form-card-container" style="max-width:500px; margin:40px auto;">
    <h1>Edit Attendance Record</h1>
    <form method="post">
        <div class="form-group-row">
            <label>Status</label>
            <select name="status" class="input-control-select">
                <option <?= ($att['attendanceStatus']??'')=='Present'?'selected':'' ?>>Present</option>
                <option <?= ($att['attendanceStatus']??'')=='Late'?'selected':'' ?>>Late</option>
                <option <?= ($att['attendanceStatus']??'')=='Absent'?'selected':'' ?>>Absent</option>
            </select>
        </div>
        <div class="form-group-row">
            <label><input type="checkbox" name="volunteer" <?= ($att['volunteer']??0)?'checked':'' ?>> Volunteer / Helper (+5 points)</label>
        </div>
        <div class="form-actions-footer-bar">
            <button type="submit" class="btn btn-submit">Save Attendance</button>
            <a href="manage_attendance.php?event_id=<?= $eventID ?>" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>