<?php
// Database connection — edit these values for your own setup.
$host = "localhost";
$user = "your_db_user";
$pass = "your_db_password";
$dbname = "tolcare";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
