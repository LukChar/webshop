<?php
/* Fehleranzeige NUR für Entwicklung */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../includes/db.php";

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    echo "Fehler: Sie müssen eingeloggt sein, um eine Bestellung abzuschließen.";
    exit;
}

/* Warenkorb prüfen */
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    echo "Fehler: Ihr Warenkorb ist leer.";
    exit;
}

$userId = $_SESSION["user_id"];
$total = 0;

/* Gesamtbetrag berechnen */
foreach ($_SESSION["cart"] as $productId => $quantity) {

    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo "Fehler: Produkt mit ID $productId nicht gefunden.";
        exit;
    }

    $total += $product["price"] * $quantity;
}

/* Bestellung speichern */
$stmt = $pdo->prepare(
    "INSERT INTO orders (user_id, total) VALUES (?, ?)"
);
$stmt->execute([$userId, $total]);

$orderId = $pdo->lastInsertId();

if (!$orderId) {
    echo "Fehler: Bestellung konnte nicht gespeichert werden.";
    exit;
}

/* Bestellpositionen speichern */
foreach ($_SESSION["cart"] as $productId => $quantity) {

    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo "Fehler bei Bestellposition.";
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([
        $orderId,
        $productId,
        $quantity,
        $product["price"]
    ]);
}

/* Warenkorb leeren */
unset($_SESSION["cart"]);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bestellung abgeschlossen</title>
</head>
<body>

<h1>Bestellung erfolgreich gespeichert</h1>

<p>Vielen Dank für Ihre Bestellung.</p>

<p>
    <a href="index.php">Zurück zur Startseite</a>
</p>

</body>
</html>