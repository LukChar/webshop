<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = $_SESSION["user_id"];

/* Favoriten laden */
$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.name,
        p.price,
        p.image
    FROM favorites f
    JOIN products p ON p.id = f.product_id
    WHERE f.user_id = ?
    ORDER BY p.name ASC
");
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Meine Favoriten</title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

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
                "surface-dark": "#1c2e22",
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

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-gray-100 font-display pb-24">

<?php require_once "../includes/header.php"; ?>

<main class="max-w-md mx-auto">

    <!-- Headline + Aktion -->
    <?php $hasFavorites = !empty($favorites); ?>
    <div class="px-4 pt-6 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold">Meine Favoriten</h1>

        <?php if ($hasFavorites): ?>
            <form method="post" action="favorites_add_all_to_cart.php" class="w-full sm:w-auto">
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-sm transition bg-primary text-[#0b1f12] shadow-primary/40 hover:brightness-95"
                >
                    <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                    Alle in den Warenkorb
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($favorites)): ?>

        <p class="px-4 text-gray-500 text-sm">
            Du hast noch keine Favoriten gespeichert.
        </p>

    <?php else: ?>

        <!-- Favoriten Grid -->
        <div class="px-4 grid grid-cols-2 gap-4">

            <?php foreach ($favorites as $product): ?>

                <?php
                $productId       = (int)$product["id"];
                $productName     = $product["name"];
                $productPrice    = (float)$product["price"];
                $productOldPrice = null;
                $productImage    = $product["image"];
                ?>

                <div class="relative">

                    <?php require "../includes/product_card.php"; ?>

                    <!-- Favorit entfernen -->
                    <form
                        method="post"
                        action="favorite_toggle.php"
                        class="absolute top-2 right-2"
                    >
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                        <button
                            type="submit"
                            class="flex size-8 items-center justify-center rounded-full bg-white/90 hover:bg-white shadow"
                        >
                            <span class="material-symbols-outlined text-red-500">favorite</span>
                        </button>
                    </form>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>
