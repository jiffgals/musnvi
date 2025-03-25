<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
} // This will start a session of a user once logged! You need to attach this codes to every pages you don't want to be access publicly
?>

<?php include 'includes/header.php'; ?>

<?php
include 'db/database.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $bookName = $conn->real_escape_string(trim($_POST['book']));
    $chapterNumber = (int)$_POST['chapter_number'];

    // Get the book ID based on the book name
    $bookIdQuery = "SELECT id FROM books WHERE name = '$bookName'";
    $bookIdResult = $conn->query($bookIdQuery);

    if ($bookIdResult->num_rows > 0) {
        $bookIdRow = $bookIdResult->fetch_assoc();
        $bookId = $bookIdRow['id'];

        // Insert the new chapter into the database
        $sql = "INSERT INTO chapters (book_id, chapter_number) VALUES ($bookId, $chapterNumber)";

        if ($conn->query($sql) === TRUE) {
            echo "New chapter added successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Book not found. Please check the book name.";
    }
}

// Optionally, add a link back to the main page or the search page
echo '<br><a href="admin_dashboard.php">Back to Dashboard</a>';
?>


<div class="register">
<div class="book-grid">
    <h2>Add a New Chapter</h2>
    <form method="POST" action="add_chapter.php">
        <p><label for="book">Book Name:</label>
        <input type="text" name="book" required></p>

        <p><label for="chapter_number">Chapter Number:</label>
        <input type="number" name="chapter_number" required></p><br/><br/>

        <input type="submit" value="Add Chapter">
    </form>
</div>
</div>


<?php include 'includes/footer.php'; ?>