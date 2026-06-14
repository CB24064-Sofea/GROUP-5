<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
require_once 'config.php';

// Strict Role Guard - Restrict administrative changes exclusively to Admin profiles
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($id)) {
    header("Location: users.php");
    exit();
}

// 1. Fetch current profile configuration data securely using prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ? LIMIT 1");
$stmt->bind_param("s", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Error: The requested user record could not be located in the system directory.");
}

// 2. Handle updating processing form payloads securely
if (isset($_POST['update'])) {
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = trim($_POST['role']);

    // Perform parameterized operational transaction execution query
    $updateStmt = $conn->prepare("
        UPDATE users 
        SET name = ?, username = ?, email = ?, phoneNumber = ?, role = ? 
        WHERE userID = ?
    ");
    $updateStmt->bind_param("ssssss", $name, $username, $email, $phone, $role, $id);
    
    if ($updateStmt->execute()) {
        $updateStmt->close();
        header("Location: users.php");
        exit();
    } else {
        $errorMsg = "Error updating database entry profile parameters.";
        $updateStmt->close();
    }
}

$page_title = "Edit User: " . htmlspecialchars($user['name']) . " - FK Portal";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="standard.css">
</head>
<body style="background-color: #f8fafc;">

    <?php include 'M1_topbar.php'; ?>

    <div class="app-container">
        <?php include 'M1_sidebar.php'; ?>

        <main class="main-content">
            <div class="workspace-stack">

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 25px;">
                    <div>
                        <h2 class="page-title" style="margin: 0;">Modify Identity Profile</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">
                            Updating tracking information parameters linked to User ID: <code><?= htmlspecialchars($id) ?></code>
                        </p>
                    </div>
                    <a href="users.php" class="btn-secondary" style="text-decoration: none; padding: 10px 16px; font-size: 0.9rem; background-color: #cbd5e1; color: #334155; border-radius: 6px; display: inline-block;">⬅️ Cancel & Return</a>
                </div>

                <?php if (isset($errorMsg)): ?>
                    <div style="background-color: #fef2f2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fee2e2; font-weight: 500;">
                        ⚠️ <?= htmlspecialchars($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <div class="form-card-container" style="box-sizing: border-box; width: 100%; max-width: 700px; padding: 25px;">
                    <form method="POST" action="edit_user.php?id=<?= urlencode($id) ?>">
                        
                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>" class="input-control-select" style="width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group-row" style="margin-bottom: 25px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Account Privilege Role</label>
                            <select name="role" class="input-control-select" style="width: 100%; box-sizing: border-box; height: 40px; padding: 6px 12px;">
                                <option value="Student" <?= $user['role'] === "Student" ? "selected" : "" ?>>Student</option>
                                <option value="Committee" <?= $user['role'] === "Committee" ? "selected" : "" ?>>Committee</option>
                                <option value="Admin" <?= $user['role'] === "Admin" ? "selected" : "" ?>>Admin</option>
                            </select>
                        </div>

                        <div class="form-actions-footer-bar" style="border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: flex-end;">
                            <button type="submit" name="update" class="btn-primary" style="padding: 12px 24px; font-size: 0.95rem; font-weight: bold; border-radius: 6px;">
                                💾 Save Profile Changes
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>