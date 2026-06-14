<?php
// 1. Force local development error reporting (Prevents silent blank white pages)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Safely initialize session context
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'group5';

// Define preferred port and the fallback port
$preferred_port = 3307;
$fallback_port = 3306;

// 3. Try connecting with the preferred port first
$conn = @new mysqli($host, $user, $pass, $dbname, $preferred_port);

// 4. If it fails, instantly try the fallback port
if ($conn->connect_error) {
    $conn = new mysqli($host, $user, $pass, $dbname, $fallback_port);
    
    // If the backup port also fails, terminate with a visible error message
    if ($conn->connect_error) {
        die("Database connection failed on both ports (" . $preferred_port . "/" . $fallback_port . "): " . $conn->connect_error);
    }
}

// Configuration standards
$conn->set_charset("utf8mb4");
date_default_timezone_set('Asia/Kuala_Lumpur');
?>