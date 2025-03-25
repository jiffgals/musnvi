<script src="js/scripts.js"></script>

<script>
    // Placeholder for any future JavaScript functionality
    console.log('Bible Database Loaded');
</script> 

<script>
function toggleSidebar() {
    var sidebar = document.getElementById("sidebar");
    if (sidebar.style.left === "0px") {
        sidebar.style.left = "-250px";
    } else {
        sidebar.style.left = "0px";
    }
} // This script used for sidebar menu function
</script>

<script>
function filterBook(bookId) {
    window.location.href = "index.php?book_id=" + bookId;
} // This script used for filtering BookId in database Books
</script>

<script>
    function jumpToPage() {
        var page = document.getElementById('jumpPage').value;
        var maxPage = <?php echo $totalPages; ?>;
        if (page >= 1 && page <= maxPage) {
            window.location.href = "index.php?page=" + page + "&book_id=<?php echo $selectedBookId; ?>&search=<?php echo urlencode($searchTerm); ?>";
        } else {
            alert("Please enter a valid page number between 1 and " + maxPage);
        }
    }
</script>

</body>
</html>