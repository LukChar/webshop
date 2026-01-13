<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

/* Warenkorb zählen */
$cartCount = isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : 0;

/* Favoriten zählen */
$favoritesCount = 0;
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM favorites
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION["user_id"]]);
    $favoritesCount = (int)$stmt->fetchColumn();
}
?>

<nav class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-background-dark border-t border-gray-200 dark:border-gray-800">
    <div class="flex items-center justify-center gap-8 h-16 max-w-md mx-auto">

        <!-- Home -->
        <a href="/index.php"
           class="flex flex-col items-center text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">home</span>
            <span class="text-[10px] font-medium">Home</span>
        </a>

        <!-- Favoriten -->
        <a href="/favorites.php"
           class="relative flex flex-col items-center text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">favorite</span>
            <span class="text-[10px] font-medium">Favoriten</span>

            <?php if ($favoritesCount > 0): ?>
                <span class="absolute -top-1 -right-3 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                    <?php echo $favoritesCount; ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Warenkorb -->
        <a href="/cart.php"
           class="relative flex flex-col items-center text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
            <span class="text-[10px] font-medium">Warenkorb</span>

            <?php if ($cartCount > 0): ?>
                <span class="absolute -top-1 -right-3 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-black">
                    <?php echo $cartCount; ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Profil -->
        <a href="/profile.php"
           class="flex flex-col items-center text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined text-[24px]">person</span>
            <span class="text-[10px] font-medium">Profil</span>
        </a>

    </div>
</nav>