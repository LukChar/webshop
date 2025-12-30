<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : 0;
?>
<nav class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-background-dark border-t border-gray-200 dark:border-gray-800">
    <div class="flex justify-around items-center h-16 max-w-md mx-auto">

        <!-- Home -->
        <a href="/webshop/public/index.php"
           class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">home</span>
            <span class="text-[10px] font-medium">Home</span>
        </a>

        <!-- Suche (optional Platzhalter) -->
        <div class="flex flex-col items-center justify-center w-full h-full text-gray-300">
            <span class="material-symbols-outlined text-[24px]">search</span>
            <span class="text-[10px] font-medium">Suche</span>
        </div>

        <!-- Warenkorb -->
        <a href="/webshop/public/cart.php"
           class="relative flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
            <span class="text-[10px] font-medium">Warenkorb</span>

            <?php if ($cartCount > 0): ?>
                <span class="absolute top-2 right-6 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                    <?php echo $cartCount; ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Profil -->
        <a href="/webshop/public/profile.php"
           class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">person</span>
            <span class="text-[10px] font-medium">Profil</span>
        </a>

    </div>
</nav>