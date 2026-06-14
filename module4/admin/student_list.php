<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Strict Role Guard - Only Administrators can view the complete student master directory
if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$page_title = "Student Directory - FK Portal";

// Execute operational data-join query from users map metadata to target student metrics table
$studentsQuery = $conn->query("
    SELECT
        u.userID,
        u.name,
        u.email,
        s.totalPoints,
        s.recognitionLevel
    FROM user u
    INNER JOIN student s
        ON u.userID = s.userID
    ORDER BY u.name ASC
");
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
                
                <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 25px;">
                    <h2 class="page-title" style="margin: 0;">Student Master Directory</h2>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">View complete list of students registered in the system along with their points status levels.</p>
                </div>

                <div class="form-card-container" style="box-sizing: border-box; width: 100%;">
                    <div style="margin-bottom: 15px;">
                        <h3 style="color: #0a2540; margin: 0; font-size: 1.15rem;">Registered Student Profiles</h3>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Matric No. / ID</th>
                                <th style="width: 30%;">Full Name</th>
                                <th style="width: 25%;">Email Address</th>
                                <th style="width: 12%; text-align: center;">Total Points</th>
                                <th style="width: 13%; text-align: center;">Recognition Status</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($studentsQuery && $studentsQuery->num_rows > 0): ?>
                                <?php while($row = $studentsQuery->fetch_assoc()): 
                                    // Dynamically set status badge coloring properties based on validation status values
                                    $recLevel = trim($row['recognitionLevel'] ?? 'Regular');
                                    $badgeStyle = "background-color: #cbd5e1; color: #475569;"; // Default
                                    
                                    if (strcasecmp($recLevel, 'Gold') === 0) {
                                        $badgeStyle = "background-color: #fef08a; color: #a16207; border: 1px solid #fef08a;";
                                    } elseif (strcasecmp($recLevel, 'Silver') === 0) {
                                        $badgeStyle = "background-color: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;";
                                    } elseif (strcasecmp($recLevel, 'Bronze') === 0) {
                                        $badgeStyle = "background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;";
                                    } elseif (strcasecmp($recLevel, 'Elite') === 0 || strcasecmp($recLevel, 'Active') === 0) {
                                        $badgeStyle = "background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;";
                                    }
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['userID']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                                    <td style="text-align: center; font-weight: bold; color: #0284c7;">
                                        <?= number_format((int)$row['totalPoints']) ?> pts
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="status-badge" style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; <?= $badgeStyle ?>">
                                            <?= htmlspecialchars($recLevel) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="view_student.php?id=<?= urlencode($row['userID']) ?>" class="btn-primary" style="text-decoration: none; padding: 6px 12px; font-size: 0.8rem; display: inline-block; white-space: nowrap; min-width: auto; height: auto; border-radius: 4px;">
                                            🔍 History
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 25px; color: #64748b;">No active student accounts are currently linked inside the application profiles directory.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

</body>
</html>