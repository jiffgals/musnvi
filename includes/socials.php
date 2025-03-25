<?php
$shareUrl = "https://shrinkme.ink/musnvi";
$encodedShareUrl = urlencode($shareUrl);
$shareText = "Read {$books[$selectedBook]} Chapter {$selectedChapter} online! ($shareText)"; 
$encodedShareText = urlencode($shareText);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<style> 
    .share-buttons { margin: 3px 0 0 0; }
    .share-buttons a { height: 18px; padding: 2px 6px; text-decoration: none; color: white; border-radius: 5px; font-size: 14px; }
    .fb-share { background-color: #1877F2; }
    .tw-share { background-color: #1DA1F2; }
    .wa-share { background-color: #25D366; }
</style>

</head>
 
    <div class="share-buttons">
    <!-- Facebook -->
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedShareUrl ?>" target="_blank" class="fb-share">Facebook</a> 
    </div>
    
    <div class="share-buttons">
    <!-- Twitter -->
    <a href="https://twitter.com/intent/tweet?text=<?= urlencode("Read {$books[$selectedBook]} Chapter {$selectedChapter} online!") ?>&url=<?= $encodedShareUrl ?>" target="_blank" class="tw-share">Twitter</a>
    </div>

    <div class="share-buttons">
    <!-- WhatsApp -->
    <a href="https://api.whatsapp.com/send?text=<?= $encodedShareText ?>%20<?= $encodedShareUrl ?>" target="_blank" class="wa-share">WhatsApp</a>
    </div>

</body>
</html>

