<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fixed path lookup to securely read config.php in the same directory
require_once __DIR__ . '/config.php';

// ==========================================
// CORE SECURITY UTILITIES & HELPER FUNCTIONS
// ==========================================
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
    return $_SESSION['name'] ?? 'User';
}

function isAdmin() {
    $role = strtolower(getUserRole());
    return $role === 'administrator' || $role === 'admin';
}

function isCommittee() {
    $role = strtolower(getUserRole());
    return $role === 'committee' || $role === 'club committee member';
}

function isStudent() {
    $role = strtolower(getUserRole());
    return $role === 'student' || $role === 'undergraduate' || $role === 'postgraduate' || isCommittee();
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Force the browser to go directly to the correct web folder path
        header("Location: /GROUP%205/module1/login.php");
        exit();
    }
}
function requireCommitteeOrAdmin()
{
    if (!isCommittee() && !isAdmin()) {
        header("Location: /GROUP%205/module1/login.php");
        exit();
    }
}
function getCommitteeClubID()
{
    global $conn;

    if (!isset($_SESSION['userID'])) {
        return 0;
    }

    $userID = $_SESSION['userID'];

    $stmt = $conn->prepare("
        SELECT clubID
        FROM club_committee
        WHERE userID = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("s", $userID);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $result['clubID'] ?? 0;
}
function canManageEvent($eventID)
{
    global $conn;

    if (isAdmin()) {
        return true;
    }

    if (!isCommittee()) {
        return false;
    }

    $userID = getUserID();

    $stmt = $conn->prepare("
        SELECT e.eventID
        FROM event e
        INNER JOIN club_committee cc
            ON e.clubID = cc.clubID
        WHERE e.eventID = ?
        AND cc.userID = ?
        LIMIT 1
    ");

    $stmt->bind_param("is", $eventID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

?>