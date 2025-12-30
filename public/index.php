<?php
session_start();
require "../includes/db.php";
require "../includes/nav.php";

/* Produkte aus der Datenbank laden */
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Webshop</title>
</head>
<body>

<h1>Produkte</h1>

<?php if (empty($products)): ?>
    <p>Derzeit sind keine Produkte verfügbar.</p>
<?php else: ?>

    <?php foreach ($products as $product): ?>

        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">

            <h2>
                <a href="product.php?id=<?php echo $product["id"]; ?>">
                    <?php echo htmlspecialchars($product["name"]); ?>
                </a>
            </h2>

            <p>
                <?php echo htmlspecialchars($product["description"]); ?>
            </p>

            <p>
                <strong>
                    Preis:
                    <?php echo number_format($product["price"], 2, ",", "."); ?> €
                </strong>
            </p>

            <p>
                <a href="cart_add.php?id=<?php echo $product["id"]; ?>">
                    In den Warenkorb
                </a>
            </p>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>