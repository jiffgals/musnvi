<?php
    $shareUrl = "https://shrinkme.ink/musnvi";
    $encodedShareUrl = urlencode($shareUrl);
    $shareText = "Read {$books[$selectedBook]} Chapter {$selectedChapter} online! ($shareText)"; 
    $encodedShareText = urlencode($shareText);
?> <!-- This will trigger content to share on social platforms - facebook, twitter, whatsapp -->

<?php include 'includes/header.php'; ?> <!-- This includes header content, database -->

<?php
    session_start();
    include 'db/database.php';

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']); 

    // Book ID to Name Mapping
    $books = [
        1 => "Genesis", 2 => "Exodus", 3 => "Leviticus", 4 => "Numbers", 5 => "Deuteronomy",
        6 => "Joshua", 7 => "Judges", 8 => "Ruth", 9 => "1 Samuel", 10 => "2 Samuel",
        11 => "1 Kings", 12 => "2 Kings", 13 => "1 Chronicles", 14 => "2 Chronicles",
        15 => "Ezra", 16 => "Nehemiah", 17 => "Esther", 18 => "Job", 19 => "Psalms",
        20 => "Proverbs", 21 => "Ecclesiastes", 22 => "Song of Solomon", 23 => "Isaiah",
        24 => "Jeremiah", 25 => "Lamentations", 26 => "Ezekiel", 27 => "Daniel",
        28 => "Hosea", 29 => "Joel", 30 => "Amos", 31 => "Obadiah", 32 => "Jonah",
        33 => "Micah", 34 => "Nahum", 35 => "Habakkuk", 36 => "Zephaniah", 37 => "Haggai",
        38 => "Zechariah", 39 => "Malachi", 40 => "Matthew", 41 => "Mark", 42 => "Luke",
        43 => "John", 44 => "Acts", 45 => "Romans", 46 => "1 Corinthians", 47 => "2 Corinthians",
        48 => "Galatians", 49 => "Ephesians", 50 => "Philippians", 51 => "Colossians",
        52 => "1 Thessalonians", 53 => "2 Thessalonians", 54 => "1 Timothy", 55 => "2 Timothy",
        56 => "Titus", 57 => "Philemon", 58 => "Hebrews", 59 => "James", 60 => "1 Peter",
        61 => "2 Peter", 62 => "1 John", 63 => "2 John", 64 => "3 John", 65 => "Jude", 66 => "Revelation"
    ];

    // Get selected book, chapter, and search query
    $selectedBook = isset($_GET['book']) ? intval($_GET['book']) : 1;
    $selectedChapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";

    // Get chapters count for selected book
    $chaptersCount = 1;
    if ($selectedBook > 0) {
        $chapterQuery = $conn->query("SELECT MAX(chapter) as max_chapter FROM bible_en_verses WHERE book = $selectedBook");
        if ($chapterRow = $chapterQuery->fetch_assoc()) {
            $chaptersCount = $chapterRow['max_chapter'];
        }
    }

    // Query to fetch verses
    if (!empty($searchQuery)) {
        $sql = "SELECT book, chapter, verse, words FROM bible_en_verses WHERE words LIKE '%" . $conn->real_escape_string($searchQuery) . "%' ORDER BY book, chapter, verse";
        } else {
            $sql = "SELECT book, chapter, verse, words FROM bible_en_verses WHERE book = $selectedBook AND chapter = $selectedChapter ORDER BY verse";
        }

        $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read the Bible online - <?= $books[$selectedBook] ?> Chapter <?= $selectedChapter ?>">
    <meta name="keywords" content="Bible, Scriptures, <?= $books[$selectedBook] ?>, Chapter <?= $selectedChapter ?>">
    <meta property="og:title" content="Read <?= $books[$selectedBook] ?> Chapter <?= $selectedChapter ?> Online"> <!-- these 3 lines from description til here used for SEO Optimization (Meta Tags)-->

    <script src="musjs/sidebar.js"></script>
    <script src="musjs/jumpxxx.js"></script>
    <script src="musjs/filterxxx_book.js"></script>
    <script src="musjs/updatexxx_chapter.js"></script>
    
    <title>Musnvi</title>

