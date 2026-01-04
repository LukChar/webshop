<?php
/*
Erwartete Variablen:
$productId
$productName
$productPrice
$productImage
$productOldPrice (optional)
*/
?>
<div class="group flex flex-col bg-white dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">

    <!-- Ganze Karte klickbar (zur Produktseite) -->
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

                <!-- Add to Cart: eigener Bereich, klickt NICHT auf Produktseite -->
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
