<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';

requireCommitteeOrAdmin();

$clubID = getCommitteeClubID();
$eventID = isset($_GET['eventID']) ? (int)$_GET['eventID'] : 0;

if (isCommittee()) {
    $stmt = $conn->prepare("
        SELECT eventID, eventName
        FROM event
        WHERE clubID = ?
        ORDER BY eventDate DESC
    ");
    $stmt->bind_param("i", $clubID);
} else {
    $stmt = $conn->prepare("
        SELECT eventID, eventName
        FROM event
        ORDER BY eventDate DESC
    ");
}

$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($eventID === 0 && $events) {
    $eventID = (int)$events[0]['eventID'];
}

$participants = [];

if ($eventID > 0 && canManageEvent($eventID)) {
    $stmt = $conn->prepare("
        SELECT
            er.registrationID,
            er.registrationStatus,
            er.registrationDate,
            u.userID,
            u.name,
            u.email,
            s.program
        FROM event_registration er
        JOIN user u ON er.userID = u.userID
        LEFT JOIN student s ON u.userID = s.userID
        WHERE er.eventID = ?
        ORDER BY u.name ASC
    ");
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Participant List</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>
<?php include __DIR__ . '/../topbar.php'; ?>

<div class="app-container">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <main class="main-content">

    <div class="workspace-stack">

        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">

            <div>
                <h3 style="font-size:16px;color:#1e293b;margin-bottom:4px;">
                    Event Participant List
                </h3>

                <p style="font-size:13px;color:#64748b;">
                    Manage and monitor registrations for institution events.
                </p>
            </div>

            <form method="GET">
                <label style="display:block;font-size:12px;color:#475569;margin-bottom:5px;">
                    Event Selection
                </label>

                <select
                    name="eventID"
                    onchange="this.form.submit()"
                    style="
                        min-width:220px;
                        padding:10px;
                        border:1px solid #cbd5e1;
                        border-radius:4px;
                    "
                >
                    <?php foreach ($events as $event): ?>
                        <option
                            value="<?= $event['eventID'] ?>"
                            <?= $eventID == $event['eventID'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($event['eventName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

        </div>

        <div style="
            background:#ffffff;
            border:1px solid #e2e8f0;
            border-radius:6px;
            overflow:hidden;
        ">

            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                padding:15px 20px;
                border-bottom:1px solid #e2e8f0;
            ">

                <div style="font-size:13px;font-weight:600;color:#334155;">
                    👥 <?= count($participants) ?> Participants Registered
                </div>

                <a href="#"
                   style="
                        text-decoration:none;
                        color:#0f4c81;
                        font-size:12px;
                        font-weight:600;
                   ">
                    ⬇ Export CSV
                </a>

            </div>

            <table style="
                width:100%;
                border-collapse:collapse;
                font-size:13px;
            ">

                <thead>
                    <tr style="background:#f8fafc;">

                        <th style="padding:14px;text-align:left;">USER ID</th>
                        <th style="padding:14px;text-align:left;">NAME</th>
                        <th style="padding:14px;text-align:left;">PROGRAMME</th>
                        <th style="padding:14px;text-align:left;">EMAIL</th>
                        <th style="padding:14px;text-align:left;">STATUS</th>
                        <th style="padding:14px;text-align:left;">REGISTERED AT</th>
                        <th style="padding:14px;text-align:center;">ACTION</th>

                    </tr>
                </thead>

                <tbody>

                <?php if(empty($participants)): ?>

                    <tr>
                        <td colspan="7" style="padding:30px;text-align:center;">
                            No participants found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach($participants as $participant): ?>

                    <tr style="border-top:1px solid #e2e8f0;">

                        <td style="padding:16px;font-weight:600;">
                            <?= htmlspecialchars($participant['userID']) ?>
                        </td>

                        <td style="padding:16px;">
                            <?= htmlspecialchars($participant['name']) ?>
                        </td>

                        <td style="padding:16px;">
                            <?= htmlspecialchars($participant['program'] ?? '-') ?>
                        </td>

                        <td style="padding:16px;">
                            <a href="mailto:<?= htmlspecialchars($participant['email']) ?>">
                                <?= htmlspecialchars($participant['email']) ?>
                            </a>
                        </td>

                        <td style="padding:16px;">

                            <span style="
                                background:#dcfce7;
                                color:#15803d;
                                border:1px solid #bbf7d0;
                                border-radius:20px;
                                padding:4px 10px;
                                font-size:12px;
                            ">
                                <?= htmlspecialchars($participant['registrationStatus']) ?>
                            </span>

                        </td>

                        <td style="padding:16px;">
                            <?= htmlspecialchars($participant['registrationDate']) ?>
                        </td>

                        <td style="padding:16px;text-align:center;">

                            <?php if($participant['registrationStatus']=='Success'): ?>

                                <a
                                    href="manage_event_participant.php?action=remove&registrationID=<?= $participant['registrationID'] ?>&eventID=<?= $eventID ?>"
                                    onclick="return confirm('Remove this participant?');"
                                    style="
                                        color:#991b1b;
                                        text-decoration:none;
                                        font-size:18px;
                                    "
                                >
                                    🗑️
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>
</div>
</body>
</html>