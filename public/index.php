<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Startseite</title>
</head>
<body>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION["user_id"])) {
    echo "Status: Eingeloggt<br>";
    echo '<a href="../auth/logout.php">Logout</a>';
} else {
    echo "Status: Ausgeloggt<br>";
    echo '<a href="../auth/login.php">Login</a>';
}
?>

</body>
</html>