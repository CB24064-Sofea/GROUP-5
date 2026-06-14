<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

// Strict Role Access Guard
requireCommitteeOrAdmin();

$eventID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventID <= 0 || !canManageEvent($eventID)) {
    $_SESSION['errorMessage'] = "Invalid event selected or you do not have permission to manage it.";
    header("Location: event_management.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM event WHERE eventID = ?");
$stmt->bind_param("i", $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION['errorMessage'] = "Event not found.";
    header("Location: event_management.php");
    exit();
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = trim($_POST['eventName']);
    $eventDescription = trim($_POST['eventDescription']);
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $venueLocation = trim($_POST['venueLocation']);
    $maxParticipants = (int)$_POST['maxParticipants'];
    $registrationDeadline = str_replace('T', ' ', $_POST['registrationDeadline']) . ':00';
    $eventStatus = $_POST['eventStatus'];

    if (!$eventName || !$eventDescription || !$eventDate || !$eventTime || !$venueLocation || $maxParticipants < 1) {
        $errorMessage = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare("
            UPDATE event 
            SET eventName = ?, 
                eventDescription = ?, 
                eventDate = ?, 
                eventTime = ?, 
                venueLocation = ?, 
                maxParticipants = ?, 
                registrationDeadline = ?, 
                eventStatus = ? 
            WHERE eventID = ?
        ");

        $stmt->bind_param(
            "sssssissi",
            $eventName,
            $eventDescription,
            $eventDate,
            $eventTime,
            $venueLocation,
            $maxParticipants,
            $registrationDeadline,
            $eventStatus,
            $eventID
        );

        if ($stmt->execute()) {
            $_SESSION['successMessage'] = "Event profile modifications saved successfully.";
            header("Location: event_management.php");
            exit();
        }

        $errorMessage = "System error occurred while updating the event registry entry.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../topbar.php';  ?>

    <div class="app-container">
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">

    <div style="
        max-width:650px;
        margin:0 auto;
    ">

        <div style="
            background:#ffffff;
            border-radius:8px;
            padding:25px;
            box-shadow:0 2px 8px rgba(0,0,0,.08);
        ">

            <h2 style="
                text-align:center;
                color:#0f2d5c;
                margin-bottom:5px;
                font-size:24px;
                font-weight:700;
            ">
                Modify Event Configuration
            </h2>

            <p style="
                text-align:center;
                color:#64748b;
                font-size:12px;
                margin-bottom:25px;
            ">
                Update the event information below.
            </p>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_event.php?id=<?= $eventID ?>">

                <table style="width:100%; border-collapse:collapse;">

                    <tr>
                        <td style="width:180px;padding:8px;">
                            Event Name
                        </td>

                        <td style="padding:8px;">
                            <input
                                type="text"
                                name="eventName"
                                value="<?= htmlspecialchars($event['eventName']) ?>"
                                required
                                style="width:100%;"
                            >
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px;">
                            Description
                        </td>

                        <td style="padding:8px;">
                            <textarea
                                name="eventDescription"
                                rows="4"
                                required
                                style="width:100%; resize:none;"
                            ><?= htmlspecialchars($event['eventDescription']) ?></textarea>
                        </td>
                    </tr>

                </table>

                <div style="
                    display:grid;
                    grid-template-columns:1fr 1fr;
                    gap:20px;
                    margin-top:10px;
                ">

                    <div>
                        <label>Date</label>
                        <input
                            type="date"
                            name="eventDate"
                            value="<?= htmlspecialchars($event['eventDate']) ?>"
                            required
                            style="width:100%;"
                        >
                    </div>

                    <div>
                        <label>Time</label>
                        <input
                            type="time"
                            name="eventTime"
                            value="<?= htmlspecialchars(substr($event['eventTime'],0,5)) ?>"
                            required
                            style="width:100%;"
                        >
                    </div>

                    <div>
                        <label>Venue Location</label>
                        <input
                            type="text"
                            name="venueLocation"
                            value="<?= htmlspecialchars($event['venueLocation']) ?>"
                            required
                            style="width:100%;"
                        >
                    </div>

                    <div>
                        <label>Registration Closing Deadline</label>
                        <input
                            type="datetime-local"
                            name="registrationDeadline"
                            value="<?= date('Y-m-d\TH:i', strtotime($event['registrationDeadline'])) ?>"
                            required
                            style="width:100%;"
                        >
                    </div>

                    <div>
                        <label>Maximum Seats / Slots</label>
                        <input
                            type="number"
                            name="maxParticipants"
                            min="1"
                            value="<?= (int)$event['maxParticipants'] ?>"
                            required
                            style="width:100%;"
                        >
                    </div>

                    <div>
                        <label>Current Event Status</label>

                        <select
                            name="eventStatus"
                            style="width:100%;"
                            required
                        >
                            <option value="Open" <?= $event['eventStatus']=='Open'?'selected':'' ?>>
                                Open (Accepting Registrations)
                            </option>

                            <option value="Closed" <?= $event['eventStatus']=='Closed'?'selected':'' ?>>
                                Closed
                            </option>

                            <option value="Cancelled" <?= $event['eventStatus']=='Cancelled'?'selected':'' ?>>
                                Cancelled
                            </option>

                            <option value="Completed" <?= $event['eventStatus']=='Completed'?'selected':'' ?>>
                                Completed
                            </option>
                        </select>
                    </div>

                </div>

                <div style="
                    margin-top:30px;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                ">

                    <a
                        href="event_management.php"
                        style="
                            color:#1d4ed8;
                            text-decoration:none;
                            font-size:14px;
                        "
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        style="
                            background:#0f2d5c;
                            color:white;
                            border:none;
                            padding:12px 22px;
                            border-radius:6px;
                            font-weight:600;
                            cursor:pointer;
                        "
                    >
                        💾 Save Event Updates
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>
    </div>
</body>
</html>