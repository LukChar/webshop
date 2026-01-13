<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* Kategorien laden */
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";
$success = "";

/* Formular verarbeiten */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $image = trim($_POST["image"] ?? "");
    $categoryId = ($_POST["category_id"] ?? "") !== "" ? (int)$_POST["category_id"] : null;
    $stock = (int)($_POST["stock"] ?? 0);

    if ($name === "" || $price === "" || $description === "") {
        $error = "Bitte alle Pflichtfelder ausfüllen.";
    } elseif (!is_numeric($price)) {
        $error = "Preis muss eine Zahl sein.";
    } elseif ($stock < 0) {
        $error = "Bestand darf nicht negativ sein.";
    } else {

        /* Produkt anlegen */
        $stmt = $pdo->prepare("
            INSERT INTO products (name, price, description, image, category_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $price,
            $description,
            $image,
            $categoryId
        ]);

        $productId = $pdo->lastInsertId();

        /* Stock anlegen */
        if ($productId) {
            $stmt = $pdo->prepare("
                INSERT INTO stock (product_id, quantity)
                VALUES (?, ?)
            ");
            $stmt->execute([
                (int)$productId,
                $stock
            ]);
        }

        $success = "Produkt erfolgreich angelegt.";

        /* Formular leeren */
        $name = $price = $description = $image = "";
        $categoryId = null;
        $stock = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produkt anlegen</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "surface-light": "#ffffff"
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-background-light font-display">

<div class="max-w-xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Neues Produkt anlegen</h1>

    <?php if ($error): ?>
        <div class="mb-4 text-red-600 font-medium">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-4 text-green-600 font-medium">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="flex flex-col gap-4">

        <label>
            <span class="text-sm font-medium">Produktname *</span>
            <input
                type="text"
                name="name"
                required
                value="<?php echo htmlspecialchars($name ?? ""); ?>"
                class="w-full h-12 rounded-lg border p-3"
            >
        </label>

        <label>
            <span class="text-sm font-medium">Preis (€) *</span>
            <input
                type="number"
                step="0.01"
                name="price"
                required
                value="<?php echo htmlspecialchars($price ?? ""); ?>"
                class="w-full h-12 rounded-lg border p-3"
            >
        </label>

        <label>
            <span class="text-sm font-medium">Bestand *</span>
            <input
                type="number"
                name="stock"
                min="0"
                required
                value="<?php echo htmlspecialchars($stock ?? 0); ?>"
                class="w-full h-12 rounded-lg border p-3"
            >
        </label>

        <label>
            <span class="text-sm font-medium">Kategorie</span>
            <select
                name="category_id"
                class="w-full h-12 rounded-lg border p-3"
            >
                <option value="">– Keine Kategorie –</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat["id"]; ?>"
                        <?php if (($categoryId ?? null) === (int)$cat["id"]) echo "selected"; ?>>
                        <?php echo htmlspecialchars($cat["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span class="text-sm font-medium">Bild-URL (optional)</span>
            <input
                type="text"
                name="image"
                value="<?php echo htmlspecialchars($image ?? ""); ?>"
                class="w-full h-12 rounded-lg border p-3"
                placeholder="https://..."
            >
        </label>

        <label>
            <span class="text-sm font-medium">Beschreibung *</span>
            <textarea
                name="description"
                required
                rows="5"
                class="w-full rounded-lg border p-3"
            ><?php echo htmlspecialchars($description ?? ""); ?></textarea>
        </label>

        <div class="flex gap-3 mt-4">
            <button
                type="submit"
                class="flex-1 h-12 bg-primary font-bold rounded-lg">
                Produkt speichern
            </button>

            <a href="products.php"
               class="flex-1 h-12 flex items-center justify-center border rounded-lg">
                Abbrechen
            </a>
        </div>

    </form>

</div>

</body>
</html>