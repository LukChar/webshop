<?php
session_start();
require_once "../includes/db.php";

/* Kategorie-Filter */
$activeCategory = isset($_GET["category"]) ? (int)$_GET["category"] : 0;

/* Kategorien laden */
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Favoriten */
$favorites = [];
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/* Produkte laden (mit Filter) */
if ($activeCategory > 0) {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE category_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$activeCategory]);
} else {
    $stmt = $pdo->query("
        SELECT id, name, price, image
        FROM products
        ORDER BY id DESC
    ");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CampusShop – Produkte</title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "background-dark": "#102216",
                "surface-light": "#ffffff",
                "surface-dark": "#1c2e22",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>

<style>
body { min-height: max(884px, 100dvh); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { scrollbar-width: none; }
</style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-[#e0e6e2] font-display pb-24">

<?php require "../includes/header.php"; ?>

<!-- Suche -->
<div class="px-4 py-2">
    <input
        type="text"
        placeholder="Suche nach Produkten..."
        class="w-full h-12 rounded-lg px-4 bg-white dark:bg-surface-dark"
    >
</div>

<!-- Kategorien Chips -->
<div class="w-full overflow-x-auto no-scrollbar pb-2">
    <div class="flex gap-3 px-4 py-2 min-w-max">

        <!-- Alle -->
        <a href="index.php"
           class="flex h-9 items-center px-4 rounded-full text-sm font-medium border
           <?php echo $activeCategory === 0 ? "bg-primary text-black" : "bg-white dark:bg-surface-dark"; ?>">
            Alle
        </a>

        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?php echo $cat["id"]; ?>"
               class="flex h-9 items-center px-4 rounded-full text-sm font-medium border
               <?php echo $activeCategory === (int)$cat["id"]
                   ? "bg-primary text-black"
                   : "bg-white dark:bg-surface-dark"; ?>">
                <?php echo htmlspecialchars($cat["name"]); ?>
            </a>
        <?php endforeach; ?>

    </div>
</div>

<!-- Headline -->
<div class="flex items-center justify-between px-4 pt-4 pb-2">
    <h2 class="text-xl font-bold">Produkte</h2>
</div>

<?php if (empty($products)): ?>

    <p class="px-4 text-gray-500">Keine Produkte vorhanden.</p>

<?php else: ?>

<!-- Produkt Grid (KOMPAKT) -->
<div class="px-4 pb-4">
    <div class="grid grid-cols-2 gap-3">

        <?php foreach ($products as $product): ?>
            <?php
            $isFavorite = in_array($product["id"], $favorites, true);
            ?>
            <div class="group bg-white dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm">

                <!-- Bild -->
                <div class="relative aspect-[4/3] overflow-hidden">
                    <img
                        src="<?php echo htmlspecialchars($product["image"]); ?>"
                        class="w-full h-full object-cover transition-transform group-hover:scale-105"
                        alt=""
                    >
                </div>

                <!-- Content -->
                <div class="p-3 flex flex-col gap-1">
                    <h3 class="text-sm font-medium line-clamp-2 min-h-[2.5em]">
                        <?php echo htmlspecialchars($product["name"]); ?>
                    </h3>

                    <div class="flex items-center justify-between mt-1">
                        <span class="font-bold">
                            <?php echo number_format($product["price"], 2, ",", "."); ?> €
                        </span>

                        <a href="cart_add.php?id=<?php echo $product["id"]; ?>"
                           class="flex size-8 items-center justify-center rounded-full bg-primary text-black">
                            <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php endif; ?>

<?php require "../includes/footer.php"; ?>
<?php require "../includes/bottom_nav.php"; ?>

</body>
</html>