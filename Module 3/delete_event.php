<?php
require_once 'auth.php'; 

/** @var PDO $pdo */

// Authorization check: Ensure user is a Committee member
if (!isset($_SESSION['user_id']) || strpos($_SESSION['role'], 'Committee') === false) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['errorMessage'] = "Invalid event selected.";
    header("Location: event_management.php");
    exit();
}

try {
    // Start transaction to ensure data integrity
    $pdo->beginTransaction();

    // 1. Delete associated registrations first (eventID matches schema)
    $stmt1 = $pdo->prepare("DELETE FROM event_registration WHERE eventID = ?");
    $stmt1->execute([$id]);

    // 2. Delete the event itself
    $stmt2 = $pdo->prepare("DELETE FROM event WHERE eventID = ?");
    $stmt2->execute([$id]);

    $pdo->commit();

    $_SESSION['successMessage'] = "Event deleted successfully.";
    header("Location: event_management.php");
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['errorMessage'] = "Failed to delete event: " . $e->getMessage();
    header("Location: event_management.php");
    exit();
}
?>