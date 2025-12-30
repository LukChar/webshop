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

    <div class="relative aspect-[4/3] w-full overflow-hidden">
        <img
            src="<?php echo $productImage; ?>"
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
                        <?php echo number_format($productOldPrice, 2, ",", "."); ?> €
                    </span>
                <?php endif; ?>

                <span class="text-[#111813] dark:text-white font-bold text-base">
                    <?php echo number_format($productPrice, 2, ",", "."); ?> €
                </span>
            </div>

            <!-- Add to Cart -->
            <form method="post" action="/webshop/public/cart_add.php">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <button
                    type="submit"
                    class="flex size-8 items-center justify-center rounded-full bg-primary text-black hover:bg-green-400 transition-colors"
                >
                    <span class="material-symbols-outlined" style="font-size: 20px;">
                        add
                    </span>
                </button>
            </form>

        </div>
    </div>

</div>