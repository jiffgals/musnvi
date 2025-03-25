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
    $name = $conn->real_escape_string(trim($_POST['name']));

    // Insert the new book into the database
    $sql = "INSERT INTO books (name) VALUES ('$name')";

    if ($conn->query($sql) === TRUE) {
        echo "New book added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Optionally, add a link back to the main page or the search page
echo '<br><a href="admin_dashboard.php">Back to Dashboard</a>';
?>

<br/><br/>

<div class="register">
<div class="book-grid">
    <h2>Add a New Book</h2>
    <form method="POST" action="add_book.php">
        <p><label for="name">Book Name:</label>
        <input type="text" name="name" required></p><br/><br/>
        <input type="submit" value="Add Book">
    </form>
</div>
</div>


<?php include 'includes/footer.php'; ?>