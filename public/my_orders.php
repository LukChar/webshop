<?php
session_start();
require "../includes/db.php";
require "../includes/nav.php";

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

/* Bestellungen des Users laden */
$stmt = $pdo->prepare("
    SELECT id, total, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Bestellungen</title>
</head>
<body>

<h1>Meine Bestellungen</h1>

<?php if (empty($orders)): ?>

    <p>Sie haben noch keine Bestellungen.</p>

<?php else: ?>

    <?php foreach ($orders as $order): ?>

        <div style="border:1px solid #000; padding:10px; margin-bottom:15px;">

            <strong>Bestellung #<?php echo $order["id"]; ?></strong><br>
            Datum: <?php echo $order["created_at"]; ?><br>
            Gesamt: <?php echo number_format($order["total"], 2, ",", "."); ?> €

            <h4>Positionen:</h4>

            <ul>
                <?php
                $stmtItems = $pdo->prepare("
                    SELECT order_items.quantity, order_items.price, products.name
                    FROM order_items
                    JOIN products ON order_items.product_id = products.id
                    WHERE order_items.order_id = ?
                ");
                $stmtItems->execute([$order["id"]]);
                $items = $stmtItems->fetchAll();

                foreach ($items as $item):
                ?>
                    <li>
                        <?php echo htmlspecialchars($item["name"]); ?> –
                        <?php echo $item["quantity"]; ?> ×
                        <?php echo number_format($item["price"], 2, ",", "."); ?> €
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<p>
    <a href="index.php">Zurück zur Startseite</a>
</p>

</body>
</html>