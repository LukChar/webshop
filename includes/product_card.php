<?php
/* Favoritenstatus prüfen */
$isFavorite = false;

if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("
        SELECT 1
        FROM favorites
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$_SESSION["user_id"], $productId]);
    $isFavorite = (bool)$stmt->fetch();
}
?>

<div class="group relative flex flex-col bg-white dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">

    <!-- Favoriten-Button -->
    <?php if (isset($_SESSION["user_id"])): ?>
        <form
            method="post"
            action="/webshop/public/favorite_toggle.php"
            class="absolute top-2 right-2 z-10"
            onclick="event.stopPropagation();"
        >
            <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">

            <button
                type="submit"
                class="flex items-center justify-center rounded-full p-1.5 shadow
                <?php echo $isFavorite
                    ? 'bg-primary text-black'
                    : 'bg-white text-gray-400 hover:text-red-500'; ?>"
                onclick="event.stopPropagation();"
            >
                <span class="material-symbols-outlined" style="font-size:20px;">
                    favorite
                </span>
            </button>
        </form>
    <?php endif; ?>

    <!-- Ganze Karte klickbar -->
    <a href="product.php?id=<?php echo urlencode($productId); ?>" class="block">
        <div class="relative aspect-[4/3] w-full overflow-hidden">
            <img
                src="<?php echo htmlspecialchars($productImage); ?>"
                alt="<?php echo htmlspecialchars($productName); ?>"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            >
        </div>

        <div class="flex flex-col p-3 gap-1">
            <h3 class="text-[#111813] dark:text-white text-sm font-medium line-clamp-2 min-h-[2.5em]">
                <?php echo htmlspecialchars($productName); ?>
            </h3>

            <div class="flex items-center justify-between mt-1">
                <div class="flex flex-col leading-none">

                    <?php if (!empty($productOldPrice)): ?>
                        <span class="text-xs text-gray-400 line-through">
                            <?php echo number_format((float)$productOldPrice, 2, ",", "."); ?> €
                        </span>
                    <?php endif; ?>

                    <span class="text-[#111813] dark:text-white font-bold text-base">
                        <?php echo number_format((float)$productPrice, 2, ",", "."); ?> €
                    </span>
                </div>

                <!-- Add to Cart -->
                <form method="post" action="cart_add.php" onclick="event.stopPropagation();">
                    <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                    <button
                        type="submit"
                        class="flex size-8 items-center justify-center rounded-full bg-primary text-black hover:bg-green-400 transition-colors"
                        onclick="event.stopPropagation();"
                    >
                        <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
                    </button>
                </form>

            </div>
        </div>
    </a>

</div>