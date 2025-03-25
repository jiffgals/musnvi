<?php
$servername = 'sql204.infinityfree.com'; // or your host
$username = 'if0_38365506';      // your MySQL username
$password = 'amp1530oftZamp';      // your MySQL password
$database = 'if0_38365506_bible_db';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>