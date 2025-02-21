<?php include 'db/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Musnvi Bible Concordance</title>

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
            border: 1px solid green;
            max-width: 100%;
            margin: relative;
            margin-top: -10px;
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
            margin-left: 4px;
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
            margin-right: 4px;
            color: black;
            padding: 4px;
        }

    </style>
</head>
<body>
    <h1>Musnvi - Bible Concordance</h1>

    <div class="search-container">
        <form method="GET" action="meaning.php">
            <input type="text" name="search" placeholder="Search verses..." required>
            <input type="submit" value="Search">
        </form>
    </div>

    <div id="details">
        <?php
        // Initialize variables for pagination and search
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; // Number of verses to display per page
        $offset = ($currentPage - 1) * $perPage; // Calculate offset
        
        // Prepare search query if search term is given
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        $sql = "SELECT m.name AS meaning, w.word_number, d.detail_number, d.text
                FROM details d
                JOIN words w ON d.word_id = w.id
                JOIN meanings m ON w.meaning_id = m.id";
        
        if ($searchTerm) {
            $sql .= " WHERE d.text LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
        }
        
        // Get total number of verses after search filter
        $totalQuery = "SELECT COUNT(*) as total FROM details d
                       JOIN words w ON d.word_id = w.id
                       JOIN meanings m ON w.meaning_id = m.id" . ($searchTerm ? " WHERE d.text LIKE '%" . $conn->real_escape_string($searchTerm) . "%'" : "");
        
        $totalResult = $conn->query($totalQuery);
        $totalRow = $totalResult->fetch_assoc();
        $totalVerses = $totalRow['total'];
        $totalPages = ceil($totalVerses / $perPage); // Calculate total pages
        
        // Fetch verses for current page with search term
        $sql .= " LIMIT $offset, $perPage";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($searchTerm) {
                    $highlightedText = str_ireplace($searchTerm, "<span class='highlight'>{$searchTerm}</span>", $row['text']);
                } else {
                    $highlightedText = $row['text'];
                }
        
                echo "<p><strong>{$row['meaning']}</strong> {$highlightedText}<br/></p>";
            } // Adding from line - if searchTerm til here will highlight the search to query yellow
        } else {
            echo "No verses found.";
        }
        ?>

        <div class="pagination"><button onclick="location.href='index.php'">Back to Bible</button></div>

        <div class="pagination">
            <button onclick="location.href='meaning.php?page=<?php echo max(1, $currentPage - 1); ?>'">Prev</button>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <button onclick="location.href='meaning.php?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>'"><?php echo $i; ?></button>
            <?php endfor; ?>
            <button onclick="location.href='meaning.php?page=<?php echo min($totalPages, $currentPage + 1); ?>&search=<?php echo urlencode($searchTerm); ?>'">Next</button>
        </div>
    </div>

    

    <script src="js/scripts.js"></script>
    <script>
        // Placeholder for any future JavaScript functionality
        console.log('Bible Database Loaded');
    </script>
</body>
</html>