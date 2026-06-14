<?php
// Securely ensure active session context tracking before evaluating variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Strict Role Guard - Restrict operation access exclusively to active Committee accounts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Committee') {
    header("Location: ../../module1/login.php");
    exit();
}

$page_title = "Student List";

// Fetch structural user tracking profiles cleanly
$students = $conn->query("
    SELECT u.userID, u.name, s.totalPoints, s.recognitionLevel 
    FROM user u 
    JOIN student s ON u.userID = s.userID 
    ORDER BY u.name ASC
");

if (!$students) {
    die("Database Query Failure: Unable to compile structural user dataset parameters.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../topbar.php'; ?>
    
    <div class="app-container">
        <?php include '../sidebar.php'; ?>
        
        <main class="main-content">
            <h1 class="page-title">Student Profile List</h1>
            <p style="color: #64748b; margin-bottom: 20px;">Review aggregate points balances and individual system classification statuses across all managed student metrics paths.</p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matriculation No.</th>
                        <th>Student Full Name</th>
                        <th>Total Merit Points</th>
                        <th>Recognition Classification</th>
                        <th style="text-align: center;">Operational Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students->num_rows > 0): ?>
                        <?php while($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.95rem; color: #334155;"><?= htmlspecialchars($row['userID']) ?></td>
                            <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <strong style="color: #0284c7;"><?= htmlspecialchars($row['totalPoints'] ?? '0') ?></strong> pts
                            </td>
                            <td>
                                <?php if (!empty($row['recognitionLevel'])): ?>
                                    <span class="badge badge-recognition" style="background-color: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-weight: 500; border: 1px solid #cbd5e1;">
                                        <?= htmlspecialchars($row['recognitionLevel']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-style: italic;">None assigned</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="view_student.php?id=<?= urlencode($row['userID']) ?>" class="action-link" style="color: #0284c7; font-weight: 600; text-decoration: none;">View History Log</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 30px 10px;">No registered student records found in the application workspace namespace.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <?php 
    // Release active memory cursor references and drop connections cleanly
    $students->free();
    $conn->close(); 
    ?>
</body>
</html>