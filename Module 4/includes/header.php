<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FK Club Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-header">
        <div class="header-left">
            <div class="logo-placeholder">
                <img src="../assets/logo.png" alt="Logo FK" onerror="this.style.display='none'">
            </div>
            <h1>FK Student Club & Event Management</h1>
        </div>
        <div class="header-right">
            <span class="admin-name"><?= htmlspecialchars($_SESSION['name']) ?></span>
            <div class="profile-container">
                <div class="profile-fallback">👤</div>
            </div>
        </div>
    </div>
    <div class="app-container">
        <div class="sidebar">
            <div class="sidebar-nav">
                <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <a href="../admin/dashboard.php" class="nav-item">Dashboard</a>
                    <a href="../admin/reports.php" class="nav-item">Reports</a>
                <?php elseif ($_SESSION['role'] == 'Committee'): ?>
                    <a href="../committee/dashboard.php" class="nav-item">Dashboard</a>
                <?php else: ?>
                    <a href="../student/dashboard.php" class="nav-item">Dashboard</a>
                    <a href="../student/dashboard.php" class="nav-item">My Points</a>
                    <a href="../student/dashboard.php" class="nav-item">History</a>
                <?php endif; ?>
            </div>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
        <div class="main-content">