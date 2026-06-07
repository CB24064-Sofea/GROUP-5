<?php
session_start();

$host = 'localhost';
$port = 3307;
$user = 'root';
$pass = '';
$dbname = 'group5';

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
date_default_timezone_set('Asia/Kuala_Lumpur');
?>