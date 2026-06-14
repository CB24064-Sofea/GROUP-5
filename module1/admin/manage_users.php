<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the request path resolves cleanly to your updated users directory dashboard
header("Location: users.php");
exit();
?>