<?php
session_start(); // Ensure session is started
include 'db/database.php';

// Check if post_id is provided
if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    die("Invalid post ID.");
}

$post_id = intval($_GET['post_id']);

// Fetch the post
$query = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$query->bind_param("i", $post_id);
$query->execute();
$result = $query->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post not found.");
}

// Handle like submission
if (isset($_POST['like']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check = $conn->prepare("SELECT id FROM single_likes WHERE post_id = ? AND user_id = ?");
    $check->bind_param("ii", $post_id, $user_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if (!$check_result->fetch_assoc()) {
        $insert = $conn->prepare("INSERT INTO single_likes (post_id, user_id) VALUES (?, ?)");
        $insert->bind_param("ii", $post_id, $user_id);
        if ($insert->execute()) {
            header("Location: view_post.php?post_id=" . $post_id);
            exit();
        } else {
            echo "Error liking post: " . $conn->error;
        }
    }
}

// Get like count
$like_query = $conn->prepare("SELECT COUNT(*) as like_count FROM single_likes WHERE post_id = ?");
$like_query->bind_param("i", $post_id);
$like_query->execute();
$like_count = $like_query->get_result()->fetch_assoc()['like_count'];

// Handle comment submission
if (isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $insert = $conn->prepare("INSERT INTO single_comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $post_id, $_SESSION['user_id'], $comment);
        if ($insert->execute()) {
            header("Location: view_post.php?post_id=" . $post_id);
            exit();
        } else {
            echo "Error adding comment: " . $conn->error;
        }
    }
}

// Handle reply submission
if (isset($_POST['reply']) && isset($_POST['comment_id']) && isset($_SESSION['user_id'])) {
    $reply = trim($_POST['reply']);
    $comment_id = intval($_POST['comment_id']);
    if (!empty($reply)) {
        $insert = $conn->prepare("INSERT INTO single_comments (post_id, user_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iisi", $post_id, $_SESSION['user_id'], $reply, $comment_id);
        if ($insert->execute()) {
            header("Location: view_post.php?post_id=" . $post_id);
            exit();
        } else {
            echo "Error adding reply: " . $conn->error;
        }
    }
}

// Fetch comments
$comments_query = $conn->prepare("SELECT single_comments.*, users.username 
    FROM single_comments 
    JOIN users ON single_comments.user_id = users.id 
    WHERE post_id = ? AND parent_comment_id IS NULL 
    ORDER BY created_at DESC");
$comments_query->bind_param("i", $post_id);
$comments_query->execute();
$comments = $comments_query->get_result();

// Define image_url for metadata
$image_url = !empty($post['media']) && in_array(strtolower(pathinfo($post['media'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']) 
    ? "https://musnvi.42web.io/uploads/" . htmlspecialchars($post['media']) 
    : "https://musnvi.42web.io/default-image.jpg";
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['content']); ?></title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['content']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['content']); ?>">
    <meta property="og:url" content="https://musnvi.42web.io/view_post.php?post_id=<?php echo $post['id']; ?>">
    <?php if (!empty($post['media']) && in_array(pathinfo($post['media'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])): ?>
        <meta property="og:image" content="https://musnvi.42web.io/uploads/<?php echo htmlspecialchars($post['media']); ?>">
    <?php endif; ?>
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Your Community Site">
    
    <!-- Twitter Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars(substr($post['content'], 0, 70)); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    <meta name="twitter:url" content="https://musnvi.42web.io/view_post.php?post_id=<?php echo $post_id; ?>">
    
<style>
        body { font-family: Arial, sans-serif; }
        .community { text-decoration: none; }
        .post-container { margin: 0 auto; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-shadow: 2px 2px 10px rgba(0,0,0,0.1); }
        .post-content { white-space: pre-wrap; font-family: Arial, sans-serif; }
        .post-media { max-width: 90%; border: 1px solid orange; border-radius: 5px; }
        .timestamp { color: gray; font-size: 12px; }
        .username { color: red; text-decoration: none; }
    .post-box {
        width: 90%;
        margin: auto;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        text-align: left;
    }
    .content { font-family: Arial, sans-serif; }
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
    .uname { text-decoration: none; }
    .like-section { margin: 10px 0; }
    .like-btn {
        background: #fff;
        border: 1px solid #ccc;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
    }
    .comment-section { margin-top: 15px; }
    .comment-input, .reply-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    .comment-btn, .reply-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
    }
    .comment {
        margin: 10px 0;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 3px;
    }
    .reply-link {
        color: #007bff;
        cursor: pointer;
        font-size: 0.9em;
        margin-left: 10px;
    }
    .reply-section {
        display: none;
        margin-left: 20px;
        margin-top: 5px;
    }
    .reply {
        margin: 5px 0;
        padding: 5px;
        background: #f0f0f0;
        border-radius: 3px;
    }
    .error { color: red; }
</style>
</head>
<body>
    <div class="post-container">
        <h3>Post by 
            <a href="view_profile.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>" class="username">
                <?php echo htmlspecialchars($post['username']); ?>
            </a>
        </h3>

        <!-- Display Media -->
        <?php if (!empty($post['media'])): ?>
            <?php 
            $file_ext = pathinfo($post['media'], PATHINFO_EXTENSION);
            $media_url = "uploads/" . $post['media'];
            ?>
            <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?php echo $media_url; ?>" alt="Post Image" class="post-media">
            <?php elseif (in_array($file_ext, ['mp4', 'mov', 'avi'])): ?>
                <video controls class="post-media">
                    <source src="<?php echo $media_url; ?>" type="video/<?php echo $file_ext; ?>">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
        <?php endif; ?>
        
        <p class="post-content"><?php echo preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank">$1</a>', htmlspecialchars($post['content'])); ?></p>
        <p class="timestamp">Posted on: <?php echo date('F j, Y, g:i A', strtotime($post['created_at'])); ?></p>

        <!-- Like Section -->
        <div class="like-section">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p class="error">Please login to like this post</p>
            <?php else: ?>
                <form method="POST" action="">
                    <button type="submit" name="like" class="like-btn">Like (<?php echo $like_count; ?>)</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="comment-section">
            <!-- Comments Display -->
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                    <?php echo htmlspecialchars($comment['content']); ?>
                    <span class="reply-link">Reply</span>
                    
                    <div class="reply-section">
                        <!-- Replies Display -->
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
                            <div class="reply">
                                <strong><?php echo htmlspecialchars($reply['username']); ?>:</strong>
                                <?php echo htmlspecialchars($reply['content']); ?>
                            </div>
                        <?php endwhile; ?>

                        <!-- Reply Form -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="">
                                <textarea name="reply" class="reply-input" placeholder="Write a reply..."></textarea>
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="reply-btn">Post Reply</button>
                            </form>
                        <?php else: ?>
                            <p class="error">Please login to reply</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Comment Section -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p class="error">Please login to comment</p>
            <?php else: ?>
                <form method="POST" action="">
                    <textarea name="comment" class="comment-input" placeholder="Write a comment..."></textarea>
                    <button type="submit" class="comment-btn">Post Comment</button>
                </form>
            <?php endif; ?>

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

        <p><a class="community" href="community.php">Back to Community</a></p>
    </div>

    <script>
        document.querySelectorAll('.reply-link').forEach(link => {
            link.addEventListener('click', () => {
                const replySection = link.nextElementSibling;
                replySection.style.display = 
                    replySection.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>