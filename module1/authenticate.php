<?php
session_start();

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit();
}

// 1. CHANGED: Read 'username' from the form submission instead of 'userID'
$usernameInput = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = strtolower(trim($_POST['role'] ?? ''));

if (empty($usernameInput) || empty($password) || empty($role)) {
    header("Location: login.php?error=invalid_credentials");
    exit();
}

// 2. CHANGED: Search the database using the 'username' column instead of 'userID'
$stmt = $conn->prepare("SELECT userID, name, password, role FROM user WHERE username = ?");
$stmt->bind_param("s", $usernameInput);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: login.php?error=invalid_credentials");
    exit();
}

$user = $result->fetch_assoc();

$dbRole = strtolower(trim($user['role']));

$passwordValid = false;

if ($password === $user['password']) {
    $passwordValid = true;
} elseif (password_verify($password, $user['password'])) {
    $passwordValid = true;
}

if (!$passwordValid) {
    header("Location: login.php?error=invalid_credentials");
    exit();
}

$roleMatch = false;

if ($dbRole == $role) {
    $roleMatch = true;
}

if ($dbRole == 'administrator' && $role == 'admin') {
    $roleMatch = true;
}

if ($dbRole == 'club committee member' && $role == 'committee') {
    $roleMatch = true;
}

if (!$roleMatch) {
    header("Location: login.php?error=invalid_credentials");
    exit();
}

// Note: $_SESSION['userID'] will still store 'CB24003' correctly for tracking!
$_SESSION['userID'] = $user['userID'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];

if ($role == 'admin') {
    header("Location: ../module4/admin/dashboard.php");
    exit();
}

header("Location: student/dashboard.php");
exit();
?>