<?php
session_start();
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>CampusShop – Produktübersicht</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Tailwind Config -->
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
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-[#e0e6e2] font-display antialiased pb-24">

<?php require_once "../includes/header.php"; ?>

<!-- Suchfeld -->
<div class="px-4 py-2">
    <input
        type="text"
        placeholder="Suche nach Produkten..."
        class="w-full h-12 rounded-lg px-4 bg-white dark:bg-surface-dark text-[#111813] dark:text-white"
    >
</div>

<!-- Headline -->
<div class="flex items-center justify-between px-4 pt-4 pb-2">
    <h2 class="text-xl font-bold">Beliebt bei Studis</h2>
    <span class="text-sm text-[#61896f]">Alle ansehen</span>
</div>

<?php
require_once "../includes/db.php";

/* Produkte laden */
$stmt = $pdo->query("
    SELECT id, name, price, image
    FROM products
");
$products = $stmt->fetchAll();
?>

<?php if (empty($products)): ?>

    <p class="px-4 text-gray-500">Keine Produkte vorhanden.</p>

<?php else: ?>

    <div class="px-4 pb-4">
        <div class="grid grid-cols-2 gap-4">

            <?php foreach ($products as $product): ?>

                <?php
                $productId = $product["id"];
                $productName = $product["name"];
                $productPrice = $product["price"];
                $productOldPrice = null;
                $productImage = $product["image"];
                ?>

                <?php require "../includes/product_card.php"; ?>

            <?php endforeach; ?>

        </div>
    </div>

<?php endif; ?>

<?php require_once "../includes/footer.php"; ?>
<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>