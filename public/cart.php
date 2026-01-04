<?php
session_start();
require_once "../includes/db.php";

/* Warenkorb laden */
$cart = $_SESSION["cart"] ?? [];
$cartCount = array_sum($cart);

$defaultShipping = 5.00;
$freeShippingFrom = 50.00;
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Warenkorb</title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100 flex flex-col h-[100dvh] overflow-hidden">

<!-- Header -->
<header class="shrink-0 px-4 py-4 flex items-center justify-between">
    <a href="index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">
        Warenkorb (<?php echo $cartCount; ?>)
    </h1>

    <div class="size-10"></div>
</header>

<!-- Content -->
<main class="flex-1 overflow-y-auto px-4 pb-40">

<?php if (empty($cart)): ?>

    <p class="text-gray-500 text-center mt-12">
        Dein Warenkorb ist leer.
    </p>

<?php else: ?>

<?php
$subtotal = 0;

foreach ($cart as $productId => $quantity):

    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) continue;

    $lineTotal = $product["price"] * $quantity;
    $subtotal += $lineTotal;
?>

<!-- Cart Item -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 mb-4 shadow-sm">
    <div class="flex gap-4">

        <div class="shrink-0 rounded-lg w-[80px] h-[100px] bg-center bg-cover"
             style="background-image:url('<?php echo htmlspecialchars($product["image"]); ?>');">
        </div>

        <div class="flex flex-1 flex-col min-h-[100px]">

            <div class="flex justify-between items-start">
                <h3 class="font-semibold leading-tight line-clamp-2">
                    <?php echo htmlspecialchars($product["name"]); ?>
                </h3>

                <a href="cart_remove.php?id=<?php echo $productId; ?>"
                   class="text-gray-400 hover:text-red-500 p-1">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </a>
            </div>

            <div class="flex justify-between items-end mt-auto pt-4">
                <span class="text-lg font-bold">
                    <?php echo number_format($product["price"], 2, ",", "."); ?> €
                </span>

                <div class="flex items-center bg-[#f0f4f2] dark:bg-black/20 rounded-lg p-1 gap-1">
                    <a href="cart_update.php?id=<?php echo $productId; ?>&action=minus"
                       class="flex h-7 w-7 items-center justify-center rounded-md bg-white dark:bg-white/10 shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">remove</span>
                    </a>

                    <span class="w-8 text-center text-sm font-semibold">
                        <?php echo $quantity; ?>
                    </span>

                    <a href="cart_update.php?id=<?php echo $productId; ?>&action=plus"
                       class="flex h-7 w-7 items-center justify-center rounded-md bg-primary text-[#111813] shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php endforeach; ?>

<?php
/* Versandlogik */
$shippingCost = ($subtotal >= $freeShippingFrom) ? 0.00 : $defaultShipping;
$total = $subtotal + $shippingCost;
?>

<!-- Summary -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl p-5 shadow-sm">
    <h2 class="font-bold mb-4">Zusammenfassung</h2>

    <div class="flex justify-between py-2 text-sm text-gray-500">
        <span>Zwischensumme</span>
        <span><?php echo number_format($subtotal, 2, ",", "."); ?> €</span>
    </div>

    <div class="flex justify-between py-2 text-sm text-gray-500">
        <span>Versand</span>
        <?php if ($shippingCost == 0): ?>
            <span class="text-primary font-bold">Kostenlos</span>
        <?php else: ?>
            <span><?php echo number_format($shippingCost, 2, ",", "."); ?> €</span>
        <?php endif; ?>
    </div>

    <?php if ($subtotal < $freeShippingFrom): ?>
        <p class="text-xs text-gray-400 mt-1">
            Noch <?php echo number_format($freeShippingFrom - $subtotal, 2, ",", "."); ?> € bis kostenloser Versand
        </p>
    <?php endif; ?>

    <div class="flex justify-between items-end pt-4">
        <span class="font-bold">Gesamt</span>
        <span class="text-2xl font-bold">
            <?php echo number_format($total, 2, ",", "."); ?> €
        </span>
    </div>
</div>

<?php endif; ?>

</main>

<!-- Checkout Button -->
<?php if (!empty($cart)): ?>
<div class="fixed bottom-16 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t shadow-lg z-30">
    <a href="checkout.php"
       class="flex items-center justify-between w-full h-14 rounded-xl bg-primary px-6 font-bold active:scale-[0.98] transition">
        <span>Zur Kasse gehen</span>
        <span class="bg-black/10 rounded-lg px-3 py-1 text-sm">
            <?php echo number_format($total, 2, ",", "."); ?> €
        </span>
    </a>
</div>
<?php endif; ?>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>