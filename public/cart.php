<?php
session_start();
require "../includes/db.php";
require "../includes/nav.php";

/* Warenkorb aus der Session holen */
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    $cart = [];
} else {
    $cart = $_SESSION["cart"];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Warenkorb</title>
</head>
<body>

<h1>Warenkorb</h1>

<?php if (empty($cart)): ?>

    <p>Ihr Warenkorb ist leer.</p>

<?php else: ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Produkt</th>
            <th>Menge</th>
            <th>Preis</th>
            <th>Zwischensumme</th>
            <th>Aktion</th>
        </tr>

        <?php
        $total = 0;

        foreach ($cart as $productId => $quantity):

            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                continue;
            }

            $subtotal = $product["price"] * $quantity;
            $total += $subtotal;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($product["name"]); ?></td>

                <td>
                    <a href="cart_update.php?id=<?php echo $productId; ?>&action=minus">−</a>
                    <?php echo $quantity; ?>
                    <a href="cart_update.php?id=<?php echo $productId; ?>&action=plus">+</a>
                </td>

                <td><?php echo number_format($product["price"], 2, ",", "."); ?> €</td>

                <td><?php echo number_format($subtotal, 2, ",", "."); ?> €</td>

                <td>
                    <a href="cart_remove.php?id=<?php echo $productId; ?>">
                        Entfernen
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="3"><strong>Gesamt</strong></td>
            <td colspan="2">
                <strong><?php echo number_format($total, 2, ",", "."); ?> €</strong>
            </td>
        </tr>
    </table>

    <p>
        <a href="checkout.php">Bestellung abschließen</a>
    </p>

<?php endif; ?>

<p>
    <a href="index.php">Weiter einkaufen</a>
</p>

</body>
</html>