<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../includes/admin_auth.php";
require "../includes/db.php";

$productId = (int)($_GET["id"] ?? 0);
$message = "";

/* Produkt löschen */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product"])) {

    /* zuerst Stock löschen */
    $stmt = $pdo->prepare("
        DELETE FROM stock
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);

    /* dann Produkt löschen */
    $stmt = $pdo->prepare("
        DELETE FROM products
        WHERE id = ?
    ");
    $stmt->execute([$productId]);

    /* zurück zur Übersicht */
    header("Location: products.php?deleted=1");
    exit;
}

/* Produkt laden */
$stmt = $pdo->prepare("
    SELECT id, name, price, description, category_id
    FROM products
    WHERE id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Produkt nicht gefunden.";
    exit;
}

/* Stock laden */
$stmt = $pdo->prepare("
    SELECT quantity
    FROM stock
    WHERE product_id = ?
");
$stmt->execute([$productId]);
$stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
$stock = $stockRow ? (int)$stockRow["quantity"] : 0;

/* Kategorien laden */
$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Produkt speichern */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $categoryId = ($_POST["category_id"] ?? "") !== "" ? (int)$_POST["category_id"] : null;
    $stock = (int)($_POST["stock"] ?? 0);

    if ($name === "" || $price === "" || $description === "") {
        $message = "Bitte alle Pflichtfelder ausfüllen.";
    } elseif (!is_numeric($price)) {
        $message = "Preis muss eine Zahl sein.";
    } elseif ($stock < 0) {
        $message = "Bestand darf nicht negativ sein.";
    } else {

        /* Produkt aktualisieren */
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, price = ?, description = ?, category_id = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $price,
            $description,
            $categoryId,
            $productId
        ]);

        /* Stock aktualisieren (UPSERT) */
        $stmt = $pdo->prepare("
            INSERT INTO stock (product_id, quantity)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ");
        $stmt->execute([$productId, $stock]);

        $message = "Produkt erfolgreich aktualisiert.";

        /* Lokale Werte aktualisieren */
        $product["name"] = $name;
        $product["price"] = $price;
        $product["description"] = $description;
        $product["category_id"] = $categoryId;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produkt bearbeiten</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                bgLight: "#f6f8f6",
                bgDark: "#102216",
                surface: "#ffffff"
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-bgLight font-display text-[#111813]">

<div class="max-w-md mx-auto min-h-screen pb-24">

<!-- HEADER -->
<header class="sticky top-0 bg-bgLight/95 backdrop-blur border-b px-4 py-4 flex items-center gap-3">
    <a href="products.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 class="text-xl font-bold">Produkt bearbeiten</h1>
</header>

<!-- CONTENT -->
<div class="px-4 mt-6 space-y-6">

<?php if ($message): ?>
    <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-surface rounded-xl p-4 shadow-sm">
    <form method="post" class="space-y-4">

        <label class="block">
            <span class="text-sm font-medium">Produktname *</span>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($product["name"]); ?>"
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Preis (€) *</span>
            <input type="number" step="0.01" name="price" required
                   value="<?php echo htmlspecialchars($product["price"]); ?>"
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Bestand *</span>
            <input type="number" min="0" name="stock" required
                   value="<?php echo htmlspecialchars((string)$stock); ?>"
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Kategorie</span>
            <select name="category_id" class="w-full h-12 rounded-lg border p-3">
                <option value="">– Keine Kategorie –</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat["id"]; ?>"
                        <?php if ((int)$cat["id"] === (int)$product["category_id"]) echo "selected"; ?>>
                        <?php echo htmlspecialchars($cat["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="block">
            <span class="text-sm font-medium">Beschreibung *</span>
            <textarea name="description" rows="4" required
                      class="w-full rounded-lg border p-3"><?php
                echo htmlspecialchars($product["description"]);
            ?></textarea>
        </label>

        <button type="submit"
                class="w-full h-12 bg-primary font-bold rounded-lg text-[#102216]">
            Änderungen speichern
        </button>

        <hr class="my-6">

        <form method="post" onsubmit="return confirm('Produkt wirklich endgültig löschen?');">
            <input type="hidden" name="delete_product" value="1">
            <button type="submit"
                class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg">
            Produkt löschen
        </button>
        </form>

    </form>
</div>

</div>
</div>

</body>
</html>