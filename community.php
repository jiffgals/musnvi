<?php
session_start();
include 'db/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_GET['action'])) {
        echo "<script>alert('You must be logged in to view this page.'); window.location.href='login.php';</script>";
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit();
    }
}

$user_id = $_SESSION['user_id'];

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    // Handle Like/Unlike
    if ($_GET['action'] === 'like' && isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        $check = $conn->prepare("SELECT id FROM single_likes WHERE post_id = ? AND user_id = ?");
        $check->bind_param("ii", $post_id, $user_id);
        $check->execute();
        $like_exists = $check->get_result()->fetch_assoc();

        if ($like_exists) {
            $delete = $conn->prepare("DELETE FROM single_likes WHERE post_id = ? AND user_id = ?");
            $delete->bind_param("ii", $post_id, $user_id);
            $delete->execute();
            $action = 'unliked';
        } else {
            $insert = $conn->prepare("INSERT INTO single_likes (post_id, user_id) VALUES (?, ?)");
            $insert->bind_param("ii", $post_id, $user_id);
            $insert->execute();
            $action = 'liked';
        }

        $like_query = $conn->prepare("SELECT COUNT(*) as like_count FROM single_likes WHERE post_id = ?");
        $like_query->bind_param("i", $post_id);
        $like_query->execute();
        $like_count = $like_query->get_result()->fetch_assoc()['like_count'];

        echo json_encode(['status' => 'success', 'action' => $action, 'likes' => $like_count]);
        exit();
    }

    // Handle Comment
    if ($_GET['action'] === 'comment' && isset($_POST['post_id']) && isset($_POST['comment'])) {
        $post_id = intval($_POST['post_id']);
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $insert = $conn->prepare("INSERT INTO single_comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $insert->bind_param("iis", $post_id, $user_id, $comment);
            if ($insert->execute()) {
                $comment_id = $conn->insert_id;
                $user_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $user_query->bind_param("i", $user_id);
                $user_query->execute();
                $username = $user_query->get_result()->fetch_assoc()['username'];

                echo json_encode(['status' => 'success', 'comment_id' => $comment_id, 'username' => $username, 'user_id' => $user_id, 'created_at' => date('F j, Y, g:i A')]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
        }
        exit();
    }

    // Handle Reply
    if ($_GET['action'] === 'reply' && isset($_POST['post_id']) && isset($_POST['comment_id']) && isset($_POST['reply'])) {
        $post_id = intval($_POST['post_id']);
        $comment_id = intval($_POST['comment_id']);
        $reply = trim($_POST['reply']);
        if (!empty($reply)) {
            $insert = $conn->prepare("INSERT INTO single_comments (post_id, user_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iisi", $post_id, $user_id, $reply, $comment_id);
            if ($insert->execute()) {
                $user_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $user_query->bind_param("i", $user_id);
                $user_query->execute();
                $username = $user_query->get_result()->fetch_assoc()['username'];
                echo json_encode(['status' => 'success', 'username' => $username, 'user_id' => $user_id, 'created_at' => date('F j, Y, g:i A')]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Reply cannot be empty']);
        }
        exit();
    }

    // Handle Edit Comment/Reply
    if ($_GET['action'] === 'edit_comment' && isset($_POST['comment_id']) && isset($_POST['content'])) {
        $comment_id = intval($_POST['comment_id']);
        $content = trim($_POST['content']);
        
        $check = $conn->prepare("SELECT user_id FROM single_comments WHERE id = ?");
        $check->bind_param("i", $comment_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        if ($result && $result['user_id'] == $user_id) {
            if (!empty($content)) {
                $update = $conn->prepare("UPDATE single_comments SET content = ? WHERE id = ?");
                $update->bind_param("si", $content, $comment_id);
                if ($update->execute()) {
                    echo json_encode(['status' => 'success', 'content' => $content]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Database error']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Content cannot be empty']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
        exit();
    }

    // Handle Delete Comment/Reply
    if ($_GET['action'] === 'delete_comment' && isset($_POST['comment_id'])) {
        $comment_id = intval($_POST['comment_id']);
        
        $check = $conn->prepare("SELECT user_id FROM single_comments WHERE id = ?");
        $check->bind_param("i", $comment_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        if ($result && $result['user_id'] == $user_id) {
            $delete = $conn->prepare("DELETE FROM single_comments WHERE id = ? OR parent_comment_id = ?");
            $delete->bind_param("ii", $comment_id, $comment_id);
            if ($delete->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
        exit();
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit();
}

// Fetch posts with search filter
$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchQuery = "WHERE posts.content LIKE '%$search%'";
}
$query = "SELECT posts.*, users.username FROM posts 
          JOIN users ON posts.user_id = users.id 
          $searchQuery 
          ORDER BY posts.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Page</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 100%; border-radius: 5px; }
        .post-box { margin-bottom: 3px; padding: 3px; border: 1px solid #eee; border-radius: 5px; }
        .edit-btn, .edit-comment-btn { background-color: white; color: black; border: 0.5px solid #ccc; border-radius: 5px; padding: 4px 10px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .edit-btn:hover, .edit-comment-btn:hover { background-color: #bbb; }
        .delete-btn, .delete-comment-btn { background-color: white; color: black; border: 0.5px solid #ccc; border-radius: 5px; padding: 4px 10px; cursor: pointer; margin: 0 0 2px 0; box-shadow: none; }
        .delete-btn:hover, .delete-comment-btn:hover { background-color: #bbb; }
        .user, a { color: red; padding: 1px 2px 0 2px; margin: 0 0 6px 0; text-decoration: none; }
        .content { padding: 4px 2px 0 2px; margin: 0; color: black; white-space: pre-wrap; }
        .timestamp { color: gray; font-size: 10px; padding: 4px 6px; margin: 0 0 3px 0; }
        p { border: none; margin: none; }
        button { background: yellow; color: black; border: 1px solid #bbb; border-radius: 5px; padding: 6px 8px; }
        .post-btn { background: orange; }
        .post-btn:hover { background: yellow; }
        .ksearch-btn { background: yellow; padding: 3px 6px; }
        .ksearch-btn:hover { background: orange; }
        .like-btn { background: white; border-radius: 50px; padding: 4px 4px; }
        .like-btn.liked { background: orange; color: white; }
        .like-btn:hover { background: orange; }
        .like-btn.liked:hover { background: white; }
        input { max-width: 100%; background: none; border: 1px solid #bbb; border-radius: 5px; padding: 3px 5px; }
        .ta-height { border: 1px solid orange; height: 30px; padding: 5px 10px; }
        textarea { background: #ddd; width: 94.9%; height: 40px; border: 0.5px solid #ddd; border-radius: 5px; resize: none; }
        textarea:hover { background-color: #ddd; border: 1px solid #ccc; border-radius: 4px; }
        .comments-section { border-radius: 4px; padding: 0 4px; }
        .comment-box { border-top: 1px solid #eee; }
        .comment-form { max-width: 99.1%; border-radius: 4px; margin: 2px 0; padding: 0 0 2px 0; }
        .comment-btn { background: #ddd; }
        .comment-btn2 { width: 21%; background: #ddd; }
        .comlabel { padding: 4px 2px; margin: 3px 0; }
        .reply-form, .edit-comment-form { display: none; margin-top: 5px; }
        .textstick { max-width: 99.1%; background-color: white; position: sticky; top: 69.5px; border-bottom: 0.2px solid orange; border-radius: 5px; padding: 3px 1px 3px 1px; }
        pre { font-family: Arial, sans-serif; }
        .twitter-share { background-color: #1DA1F2; color: white; border: none; padding: 2px 5px; cursor: pointer; font-size: 14px; border-radius: 5px; }
        .twitter-share:hover { background-color: #0d8ddb; }
        .social-share { margin: 5px 2px 10px 2px; }
        .facebook-share { background-color: #3b5998; color: white; border: none; padding: 2px 5px; cursor: pointer; font-size: 14px; border-radius: 5px; }
        .facebook-share:hover { background-color: #2d4373; }
        .reddit-share { background-color: #ff4500; color: white; border: none; padding: 2px 5px; cursor: pointer; font-size: 14px; border-radius: 5px; }
        .reddit-share:hover { background-color: #cc3700; }
        .linkedin-share { background-color: #0077b5; color: white; border: none; padding: 2px 5px; cursor: pointer; font-size: 14px; border-radius: 5px; }
        .linkedin-share:hover { background-color: #005582; }
        .pinterest-share { background-color: #bd081c; color: white; border: none; padding: 2px 5px; cursor: pointer; font-size: 14px; border-radius: 5px; }
        .pinterest-share:hover { background-color: #8c0615; }
        .psearch { max-width: 100%; float: right; margin: 0 -1px 3px 0; }
        .popup-container { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255); justify-content: center; align-items: center; z-index: 1000; margin-top: 70px; }
        .popup-content { background: rgba(255, 0, 0, 0.1); padding: 27px 3px; margin: 4px 3px 75px 3px; width: 99%; max-width: 100%; height: auto; border: 0.5px solid red; border-radius: 10px; text-align: center; }
        .popup-content textarea { background: white; max-width: 90%; height: 490px; font-size: 18px; padding: 10px; margin: 0 3px; border: 1px solid orange; border-radius: 5px; }
        .popup-buttons { margin-top: 10px; }
        .popup-buttons button { padding: 10px; margin: 5px; cursor: pointer; }
        .rep-comment-box, a { padding: 0 2px 2px 2px; margin: 0; }
        .rep-user { margin: 2px 0 4px 0; font-size: 14px; text-decoration: none; }
        .rep-timestamp { color: #aaa; font-size: 8px; padding: 0 4px 0 4px; margin: 0; }
        .reply-btn { background: transparent; color: gray; font-size: 12px; border: none; padding: 3px 4px; margin: 0 1px 1px 1px; box-shadow: none; }
        .reply-btn:hover { background-color: transparent; }
        .view-replies-btn { background: transparent; color: gray; font-size: 12px; border: none; padding: 3px 4px; margin: 0 1px 1px 1px; box-shadow: none; }
        .sreply-btn { background: #eee; padding: 5px 8px; margin: 2px 0 1px 0; }
        .load-more-btn { background: transparent; color: gray; font-size: 12px; border: none; padding: 3px 4px; margin: 0 2px 1px 2px; box-shadow: none; }
        .click-more-btn { background: #eee; border: 1px solid #ddd; border-radius: 5px; color: #ff4500; text-decoration: none; font-size: 12px; padding: 4px 10px; margin-left: -32px; }
        .click-more-btn:hover { background: #ddd; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; }
        .reply-content { color: red; padding: 4px; margin: 0; }
        .replies { padding: 0; margin: 0; }
        .comments-container { display: block; }
        .more-comments { display: none; }
    </style>
</head>
<body>
<div class="container">
    <h3>Community Posts</h3>

    <!-- Post Submission Form -->
    <div class="textstick">
        <form method="GET" action="community.php" class="psearch">
            <input type="text" name="search" placeholder="Search posts..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="ksearch-btn">Search</button>
        </form>

        <form action="post_submit.php" method="POST" enctype="multipart/form-data" id="post-form">
            <textarea class="ta-height" name="content" id="post-textarea" placeholder="What's on your mind?" onclick="openPopup()"></textarea><br>
            <input type="file" name="media" accept="image/*,video/*"> 
            <button class="post-btn" type="submit">Post</button>
        </form>
    </div>

    <!-- Popup for Fullscreen Textarea -->
    <div id="popup-container" class="popup-container">
        <div class="popup-content">
            <form action="post_submit.php" method="POST" enctype="multipart/form-data" id="popup-post-form">
                <textarea id="popup-textarea" name="content" placeholder="What's on your mind?" oninput="syncText()"></textarea><br>
                <input type="file" name="media" accept="image/*,video/*"> 
                <div class="popup-buttons">
                    <button class="comment-btn2" type="button" onclick="closePopup()">Cancel</button>
                    <button class="comment-btn2" type="submit">Post</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Display Posts -->
    <?php while ($post = mysqli_fetch_assoc($result)): 
        $content = htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8');
        $highlighted = isset($search) && !empty($search) ? 
            preg_replace("/($search)/i", "<span style='background-color: yellow; font-weight: bold;'>$1</span>", $content) : 
            $content;
    ?>
        <div class="post-box">
            <p class="user">
                <a href="view_profile.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>">
                    <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                </a>
            </p>

            <!-- Display Media -->
            <?php if (!empty($post['media'])): ?>
                <?php 
                    $file_ext = pathinfo($post['media'], PATHINFO_EXTENSION);
                    $media_url = "uploads/" . $post['media'];
                ?>
                <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <img src="<?php echo $media_url; ?>" alt="Post Image" style="max-width:90%; height:auto; border:1px solid orange;border-radius:5px;">
                <?php elseif (in_array($file_ext, ['mp4', 'mov', 'avi'])): ?>
                    <video controls style="max-width:90%; border:1px solid orange;border-radius:5px;">
                        <source src="<?php echo $media_url; ?>" type="video/<?php echo $file_ext; ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            $content_full = htmlspecialchars_decode($highlighted);
            $content_truncated = strlen($content_full) > 480 ? substr($content_full, 0, 477) . '...' : $content_full;
            ?>
            <p class="content"><?php echo $content_truncated; ?>
                <?php if (strlen($content_full) > 480): ?> <br/>
                    <a href="view_post.php?post_id=<?php echo $post['id']; ?>" class="click-more-btn">View Full</a>
                <?php endif; ?>
            </p>
            <p class="timestamp">Posted on: <?php echo date('F j, Y, g:i A', strtotime($post['created_at'])); ?></p>

            <!-- Like Section -->
            <?php 
            $like_query = $conn->prepare("SELECT COUNT(*) as like_count FROM single_likes WHERE post_id = ?");
            $like_query->bind_param("i", $post['id']);
            $like_query->execute();
            $like_count = $like_query->get_result()->fetch_assoc()['like_count'];
            $check_like = $conn->prepare("SELECT id FROM single_likes WHERE post_id = ? AND user_id = ?");
            $check_like->bind_param("ii", $post['id'], $user_id);
            $check_like->execute();
            $is_liked = $check_like->get_result()->fetch_assoc() ? true : false;
            ?>
            <form method="POST" action="community.php?action=like" class="like-form" data-post-id="<?php echo $post['id']; ?>">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <button type="submit" name="like" class="like-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                    <?php echo $is_liked ? '❤️' : '👍'; ?>
                </button> (<span class="like-count" data-post-id="<?php echo $post['id']; ?>"><?php echo $like_count; ?></span>)
            </form>

            <!-- Edit & Delete Buttons for Post -->
            <?php if ($post['user_id'] == $user_id): ?>
                <a class="edit-btn" href="edit_post.php?post_id=<?php echo $post['id']; ?>">Edit</a>
                <form action="delete_post.php" method="POST" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this post?');">Delete</button>
                </form>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="comments-section">
                <h4 class="comlabel">Comments</h4>

                <!-- Comments Display -->
                <div class="comments-container" id="comments-<?php echo $post['id']; ?>">
                    <?php
                    $total_comments_query = $conn->prepare("SELECT COUNT(*) as total FROM single_comments WHERE post_id = ? AND parent_comment_id IS NULL");
                    $total_comments_query->bind_param("i", $post['id']);
                    $total_comments_query->execute();
                    $total_comments = $total_comments_query->get_result()->fetch_assoc()['total'];

                    // Changed LIMIT from 3 to 1
                    $comments_query = $conn->prepare("SELECT single_comments.*, users.username 
                        FROM single_comments 
                        JOIN users ON single_comments.user_id = users.id 
                        WHERE post_id = ? AND parent_comment_id IS NULL 
                        ORDER BY created_at DESC LIMIT 1");
                    $comments_query->bind_param("i", $post['id']);
                    $comments_query->execute();
                    $comments = $comments_query->get_result();
                    $comment_count = 0;

                    while ($comment = $comments->fetch_assoc()):
                        $comment_count++;
                    ?>
                        <div class="comment-box" data-comment-id="<?php echo $comment['id']; ?>">
                            <p class="rep-user">
                                <a href="view_profile.php?user_id=<?php echo htmlspecialchars($comment['user_id']); ?>">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                </a>
                            </p>
                            <p class="reply-content" data-comment-id="<?php echo $comment['id']; ?>"><?php echo htmlspecialchars($comment['content']); ?></p>
                            <p class="rep-timestamp"><?php echo date('F j, Y, g:i A', strtotime($comment['created_at'])); ?></p>
                            <button class="reply-btn" onclick="showReplyForm(<?php echo $comment['id']; ?>)">Reply</button>
                            <?php if ($comment['user_id'] == $user_id): ?>
                                <button class="edit-comment-btn" onclick="showEditCommentForm(<?php echo $comment['id']; ?>)">Edit</button>
                                <button class="delete-comment-btn" onclick="deleteComment(<?php echo $comment['id']; ?>)">Delete</button>
                            <?php endif; ?>

                            <!-- Edit Comment Form -->
                            <div id="edit-comment-form-<?php echo $comment['id']; ?>" class="edit-comment-form">
                                <form class="edit-comment-form-inner" data-comment-id="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <textarea name="content" placeholder="Edit your comment..."><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                    <button class="sreply-btn" type="submit">Save</button>
                                </form>
                            </div>

                            <!-- Reply Form -->
                            <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form">
                                <form class="reply-form-inner" data-post-id="<?php echo $post['id']; ?>" data-comment-id="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <textarea name="reply" placeholder="Write a reply..."></textarea>
                                    <button class="sreply-btn" type="submit">Submit Reply</button>
                                </form>
                            </div>

                            <!-- Replies Display -->
                            <?php
                            $replies_query = $conn->prepare("SELECT COUNT(*) as reply_count FROM single_comments WHERE parent_comment_id = ?");
                            $replies_query->bind_param("i", $comment['id']);
                            $replies_query->execute();
                            $reply_count = $replies_query->get_result()->fetch_assoc()['reply_count'];
                            ?>
                            <?php if ($reply_count > 0): ?>
                                <button class="view-replies-btn" onclick="toggleReplies(<?php echo $comment['id']; ?>)">View Replies (<?php echo $reply_count; ?>)</button>
                                <div id="replies-<?php echo $comment['id']; ?>" class="replies" style="display: none;">
                                    <?php
                                    $replies_query = $conn->prepare("SELECT single_comments.*, users.username 
                                        FROM single_comments 
                                        JOIN users ON single_comments.user_id = users.id 
                                        WHERE parent_comment_id = ? 
                                        ORDER BY created_at ASC");
                                    $replies_query->bind_param("i", $comment['id']);
                                    $replies_query->execute();
                                    $replies = $replies_query->get_result();
                                    while ($reply = $replies->fetch_assoc()): ?>
                                        <div class="rep-comment-box" data-comment-id="<?php echo $reply['id']; ?>">
                                            <p class="rep-user">
                                                <a href="view_profile.php?user_id=<?php echo htmlspecialchars($reply['user_id']); ?>">
                                                    <strong><?php echo htmlspecialchars($reply['username']); ?></strong>
                                                </a>
                                            </p>
                                            <p class="reply-content" data-comment-id="<?php echo $reply['id']; ?>"><?php echo htmlspecialchars($reply['content']); ?></p>
                                            <p class="rep-timestamp"><?php echo date('F j, Y, g:i A', strtotime($reply['created_at'])); ?></p>
                                            <?php if ($reply['user_id'] == $user_id): ?>
                                                <button class="edit-comment-btn" onclick="showEditCommentForm(<?php echo $reply['id']; ?>)">Edit</button>
                                                <button class="delete-comment-btn" onclick="deleteComment(<?php echo $reply['id']; ?>)">Delete</button>
                                            <?php endif; ?>

                                            <!-- Edit Reply Form -->
                                            <div id="edit-comment-form-<?php echo $reply['id']; ?>" class="edit-comment-form">
                                                <form class="edit-comment-form-inner" data-comment-id="<?php echo $reply['id']; ?>">
                                                    <input type="hidden" name="comment_id" value="<?php echo $reply['id']; ?>">
                                                    <textarea name="content" placeholder="Edit your reply..."><?php echo htmlspecialchars($reply['content']); ?></textarea>
                                                    <button class="sreply-btn" type="submit">Save</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>

                    <!-- More Comments Section -->
                    <?php if ($total_comments > 1): // Changed from > 3 to > 1 ?>
                        <div class="more-comments" id="more-comments-<?php echo $post['id']; ?>">
                            <?php
                            // Changed offset from 3 to 1
                            $more_comments_query = $conn->prepare("SELECT single_comments.*, users.username 
                                FROM single_comments 
                                JOIN users ON single_comments.user_id = users.id 
                                WHERE post_id = ? AND parent_comment_id IS NULL 
                                ORDER BY created_at DESC LIMIT 1, " . ($total_comments - 1));
                            $more_comments_query->bind_param("i", $post['id']);
                            $more_comments_query->execute();
                            $more_comments = $more_comments_query->get_result();
                            while ($comment = $more_comments->fetch_assoc()): ?>
                                <div class="comment-box" data-comment-id="<?php echo $comment['id']; ?>">
                                    <p class="rep-user">
                                        <a href="view_profile.php?user_id=<?php echo htmlspecialchars($comment['user_id']); ?>">
                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                        </a>
                                    </p>
                                    <p class="reply-content" data-comment-id="<?php echo $comment['id']; ?>"><?php echo htmlspecialchars($comment['content']); ?></p>
                                    <p class="rep-timestamp"><?php echo date('F j, Y, g:i A', strtotime($comment['created_at'])); ?></p>
                                    <button class="reply-btn" onclick="showReplyForm(<?php echo $comment['id']; ?>)">Reply</button>
                                    <?php if ($comment['user_id'] == $user_id): ?>
                                        <button class="edit-comment-btn" onclick="showEditCommentForm(<?php echo $comment['id']; ?>)">Edit</button>
                                        <button class="delete-comment-btn" onclick="deleteComment(<?php echo $comment['id']; ?>)">Delete</button>
                                    <?php endif; ?>

                                    <!-- Edit Comment Form -->
                                    <div id="edit-comment-form-<?php echo $comment['id']; ?>" class="edit-comment-form">
                                        <form class="edit-comment-form-inner" data-comment-id="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <textarea name="content" placeholder="Edit your comment..."><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                            <button class="sreply-btn" type="submit">Save</button>
                                        </form>
                                    </div>

                                    <!-- Reply Form -->
                                    <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form">
                                        <form class="reply-form-inner" data-post-id="<?php echo $post['id']; ?>" data-comment-id="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <textarea name="reply" placeholder="Write a reply..."></textarea>
                                            <button class="sreply-btn" type="submit">Submit Reply</button>
                                        </form>
                                    </div>

                                    <!-- Replies Display -->
                                    <?php
                                    $replies_query = $conn->prepare("SELECT COUNT(*) as reply_count FROM single_comments WHERE parent_comment_id = ?");
                                    $replies_query->bind_param("i", $comment['id']);
                                    $replies_query->execute();
                                    $reply_count = $replies_query->get_result()->fetch_assoc()['reply_count'];
                                    ?>
                                    <?php if ($reply_count > 0): ?>
                                        <button class="view-replies-btn" onclick="toggleReplies(<?php echo $comment['id']; ?>)">View Replies (<?php echo $reply_count; ?>)</button>
                                        <div id="replies-<?php echo $comment['id']; ?>" class="replies" style="display: none;">
                                            <?php
                                            $replies_query = $conn->prepare("SELECT single_comments.*, users.username 
                                                FROM single_comments 
                                                JOIN users ON single_comments.user_id = users.id 
                                                WHERE parent_comment_id = ? 
                                                ORDER BY created_at ASC");
                                            $replies_query->bind_param("i", $comment['id']);
                                            $replies_query->execute();
                                            $replies = $replies_query->get_result();
                                            while ($reply = $replies->fetch_assoc()): ?>
                                                <div class="rep-comment-box" data-comment-id="<?php echo $reply['id']; ?>">
                                                    <p class="rep-user">
                                                        <a href="view_profile.php?user_id=<?php echo htmlspecialchars($reply['user_id']); ?>">
                                                            <strong><?php echo htmlspecialchars($reply['username']); ?></strong>
                                                        </a>
                                                    </p>
                                                    <p class="reply-content" data-comment-id="<?php echo $reply['id']; ?>"><?php echo htmlspecialchars($reply['content']); ?></p>
                                                    <p class="rep-timestamp"><?php echo date('F j, Y, g:i A', strtotime($reply['created_at'])); ?></p>
                                                    <?php if ($reply['user_id'] == $user_id): ?>
                                                        <button class="edit-comment-btn" onclick="showEditCommentForm(<?php echo $reply['id']; ?>)">Edit</button>
                                                        <button class="delete-comment-btn" onclick="deleteComment(<?php echo $reply['id']; ?>)">Delete</button>
                                                    <?php endif; ?>

                                                    <!-- Edit Reply Form -->
                                                    <div id="edit-comment-form-<?php echo $reply['id']; ?>" class="edit-comment-form">
                                                        <form class="edit-comment-form-inner" data-comment-id="<?php echo $reply['id']; ?>">
                                                            <input type="hidden" name="comment_id" value="<?php echo $reply['id']; ?>">
                                                            <textarea name="content" placeholder="Edit your reply..."><?php echo htmlspecialchars($reply['content']); ?></textarea>
                                                            <button class="sreply-btn" type="submit">Save</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <!-- Changed count from -3 to -1 -->
                        <button class="load-more-btn" onclick="toggleMoreComments(<?php echo $post['id']; ?>)">View More Comments (<?php echo $total_comments - 1; ?>)</button>
                    <?php endif; ?>
                </div>

                <!-- Comment Form -->
                <form class="comment-form" data-post-id="<?php echo $post['id']; ?>">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <textarea name="comment" placeholder="Write a comment..."></textarea>
                    <button class="comment-btn" type="submit">Comment</button>
                </form>
            </div>

            <!-- Social Media Sharing Buttons -->
            <div class="social-share">
                Share on: 
                <?php
                $twitter_content = substr($post['content'], 0, 200);
                $reddit_content = substr($post['content'], 0, 300);
                $post_url = "https://musnvi.42web.io/view_post.php?post_id=" . $post['id'];
                ?>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($twitter_content); ?>&url=<?php echo urlencode($post_url); ?>" target="_blank">
                    <button class="twitter-share">Twit</button>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($post_url); ?>" target="_blank">
                    <button class="facebook-share">FB</button>
                </a>
                <a href="https://www.reddit.com/submit?url=<?php echo urlencode($post_url); ?>&title=<?php echo urlencode($reddit_content); ?>" target="_blank">
                    <button class="reddit-share">Red</button>
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($post_url); ?>&title=<?php echo urlencode($post['content']); ?>&summary=<?php echo urlencode($post['content']); ?>" target="_blank">
                    <button class="linkedin-share">Link</button>
                </a>
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($post_url); ?>&description=<?php echo urlencode($post['content']); ?><?php echo !empty($post['media']) && in_array(pathinfo($post['media'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']) ? '&media=' . urlencode("https://musnvi.42web.io/uploads/" . $post['media']) : ''; ?>" target="_blank">
                    <button class="pinterest-share">Pin</button>
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
function openPopup() {
    document.getElementById("popup-container").style.display = "flex";
    document.getElementById("popup-textarea").value = document.getElementById("post-textarea").value;
}

function syncText() {
    document.getElementById("post-textarea").value = document.getElementById("popup-textarea").value;
}

function closePopup() {
    document.getElementById("popup-container").style.display = "none";
}

function showReplyForm(comment_id) {
    let replyForm = document.getElementById(`reply-form-${comment_id}`);
    if (replyForm.style.display === "none" || replyForm.style.display === "") {
        replyForm.style.display = "block";
    } else {
        replyForm.style.display = "none";
    }
}

function toggleReplies(comment_id) {
    let repliesDiv = document.getElementById(`replies-${comment_id}`);
    if (repliesDiv.style.display === "none" || repliesDiv.style.display === "") {
        repliesDiv.style.display = "block";
    } else {
        repliesDiv.style.display = "none";
    }
}

function toggleMoreComments(post_id) {
    let moreCommentsDiv = document.getElementById(`more-comments-${post_id}`);
    let loadMoreBtn = document.querySelector(`#comments-${post_id} .load-more-btn`);
    if (moreCommentsDiv.style.display === "none" || moreCommentsDiv.style.display === "") {
        moreCommentsDiv.style.display = "block";
        loadMoreBtn.textContent = "Hide Extra Comments";
    } else {
        moreCommentsDiv.style.display = "none";
        loadMoreBtn.textContent = `View More Comments (${moreCommentsDiv.querySelectorAll('.comment-box').length})`;
    }
}

function showEditCommentForm(comment_id) {
    let editForm = document.getElementById(`edit-comment-form-${comment_id}`);
    if (editForm.style.display === "none" || editForm.style.display === "") {
        editForm.style.display = "block";
    } else {
        editForm.style.display = "none";
    }
}

// Handle Like/Unlike Submission
document.querySelectorAll('.like-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const postId = this.dataset.postId;
        const likeBtn = this.querySelector('.like-btn');
        const likeCountSpan = document.querySelector(`.like-count[data-post-id="${postId}"]`);

        fetch('community.php?action=like', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                likeCountSpan.textContent = data.likes;
                if (data.action === 'liked') {
                    likeBtn.classList.add('liked');
                    likeBtn.innerHTML = '❤️';
                } else if (data.action === 'unliked') {
                    likeBtn.classList.remove('liked');
                    likeBtn.innerHTML = '👍';
                }
            } else {
                alert('Error processing like: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

// Handle Comment Submission
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const postId = this.dataset.postId;

        fetch('community.php?action=comment', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const commentsContainer = document.getElementById(`comments-${postId}`);
                const commentBox = document.createElement('div');
                commentBox.className = 'comment-box';
                commentBox.dataset.commentId = data.comment_id;
                commentBox.innerHTML = `
                    <p class="rep-user"><a href="view_profile.php?user_id=${data.user_id}"><strong>${data.username}</strong></a></p>
                    <p class="reply-content" data-comment-id="${data.comment_id}">${formData.get('comment')}</p>
                    <p class="rep-timestamp">${data.created_at}</p>
                    <button class="reply-btn" onclick="showReplyForm(${data.comment_id})">Reply</button>
                    <button class="edit-comment-btn" onclick="showEditCommentForm(${data.comment_id})">Edit</button>
                    <button class="delete-comment-btn" onclick="deleteComment(${data.comment_id})">Delete</button>
                    <div id="edit-comment-form-${data.comment_id}" class="edit-comment-form">
                        <form class="edit-comment-form-inner" data-comment-id="${data.comment_id}">
                            <input type="hidden" name="comment_id" value="${data.comment_id}">
                            <textarea name="content" placeholder="Edit your comment...">${formData.get('comment')}</textarea>
                            <button class="sreply-btn" type="submit">Save</button>
                        </form>
                    </div>
                    <div id="reply-form-${data.comment_id}" class="reply-form">
                        <form class="reply-form-inner" data-post-id="${postId}" data-comment-id="${data.comment_id}">
                            <input type="hidden" name="post_id" value="${postId}">
                            <input type="hidden" name="comment_id" value="${data.comment_id}">
                            <textarea name="reply" placeholder="Write a reply..."></textarea>
                            <button class="sreply-btn" type="submit">Submit Reply</button>
                        </form>
                    </div>
                    <div id="replies-${data.comment_id}" class="replies" style="display: none;"></div>
                `;
                commentsContainer.insertBefore(commentBox, commentsContainer.firstChild);
                this.reset();

                attachReplyFormListeners();
                attachEditCommentFormListeners();
            } else {
                alert('Error posting comment: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

// Handle Reply Submission
function attachReplyFormListeners() {
    document.querySelectorAll('.reply-form-inner').forEach(form => {
        form.removeEventListener('submit', handleReplySubmit);
        form.addEventListener('submit', handleReplySubmit);
    });
}

function handleReplySubmit(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const postId = this.dataset.postId;
    const commentId = this.dataset.commentId;

    fetch('community.php?action=reply', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const repliesContainer = document.getElementById(`replies-${commentId}`);
            const replyBox = document.createElement('div');
            replyBox.className = 'rep-comment-box';
            replyBox.dataset.commentId = commentId;
            replyBox.innerHTML = `
                <p class="rep-user"><a href="view_profile.php?user_id=${data.user_id}}><strong>${data.username}</strong></a></p>
                <p class="reply-content" data-comment-id="${commentId}">${formData.get('reply')}</p>
                <p class="rep-timestamp">${data.created_at}</p>
                <button class="edit-comment-btn" onclick="showEditCommentForm(${commentId})">Edit</button>
                <button class="delete-comment-btn" onclick="deleteComment(${commentId})">Delete</button>
                <div id="edit-comment-form-${commentId}" class="edit-comment-form">
                    <form class="edit-comment-form-inner" data-comment-id="${commentId}">
                        <input type="hidden" name="comment_id" value="${commentId}">
                        <textarea name="content" placeholder="Edit your reply...">${formData.get('reply')}</textarea>
                        <button class="sreply-btn" type="submit">Save</button>
                    </form>
                </div>
            `;
            repliesContainer.appendChild(replyBox);
            this.reset();

            let viewRepliesBtn = document.querySelector(`#replies-${commentId}`).previousElementSibling;
            if (!viewRepliesBtn || !viewRepliesBtn.classList.contains('view-replies-btn')) {
                viewRepliesBtn = document.createElement('button');
                viewRepliesBtn.className = 'view-replies-btn';
                viewRepliesBtn.textContent = 'View Replies (1)';
                viewRepliesBtn.onclick = () => toggleReplies(commentId);
                repliesContainer.insertAdjacentElement('beforebegin', viewRepliesBtn);
            } else {
                let currentCount = parseInt(viewRepliesBtn.textContent.match(/\d+/)) || 0;
                viewRepliesBtn.textContent = `View Replies (${currentCount + 1})`;
            }
            repliesContainer.style.display = 'block';

            attachEditCommentFormListeners();
        } else {
            alert('Error posting reply: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Handle Edit Comment/Reply Submission
function attachEditCommentFormListeners() {
    document.querySelectorAll('.edit-comment-form-inner').forEach(form => {
        form.removeEventListener('submit', handleEditCommentSubmit);
        form.addEventListener('submit', handleEditCommentSubmit);
    });
}

function handleEditCommentSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const commentId = this.dataset.commentId;

    fetch('community.php?action=edit_comment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const contentElement = document.querySelector(`.reply-content[data-comment-id="${commentId}"]`);
            contentElement.textContent = data.content;
            document.getElementById(`edit-comment-form-${commentId}`).style.display = 'none';
        } else {
            alert('Error editing comment: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Handle Delete Comment/Reply
function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        const formData = new FormData();
        formData.append('comment_id', commentId);

        fetch('community.php?action=delete_comment', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const commentBox = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (commentBox.classList.contains('comment-box')) {
                    commentBox.remove();
                } else if (commentBox.classList.contains('rep-comment-box')) {
                    const repliesContainer = commentBox.closest('.replies');
                    commentBox.remove();
                    const remainingReplies = repliesContainer.querySelectorAll('.rep-comment-box').length;
                    const viewRepliesBtn = repliesContainer.previousElementSibling;
                    if (remainingReplies === 0) {
                        viewRepliesBtn.remove();
                        repliesContainer.remove();
                    } else {
                        viewRepliesBtn.textContent = `View Replies (${remainingReplies})`;
                    }
                }
            } else {
                alert('Error deleting comment: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Initialize listeners
document.addEventListener('DOMContentLoaded', function() {
    attachReplyFormListeners();
    attachEditCommentFormListeners();
});
</script>
</body>
</html>

<?php include 'includes/footer.php'; ?>