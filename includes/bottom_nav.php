<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER["PHP_SELF"]);

/* Warenkorb-Anzahl */
$cartCount = 0;
if (isset($_SESSION["cart"])) {
    $cartCount = array_sum($_SESSION["cart"]);
}
?>
<nav class="fixed bottom-0 left-0 right-0 bg-surface-light dark:bg-surface-dark border-t border-gray-200 dark:border-border-dark px-6 py-2 z-50">
    <div class="flex justify-between items-end max-w-md mx-auto">

        <!-- Home -->
        <a href="/webshop/public/index.php"
           class="flex flex-col items-center gap-1
           <?php echo ($currentPage === 'index.php')
               ? 'text-[#111813] dark:text-white font-bold'
               : 'text-gray-500 hover:text-[#111813] dark:hover:text-white'; ?>">
            <span class="material-symbols-outlined text-[24px]">home</span>
            <span class="text-[10px]">Start</span>
        </a>

        <!-- Warenkorb -->
        <a href="/webshop/public/cart.php"
           class="relative flex flex-col items-center gap-1
           <?php echo ($currentPage === 'cart.php')
               ? 'text-[#111813] dark:text-white font-bold'
               : 'text-gray-500 hover:text-[#111813] dark:hover:text-white'; ?>">

            <span class="material-symbols-outlined text-[24px]">
                shopping_cart
            </span>

            <?php if ($cartCount > 0): ?>
                <span class="absolute -top-1 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                    <?php echo $cartCount; ?>
                </span>
            <?php endif; ?>

            <span class="text-[10px]">Warenkorb</span>
        </a>

        <!-- Profil -->
        <a href="/webshop/public/profile.php"
           class="flex flex-col items-center gap-1
           <?php echo ($currentPage === 'profile.php')
               ? 'text-primary font-bold'
               : 'text-gray-500 hover:text-[#111813] dark:hover:text-white'; ?>">
            <span class="material-symbols-outlined text-[24px]">person</span>
            <span class="text-[10px]">Profil</span>
        </a>

    </div>
</nav>