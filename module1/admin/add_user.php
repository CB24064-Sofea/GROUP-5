<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config.php';

// Strict Role Guard - Ensure that only an Admin can access this form
if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$page_title = "Provision New User Profile - FK Portal";
$errorMsg = null;

if (isset($_POST['submit'])) {
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password']; // Raw input; ideally should be hashed using password_hash() depending on your schema rules
    $phone    = trim($_POST['phone']);
    $role     = trim($_POST['role']);

    // Standardized target to users table context
    $sql = "INSERT INTO users (name, username, email, password, phoneNumber, role) VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $name, $username, $email, $password, $phone, $role);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: users.php");
            exit();
        } else {
            $errorMsg = "Execution failed: Account creation criteria rejected (Username or Email may already exist).";
            $stmt->close();
        }
    } else {
        $errorMsg = "Database query compilation error.";
    }
}
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
                        <h2 class="page-title" style="margin: 0;">Add New User Account</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Provision a new identity ledger credential into the application database framework.</p>
                    </div>
                    <a href="users.php" class="btn-secondary" style="text-decoration: none; padding: 10px 16px; font-size: 0.9rem; background-color: #cbd5e1; color: #334155; border-radius: 6px; display: inline-block;">⬅️ Back to Directory</a>
                </div>

                <?php if ($errorMsg): ?>
                    <div style="background-color: #fef2f2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fee2e2; font-weight: 500;">
                        ⚠️ <?= htmlspecialchars($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <div class="form-card-container" style="box-sizing: border-box; width: 100%; max-width: 700px; padding: 25px;">
                    <form method="POST" action="add_user.php">

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Full Name</label>
                            <input type="text" name="name" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Username ID Reference</label>
                            <input type="text" name="username" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Email Address</label>
                            <input type="email" name="email" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Account Access Password</label>
                            <input type="password" name="password" class="input-control-select" style="width: 100%; box-sizing: border-box;" required>
                        </div>

                        <div class="form-group-row" style="margin-bottom: 18px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Phone Number (Optional)</label>
                            <input type="text" name="phone" class="input-control-select" style="width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group-row" style="margin-bottom: 25px;">
                            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">System Governance Role</label>
                            <select name="role" class="input-control-select" style="width: 100%; box-sizing: border-box; height: 40px; padding: 6px 12px;">
                                <option value="Student">Student</option>
                                <option value="Committee">Committee</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-actions-footer-bar" style="border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: flex-end;">
                            <button type="submit" name="submit" class="btn-primary" style="padding: 12px 24px; font-size: 0.95rem; font-weight: bold; border-radius: 6px;">
                                🚀 Initialize & Save Profile
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>