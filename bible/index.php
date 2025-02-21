<?php include 'db/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Musnvi Bible Hub</title>

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: relative;
            padding: 4px 10px 4px 10px;
            border: solid 1px blue;
        }

        p {
            border: 1px solid transparent;
            max-width: 100%;
            margin: relative;
            padding: 20px;
        }

        .highlight {
            background-color: yellow; /* Change to any color you prefer */
            font-weight: bold;
        }

        .highlight a {
            color: red; /* Color for the link */
            text-decoration: none; /* Remove underline */
        }

        .highlight a:hover {
            text-decoration: underline; /* Underline on hover */
        }

        .search-container {
            border: 1px solid transparent;
            text-align: relative;
            margin: 0px;
            margin-top: -8px;
            margin-right: -1px;
            padding: 0px 0px 0px 4px;
            float: right;
        }

        .pagination {
            border: 1px solid blue;
            text-align: relative;
            margin: 0px;
            margin-top: -7px;
            padding: 2px;
            float: right;
        }

        button {
            background: yellow;
            color: blue;
            border: 1px solid orange;
        }

        input {
            background: yellow;
            border: solid 1px blue;
            color: black;
            padding: 4px;
        }

    </style>
</head>
<body>
    <h1>Musnvi - Bible Hub</h1>

    <div class="search-container">
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search verses..." required>
            <input type="submit" value="Search">
        </form>
    </div>

    <div id="verses">
        <?php
        // Initialize variables for pagination and search
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 1; // Number of verses to display per page
        $offset = ($currentPage - 1) * $perPage; // Calculate offset
        
        // Prepare search query if search term is given
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        $sql = "SELECT b.name AS book, c.chapter_number, v.verse_number, v.to, v.text
                FROM verses v
                JOIN chapters c ON v.chapter_id = c.id
                JOIN books b ON c.book_id = b.id";
        
        if ($searchTerm) {
            $sql .= " WHERE v.text LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
        }
        
        // Get total number of verses after search filter
        $totalQuery = "SELECT COUNT(*) as total FROM verses v
                       JOIN chapters c ON v.chapter_id = c.id
                       JOIN books b ON c.book_id = b.id" . ($searchTerm ? " WHERE v.text LIKE '%" . $conn->real_escape_string($searchTerm) . "%'" : "");
        
        $totalResult = $conn->query($totalQuery);
        $totalRow = $totalResult->fetch_assoc();
        $totalVerses = $totalRow['total'];
        $totalPages = ceil($totalVerses / $perPage); // Calculate total pages
        
        // Fetch verses for current page with search term
        $sql .= " LIMIT $offset, $perPage";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        // Highlight the searched term in the text and create a link for it
        if ($searchTerm) {
            // Encode the search term for the URL
            $encodedTerm = urlencode($searchTerm);
            // Wrap the searched term with a link
            $highlightedText = str_ireplace(
                $searchTerm,
                "<a href='meaning.php?search={$encodedTerm}' class='highlight'>{$searchTerm}</a>",
                $row['text']
            );
        } else {
            $highlightedText = $row['text'];
        }

                echo "<p><strong>{$row['book']} {$row['chapter_number']}:{$row['verse_number']}-{$row['to']}</strong> <br> {$highlightedText}</p>";
            } // Adding from line - if searchTerm til here will highlight the search to query yellow
        } else {
            echo "No verses found.";
        }
        ?>

        <div class="pagination">
            <button onclick="location.href='index.php?page=<?php echo max(1, $currentPage - 1); ?>'">Prev</button>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <button onclick="location.href='index.php?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>'"><?php echo $i; ?></button>
            <?php endfor; ?>
            <button onclick="location.href='index.php?page=<?php echo min($totalPages, $currentPage + 1); ?>&search=<?php echo urlencode($searchTerm); ?>'">Next</button>
        </div>
    </div>

    <script src="js/scripts.js"></script>
    <script>
        // Placeholder for any future JavaScript functionality
        console.log('Bible Database Loaded');
    </script>
</body>
</html>