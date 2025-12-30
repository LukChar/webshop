<?php
require "../includes/admin_auth.php";
require "../includes/db.php";

/* ID prüfen */
$productId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($productId <= 0) {
    die("Produkt nicht gefunden");
}

/* Produkt laden */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    die("Produkt nicht gefunden");
}

/* Speichern */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $price = $_POST["price"];
    $description = trim($_POST["description"]);

    $stmt = $pdo->prepare("
        UPDATE products
        SET name = ?, price = ?, description = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $price, $description, $productId]);

    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Produkt bearbeiten</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-100 font-[Inter]">

<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Produkt bearbeiten</h1>

    <form method="post" class="bg-white p-6 rounded-xl shadow-sm flex flex-col gap-4">

        <label class="font-semibold">
            Produktname
            <input
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($product["name"]); ?>"
                class="w-full border rounded-lg p-3 mt-1"
                required
            >
        </label>

        <label class="font-semibold">
            Preis (€)
            <input
                type="number"
                step="0.01"
                name="price"
                value="<?php echo htmlspecialchars($product["price"]); ?>"
                class="w-full border rounded-lg p-3 mt-1"
                required
            >
        </label>

        <label class="font-semibold">
            Beschreibung
            <textarea
                name="description"
                class="w-full border rounded-lg p-3 mt-1 h-32"
                required
            ><?php echo htmlspecialchars($product["description"]); ?></textarea>
        </label>

        <div class="flex gap-3 mt-4">
            <a href="products.php"
               class="flex-1 text-center border rounded-lg p-3 font-semibold">
                Abbrechen
            </a>

            <button
                type="submit"
                class="flex-1 bg-[#13ec5b] rounded-lg p-3 font-bold">
                Speichern
            </button>
        </div>

    </form>
</div>

</body>
</html>