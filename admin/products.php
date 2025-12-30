<?php
require "../includes/admin_auth.php";
require "../includes/db.php";

$message = "";

/* Produkt anlegen */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $price = $_POST["price"];
    $description = trim($_POST["description"]);

    if ($name === "" || $price === "" || $description === "") {
        $message = "Bitte alle Felder ausfüllen";
    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO products (name, price, description)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$name, $price, $description]);

        $message = "Produkt erfolgreich angelegt";
    }
}

/* Alle Produkte laden */
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Produkte verwalten</title>
</head>
<body>

<h1>Produkte verwalten</h1>

<?php
if ($message !== "") {
    echo "<p>$message</p>";
}
?>

<h2>Neues Produkt</h2>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Preis (€):</label><br>
    <input type="number" name="price" step="0.01" required><br><br>

    <label>Beschreibung:</label><br>
    <textarea name="description" required></textarea><br><br>

    <button type="submit">Produkt speichern</button>
</form>

<hr>

<h2>Vorhandene Produkte</h2>

<?php if (empty($products)): ?>
    <p>Keine Produkte vorhanden.</p>
<?php else: ?>

    <ul>
        <?php foreach ($products as $product): ?>
            <li>
                <strong><?php echo htmlspecialchars($product["name"]); ?></strong>
                (<?php echo number_format($product["price"], 2, ",", "."); ?> €)
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>

<p>
    <a href="dashboard.php">Zurück zum Dashboard</a> |
    <a href="../public/index.php">Zur Startseite</a>
</p>

</body>
</html>