<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

if (!isAdmin()) {
    $_SESSION['msg'] = "Unauthorized access.";
    $_SESSION['msgClass'] = "alert-error";
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['msg'] = "Invalid club ID.";
    $_SESSION['msgClass'] = "alert-error";
    header("Location: manage_club.php");
    exit();
}

$clubID = (int)$_GET['id'];

$conn->begin_transaction();

try {

    // Delete club memberships
    $stmt = $conn->prepare("
        DELETE FROM membership
        WHERE clubID = ?
    ");
    $stmt->bind_param("i", $clubID);
    $stmt->execute();
    $stmt->close();

    // Delete committee assignments (if table exists)
    $stmt = $conn->prepare("
        DELETE FROM club_committee
        WHERE clubID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $clubID);
        $stmt->execute();
        $stmt->close();
    }

    // Delete events under this club
    $stmt = $conn->prepare("
        DELETE FROM event
        WHERE clubID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $clubID);
        $stmt->execute();
        $stmt->close();
    }

    // Delete club
    $stmt = $conn->prepare("
        DELETE FROM club
        WHERE clubID = ?
    ");

    if (!$stmt) {
        throw new Exception("Unable to prepare club deletion query.");
    }

    $stmt->bind_param("i", $clubID);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {

        $conn->commit();

        $_SESSION['msg'] =
            "Club deleted successfully.";

        $_SESSION['msgClass'] =
            "alert-success";

    } else {

        $conn->rollback();

        $_SESSION['msg'] =
            "Club not found.";

        $_SESSION['msgClass'] =
            "alert-error";
    }

    $stmt->close();

} catch (Exception $e) {

    $conn->rollback();

    $_SESSION['msg'] =
        "Delete failed: " . $e->getMessage();

    $_SESSION['msgClass'] =
        "alert-error";
}

$conn->close();

header("Location: manage_club.php");
exit();
?>