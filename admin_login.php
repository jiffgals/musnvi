<?php
session_start();
include 'db/database.php'; // Adjust the path if needed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="register">
<div class="book-grid">
    <h2>Admin Login</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" action="">
        <p><label>Username:</label><br/>
        <input type="text" name="username" required></p>
        <p><label>Password:</label><br/>
        <input type="password" name="password" required></p><br/><br/>
        <button type="submit">Login</button>
    </form>
</div>
</div>

 <?php include 'includes/footer.php'; ?>

</body>
</html>