<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
} // This will start a session of a user once logged! You need to attach this codes to every pages you don't want to be access publicly
?>

<?php include 'includes/header.php'; ?>

    <h2>Welcome, <?php echo $_SESSION['admin_username']; ?>!</h2>
    <p><a href="logout.php">Logout</a></p> <br/><br/>

<div class="register">
<div class="book-grid">
    <h3>Manage Content</h3>
    
        <p><a href="add_book.php">Upload Books</a></p>
        <p><a href="add_chapter.php">Upload Chapters</a></p>
        <p><a href="add_verse.php">Upload Verses</a></p>
    
</div>
</div>
    

<?php include 'includes/footer.php'; ?>