<style>
    body { font-family: Arial, sans-serif; }
    .container { border: 0.5px solid #eee; border-radius: 5px; justify-items: left; text-align: left; max-width: 100%; margin: 0; padding: 1px 8px 4px 8px; box-shadow: 1px 0px 2px 1px #ddd /*rgb(255,124,0,0.3)*/; }
    a { font-color: red; font-size: 10px; }
    .verse { margin-bottom: 10px; }
    select, input[type="text"] { background: white; color: black; border: 0.5px solid orange; border-radius: 5px; padding: 5px; margin: 2px; box-shadow: 1px 0px 1px 1px rgb(255,124,0,0.3); }
    .save-link { color: blue; text-decoration: none; }
    input, input[type=text] { max-width: auto; background-color: white; color: black; border: 1px solid orange; border-radius: 5px; margin: 8px 0 5px 0; box-shadow: 1px 0px 1px 1px rgb(255,124,0,0.3); }
    .pagination { max-width: 100%; color: white; position: sticky; bottom: 126px; margin: 3px 6px 0 0; float: right; }
    .notesticky { max-width: 96.8%; color: red; position: sticky; bottom: 0px; border-radius: 4px; }
    label { color: #000; }
    textarea { width: 100%; background: white; color: red; border: 0.5px solid orange; border-radius: 4px; padding: 10px 5px; height: auto; box-shadow: 1px 0px 1px 1px rgb(255,124,0,0.3); resize: none; }
    /*button { background: yellow; border: 1px solid orange; border-radius: 4px; padding: 5px 4px; box-shadow: 1px 0px 1px 1px rgb(255,124,0,0.3); } 
    .menubutton { max-width: auto; background: white; color: black; border: 1px solid #ccc; border-radius: 5px; margin: 3px 0 0 0; box-shadow: 4px 1px 6px rgb(255,0,0,0.3); }*/
    /*.sbar-container { background: transparent; border: 1px solid blueviolet; border-radius: 5px; color: red; position: sticky; top: 0; margin-top: 40px; margin-right: -1px; max-height: 100%; padding: 10px 4px; }
    .sbar2-container { border: 1px solid blueviolet; border-radius: 5px; margin-top: 5px; max-height: 100%; padding: 3px; padding-top: 10px; padding-bottom: 670px; }
    .hamburger { font-size: 24px; background: transparent; color: white; border: none; cursor: pointer; position: fixed; left: 15px; top: 0px; padding: 0; margin-left: -8px; }
    .close-btn { float: right; }
    .sidebar { position: fixed; left: -300px; top: 0; width: 200px; height: auto; background: white; padding: 4px; transition: left 0.2s ease; box-shadow: 5px 0 8px rgba(0,0,255,0.5); float: left; }
    .sidebar h2 { color: white; text-align: left; }*/
    /*.iframe { background: white; border: 1px solid yellow; border-radius: 5px; margin: 0 2px 2px 0; box-shadow: 4px 1px 6px rgb(255,0,0,0.3); }
    .emb { background: #000; color: yellow; padding: 2px 5px; border-radius: 4px; text-align: center; margin-bottom: 5px; }*/
    /*.tb-share { background: yellow; border: 1px solid yellow; border-radius: 100px; padding: 10px 4px; box-shadow: 5px 0 8px rgba(0,0,255,0.5); }
    video { border-radius: 4px; }
    .chatshad { filter: drop-shadow(2px 2px 10px black); font-size: 13px; margin: 10px 0 0 1px; }*/
</style>

<script>
    function updateChapters() {
        var bookId = document.getElementById("book").value;
        window.location.href = "?book=" + bookId;
    }
</script>

</head>
<body>

<div id="first"></div>

<!--From here executing a sidebar menu in a hamburge button-->
<!-- <button class="hamburger" onclick="toggleSidebar()">
        <div class="chatshad"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"> Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.<path fill="#fff" d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
</button>

<div id="sidebar" class="sidebar">
    <button class="close-btn" onclick="toggleSidebar()">✖</button>

<div class="sbar-container">

<div class="emb">Facebook Pinned Post</div> 
<div class="iframe"><iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fweb.facebook.com%2F100090643575210%2Fvideos%2F1251753092512939%2F&show_text=false&width=560&t=0" width="100%" height="auto" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe></div>

<div class="iframe"><iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fweb.facebook.com%2F100090643575210%2Fvideos%2F420609837785285%2F&show_text=false&width=560&t=0" width="100%" height="auto" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe></div>
</div>

<div class="sbar2-container">
    <?php if (!$isLoggedIn): ?>
            <div class="menubutton"><button onclick="location.href='register.php'">Register</button></div>
            <div class="menubutton"><button onclick="location.href='login.php'">Login</button></div>
            <div class="menubutton"><button onclick="location.href='admin_login.php'">Admin</button></div>
        <?php else: ?>
            <div class="menubutton"><button onclick="location.href='dashboard.php'">Dashboard</button></div>
            <div class="menubutton"><button onclick="location.href='edit_profile.php'">Edit Profile</button></div>
            <div class="menubutton"><button onclick="location.href='chat.php#type'">Chat</button></div>
            <div class="menubutton"><button onclick="location.href='ulogout.php'">Logout</button></div>
        <?php endif; ?>

        
</div>

</div> --> <!--Til here-->


<div class="container">
    <h3>Bible Viewer</h3>

    <!-- Book & Chapter Selection -->
    <form method="GET">
        <label for="book">Book:</label>
        <select name="book" id="book" onchange="updateChapters()">
            <?php
            foreach ($books as $id => $name) {
                echo "<option value='$id' " . ($selectedBook == $id ? "selected" : "") . ">$name</option>";
            }
            ?>
        </select>

        <label for="chapter">Chapter:</label>
        <select name="chapter" id="chapter" onchange="window.location.href='?book='+document.getElementById('book').value+'&chapter='+this.value;">
            <?php
            for ($i = 1; $i <= $chaptersCount; $i++) {
                echo "<option value='$i' " . ($selectedChapter == $i ? "selected" : "") . ">$i</option>";
            }
            ?>
        </select>

        <!-- Search -->
        <label for="search">Search:</label>
        <input type="text" name="search" id="search" placeholder="Enter keyword" value="<?php echo htmlspecialchars($searchQuery); ?>">
        
        <button type="submit">Filter</button>
    </form>

    <!-- Display Bible Verses -->
    <?php
    if ($result->num_rows > 0) {
        if (!empty($searchQuery)) {
            echo "<h3>Search Results for: <em>" . htmlspecialchars($searchQuery) . "</em></h3>";
        } else {
            echo "<h3>{$books[$selectedBook]} Chapter $selectedChapter</h3>";
        }
        
        while ($row = $result->fetch_assoc()) {
            $bookName = $books[$row['book']];
            $verseReference = "$bookName {$row['chapter']}:{$row['verse']}";/*originally: $bookName {$row['chapter']}:{$row['verse']}*/
            $verseText = htmlspecialchars($row['words']);
            
        // Highlight search term
        if (!empty($searchQuery)) {
            $highlightedText = preg_replace("/(" . preg_quote($searchQuery, '/') . ")/i", "<mark>$1</mark>", $verseText);
        } else {
            $highlightedText = $verseText;
        }

            echo "<div class='verse'><strong>$verseReference</strong> $highlightedText";
        if ($isLoggedIn) {
            echo " <a href='save_note.php?verse_reference=" . urlencode($verseReference) . "&verse_text=" . urlencode($verseText) . "' class='save-link'>Save</a>";
        }
            echo "</div>";
        }
    } else {
            echo "<p>No results found.</p>";
    }
    ?>
</div>

<div class="notesticky">
    <form method="POST" action="save_note.php">
        <input type="text" name="verse_reference" value="Title...<?php echo "{$row['book']} {$row['chapter_number']}{$row['verse_number']}"; ?>">
        <textarea name="note_text" placeholder="Write your note here..." required></textarea>
        <button type="submit">Save Note</button>
    </form> <!--This is a form to save the notes-->
</div>

<div id="last"></div>

</body>
</html>

<?php include 'footer.php'; ?>
