<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';
// Allow access if the user is either a Student OR a Committee member
if (!isStudent() && !isCommittee()) {
    header("Location: /GROUP%205/login.php");
    exit();
}

require_once __DIR__ . '/waitinglist_helper.php';

$userID = getUserID();
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_registration_id'])) {
    $registrationID = (int)$_POST['cancel_registration_id'];

    $stmt = $conn->prepare("
        SELECT eventID
        FROM event_registration
        WHERE registrationID = ?
        AND userID = ?
        AND registrationStatus = 'Success'
    ");
    $stmt->bind_param("is", $registrationID, $userID);
    $stmt->execute();
    $registration = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($registration) {
        $stmt = $conn->prepare("
            UPDATE event_registration
            SET registrationStatus = 'Cancel',
                cancellationDate = NOW()
            WHERE registrationID = ?
        ");
        $stmt->bind_param("i", $registrationID);
        $stmt->execute();
        $stmt->close();

        $promoted = promoteNextFromWaitingList($conn, (int)$registration['eventID']);

        if ($promoted) {
            $successMessage = "Registration cancelled. The next participant in the waiting list has been promoted.";
        } else {
            $successMessage = "Registration cancelled successfully.";
        }
    } else {
        $errorMessage = "Registration could not be cancelled.";
    }
}

$stmt = $conn->prepare("
    SELECT 
        er.*,
        e.eventName,
        e.eventDate,
        e.eventTime,
        e.venueLocation,
        c.clubName
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE er.userID = ?
    ORDER BY e.eventDate DESC, e.eventTime DESC
");
$stmt->bind_param("s", $userID);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
    SELECT 
        w.*,
        e.eventName,
        e.eventDate,
        e.eventTime,
        e.venueLocation,
        c.clubName
    FROM waitinglist w
    JOIN event e ON w.eventID = e.eventID
    LEFT JOIN club c ON e.clubID = c.clubID
    WHERE w.userID = ?
    ORDER BY w.registerAt DESC
");
$stmt->bind_param("s", $userID);
$stmt->execute();
$waitingList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .app-container {
            display: flex;
            min-height: calc(100vh - 60px);
            margin-top: 60px;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 32px;
            box-sizing: border-box;
        }
        .workspace-stack {
            max-width: 1100px;
            margin: 0 auto;
        }
        .page-title {
            font-size: 26px;
            font-weight: 700;
            color: #0a2540;
            margin-bottom: 24px;
        }
        .form-card-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }
        .form-card-container h3 {
            margin: 0 0 16px 0;
            font-size: 16px;
            color: #0a2540;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            background-color: #e2e8f0;
            color: #475569;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../topbar.php';  ?>

    <div class="app-container">
        
        <?php include __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">
                <h1 class="page-title">My Event Registrations</h1>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <div class="form-card-container">
                    <h3>Registered Events</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$registrations): ?>
                                <tr>
                                    <td colspan="6">No registrations found.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($registrations as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['eventName']) ?></strong><br>
                                        <small><?= htmlspecialchars($row['clubName'] ?? '-') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['eventDate']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['eventTime'], 0, 5)) ?></td>
                                    <td><?= htmlspecialchars($row['venueLocation']) ?></td>
                                    <td>
                                        <span class="status-badge">
                                            <?= htmlspecialchars($row['registrationStatus']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['registrationStatus'] === 'Success'): ?>
                                            <form method="POST" onsubmit="return confirm('Cancel this registration?');">
                                                <input type="hidden" name="cancel_registration_id" value="<?= $row['registrationID'] ?>">
                                                <button type="submit" class="btn btn-danger">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-card-container">
                    <h3>Waiting List</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Position</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$waitingList): ?>
                                <tr>
                                    <td colspan="6">You are not in any waiting list.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($waitingList as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['eventName']) ?></strong><br>
                                        <small><?= htmlspecialchars($row['clubName'] ?? '-') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['eventDate']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['eventTime'], 0, 5)) ?></td>
                                    <td><?= htmlspecialchars($row['venueLocation']) ?></td>
                                    <td><?= (int)$row['position'] ?></td>
                                    <td><?= htmlspecialchars($row['registerAt']) ?></td>
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