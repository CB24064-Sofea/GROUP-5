<?php
require_once 'auth.php';
requireLogin();

$userID = getUserID();
$userName = getUserName();
$profileImageSrc = null;

$stmt = $conn->prepare("SELECT name, profilePhoto FROM user WHERE userID = ? LIMIT 1");
$stmt->bind_param("s", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    $userName = $user['name'];

    if (!empty($user['profilePhoto'])) {
        $profileImageSrc = 'data:image/jpeg;base64,' . base64_encode($user['profilePhoto']);
    }
}
?>

<header class="main-header">
    <div class="header-left">
        <div class="logo-placeholder">
            <img src="../module 2/UMP LOGO.png" alt="Universiti Malaysia Pahang Logo">
        </div>
        <h1>FK Student Club &amp; Event Management</h1>
    </div>

    <div class="header-right">
        <span class="admin-name"><?= htmlspecialchars($userName) ?></span>

        <div class="profile-container">
            <?php if ($profileImageSrc): ?>
                <img src="<?= htmlspecialchars($profileImageSrc) ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <div class="profile-fallback">👤</div>
            <?php endif; ?>
        </div>
    </div>
</header>