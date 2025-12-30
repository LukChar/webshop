<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Bottom Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 h-16 bg-white dark:bg-background-dark border-t border-gray-100 dark:border-gray-800 flex justify-around items-center px-2 z-50">

    <!-- Home -->
    <a href="/webshop/public/index.php"
       class="flex flex-col items-center justify-center w-full h-full gap-1 text-[#111813] dark:text-white">
        <span class="material-symbols-outlined filled"
              style="font-variation-settings: 'FILL' 1;">
            home
        </span>
        <span class="text-[10px] font-medium">Home</span>
    </a>

    <!-- Kategorien (Platzhalter) -->
    <a href="#"
       class="flex flex-col items-center justify-center w-full h-full gap-1 text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white">
        <span class="material-symbols-outlined">grid_view</span>
        <span class="text-[10px] font-medium">Kategorien</span>
    </a>

    <!-- Warenkorb -->
    <a href="/webshop/public/cart.php"
       class="flex flex-col items-center justify-center w-full h-full gap-1 text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white">
        <span class="material-symbols-outlined">shopping_bag</span>
        <span class="text-[10px] font-medium">Warenkorb</span>
    </a>

    <!-- Profil -->
    <a href="/webshop/public/my_orders.php"
       class="flex flex-col items-center justify-center w-full h-full gap-1 text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white">
        <span class="material-symbols-outlined">person</span>
        <span class="text-[10px] font-medium">Profil</span>
    </a>

</div>