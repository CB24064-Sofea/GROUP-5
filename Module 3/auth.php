<?php
// auth.php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['userID']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserID() {
    return $_SESSION['userID'] ?? null;
}

function getUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

function getCommitteeClubID($userID) {
    global $conn;
    $stmt = $conn->prepare("SELECT clubID FROM club_committee WHERE userID = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['clubID'];
    }
    return null;
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

// Redirect if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

// Redirect if not committee of the event's club
function requireEventOwnership($eventID) {
    requireLogin();
    if (isAdmin()) return true;
    if (!isCommittee()) {
        header("Location: index.php");
        exit();
        //
    }
    $clubID = getCommitteeClubID(getUserID());
    $stmt = $conn->prepare("SELECT clubID FROM event WHERE eventID = ?");
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    if (!$event || $event['clubID'] != $clubID) {
        header("Location: events.php");
        exit();
    }
    return true;
}
?>