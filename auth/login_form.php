<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h1>Login</h1>

<?php
if (isset($message) && $message !== "") {
    echo "<p>$message</p>";
}
?>

<form method="post" action="login.php">
    <label>E-Mail:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Passwort:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>