<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* Produkte laden */
$stmt = $pdo->query("
    SELECT id, name, price, image
    FROM products
    ORDER BY id DESC
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Produktverwaltung (Admin)</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "background-dark": "#102216",
                "surface-light": "#ffffff",
                "surface-dark": "#162e20",
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
</style>
</head>

<body class="bg-background-light font-display">

<div class="flex min-h-screen flex-col pb-24">

<!-- Header -->
<header class="sticky top-0 z-10 bg-background-light px-4 pt-4 pb-2">
    <div class="flex items-center justify-between h-12">
        <a href="index.php"
           class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-black/5">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>

        <a href="product_create.php"
           class="flex items-center justify-center h-10 px-4 rounded-full bg-primary text-black gap-2 text-sm font-bold">
            <span class="material-symbols-outlined">add</span>
            Neu
        </a>
    </div>

    <h1 class="mt-2 text-[32px] font-bold">
        Produktverwaltung
    </h1>
</header>

<!-- Produktliste -->
<main class="flex flex-col px-4 mt-4 gap-3">

<?php if (empty($products)): ?>

    <p class="text-gray-500">Keine Produkte vorhanden.</p>

<?php else: ?>

<?php foreach ($products as $product): ?>

    <div class="flex items-center gap-4 bg-white p-3 rounded-xl shadow-sm border">

        <!-- Bild -->
        <div class="shrink-0">
            <div class="size-16 rounded-lg bg-gray-100 bg-cover bg-center"
                 style="background-image: url('<?php echo htmlspecialchars($product["image"] ?? ""); ?>');">
            </div>
        </div>

        <!-- Infos -->
        <div class="flex flex-col flex-1 min-w-0">
            <p class="text-base font-semibold truncate">
                <?php echo htmlspecialchars($product["name"]); ?>
            </p>
            <p class="text-sm text-gray-500">
                <?php echo number_format($product["price"], 2, ",", "."); ?> €
            </p>
        </div>

        <!-- Aktionen (später Edit/Delete) -->
        <div>
            <a href="product_edit.php?id=<?php echo $product["id"]; ?>"
               class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100">
                <span class="material-symbols-outlined">edit</span>
            </a>
        </div>

    </div>

<?php endforeach; ?>

<?php endif; ?>

</main>

</div>

</body>
</html>