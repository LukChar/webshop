<?php
session_start();
require_once "../includes/db.php";

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

/* Warenkorb darf nicht leer sein */
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$userId = $_SESSION["user_id"];
$cart = $_SESSION["cart"];
$total = 0;

/* Gesamtbetrag berechnen */
foreach ($cart as $productId => $quantity) {

    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        continue;
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
foreach ($cart as $productId => $quantity) {

    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        continue;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-background-light font-display text-[#111813] flex items-center justify-center min-h-screen">

<div class="bg-white rounded-xl shadow-sm p-8 max-w-md w-full text-center">

    <h1 class="text-2xl font-bold mb-4">Bestellung erfolgreich</h1>

    <p class="text-gray-600 mb-6">
        Vielen Dank für Ihre Bestellung.  
        Ihre Bestellung wurde erfolgreich gespeichert.
    </p>

    <a href="index.php"
       class="inline-block bg-primary text-black font-bold px-6 py-3 rounded-lg hover:bg-green-400 transition-colors">
        Zurück zur Startseite
    </a>

</div>

</body>
</html>