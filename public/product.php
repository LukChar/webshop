<?php
session_start();
require "../includes/db.php";
require "../includes/nav.php";

/* Prüfen, ob eine ID übergeben wurde */
if (!isset($_GET["id"])) {
    echo "Kein Produkt ausgewählt.";
    exit;
}

$id = (int) $_GET["id"];

/* Produkt aus der Datenbank laden */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Produkt nicht gefunden.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product["name"]); ?></title>
</head>
<body>

<h1><?php echo htmlspecialchars($product["name"]); ?></h1>

<p><?php echo htmlspecialchars($product["description"]); ?></p>

<p>
    <strong>
        Preis:
        <?php echo number_format($product["price"], 2, ",", "."); ?> €
    </strong>
</p>

<p>
    <a href="index.php">Zurück zur Übersicht</a>
</p>

</body>
</html>