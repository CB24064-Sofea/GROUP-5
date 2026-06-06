<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;

if ($_SESSION['role'] != 'Committee') die("Access denied.");
$eventID = $_GET['event_id'] ?? 0;
if (!$eventID) die("Event ID missing.");

$base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
$url = $base_url . "/WEB_ENGINEERING/Module_4/committee/mark_attendance.php?event_id=" . $eventID;

// No options – uses default PNG output
$qrCode = new QRCode;
$qrDataUri = $qrCode->render($url); // returns a data URI like "data:image/png;base64,..."
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code Check-in</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-card-container" style="max-width:500px; margin:40px auto; text-align:center;">
        <h1>QR Code Check-in</h1>
        <p>Students scan this code to mark attendance automatically.</p>
        <div style="margin:20px 0;">
            <img src="<?= $qrDataUri ?>" alt="QR Code">
        </div>
        <p><strong>Event ID:</strong> <?= htmlspecialchars($eventID) ?></p>
        <a href="dashboard.php" class="btn btn-cancel">Back to Dashboard</a>
    </div>
</body>
</html>