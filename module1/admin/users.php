<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config.php';


// Strict Role Guard - Restrict user directory administration controls strictly to Admin accounts
if (!isAdmin()) {
   header("Location: /GROUP%205/module1/login.php");
    exit();

}

$page_title = "Manage Users - FK Portal";

// Pull all system profiles ordered by ID values safely from the correct 'users' table
$result = $conn->query("SELECT * FROM user ORDER BY userID ASC");
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
                        <h2 class="page-title" style="margin: 0;">User Account Management</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">View, modify, or provision identity control accounts registered in the application system.</p>
                    </div>
                    <a href="add_user.php" class="btn-primary" style="text-decoration: none; padding: 10px 18px; font-size: 0.9rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                        ➕ Add New User
                    </a>
                </div>

                <div class="form-card-container" style="box-sizing: border-box; width: 100%;">
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">User ID / Matric</th>
                                <th style="width: 22%;">Full Name</th>
                                <th style="width: 15%;">Username</th>
                                <th style="width: 20%;">Email Address</th>
                                <th style="width: 13%;">Phone Number</th>
                                <th style="width: 10%; text-align: center;">Account Role</th>
                                <th style="width: 8%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): 
                                    // Set dynamic badge styles for roles to optimize readability
                                    $role = trim($row['role'] ?? 'Student');
                                    $roleStyle = "background-color: #e2e8f0; color: #475569;";
                                    if ($role === 'Admin') {
                                        $roleStyle = "background-color: #fecdd3; color: #9f1239;";
                                    } elseif ($role === 'Committee') {
                                        $roleStyle = "background-color: #dbeafe; color: #1e40af;";
                                    }
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['userID']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['phoneNumber'] ?? '-') ?></td>
                                    <td style="text-align: center;">
                                        <span class="status-badge" style="display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; <?= $roleStyle ?>">
                                            <?= htmlspecialchars($role) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <a href="edit_user.php?id=<?= urlencode($row['userID']) ?>" title="Edit User Account" style="color: #0284c7; text-decoration: none; font-size: 0.9rem; font-weight: 600;">Edit</a>
                                            <span style="color: #cbd5e1;">|</span>
                                            <a href="delete_user.php?id=<?= urlencode($row['userID']) ?>" onclick="return confirm('Are you sure you want to delete user account: <?= htmlspecialchars($row['userID']) ?>? This action cannot be undone.')" title="Delete User Account" style="color: #ef4444; text-decoration: none; font-size: 0.9rem; font-weight: 600;">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">No registered user profile records discovered inside the database system.</td>
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