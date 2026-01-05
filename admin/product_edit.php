<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../includes/admin_auth.php";
require "../includes/db.php";

$message = "";
$error = "";

/* Produkt-ID */
$productId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($productId <= 0) {
    header("Location: products.php");
    exit;
}

/* Kategorien laden */
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Produkt laden */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

/* Speichern */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_product"])) {

    $name = trim($_POST["name"] ?? "");
    $price = $_POST["price"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $categoryId = $_POST["category_id"] ?? "";
    $image = trim($_POST["image"] ?? "");

    if ($name === "" || $price === "" || $description === "" || $categoryId === "") {
        $error = "Bitte alle Pflichtfelder ausfüllen.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, price = ?, description = ?, category_id = ?, image = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $price, $description, $categoryId, $image, $productId]);
        $message = "Produkt erfolgreich gespeichert.";

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* Reviews für dieses Produkt */
$stmt = $pdo->prepare("
    SELECT r.*, u.email
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$productId]);
$productReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h1 class="text-xl font-bold truncate">Produkt bearbeiten</h1>
</header>

<!-- CONTENT -->
<div class="px-4 mt-6 space-y-6">

    <?php if ($message): ?>
        <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- PRODUKT FORM -->
    <div class="bg-surface rounded-xl p-4 shadow-sm">
        <h2 class="text-lg font-bold mb-4">Produktdetails</h2>

        <form method="post" class="space-y-4">
            <input type="hidden" name="save_product" value="1">

            <label class="block">
                <span class="text-sm font-medium">Produktname</span>
                <input type="text" name="name" required
                       value="<?php echo htmlspecialchars($product["name"] ?? ""); ?>"
                       class="w-full h-12 rounded-lg border p-3">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Preis (€)</span>
                <input type="number" step="0.01" min="0" name="price" required
                       value="<?php echo htmlspecialchars((string)($product["price"] ?? "0")); ?>"
                       class="w-full h-12 rounded-lg border p-3">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Kategorie</span>
                <select name="category_id" required class="w-full h-12 rounded-lg border p-3">
                    <option value="">Bitte wählen</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat["id"]; ?>"
                            <?php echo ((int)$product["category_id"] === (int)$cat["id"]) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($cat["name"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium">Bild-URL</span>
                <input type="text" name="image"
                       value="<?php echo htmlspecialchars($product["image"] ?? ""); ?>"
                       class="w-full h-12 rounded-lg border p-3">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Beschreibung</span>
                <textarea name="description" rows="4" required
                          class="w-full rounded-lg border p-3"><?php
                    echo htmlspecialchars($product["description"] ?? "");
                ?></textarea>
            </label>

            <button type="submit"
                    class="w-full h-12 bg-primary font-bold rounded-lg text-[#102216]">
                Speichern
            </button>
        </form>
    </div>

    <!-- REVIEWS -->
    <div id="reviews" class="bg-surface rounded-xl border p-4 shadow-sm">
        <h2 class="text-lg font-bold mb-4">Rezensionen</h2>

        <?php if (empty($productReviews)): ?>
            <p class="text-gray-500 text-sm">Keine Rezensionen vorhanden.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($productReviews as $r): ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between gap-4">
                            <div class="min-w-0">
                                <div class="font-bold truncate">
                                    <?php echo htmlspecialchars($r["author_name"]); ?>
                                    <span class="text-xs text-gray-500 font-normal">
                                        (<?php echo htmlspecialchars($r["email"]); ?>)
                                    </span>
                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    Rating: <span class="font-bold"><?php echo (int)$r["rating"]; ?>/5</span> ·
                                    Hilfreich: <?php echo (int)$r["helpful"]; ?> ·
                                    <?php echo htmlspecialchars(date("d.m.Y H:i", strtotime($r["created_at"]))); ?>
                                </div>
                            </div>

                            <div class="shrink-0 whitespace-nowrap flex items-center gap-3">
                                <a class="font-bold underline"
                                   href="reviews/reviews_edit.php?id=<?php echo (int)$r["id"]; ?>">
                                    Bearbeiten
                                </a>

                                <form action="reviews/reviews_delete.php" method="post"
                                      onsubmit="return confirm('Rezension wirklich löschen?');">
                                    <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                    <button class="font-bold text-red-600 underline">Löschen</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-3 text-sm text-gray-800">
                            <?php echo nl2br(htmlspecialchars($r["text"])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
</div>

</body>
</html>
