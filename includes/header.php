<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : 0;
$isAdmin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
?>

<div class="sticky top-0 z-40 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-gray-100 dark:border-gray-800">
    <div class="relative h-16">

        <!-- 🔹 EBENE 1: TITEL (KLICKBAR, IMMER OBEN) -->
        <div class="absolute inset-0 z-20 flex items-center justify-center">
            <a href="index.php"
                class="text-lg font-bold text-[#111813] dark:text-white hover:opacity-80
                focus:outline-none focus:ring-0 focus-visible:outline-none">
    CampusShop
</a>

        </div>

        <!-- 🔹 EBENE 2: INHALT (Darf Titel NICHT blockieren) -->
        <div class="relative z-10 flex items-center h-full px-4 pointer-events-none">

            <!-- Profil -->
            <a href="/webshop/public/profile.php"
               class="pointer-events-auto flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-surface-dark">
                <span class="material-symbols-outlined" style="font-size:28px;">
                    account_circle
                </span>
            </a>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Admin Zahnrad -->
            <?php if ($isAdmin): ?>
                <a href="/webshop/admin/index.php"
                   class="pointer-events-auto flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-surface-dark"
                   title="Admin Bereich">
                    <span class="material-symbols-outlined">settings</span>
                </a>
            <?php endif; ?>

            <!-- Warenkorb -->
            <a href="/webshop/public/cart.php"
               class="pointer-events-auto relative flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-surface-dark ml-1">
                <span class="material-symbols-outlined" style="font-size:26px;">
                    shopping_cart
                </span>

                <?php if ($cartCount > 0): ?>
                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                        <?php echo $cartCount; ?>
                    </span>
                <?php endif; ?>
            </a>

        </div>
    </div>
</div>
