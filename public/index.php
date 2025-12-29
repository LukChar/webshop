<?php
session_start();
require "../includes/nav.php";
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Startseite</title>
</head>
<body>

<h1>Willkommen im Webshop</h1>

<p>
    <?php
    if (isset($_SESSION["user_id"])) {
        echo "Sie sind eingeloggt.";
    } else {
        echo "Sie sind nicht eingeloggt.";
    }
    ?>
</p>

<p>Hier werden später die Produkte angezeigt.</p>

</body>
</html>