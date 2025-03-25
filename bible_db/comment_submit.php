<?php
session_start();
include 'db/database.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to comment."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;
$parent_id = $_POST['parent_id'] ?? null; // For replies
$content = trim($_POST['content'] ?? '');

if (!$post_id || empty($content)) {
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
    exit();
}

// Insert comment into database
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $post_id, $user_id, $parent_id, $content);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Comment posted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to post comment."]);
}

$stmt->close();
$conn->close();
?>
