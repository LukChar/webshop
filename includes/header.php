<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Warenkorb-Anzahl berechnen */
$cartCount = 0;
if (isset($_SESSION["cart"])) {
    $cartCount = array_sum($_SESSION["cart"]);
}
?>

<!-- Top App Bar (Sticky) -->
<div class="sticky top-0 z-40 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-gray-100 dark:border-gray-800">
    <div class="flex items-center p-4 justify-between h-16">

        <!-- Profil -->
        <a href="/webshop/public/my_orders.php"
           class="flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-surface-dark transition-colors">
            <span class="material-symbols-outlined text-[#111813] dark:text-white" style="font-size: 28px;">
                account_circle
            </span>
        </a>

        <!-- Branding -->
        <h1 class="text-[#111813] dark:text-white text-xl font-bold tracking-tight">
            CampusShop
        </h1>

        <!-- Warenkorb -->
        <a href="/webshop/public/cart.php"
           class="flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-surface-dark transition-colors relative">

            <span class="material-symbols-outlined text-[#111813] dark:text-white" style="font-size: 26px;">
                shopping_cart
            </span>

            <?php if ($cartCount > 0): ?>
                <span
                    class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                    <?php echo $cartCount; ?>
                </span>
            <?php endif; ?>

        </a>

    </div>
</div>