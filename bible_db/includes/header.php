<?php
session_start();
include 'db/database.php';

// Debugging: Check if session is working
// Uncomment this to check if user_id is set
// echo "User ID: " . ($_SESSION['user_id'] ?? "Not logged in");

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read the Bible online - <?= $books[$selectedBook] ?> Chapter <?= $selectedChapter ?>">
    <meta name="keywords" content="Bible, Scriptures, <?= $books[$selectedBook] ?>, Chapter <?= $selectedChapter ?>">
    <meta property="og:title" content="Read <?= $books[$selectedBook] ?> Chapter <?= $selectedChapter ?> Online"> <!-- these 3 lines from description til here used for SEO Optimization (Meta Tags)-->

    <link rel="stylesheet" href="css/style.css">
    <title>Musnvi Bible Resource</title>

    <style>
        h1 { background: #ff914d; text-align: relative; position: sticky; top: 0; padding: 10px 0px 0px 26px; border: solid 1px orange; margin-top: -10px; margin-left: -10px; margin-right: -8px; }
        hd { background: #ff914d; text-align: relative; position: sticky; top: 0; padding: 10px 0px 10px 26px; border: solid 1px orange; margin-top: -10px; margin-left: -12px; margin-right: -70px; } /*this will be the header design at the top of pages*/
        img { margin: 0 0 0 8px; } 
        .comm a { background: transparent; max-width: 100%; diplay: grid; grid-template-columns: repeat(6, 1fr); gap: auto; padding: 15px; justify-items: center; color: black; float: right; position: sticky; top: 0; margin: -64px auto 0 auto; font-size: 15px; text-decoration: none; }
        /*.chatsha { filter: drop-shadow(2px 2px 10px black); }
        .chatsha:hover { transform: scale(1.1); /*translate(-10px, -20px); rotate(30deg); skew(-19deg, -10deg); scale(1.1); matrix(0,1,1,0,0,0);*/ 
        .chatshad { filter: drop-shadow(2px 2px 10px black); font-size: 14px; margin: 16px 0 0 1px; }
        .sbar-container { background: transparent; border: 1px solid blueviolet; border-radius: 5px; color: red; position: sticky; top: 0; margin-top: 40px; margin-right: -1px; max-height: 100%; padding: 10px 4px; }
        .sbar2-container { border: 1px solid blueviolet; border-radius: 5px; margin-top: 5px; max-height: 100%; padding: 3px; padding-top: 10px; padding-bottom: 670px; }
        .hamburger { font-size: 24px; background: transparent; color: white; border: none; cursor: pointer; position: fixed; left: 15px; top: 0px; padding: 0; margin-left: -8px; }
        .hamburger:hover { transform: scale(1.05); }
        .close-btn { float: right; }
        .sidebar { position: fixed; left: -300px; top: 0; width: 200px; height: auto; background: white; padding: 4px; transition: left 0.2s ease; /*box-shadow: 5px 0 8px rgba(0,0,255,0.5);*/ float: left; }
        .sidebar h2 { color: white; text-align: left; }
        button { background: yellow; border: 1px solid orange; border-radius: 4px; padding: 5px 4px; box-shadow: 1px 0px 1px 1px rgb(255,124,0,0.3); } 
        .menubutton { max-width: auto; background: white; color: black; border: 1px solid #ccc; border-radius: 5px; margin: 3px 0 0 0; box-shadow: 4px 1px 6px rgb(255,0,0,0.3); }
        .iframe { background: white; border: 1px solid yellow; border-radius: 5px; margin: 0 2px 2px 0; box-shadow: 4px 1px 6px rgb(255,0,0,0.3); }
        .emb { background: #000; color: yellow; padding: 2px 5px; border-radius: 4px; text-align: center; margin-bottom: 5px; }
        .tb-share { background: yellow; border: 1px solid yellow; border-radius: 100px; padding: 10px 4px; box-shadow: 5px 0 8px rgba(0,0,255,0.5); }
        video { border-radius: 4px; }

        /*for header icons*/
        .cicon { filter: drop-shadow(2px 2px 10px black); }
        .cicon:hover:before { content:"Chat"; }
        .cicon:hover { background-color: transparent; color: white; width: 25px; padding-bottom: 20px; font-size: 10px; }
        .dicon { filter: drop-shadow(2px 2px 10px black); }
        .dicon:hover:before { content:"Dash"; }
        .dicon:hover { background-color: transparent; color: white; width: 25px; padding-bottom: 20px; font-size: 10px; }
        .picon { filter: drop-shadow(2px 2px 10px black); }
        .picon:hover:before { content:"Profile"; }
        .picon:hover { background-color: transparent; color: white; width: 25px; padding-bottom: 20px; font-size: 10px; }
        .ficon { filter: drop-shadow(2px 2px 10px black); }
        .ficon:hover:before { content:"Feeds"; }
        .ficon:hover { background-color: transparent; color: white; width: 25px; padding-bottom: 20px; font-size: 10px; }
        .loicon { filter: drop-shadow(2px 2px 10px black); }
        .loicon:hover:before { content:"Exit"; }
        .loicon:hover { background-color: transparent; color: white; width: 25px; padding-bottom: 20px; font-size: 10px; }

        /*for users posting*/
        .iupost { filter: drop-shadow(2px 2px 10px orange); } 
        .iupost:hover { transform:  rotate(120deg); }





    </style>

</head>
<body>

<h1>
<a href="index.php"><img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEg05dWEaU-mIDdXJATBWl4ZOrYDGITpvuj82mDlog4plsJso4VFZOYLFHM7lRXpTK5Ca-0cLxQ3j8VfGP3zo1QHv4WcB3-FA4KP0LCub-dBrTERpR377J2uYj7B3bWl-QfloTgk7Rw0zUygtK-vZ0v54Z0j6rrSyYcjgIo1Le5LuEcWieK0dx42Obyv64c/s497/Musnvi%20Logo.png" width="100px" height="50px"></a>
    <!-- https://musnvi.42web.io/uploads/67d12ff2a2ebe.png -->

<div class="comm">
    <?php if (!$isLoggedIn): ?>
<a href="login.php"><div class="chatsha"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M217.9 105.9L340.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L217.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1L32 320c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM352 416l64 0c17.7 0 32-14.3 32-32l0-256c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64 0c53 0 96 43 96 96l0 256c0 53-43 96-96 96l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a>

<a href="register.php"><div class="chatsha"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm80 256l64 0c44.2 0 80 35.8 80 80c0 8.8-7.2 16-16 16L80 384c-8.8 0-16-7.2-16-16c0-44.2 35.8-80 80-80zm-32-96a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zm256-32l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a>
    <?php else: ?>
<a href="ulogout.php"><div class="loicon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a>

<a href="community.php"><div class="ficon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M448 96c0-35.3-28.7-64-64-64L64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-320zM256 160c0 17.7-14.3 32-32 32l-96 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l96 0c17.7 0 32 14.3 32 32zm64 64c17.7 0 32 14.3 32 32s-14.3 32-32 32l-192 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l192 0zM192 352c0 17.7-14.3 32-32 32l-32 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l32 0c17.7 0 32 14.3 32 32z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a> 

<a href="edit_profile.php"><div class="picon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304l-91.4 0z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a> 

<a href="dashboard.php"><div class="dicon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zm64 64l0 256 160 0 0-256L64 160zm384 0l-160 0 0 256 160 0 0-256z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a>

<a href="chat.php"><div class="cicon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M512 240c0 114.9-114.6 208-256 208c-37.1 0-72.3-6.4-104.1-17.9c-11.9 8.7-31.3 20.6-54.3 30.6C73.6 471.1 44.7 480 16 480c-6.5 0-12.3-3.9-14.8-9.9c-2.5-6-1.1-12.8 3.4-17.4c0 0 0 0 0 0s0 0 0 0s0 0 0 0c0 0 0 0 0 0l.3-.3c.3-.3 .7-.7 1.3-1.4c1.1-1.2 2.8-3.1 4.9-5.7c4.1-5 9.6-12.4 15.2-21.6c10-16.6 19.5-38.4 21.4-62.9C17.7 326.8 0 285.1 0 240C0 125.1 114.6 32 256 32s256 93.1 256 208z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></a>

    <?php endif; ?> 
</div>
</h1>     

<!--From here executing a sidebar menu in a hamburge button-->
<button class="hamburger" onclick="toggleSidebar()">
    <div class="chatshad">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#fff" d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"/></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>
</button>

<div id="sidebar" class="sidebar">
    <button class="close-btn" onclick="toggleSidebar()">✖</button>

<div class="sbar-container">

<div class="emb">Facebook Pinned Post</div> 
<div class="iframe"><iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fweb.facebook.com%2F100090643575210%2Fvideos%2F1251753092512939%2F&show_text=false&width=560&t=0" width="100%" height="auto" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe></div>

<div class="iframe"><iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Fweb.facebook.com%2F100090643575210%2Fvideos%2F420609837785285%2F&show_text=false&width=560&t=0" width="100%" height="auto" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe></div>
</div>

<div class="sbar2-container">
    <div class="menubutton"><?php include 'includes/socials.php'; ?></div>
</div>

</div> <!--Til here-->

