<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrierung</title>
</head>
<body>

<h1>Registrieren</h1>

<?php
if ($message !== "") {
    echo "<p>$message</p>";
}
?>

<form method="post" action="register.php">
    <label>E-Mail:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Passwort:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Registrieren</button>
</form>

</body>
</html>