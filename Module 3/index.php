<?php
require_once 'auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Module 3 Dashboard</title>
    <link rel="stylesheet" href="standard.css">
</head>
<body>
<?php include 'M3_topbar.php'; ?>

<div class="app-container">
    <?php include 'M3_sidebar.php'; ?>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Welcome, <?= htmlspecialchars(getUserName()) ?></h2>

            <div class="form-card-container">
                <h3>Your Role: <?= htmlspecialchars(getUserRole()) ?></h3>
                <p>Use the sidebar to manage events and registrations.</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>