<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Strict Role Guard - Only Administrators can view raw history logs
if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

// Extract and sanitize input query parameter
$studentID = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($studentID)) {
    die("Error: Student tracking token missing.");
}

// 1. Fetch Student master details securely using prepared statements
$infoStmt = $conn->prepare("
    SELECT
    u.name,
    s.totalPoints,
    s.recognitionLevel,
    s.program,
    s.studyLevel,
    s.yearOfStudy,
    s.semester
    FROM user u 
    JOIN student s ON u.userID = s.userID 
    WHERE u.userID = ? 
    LIMIT 1
");
$infoStmt->bind_param("s", $studentID);
$infoStmt->execute();
$studentInfo = $infoStmt->get_result()->fetch_assoc();
$infoStmt->close();

if (!$studentInfo) {
    die("Error: The requested student record could not be found within the database system.");
}

// 2. Count absolute verified events attended securely
$countStmt = $conn->prepare("
    SELECT COUNT(*) as cnt 
    FROM event_registration er 
    JOIN attendance a ON er.registrationID = a.registrationID 
    WHERE er.userID = ? AND a.attendanceStatus IN ('Present','Late')
");
$countStmt->bind_param("s", $studentID);
$countStmt->execute();
$eventCount = $countStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$countStmt->close();

// 3. Fetch full operational participation chronicle history
$historyStmt = $conn->prepare("
    SELECT e.eventName, c.clubName, e.eventDate, a.attendanceStatus, a.points
    FROM event_registration er
    JOIN event e ON er.eventID = e.eventID
    JOIN club c ON e.clubID = c.clubID
    LEFT JOIN attendance a ON er.registrationID = a.registrationID
    WHERE er.userID = ? AND er.registrationStatus = 'Success'
    ORDER BY e.eventDate DESC
");
$historyStmt->bind_param("s", $studentID);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

// 4. Evaluate system leaderboards ranking position accurately
$rankStmt = $conn->prepare("
    SELECT COUNT(*) + 1 AS rank
    FROM student
    WHERE totalPoints > (
        SELECT COALESCE(totalPoints,0)
        FROM student
        WHERE userID = ?
        LIMIT 1
    )
");
$rankStmt->bind_param("s", $studentID);
$rankStmt->execute();
$rank = $rankStmt->get_result()->fetch_assoc()['rank'] ?? 1;
$rankStmt->close();

$page_title = "Participation Chronicle - " . htmlspecialchars($studentInfo['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body style="background-color: #f8fafc;">

    <?php include __DIR__ . '/../../topbar.php'; ?>


    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>


        <main class="main-content">
            <div class="workspace-stack">
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 25px;">
                    <div>
                        <h2 class="page-title" style="margin: 0;">Student Participation File</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.95rem;">
                            Profile dossier for: <strong><?= htmlspecialchars($studentInfo['name']) ?></strong> <code>(<?= htmlspecialchars($studentID) ?>)</code>
                        </p>
                    </div>
                    <a href="student_list.php" class="btn-cancel" style="text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 0.9rem; background-color: #cbd5e1; color: #334155;">⬅️ Back to Directory</a>
                </div>

                <div class="stats" style="display: flex; gap: 15px; margin-bottom: 25px; width: 100%;">
                    <div class="stat-card" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px;">
                        <h3 style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Total Merited Points</h3>
                        <div class="number" style="font-size: 1.8rem; font-weight: 700; color: #0284c7; margin-top: 5px;"><?= number_format((int)$studentInfo['totalPoints']) ?></div>
                    </div>
                    <div class="stat-card" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px;">
                        <h3 style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Recognition Standing</h3>
                        <div class="number" style="font-size: 1.4rem; font-weight: 700; color: #0a2540; margin-top: 5px; text-transform: capitalize;"><?= htmlspecialchars($studentInfo['recognitionLevel'] ?: 'Regular') ?></div>
                    </div>
                    <div class="stat-card" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px;">
                        <h3 style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Events Attended</h3>
                        <div class="number" style="font-size: 1.8rem; font-weight: 700; color: #2ecc71; margin-top: 5px;"><?= $eventCount ?></div>
                    </div>
                    <div class="stat-card" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px;">
                        <h3 style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Leaderboard Ranking</h3>
                        <div class="number" style="font-size: 1.8rem; font-weight: 700; color: #e74c3c; margin-top: 5px;">#<?= $rank ?></div>
                    </div>
                </div>
                <div class="stat-card" style="flex:1;background:#ffffff;border:1px solid #e2e8f0;padding:18px;border-radius:8px;">
                    <h3 style="margin:0;font-size:0.8rem;color:#64748b;text-transform:uppercase;">
                        Programme
                    </h3>
                    <div style="margin-top:5px;font-weight:600;">
                        <?= htmlspecialchars($studentInfo['program']) ?>
                    </div>
                </div>
                <div class="form-card-container" style="box-sizing: border-box; width: 100%;">
                    <h3 style="color: #0a2540; margin-top: 0; margin-bottom: 15px; font-size: 1.15rem;">Complete Participation History</h3>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Event Scope Title</th>
                                <th style="width: 25%;">Hosting Club</th>
                                <th style="width: 15%; text-align: center;">Event Date</th>
                                <th style="width: 15%; text-align: center;">Attendance Status</th>
                                <th style="width: 15%; text-align: center;">Points Awarded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($historyResult && $historyResult->num_rows > 0): ?>
                                <?php while($row = $historyResult->fetch_assoc()): 
                                    $status = $row['attendanceStatus'] ?? 'Not Marked';
                                    $statusColor = "color: #64748b; font-weight: normal;";
                                    
                                    if (in_array($status, ['Present', 'Late'])) {
                                        $statusColor = "color: #2ecc71; font-weight: bold;";
                                    } elseif ($status === 'Absent') {
                                        $statusColor = "color: #e74c3c; font-weight: bold;";
                                    }
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['eventName']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['clubName']) ?></td>
                                    <td style="text-align: center; color: #475569;">
                                        <?= $row['eventDate'] ? htmlspecialchars(date('Y-m-d', strtotime($row['eventDate']))) : '-' ?>
                                    </td>
                                    <td style="text-align: center; <?= $statusColor ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </td>
                                    <td style="text-align: center; font-weight: bold; color: #0a2540;">
                                        <?= !is_null($row['points']) ? '+' . number_format((int)$row['points']) : '0' ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 25px; color: #64748b;">No event attendance log streams exist for this student profile yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
    <?php
        $historyStmt->close();
        $conn->close();
    ?>
</body>
</html>