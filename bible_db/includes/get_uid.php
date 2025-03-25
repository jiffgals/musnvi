<?php 
include '../db/database.php'; 
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"]; // Retrieve username from session

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>


<!DOCTYPE html>
<html lang="en">
<head>

</head>
<body>


<!--Start here just to create profile link-->
<a href="https://musnvi.42web.io/view_profile.php?user_id=<?php
include 'db/database.php'; // Adjust path if necessary

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT user_id FROM user_profiles WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['user_id']}";
    }
} else {
    echo "<p>No user_id fetched</p>";
}

$stmt->close();
$conn->close();

?>">Profile</a>
<!--End here just to create profile link-->

</body>
</html>