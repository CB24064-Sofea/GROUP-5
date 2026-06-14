<?php
session_start();
require_once 'config.php'; // Kept in same folder level as authenticate.php based on your code

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = trim($_POST['userID']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Structural matching integrity validation check
    if (empty($userID) || empty($new_password) || empty($confirm_password)) {
        header("Location: forgot_password.php?error=All fields are required.");
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: forgot_password.php?error=Passwords do not match.");
        exit();
    }

    // 2. Length checking evaluation matrix matching UI threshold definitions
    if (strlen($new_password) < 5) {
        header("Location: forgot_password.php?error=Password must be at least 5 characters.");
        exit();
    }

    // 3. Complexity requirement verification evaluation context mapping
    if (!preg_match('/[0-9]/', $new_password) && !preg_match('/[^A-Za-z0-9]/', $new_password)) {
        header("Location: forgot_password.php?error=Password must contain at least one number or special character.");
        exit();
    }

    // 4. Look up user account records in the target table (Aligned with authenticate.php using 'users')
    $stmt = $conn->prepare("SELECT userID FROM users WHERE userID = ? LIMIT 1");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: forgot_password.php?error=User ID not found in system storage database.");
        exit();
    }
    $stmt->close();

    // 5. Encrypt the password to match password_verify() processing mechanics
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 6. Update user password row record
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE userID = ?");
    $updateStmt->bind_param("ss", $hashed_password, $userID);

    if ($updateStmt->execute()) {
        header("Location: forgot_password.php?success=Password updated successfully! You can now log in.");
    } else {
        header("Location: forgot_password.php?error=Database transaction execution error. Please retry.");
    }

    $updateStmt->close();
    $conn->close();
} else {
    header("Location: forgot_password.php");
    exit();
}
?>