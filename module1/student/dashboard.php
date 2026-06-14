<?php
require_once __DIR__ . '/../../auth.php';

if (!isStudent() && !isCommittee()) {
    header("Location: ../login.php?error=unauthorized");
    exit();
}

require_once __DIR__ . '/../../config.php';

$userID = getUserID();
$student = null;

if ($userID && isset($conn)) {
    $stmt = $conn->prepare("
        SELECT
            u.name,
            u.email,
            s.studyLevel,
            s.program,
            s.yearOfStudy,
            s.semester,
            s.totalPoints,
            s.recognitionLevel
        FROM user u
        LEFT JOIN student s ON u.userID = s.userID
        WHERE u.userID = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - FK Portal</title>

    <link rel="stylesheet" href="../../standard.css">

    <style>
        .workspace-stack {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #0a2540;
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-grid-layout {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .metric-card {
            flex: 1 1 250px;
            max-width: 300px;
            min-height: 140px;
        }

        .metric-card h3 {
            margin: 0 0 10px;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .metric-card p {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
            font-weight: 500;
            line-height: 1.5;
        }

        .metric-card.highlighted {
            border-left: 4px solid #3498db;
        }

        .metric-card.points-badge {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-left: 4px solid #0284c7;
        }

        .metric-card.points-badge p {
            font-size: 30px;
            font-weight: 700;
            color: #0369a1;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <div class="workspace-stack">

            <h1 class="page-title">
                Welcome Back,
                <?= htmlspecialchars($student['name'] ?? $_SESSION['name'] ?? 'Student'); ?>!
            </h1>

            <div class="profile-grid-layout">

                <div class="metric-card card highlighted">
                    <h3>Program of Study</h3>
                    <p><?= htmlspecialchars($student['program'] ?? 'Not Enrolled'); ?></p>
                </div>

                <div class="metric-card card">
                    <h3>Study Level</h3>
                    <p><?= htmlspecialchars($student['studyLevel'] ?? 'Not Specified'); ?></p>
                </div>

                <div class="metric-card card">
                    <h3>Current Timeline</h3>
                    <p>
                        <?php if (!empty($student['yearOfStudy']) || !empty($student['semester'])): ?>
                            Year <?= htmlspecialchars($student['yearOfStudy'] ?? '-'); ?>,
                            Semester <?= htmlspecialchars($student['semester'] ?? '-'); ?>
                        <?php else: ?>
                            No Active Academic Term
                        <?php endif; ?>
                    </p>
                </div>

                <div class="metric-card card points-badge">
                    <h3>Total Activity Points</h3>
                    <p><?= htmlspecialchars($student['totalPoints'] ?? '0'); ?> pts</p>
                </div>

                <div class="metric-card card">
                    <h3>Recognition Tier Level</h3>
                    <p><?= htmlspecialchars($student['recognitionLevel'] ?? 'None'); ?></p>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>