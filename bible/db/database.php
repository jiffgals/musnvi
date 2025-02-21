<?php
$host = 'localhost'; // or your host
$user = 'root';      // your MySQL username
$password = '';      // your MySQL password
$dbname = 'bible_db';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>