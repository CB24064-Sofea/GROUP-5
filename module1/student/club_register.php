<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config.php';

// Access Control Guard - Ensure the user is logged in as a Student or Committee member
if (!isStudent() && !isCommittee()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$userID = getUserID();
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);

// --- ACTION HANDLER: POST REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $clubID = (int)$_POST['club_id'];

    if ($_POST['action_type'] === 'register') {
        // Double-check if the registration entry already exists
        $checkStmt = $conn->prepare("SELECT membershipID FROM membership WHERE userID = ? AND clubID = ? LIMIT 1");
        $checkStmt->bind_param("si", $userID, $clubID);
        $checkStmt->execute();
        $alreadyMember = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($alreadyMember) {
            $_SESSION['errorMessage'] = "You are already a registered member of this club.";
        } else {
            // Auto-generate new membership row (membershipID will auto-increment in DB)
            $registerStmt = $conn->prepare("INSERT INTO membership (userID, clubID, status, joinDate) VALUES (?, ?, 'Active', NOW())");
            $registerStmt->bind_param("si", $userID, $clubID);
            
            if ($registerStmt->execute()) {
                $_SESSION['successMessage'] = "Successfully registered to the club!";
            } else {
                $_SESSION['errorMessage'] = "An error occurred while processing your registration.";
            }
            $registerStmt->close();
        }
    } 
    
    elseif ($_POST['action_type'] === 'cancel') {
        // Completely delete the student's entry from the membership table as requested
        $cancelStmt = $conn->prepare("DELETE FROM membership WHERE userID = ? AND clubID = ?");
        $cancelStmt->bind_param("si", $userID, $clubID);
        
        if ($cancelStmt->execute()) {
            $_SESSION['successMessage'] = "Your club registration has been cancelled successfully.";
        } else {
            $_SESSION['errorMessage'] = "An error occurred while attempting to cancel your membership.";
        }
        $cancelStmt->close();
    }

    header("Location: /GROUP%205/module1/student/club_register.php");
    exit();
}

// --- DATA RETRIEVAL ---
// Fetch all active clubs along with the current student's registration status
$query = "
    SELECT 
        c.clubID, 
        c.clubName, 
        c.description, 
        c.status AS clubStatus,
        m.status AS userMembershipStatus
    FROM club c
    LEFT JOIN membership m ON c.clubID = m.clubID AND m.userID = ?
    WHERE c.status = 'Active'
    ORDER BY c.clubName ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userID);
$stmt->execute();
$clubsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Registration - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
</head>
<body>

    <?php include __DIR__ . '/../../topbar.php';  ?>

    <div class="app-container">
        <?php include __DIR__ . '/../../sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">
                <h2 class="page-title">Explore & Register for Clubs</h2>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <div class="form-card-container">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Available Student Clubs</h3>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Club Name</th>
                                <th style="width: 45%;">Description</th>
                                <th style="width: 15%;">Membership Status</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clubsList)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No active clubs are currently open for registration.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clubsList as $club): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($club['clubName']) ?></strong></td>
                                        <td><?= htmlspecialchars($club['description'] ?? 'No description provided.') ?></td>
                                        <td style="text-align: center;">
                                            <?php if ($club['userMembershipStatus'] === 'Active'): ?>
                                                <span class="status-badge" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">Registered</span>
                                            <?php else: ?>
                                                <span class="status-badge" style="background-color: #e2e8f0; color: #475569;">Not a Member</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($club['userMembershipStatus'] === 'Active'): ?>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel your registration for <?= htmlspecialchars($club['clubName']) ?>?');" style="display:inline;">
                                                    <input type="hidden" name="action_type" value="cancel">
                                                    <input type="hidden" name="club_id" value="<?= $club['clubID'] ?>">
                                                    <button type="submit" class="btn-delete" style="padding: 8px 12px; font-size: 0.85rem; min-width: auto; cursor: pointer;">Cancel</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" onsubmit="return confirm('Do you want to register for <?= htmlspecialchars($club['clubName']) ?>?');" style="display:inline;">
                                                    <input type="hidden" name="action_type" value="register">
                                                    <input type="hidden" name="club_id" value="<?= $club['clubID'] ?>">
                                                    <button type="submit" class="btn-submit" style="padding: 8px 12px; font-size: 0.85rem; min-width: auto; cursor: pointer;">Register</button>
                                                </form>
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