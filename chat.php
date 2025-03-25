<?php
session_start();
include 'db/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to access chat.");
}

$loggedInUser = $_SESSION['user_id'];
$chatUser = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch list of users for selection
// Fetch list of friends
$sqlFriends = "SELECT u.id, u.username FROM friends f
               JOIN users u ON (f.user_id = u.id OR f.friend_id = u.id)
               WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted' AND u.id != ?";
$stmtFriends = $conn->prepare($sqlFriends);
$stmtFriends->bind_param("iii", $loggedInUser, $loggedInUser, $loggedInUser);
$stmtFriends->execute();
$resultFriends = $stmtFriends->get_result();

// Fetch chat messages between selected users
$messages = [];
if ($chatUser) {
    $sql = "SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY timestamp ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $loggedInUser, $chatUser, $chatUser, $loggedInUser);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
?>

<?php
// Check if the user has pending friend requests
$notif_sql = "SELECT COUNT(*) AS pending_requests FROM friends WHERE friend_id = ? AND status = 'pending'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $_SESSION['user_id']);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$pending_requests = $notif_row['pending_requests'];
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .chat-container { max-width: 100%; height: 79vh; margin: auto; margin-top: -10px; }
        /*.chat-container { width: 50%; margin: auto; }*/
        .message { padding: 10px; margin: 5px 0; border-radius: 10px; }
        .my-message { background-color: #dcf8c6; text-align: right; width: auto; margin-left: 50%; border: 1px solid orange; }
        .other-message { background-color: #f1f0f0; text-align: left; width: 50%; border: 1px solid gray; }
        .chat-box { max-widht: 100%; height: 64vh; /*700px will stick the comment box*/ overflow-y: scroll; border: 1px solid #ccc; border-radius: 4px; padding: 5px; }
        /*.chat-box { height: 300px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px; }*/
        .timestamp { font-size: 12px; color: gray; display: block; }
        .actions { font-size: 12px; color: blue; cursor: pointer; }
        .spacert { position: sticky; top: 0; margin: 6px 0 2px 0; }
        .spacerb { position: sticky; bottom: 0; margin: 2px 0 0 0; height: auto; }
        input { border-radius: 4px; }
        button { background: yellow; border: 1px solid orange; border-radius: 4px; padding: 4px 6px; }
        textarea { width: 99%; border: 1px solid orange; border-radius: 4px; height: 60px; resize: none; }
    </style>
</head>
<body>

<div class="chat-container">
    <!-- <h3>Chat</h3> -->

    <!-- Friend Selection -->
    <form method="GET">
    <div class="spacert">
        <a href="find_users.php">Find New Friends</a><label> |
        <a href="friend_requests.php">Friend Requests (<?= $pending_requests ?>)</a> <br/>
        Select friend to chat with:</label>
        <select name="user_id" onchange="this.form.submit()">
            <option value="">-- Select --</option>
            <?php while ($friend = $resultFriends->fetch_assoc()): ?>
                <option value="<?= $friend['id'] ?>" <?= ($chatUser == $friend['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($friend['username']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    </form> 

    <?php if ($chatUser): ?>
        <div class="chat-box"> <!--the disadvantage of adding this id="chatBox" will be able to hide the del and edit buttons-->
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= ($msg['sender_id'] == $loggedInUser) ? 'my-message' : 'other-message' ?>">
                    <?= htmlspecialchars($msg['message']) ?>
                    <br><small><?= date('Y-m-d H:i:s', strtotime($msg['timestamp'])) ?></small>

                    <?php if ($msg['sender_id'] == $loggedInUser): ?>
                        <span class="actions">
                            <a href="edit_message.php?id=<?= $msg['id'] ?>&user_id=<?= $chatUser ?>">Edit</a> | 
                            <a href="delete_message.php?id=<?= $msg['id'] ?>&user_id=<?= $chatUser ?>" onclick="return confirm('Delete this message?')">Delete</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
                <div id="last"></div>
        </div>


        <!-- Message Input Form -->
        <form method="POST" action="send_message.php">
        <div class="spacerb">
            <input type="hidden" name="receiver_id" value="<?= $chatUser ?>">
            <textarea name="message" required placeholder="Type your message here..." rows="3"></textarea>
            <button type="submit">Send</button>
        </div>
        </form>
    <?php endif; ?>
</div>

<!--<script>
    function scrollToBottom() {
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Scroll when the page loads
    window.onload = scrollToBottom;

    // Auto-scroll on form submission
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent form from reloading the page

        var formData = new FormData(this);

        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                // Refresh the chat messages without reloading
                fetch('fetch_latest_messages.php?user_id=<?= $chatUser ?>')
                .then(response => response.text())
                .then(html => {
                    document.getElementById("chatBox").innerHTML = html;
                    scrollToBottom(); // Scroll after new message loads
                    document.querySelector("textarea[name='message']").value = ""; // Clear input
                });
            }
        });
    });

    // Auto-update chat every 3 seconds
    setInterval(() => {
        fetch('fetch_latest_messages.php?user_id=<?= $chatUser ?>')
        .then(response => response.text())
        .then(html => {
            document.getElementById("chatBox").innerHTML = html;
            scrollToBottom();
        });
    }, 3000);
</script>-->

</body>
</html>

<?php include 'includes/footer.php'; ?>
