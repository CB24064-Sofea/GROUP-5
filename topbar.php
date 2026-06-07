<?php
require_once '../includes/config.php'; // starts session and connects to DB

if (!isset($_SESSION['userID'])) {
    // Not logged in – this file should not be included directly, but just in case:
    return;
}

$userID = $_SESSION['userID'];

// Fetch user details including profilePhoto
$stmt = $conn->prepare("SELECT name, profilePhoto FROM user WHERE userID = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // User not found – fallback
    $userName = $_SESSION['name'];
    $profilePhotoData = null;
} else {
    $userName = $user['name'];
    $profilePhotoData = $user['profilePhoto'];
}

// Convert BLOB to base64 if exists
$profileImageSrc = null;
if (!empty($profilePhotoData)) {
    $profileImageSrc = 'data:image/jpeg;base64,' . base64_encode($profilePhotoData);
}
?>
<div class="main-header">
    <div class="header-left">
        <div class="logo-placeholder">
            <img src="../assets/FK logo.png" alt="Logo FK">
        </div>
        <h1>FK Student Club & Event Management</h1>
    </div>
    <div class="header-right">
        <span class="admin-name"><?= htmlspecialchars($userName) ?></span>
        <div class="profile-container">
            <?php if ($profileImageSrc): ?>
                <img src="<?= $profileImageSrc ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <div class="profile-fallback">👤</div>
            <?php endif; ?>
        </div>
    </div>
</div>
