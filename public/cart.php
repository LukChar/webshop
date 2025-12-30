<?php
session_start();
require_once "../includes/db.php";

/* Warenkorb laden */
$cart = $_SESSION["cart"] ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Warenkorb</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Tailwind -->
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
                        "surface-dark": "#1c3326",
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100 flex flex-col h-[100dvh] overflow-hidden antialiased">

<!-- Top Bar -->
<header class="shrink-0 px-4 py-4 flex items-center justify-between">
    <a href="index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">
        Warenkorb (<?php echo array_sum($cart); ?>)
    </h1>

    <div class="size-10"></div>
</header>

<!-- Content -->
<main class="flex-1 overflow-y-auto px-4 pb-32">

<?php if (empty($cart)): ?>

    <p class="text-gray-500">Ihr Warenkorb ist leer.</p>

<?php else: ?>

<?php
$total = 0;

foreach ($cart as $productId => $quantity):

    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        continue;
    }

    $subtotal = $product["price"] * $quantity;
    $total += $subtotal;
?>

    <!-- Cart Item -->
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 mb-4 shadow-sm">
        <div class="flex gap-4">

            <!-- Image -->
            <div
                class="shrink-0 rounded-lg w-[80px] h-[100px] bg-center bg-cover"
                style="background-image: url('<?php echo htmlspecialchars($product["image"]); ?>');">
            </div>

            <!-- Details -->
            <div class="flex flex-1 flex-col">

                <div class="flex justify-between items-start">
                    <h3 class="font-semibold leading-tight">
                        <?php echo htmlspecialchars($product["name"]); ?>
                    </h3>

                    <a href="cart_remove.php?id=<?php echo $productId; ?>"
                       class="text-gray-400 hover:text-red-500">
                        <span class="material-symbols-outlined">delete</span>
                    </a>
                </div>

                <div class="flex justify-between items-end mt-auto pt-4">
                    <span class="font-bold">
                        <?php echo number_format($product["price"], 2, ",", "."); ?> €
                    </span>

                    <!-- Quantity -->
                    <div class="flex items-center gap-2">
                        <a href="cart_update.php?id=<?php echo $productId; ?>&action=minus">−</a>
                        <span><?php echo $quantity; ?></span>
                        <a href="cart_update.php?id=<?php echo $productId; ?>&action=plus">+</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php endforeach; ?>

    <!-- Summary -->
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-5 shadow-sm">
        <div class="flex justify-between py-2">
            <span>Zwischensumme</span>
            <span><?php echo number_format($total, 2, ",", "."); ?> €</span>
        </div>
        <div class="flex justify-between py-2 font-bold text-lg">
            <span>Gesamt</span>
            <span><?php echo number_format($total, 2, ",", "."); ?> €</span>
        </div>
    </div>

<?php endif; ?>

</main>

<!-- Checkout -->
<?php if (!empty($cart)): ?>
<div class="fixed bottom-0 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t">
    <a href="checkout.php"
       class="block text-center w-full h-14 bg-primary font-bold rounded-xl leading-[3.5rem]">
        Zur Kasse gehen
    </a>
</div>
<?php endif; ?>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>