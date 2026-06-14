<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
requireLogin();

$userRole = strtolower(trim(getUserRole()));

switch ($userRole) {
    case 'admin':
    case 'administrator':
        header("Location: module4/admin/dashboard.php");
        exit();

    case 'committee':
    case 'club committee member':
    case 'student':
    case 'undergraduate':
    case 'postgraduate':
        header("Location: module1/student/dashboard.php");
        exit();

    default:
        header("Location: module1/login.php?error=unauthorized_role");
        exit();
}
?>