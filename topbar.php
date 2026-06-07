<?php

if (!isset($_SESSION['userID'])) {
    // If session not started, you might want to redirect, but this file should only be included after config.
    return;
}
$userName = $_SESSION['name'];
$userRole = $_SESSION['role'];
?>
<div class="main-header">
    <div class="header-left">
        <div class="logo-placeholder">
            <img src="../assets/logo.png" alt="Logo FK" onerror="this.style.display='none'">
        </div>
        <h1>FK Student Club & Event Management</h1>
    </div>
    <div class="header-right">
        <span class="admin-name"><?= htmlspecialchars($userName) ?></span>
        <div class="profile-container">
            <div class="profile-fallback">👤</div>
        </div>
    </div>
</div>