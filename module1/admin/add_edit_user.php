<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect clean routing to the centralized user management index directory
header("Location: users.php");
exit();
?>