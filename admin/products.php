<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../includes/admin_auth.php";
require "../includes/db.php";

$message = "";

/* Kategorien laden */
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Default values fürs Formular (damit nach Error nicht alles weg ist) */
$name = "";
$price = "";
$description = "";
$categoryId = "";
$image = "";

/* Produkt anlegen */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = $_POST["price"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $categoryId = $_POST["category_id"] ?? "";
    $image = trim($_POST["image"] ?? "");

    if ($name === "" || $price === "" || $description === "" || $categoryId === "") {
        $message = "Bitte alle Pflichtfelder ausfüllen.";
    } elseif (!is_numeric($price)) {
        $message = "Preis muss eine Zahl sein.";
    } elseif ($image !== "" && filter_var($image, FILTER_VALIDATE_URL) === false) {
        $message = "Bild-URL ist ungültig (bitte vollständige URL mit https://...).";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, price, description, category_id, image)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $price, $description, $categoryId, $image]);

        $message = "Produkt erfolgreich angelegt.";

        // Formular leeren
        $name = $price = $description = $categoryId = $image = "";
    }
}

/* Produkte laden */
$stmt = $pdo->query("
    SELECT p.id, p.name, p.price, c.name AS category
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produktverwaltung</title>

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
    <a href="index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 class="text-xl font-bold">Produktverwaltung</h1>
</header>

<!-- CONTENT -->
<div class="px-4 mt-6 space-y-6">

<!-- MESSAGE -->
<?php if ($message): ?>
    <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- NEUES PRODUKT -->
<div class="bg-surface rounded-xl p-4 shadow-sm">
    <h2 class="text-lg font-bold mb-4">Neues Produkt</h2>

    <form method="post" class="space-y-4">

        <label class="block">
            <span class="text-sm font-medium">Produktname *</span>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($name); ?>"
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Preis (€) *</span>
            <input type="number" step="0.01" min="0" name="price" required
                   value="<?php echo htmlspecialchars((string)$price); ?>"
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Kategorie *</span>
            <select name="category_id" required class="w-full h-12 rounded-lg border p-3">
                <option value="">Bitte wählen</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat["id"]; ?>"
                        <?php echo ((string)$categoryId === (string)$cat["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($cat["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="block">
            <span class="text-sm font-medium">Bild-URL (optional)</span>
            <input type="text" name="image"
                   value="<?php echo htmlspecialchars($image); ?>"
                   placeholder="https://..."
                   class="w-full h-12 rounded-lg border p-3">
        </label>

        <label class="block">
            <span class="text-sm font-medium">Beschreibung *</span>
            <textarea name="description" rows="3" required
                      class="w-full rounded-lg border p-3"><?php echo htmlspecialchars($description); ?></textarea>
        </label>

        <button type="submit"
                class="w-full h-12 bg-primary font-bold rounded-lg text-[#102216]">
            Produkt anlegen
        </button>
    </form>
</div>

<!-- PRODUKTLISTE -->
<div class="space-y-3">
    <h2 class="text-lg font-bold px-1">Produkte</h2>

    <?php if (empty($products)): ?>
        <p class="text-gray-500 text-sm">Keine Produkte vorhanden.</p>
    <?php endif; ?>

    <?php foreach ($products as $product): ?>
        <div class="bg-surface rounded-xl p-4 shadow-sm flex justify-between items-center">
            <div class="min-w-0">
                <p class="font-semibold truncate">
                    <?php echo htmlspecialchars($product["name"]); ?>
                </p>
                <p class="text-xs text-gray-500">
                    <?php echo htmlspecialchars($product["category"] ?? "—"); ?>
                </p>
                <p class="text-sm font-bold mt-1">
                    <?php echo number_format((float)$product["price"], 2, ",", "."); ?> €
                </p>
            </div>

            <a href="product_edit.php?id=<?php echo (int)$product["id"]; ?>"
               class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
                <span class="material-symbols-outlined">edit</span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

</div>
</div>

</body>
</html>
