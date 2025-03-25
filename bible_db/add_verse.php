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
    $book = $conn->real_escape_string(trim($_POST['book']));
    $chapter = (int)$_POST['chapter']; // Ensure it's an integer
    $verse = (int)$_POST['verse'];
    $text = $conn->real_escape_string(trim($_POST['text']));

    // Insert the new verse into the database
    $sql = "INSERT INTO verses (chapter_id, text) 
            VALUES ((SELECT id FROM chapters WHERE chapter_number = $chapter AND book_id = (SELECT id FROM books WHERE name = '$book')), '$text')";

    if ($conn->query($sql) === TRUE) {
        echo "New verse added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Optionally, add a link back to the main page or the search page
echo '<br><a href="admin_dashboard.php">Back to Dashboard</a>';
?>


<div class="register">
<div class="book-grid">
    <h2>Add a New Verse</h2>
    <form method="POST" action="add_verse.php">
        <p><label for="book">Book:</label><br/>
        <input type="text" name="book" required></p>

        <p><label for="chapter">Chapter Number:</label><br/>
        <input type="number" name="chapter" required></p>

        <p><label for="verse">Verse Number:</label><br/>
        <input type="number" name="verse" required></p>

        <p><label for="text">Text:</label><br/>
        <textarea name="text" rows="4" required></textarea></p><br/><br/>

        <input type="submit" value="Add Verse">
    </form>
</div>
</div>

<?php include 'includes/footer.php'; ?>