<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

/*
|--------------------------------------------------------------------------
| Admin Access Only
|--------------------------------------------------------------------------
*/
if (!isAdmin()) {
    $_SESSION['msg'] = "Unauthorized access attempt blocked.";
    $_SESSION['msgClass'] = "alert-error";

    header("Location: /GROUP%205/module1/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validate Parameters
|--------------------------------------------------------------------------
*/
$userID = $_GET['uid'] ?? '';
$clubID = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;

if (empty($userID) || $clubID <= 0) {
    $_SESSION['msg'] = "Invalid request. Committee User ID and Club ID are required.";
    $_SESSION['msgClass'] = "alert-error";

    header("Location: manage_committee.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Delete Committee Assignment
|--------------------------------------------------------------------------
*/
$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        DELETE FROM club_committee
        WHERE userID = ?
        AND clubID = ?
    ");

    if (!$stmt) {
        throw new Exception("Failed to prepare delete statement.");
    }

    $stmt->bind_param("si", $userID, $clubID);

    $stmt->execute();

    if ($stmt->affected_rows > 0) {

        $conn->commit();

        $_SESSION['msg'] = "Committee assignment deleted successfully.";
        $_SESSION['msgClass'] = "alert-success";

    } else {

        $conn->rollback();

        $_SESSION['msg'] = "No matching committee assignment record was found.";
        $_SESSION['msgClass'] = "alert-error";
    }

    $stmt->close();

} catch (Exception $e) {

    $conn->rollback();

    $_SESSION['msg'] = "System Error: " . $e->getMessage();
    $_SESSION['msgClass'] = "alert-error";
}

$conn->close();

/*
|--------------------------------------------------------------------------
| Redirect Back
|--------------------------------------------------------------------------
*/
header("Location: manage_committee.php");
exit();
?>