<?php
// Start a session to pass success/error messages between pages
session_start();

// 1. Database Connection Configuration
$host = "127.0.0.1:3307";
$user = "root";
$password = "";
$database = "fk_club_system";

// Connect to the database server
$link = mysqli_connect($host, $user, $password);

if (!$link) {
    $_SESSION['msg'] = "Connection failed: " . mysqli_connect_error();
    $_SESSION['msgClass'] = "alert-error";
    header("Location: manage_club.php");
    exit();
}

// Select the database
if (!mysqli_select_db($link, $database)) {
    $_SESSION['msg'] = "Database selection failed: " . mysqli_error($link);
    $_SESSION['msgClass'] = "alert-error";
    header("Location: manage_club.php");
    mysqli_close($link);
    exit();
}

// 2. Delete Request
if (isset($_GET['id'])) {
    // Sanitize the input parameter to prevent SQL Injection
    $clubID = mysqli_real_escape_string($link, $_GET['id']);
    
    // Construct the SQL Delete Query
    $query = "DELETE FROM club WHERE clubID = '$clubID'";
    
    // Execute the query
    $result = mysqli_query($link, $query);
    
    if ($result) {
        // Check if a row was actually deleted
        if (mysqli_affected_rows($link) > 0) {
            $_SESSION['msg'] = "Club deleted successfully!";
            $_SESSION['msgClass'] = "alert-success";
        } else {
            $_SESSION['msg'] = "No club found with that ID.";
            $_SESSION['msgClass'] = "alert-error";
        }
    } else {
        $_SESSION['msg'] = "Failed to execute delete query: " . mysqli_error($link);
        $_SESSION['msgClass'] = "alert-error";
    }
} else {
    $_SESSION['msg'] = "Invalid access. No Club ID specified.";
    $_SESSION['msgClass'] = "alert-error";
}

// 3. Close Connection & Redirect Back to Manage Club Page
mysqli_close($link);
header("Location: manage_club.php");
exit();
?>