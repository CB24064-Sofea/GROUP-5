<?php
require_once 'auth.php';
requireLogin();
include 'header.php';
include 'sidebar.php';
?>
<div class="workspace-stack">
    <div class="page-title">Welcome, <?php echo htmlspecialchars(getUserName()); ?></div>
    <div class="form-card-container">
        <h3>Your Role: <?php echo getUserRole(); ?></h3>
        <p>Use the sidebar to navigate.</p>
    </div>
</div>
<?php include 'footer.php'; ?>