<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['userID']);
}

function getUserID() {
    return $_SESSION['userID'] ?? null;
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserName() {
    return $_SESSION['name'] ?? $_SESSION['username'] ?? 'User';
}

function isAdmin() {
    return getUserRole() === 'Admin';
}

function isCommittee() {
    return getUserRole() === 'Committee';
}

function isStudent() {
    return getUserRole() === 'Student';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../Module 1/login.php");
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header("Location: index.php");
        exit();
    }
}

function requireCommitteeOrAdmin() {
    requireLogin();
    if (!isCommittee() && !isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function getCommitteeClubID($userID = null) {
    global $conn;

    $userID = $userID ?? getUserID();

    $stmt = $conn->prepare("SELECT clubID FROM club_committee WHERE userID = ? LIMIT 1");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? (int)$row['clubID'] : null;
}

function canManageEvent($eventID) {
    global $conn;

    if (isAdmin()) {
        return true;
    }

    if (!isCommittee()) {
        return false;
    }

    $clubID = getCommitteeClubID();

    $stmt = $conn->prepare("SELECT eventID FROM event WHERE eventID = ? AND clubID = ? LIMIT 1");
    $stmt->bind_param("ii", $eventID, $clubID);
    $stmt->execute();
    $allowed = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return $allowed;
}

function flash($key) {
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);
    return $message;
}
?>