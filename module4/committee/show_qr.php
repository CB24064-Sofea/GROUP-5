<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;

requireCommitteeOrAdmin();

$clubID = getCommitteeClubID();

if (!$clubID) {
    die("Committee club not found.");
}

$eventID = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($eventID <= 0) {
    die("Missing or invalid event identifier.");
}

$stmt = $conn->prepare("
    SELECT eventName
    FROM event
    WHERE eventID = ?
    AND clubID = ?
    LIMIT 1
");

$stmt->bind_param("ii", $eventID, $clubID);
$stmt->execute();

$eventInfo = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$eventInfo) {
    die("Security Exception: Event not found or unauthorized access.");
}

$eventName = $eventInfo['eventName'];

/*
|--------------------------------------------------------------------------
| QR Destination URL
|--------------------------------------------------------------------------
| Change student_checkin.php path if necessary
*/
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https://'
    : 'http://';

$host = $_SERVER['HTTP_HOST'];

$checkinUrl =
    $protocol .
    $host .
    '/GROUP%205/module4/student/student_checkin.php?event_id=' .
    $eventID;

try {

    $qrDataUri = (new QRCode())->render($checkinUrl);

} catch (Throwable $e) {

    die(
        '<pre>QR Generation Error:' .
        "\n\n" .
        htmlspecialchars($e->getMessage()) .
        '</pre>'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance QR Code</title>

    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body style="background:#f1f5f9;text-align:center;padding:40px;">

<div style="
    max-width:500px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
">

    <h1>Attendance Check-In QR</h1>

    <p>
        Students scan this code to record attendance.
    </p>

    <div style="margin:20px 0;">
        <img
            src="<?= $qrDataUri ?>"
            alt="QR Code"
            style="width:300px;height:300px;"
        >
    </div>

    <div style="
        background:#f8fafc;
        padding:15px;
        border-radius:8px;
        margin-bottom:20px;
    ">
        <div style="font-size:12px;color:#64748b;">
            ACTIVE EVENT
        </div>

        <strong>
            <?= htmlspecialchars($eventName) ?>
        </strong>
    </div>

    <a
        href="manage_attendance.php?event_id=<?= $eventID ?>"
        class="btn-secondary"
        style="text-decoration:none;padding:10px 20px;"
    >
        Return to Dashboard
    </a>

</div>

</body>
</html>