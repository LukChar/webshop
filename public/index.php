<?php
session_start();
require_once "../includes/db.php";

/* Kategorie-Filter */
$activeCategory = isset($_GET["category"]) ? (int)$_GET["category"] : 0;
/* Suche */
$search = isset($_GET["q"]) ? trim($_GET["q"]) : "";

/* Kategorien */
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Favoriten */
$favorites = [];
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/* Produkte */
if ($activeCategory > 0 && $search !== "") {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE category_id = ? AND name LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute([$activeCategory, "%" . $search . "%"]);
} elseif ($activeCategory > 0) {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE category_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$activeCategory]);
} elseif ($search !== "") {
    $stmt = $pdo->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE name LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute(["%" . $search . "%"]);
} else {
    $stmt = $pdo->query("
        SELECT id, name, price, image
        FROM products
        ORDER BY id DESC
    ");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderProductGrid(array $products, array $favorites): string {
    ob_start();
    ?>
        <div class="grid grid-cols-2 gap-3" id="productGrid">

            <?php foreach ($products as $product): ?>
                <?php
                    $productId = (int)$product["id"];
                    $isFav = in_array($productId, $favorites, true);
                ?>

                <a href="product.php?id=<?php echo $productId; ?>"
                   class="group block bg-white dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                    <div class="relative aspect-[4/3] overflow-hidden">
                        <img src="<?php echo htmlspecialchars($product["image"]); ?>"
                             class="w-full h-full object-cover transition-transform group-hover:scale-105"
                             alt="">

                        <?php if (isset($_SESSION["user_id"])): ?>
                            <form action="favorite_toggle.php"
                                  method="post"
                                  onclick="event.stopPropagation();"
                                  class="absolute top-2 right-2">
                                <input type="hidden" name="product_id"
                                       value="<?php echo $productId; ?>">
                                <button type="submit"
                                        class="flex size-8 items-center justify-center rounded-full backdrop-blur shadow
                                        <?php echo $isFav
                                            ? 'bg-primary text-black'
                                            : 'bg-white/90 text-gray-500 hover:text-primary'; ?>">
                                    <span class="material-symbols-outlined"
                                          style="font-variation-settings:'FILL' <?php echo $isFav ? 1 : 0; ?>">
                                        favorite
                                    </span>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>

                    <div class="p-3 flex flex-col gap-1">
                        <h3 class="text-sm font-medium line-clamp-2 min-h-[2.5em]">
                            <?php echo htmlspecialchars($product["name"]); ?>
                        </h3>

                        <div class="flex items-center justify-between mt-1">
                            <span class="font-bold">
                                <?php echo number_format($product["price"], 2, ",", "."); ?> &euro;
                            </span>

                            <form action="cart_add.php" method="post"
                                  onclick="event.stopPropagation();">
                                <input type="hidden" name="product_id"
                                       value="<?php echo $productId; ?>">
                                <button type="submit"
                                        class="flex size-8 items-center justify-center rounded-full bg-primary text-black hover:bg-green-400 transition-colors">
                                    <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </a>

            <?php endforeach; ?>

        </div>
    <?php
    return ob_get_clean();
}

if (isset($_GET["ajax"])) {
    echo renderProductGrid($products, $favorites);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CampusShop – Produkte</title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
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
            fontFamily: { display: ["Inter","sans-serif"] }
        }
    }
}
</script>

<style>
body { min-height: max(884px, 100dvh); }
.no-scrollbar::-webkit-scrollbar { display:none; }
.no-scrollbar { scrollbar-width:none; }
</style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-[#e0e6e2] font-display pb-24">

<?php require "../includes/header.php"; ?>

<!-- Suche -->
<div class="px-4 py-2">
    <form action="index.php" method="get" id="searchForm">
        <?php if ($activeCategory > 0): ?>
            <input type="hidden" name="category" value="<?php echo $activeCategory; ?>">
        <?php endif; ?>
        <input type="text"
               id="searchInput"
               name="q"
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Suche nach Produkten..."
               class="w-full h-12 rounded-lg px-4 bg-white dark:bg-surface-dark">
    </form>
</div>

<!-- Kategorien -->
<div class="w-full overflow-x-auto no-scrollbar pb-2">
    <div class="flex gap-3 px-4 py-2 min-w-max">

        <a href="index.php<?php echo $search !== "" ? "?q=" . urlencode($search) : ""; ?>"
           class="flex h-9 items-center px-4 rounded-full text-sm font-medium border
           <?php echo $activeCategory === 0 ? "bg-primary text-black" : "bg-white dark:bg-surface-dark"; ?>">
            Alle
        </a>

        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?php echo $cat["id"]; ?><?php echo $search !== "" ? "&q=" . urlencode($search) : ""; ?>"
               class="flex h-9 items-center px-4 rounded-full text-sm font-medium border
               <?php echo $activeCategory === (int)$cat["id"]
                   ? "bg-primary text-black"
                   : "bg-white dark:bg-surface-dark"; ?>">
                <?php echo htmlspecialchars($cat["name"]); ?>
            </a>
        <?php endforeach; ?>

    </div>
</div>

<!-- Produkte -->
<div class="px-4 pb-4">
    <div id="productGridWrapper">
        <?php echo renderProductGrid($products, $favorites); ?>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
<?php require "../includes/bottom_nav.php"; ?>

<script>
(function () {
    const searchForm = document.getElementById("searchForm");
    const searchInput = document.getElementById("searchInput");
    const productGridWrapper = document.getElementById("productGridWrapper");
    const currentCategory = <?php echo json_encode($activeCategory); ?>;
    let debounceTimer = null;

    function buildQuery(includeAjax = false) {
        const params = new URLSearchParams();
        if (currentCategory > 0) {
            params.set("category", currentCategory);
        }
        const queryValue = searchInput ? searchInput.value.trim() : "";
        if (queryValue !== "") {
            params.set("q", queryValue);
        }
        if (includeAjax) {
            params.set("ajax", "1");
        }
        return params.toString();
    }

    async function refreshProducts() {
        if (!productGridWrapper) return;
        const ajaxQuery = buildQuery(true);
        const requestUrl = ajaxQuery ? `index.php?${ajaxQuery}` : "index.php";
        try {
            const response = await fetch(requestUrl, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            if (!response.ok) return;
            const html = await response.text();
            productGridWrapper.innerHTML = html;
            const cleanQuery = buildQuery(false);
            const historyUrl = cleanQuery ? `index.php?${cleanQuery}` : "index.php";
            if (window.history && window.history.replaceState) {
                history.replaceState(null, "", historyUrl);
            }
        } catch (error) {
            console.error("Fehler beim Laden der Suchergebnisse", error);
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(refreshProducts, 300);
        });
    }

    if (searchForm) {
        searchForm.addEventListener("submit", (event) => {
            event.preventDefault();
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            refreshProducts();
        });
    }
})();
</script>

</body>
</html>
