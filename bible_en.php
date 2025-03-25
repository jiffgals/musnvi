<?php
$shareUrl = "https://shrinkme.ink/musnvi";
$encodedShareUrl = urlencode($shareUrl);
$shareText = "Read {$books[$selectedBook]} Chapter {$selectedChapter} online!"; // updated
$encodedShareText = urlencode($shareText);
?>

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
    $highlightedText = preg_replace("/(" . preg_quote($searchQuery, '/') . ")/i", "<mark>$1</mark>", $verseText); // this line $highlishtedText is just added on update
} else {
    $highlightedText = $verseText; // also this line added on update to highlight search query result
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
    <meta property="og:title" content="Read <?= $books[$selectedBook] ?> Chapter <?= $selectedChapter ?> Online"> <!--These 3 lines from description are just added on update-->
    <title>Bible Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; }
        a { background: #ccc; padding: 2px 4px 2px 4px; font-size: 8px; border-radius: 5px; text-decoration: none; color: red; float: right; }
        .verse { margin-top: 8px; }
        .container { max-width: 100%; margin: auto; text-align: left; padding: 2px 10px 2px 10px; }
        .footer-links { margin-top: 20px; }
        .footer-links a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            background-color: blue;
            border-radius: 5px;
        }
        }
        .share-buttons {
            margin-top: 10px;
        }
        .share-buttons a {
            /*display: inline-block;*/
            height: 18px;
            margin: 4px 2px 4px 2px;
            padding: 2px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }
        .fb-share { background-color: #1877F2; }
        .tw-share { background-color: #1DA1F2; }
        .wa-share { background-color: #25D366; }
    </style>
    <script>
        function updateChapters() {
            var bookId = document.getElementById("book").value;
            window.location.href = "?book=" + bookId + "&chapter=1";
        }
    </script> <!--updated-->

    <script>
        function changeChapter() {
    let book = document.getElementById('book').value;
    let chapter = document.getElementById('chapter').value;
    loadContent('bible_en.php?book=' + book + '&chapter=' + chapter);
}
    </script>

    <script>
        function handleSearch(event) {
    if (event.key === "Enter") {  // Trigger search on "Enter" key
        event.preventDefault();
        let searchQuery = document.getElementById("search").value.trim();
        let book = document.getElementById("book").value;
        let chapter = document.getElementById("chapter").value;
        if (searchQuery) {
            loadContent(`bible_en.php?book=${book}&chapter=${chapter}&search=${encodeURIComponent(searchQuery)}`);
        }
    }
}
    </script>

    <script>
        function searchBible() {
    let searchQuery = document.getElementById('search').value;
    let book = document.getElementById('book') ? document.getElementById('book').value : "";
    let chapter = document.getElementById('chapter') ? document.getElementById('chapter').value : "";

    let url = `bible_en.php?book=${book}&chapter=${chapter}&search=${encodeURIComponent(searchQuery)}`;
    
    if (window.loadContent) {
        loadContent(url);  // Call loadContent from test.php
    } else {
        window.location.href = url; // If not inside test.php, just navigate normally
    }
}
    </script>

    <script>
        function handleSearchEnter(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevents default form submission
        searchBible(); // Calls the search function
    }
}
    </script>

</head>
<body>

<div class="container">
    <h2>Bible Viewer</h2>

    <!-- Book & Chapter Selection -->
    <form method="GET">
        <label for="book">Book:</label>
        <select name="book" id="book" onchange="changeChapter()">
            <?php
            foreach ($books as $id => $name) {
                echo "<option value='$id' " . ($selectedBook == $id ? "selected" : "") . ">$name</option>";
            }
            ?>
        </select>

        <label for="chapter">Chapter:</label>
        <select name="chapter" id="chapter" onchange="changeChapter()">
<!--updated-->
            <?php
            for ($i = 1; $i <= $chaptersCount; $i++) {
                echo "<option value='$i' " . ($selectedChapter == $i ? "selected" : "") . ">$i</option>";
            }
            ?>
        </select>

        <!-- Search -->
        <label for="search">Search:</label>
        <input type="text" id="search" placeholder="Enter keyword" onkeypress="handleSearchEnter(event)">
<button type="button" onclick="searchBible()">Go</button>
        
    </form>


    <div class="share-buttons">
<!-- Facebook -->
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedShareUrl ?>" 
       target="_blank" class="fb-share">Facebook</a>

    <!-- Twitter -->
    <a href="https://twitter.com/intent/tweet?text=<?= urlencode("Read {$books[$selectedBook]} Chapter {$selectedChapter} online!") ?>&url=<?= $encodedShareUrl ?>" 
   target="_blank" class="tw-share">Twitter</a>

    <!-- WhatsApp -->
    <a href="https://api.whatsapp.com/send?text=<?= $encodedShareText ?>%20<?= $encodedShareUrl ?>" 
       target="_blank" class="wa-share">WhatsApp</a>

    </div>


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

<div class="pagination">
    <?php if (!$isLoggedIn): ?>
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        <?php else: ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php" style="background-color: red;">Logout</a>
        <?php endif; ?>
        <?php if ($isLoggedIn) {
            echo "<a href='save_note.php?verse_reference=" . urlencode($verseReference) . "&verse_text=" . urlencode($verseText) . "' class='save-link'>Save</a>"; } /*this line is optional since both dashboard and save_note is the same page*/
    ?>
</div>

</body>
</html>
