<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';
require_once 'config.php';

// Strict Role Guard - Ensure that only an Admin can perform database record purges
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Extract tracking primary identifier parameters
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!empty($id)) {
    // Implement a secure parameterized statement to safely delete user accounts
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        // Log auditing records or process system notifications here if needed in the future
    }
    $stmt->close();
}

// Redirect back to user directory module view context safely
header("Location: users.php");
exit();
?>