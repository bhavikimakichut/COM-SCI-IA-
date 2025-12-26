<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "useraccounts";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Input sanitization functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function sanitizeEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return $email;
}
?>
