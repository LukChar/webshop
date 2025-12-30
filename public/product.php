<?php
session_start();
require_once "../includes/db.php";

/* ID prüfen */
$productId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($productId <= 0) {
    echo "Produkt nicht gefunden.";
    exit;
}

/* Produkt laden */
$stmt = $pdo->prepare("
    SELECT id, name, price, image, description
    FROM products
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "Produkt nicht gefunden.";
    exit;
}

/* Warenkorb-Anzahl */
$cartCount = 0;
if (isset($_SESSION["cart"])) {
    $cartCount = array_sum($_SESSION["cart"]);
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?php echo htmlspecialchars($product["name"]); ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

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
</style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-white font-display pb-32">

<!-- Header -->
<div class="sticky top-0 z-50 bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800">
    <div class="flex items-center p-4 justify-between h-16">

        <a href="index.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>

        <div class="flex gap-2">
            <a href="cart.php"
               class="relative flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="material-symbols-outlined">shopping_cart</span>

                <?php if ($cartCount > 0): ?>
                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                        <?php echo $cartCount; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

    </div>
</div>

<!-- Produktbild -->
<div class="w-full aspect-[4/5] bg-gray-200">
    <div class="w-full h-full bg-center bg-cover"
         style="background-image: url('<?php echo htmlspecialchars($product["image"]); ?>');">
    </div>
</div>

<!-- Content -->
<div class="px-5 flex flex-col gap-6 mt-6">

    <div class="flex flex-col gap-2">
        <h1 class="text-[28px] font-bold leading-tight">
            <?php echo htmlspecialchars($product["name"]); ?>
        </h1>

        <span class="text-4xl font-black">
            <?php echo number_format($product["price"], 2, ",", "."); ?> €
        </span>
    </div>

    <div class="h-px bg-gray-200 dark:bg-gray-800"></div>

    <div class="flex flex-col gap-3">
        <h3 class="text-lg font-bold">Beschreibung</h3>
        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
            <?php echo nl2br(htmlspecialchars($product["description"])); ?>
        </p>
    </div>

</div>

<!-- Bottom Bar -->
<div class="fixed bottom-0 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t border-gray-200 dark:border-gray-800 z-40">
    <form action="cart_add.php" method="post" class="flex gap-4 max-w-2xl mx-auto">
        <input type="hidden" name="product_id" value="<?php echo $product["id"]; ?>">

        <button type="submit"
                class="flex-1 bg-primary text-[#111813] font-bold text-base rounded-lg h-12 flex items-center justify-center gap-2 active:scale-[0.98]">
            <span class="material-symbols-outlined">shopping_bag</span>
            In den Warenkorb
        </button>
    </form>
</div>

</body>
</html>