<?php
session_start();
require_once "../includes/db.php";

/* ID prüfen */
$productId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($productId <= 0) {
    echo "Produkt nicht gefunden.";
    exit;
}

/* Produkt laden inkl. Bestand */
$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.name,
        p.price,
        p.image,
        p.description,
        COALESCE(s.quantity, 0) AS stock
    FROM products p
    LEFT JOIN stock s ON s.product_id = p.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Produkt nicht gefunden.";
    exit;
}
$stockQty = (int)$product["stock"];

/* Warenkorb-Anzahl */
$cartCount = isset($_SESSION["cart"]) ? array_sum($_SESSION["cart"]) : 0;

/* Sortierung (Whitelist + Mapping) */
$sort = $_GET["sort"] ?? "date_desc";
$sortMap = [
    "date_desc"   => "r.created_at DESC, r.id DESC",
    "date_asc"    => "r.created_at ASC, r.id ASC",
    "rating_desc" => "r.rating DESC, r.created_at DESC, r.id DESC",
    "rating_asc"  => "r.rating ASC, r.created_at DESC, r.id DESC",
];
if (!isset($sortMap[$sort])) $sort = "date_desc";
$orderBy = $sortMap[$sort];

/* Error msg */
$errMsg = isset($_GET["err"]) ? trim($_GET["err"]) : "";

/* Ratings Summary */
$stmt = $pdo->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt
    FROM reviews
    WHERE product_id = ?
");
$stmt->execute([$productId]);
$ratingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$avgRating = ($ratingInfo && (int)$ratingInfo["cnt"] > 0) ? (float)$ratingInfo["avg_rating"] : null;
$reviewCount = $ratingInfo ? (int)$ratingInfo["cnt"] : 0;

/* Rating Distribution (5..1) */
$dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

$stmt = $pdo->prepare("
    SELECT rating, COUNT(*) AS cnt
    FROM reviews
    WHERE product_id = ?
    GROUP BY rating
");
$stmt->execute([$productId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $r = (int)$row["rating"];
    $c = (int)$row["cnt"];
    if ($r >= 1 && $r <= 5) {
        $dist[$r] = $c;
    }
}

/* Reviews laden + Verified Purchase Flag */
$reviews = [];
if ($reviewCount > 0) {
    $stmt = $pdo->prepare("
        SELECT
            r.id,
            r.user_id,
            r.author_name,
            r.rating,
            r.text,
            r.helpful,
            r.created_at,
            EXISTS (
                SELECT 1
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.id
                WHERE o.user_id = r.user_id
                  AND oi.product_id = r.product_id
                  AND o.status = 'paid'
            ) AS verified_purchase
        FROM reviews r
        WHERE r.product_id = ?
        ORDER BY $orderBy
    ");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* Hilfsfunktionen */
function formatStars($avg) {
    $full = (int)floor($avg);
    $frac = $avg - $full;
    $half = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    $full = ($frac >= 0.75) ? $full + 1 : $full;
    $empty = 5 - $full - $half;
    return [$full, $half, $empty];
}
function pct($part, $total) {
    if ($total <= 0) return 0;
    return (int)round(($part / $total) * 100);
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($product["name"]); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>

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
                    fontFamily: { display: ["Inter", "sans-serif"] },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>

    <style>
        body { min-height: max(884px, 100dvh); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-white font-display pb-32 overflow-x-hidden transition-colors duration-200">

<!-- Sticky Header -->
<div class="sticky top-0 z-50 bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors">
    <div class="relative flex items-center p-4 h-16">

        <!-- 🔹 LINKS -->
        <a href="index.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors z-10">
            <span class="material-symbols-outlined text-[#111813] dark:text-white" style="font-size: 24px;">
                arrow_back_ios_new
            </span>
        </a>

        <!-- 🔹 ZENTRIERTER TITEL (KLICKBAR, BLOCKIERT NICHT) -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <a href="/index.php"
               class="text-xl font-bold select-none pointer-events-auto cursor-pointer">
                <span class="text-[#111813] dark:text-white">Campus</span>
                <span class="text-primary">Shop</span>
            </a>
        </div>

        <!-- 🔹 RECHTS -->
        <div class="ml-auto flex items-center gap-2 z-10">

            <a href="cart.php"
               class="relative flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-[#111813] dark:text-white" style="font-size: 24px;">
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


<!-- Product Image -->
<div class="relative w-full bg-surface-light dark:bg-surface-dark mb-4">
    <div class="flex w-full overflow-x-auto snap-x snap-mandatory no-scrollbar aspect-[4/5] md:aspect-video">
        <div class="snap-center shrink-0 w-full h-full bg-gray-200 relative">
            <div class="w-full h-full bg-center bg-cover bg-no-repeat"
                 style='background-image: url("<?php echo htmlspecialchars($product["image"]); ?>");'>
            </div>
        </div>
    </div>

    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
        <div class="w-2 h-2 rounded-full bg-primary shadow-sm"></div>
    </div>
</div>

<!-- Content -->
<div class="px-5 flex flex-col gap-6">

   <!-- Header Info -->
<div class="flex flex-col gap-2">
    <div class="flex justify-between items-start">
        <h1 class="text-[#111813] dark:text-white text-[28px] font-bold leading-tight tracking-tight">
            <?php echo htmlspecialchars($product["name"]); ?>
        </h1>
    </div>

    <?php if ($stockQty <= 0): ?>
        <div class="flex items-center gap-2 text-sm font-semibold text-red-600">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            Ausverkauft
        </div>
    <?php elseif ($stockQty <= 5): ?>
        <div class="flex items-center gap-2 text-sm font-semibold text-orange-500">
            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
            Geringer Bestand
        </div>
    <?php else: ?>
        <div class="flex items-center gap-2 text-sm font-semibold text-green-600">
            <span class="w-2 h-2 rounded-full bg-primary"></span>
            Auf Lager
        </div>
    <?php endif; ?>

    <?php if ($avgRating !== null): ?>
        <?php [$full, $half, $empty] = formatStars($avgRating); ?>
        <div class="flex items-center gap-2">
            <div class="flex text-yellow-500">
                <?php for ($i = 0; $i < $full; $i++): ?>
                    <span class="material-symbols-outlined text-[18px]"
                          style="font-variation-settings:'FILL' 1;">star</span>
                <?php endfor; ?>

                <?php if ($half === 1): ?>
                    <span class="material-symbols-outlined text-[18px]"
                          style="font-variation-settings:'FILL' 1;">star_half</span>
                <?php endif; ?>

                <?php for ($i = 0; $i < $empty; $i++): ?>
                    <span class="material-symbols-outlined text-[18px]"
                          style="font-variation-settings:'FILL' 1; opacity:0.25;">star</span>
                <?php endfor; ?>
            </div>

            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                <?php echo number_format($avgRating, 1, ",", "."); ?>
                (<?php echo $reviewCount; ?> Bewertungen)
            </span>
        </div>
    <?php else: ?>
        <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
            Noch keine Bewertungen
        </div>
    <?php endif; ?>
</div>

    <!-- Price Card -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-surface-light dark:bg-surface-dark p-5 shadow-sm">
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h2 class="text-[#111813] dark:text-white text-sm font-bold uppercase tracking-wider opacity-70">Preis</h2>
            </div>

            <div class="flex items-baseline gap-3">
                <span class="text-[#111813] dark:text-white text-4xl font-black tracking-tight">
                    <?php echo number_format((float)$product["price"], 2, ",", "."); ?> €
                </span>
            </div>

            <div class="h-px bg-gray-100 dark:bg-gray-800 my-1"></div>

            <div class="text-[13px] font-medium flex gap-2.5 items-center text-[#111813] dark:text-gray-300">
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">check_circle</span>
                    Gratis Versand ab 50€
                </div>

            <div class="text-[13px] font-medium flex gap-2.5 items-center text-[#111813] dark:text-gray-300">
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">check_circle</span>
                    Hergestellt in der EU
                </div>
            <div class="flex flex-col gap-2">
                
                <div class="text-[13px] font-medium flex gap-2.5 items-center text-[#111813] dark:text-gray-300">
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">check_circle</span>
                    Sichere Zahlung
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="flex flex-col gap-3">
        <h3 class="text-lg font-bold text-[#111813] dark:text-white">Beschreibung</h3>
        <p class="text-gray-600 dark:text-gray-300 text-base leading-relaxed">
            <?php echo nl2br(htmlspecialchars($product["description"] ?? "")); ?>
        </p>
    </div>

    <div class="h-px w-full bg-gray-200 dark:bg-gray-800"></div>

    <!-- Ratings & Reviews -->
    <div class="flex flex-col gap-4" id="reviews">

        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-bold text-[#111813] dark:text-white">Bewertungen</h3>

            <!-- Sort Dropdown -->
            <form method="get" action="product.php#reviews" class="flex items-center gap-2 shrink-0">
                <input type="hidden" name="id" value="<?php echo (int)$productId; ?>">
                <select name="sort"
                        class="h-10 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-surface-dark text-sm px-2"
                        onchange="this.form.submit()">
                    <option value="date_desc" <?php echo $sort==="date_desc" ? "selected" : ""; ?>>Neueste</option>
                    <option value="date_asc" <?php echo $sort==="date_asc" ? "selected" : ""; ?>>Älteste</option>
                    <option value="rating_desc" <?php echo $sort==="rating_desc" ? "selected" : ""; ?>>Beste Bewertung</option>
                    <option value="rating_asc" <?php echo $sort==="rating_asc" ? "selected" : ""; ?>>Schlechteste Bewertung</option>
                </select>
            </form>
        </div>

        <!-- Rating Summary Card -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-x-8 gap-y-6">
                <div class="flex flex-col gap-1 items-center justify-center min-w-[80px]">
                    <?php if ($avgRating !== null): ?>
                        <p class="text-[#111813] dark:text-white text-5xl font-black leading-none tracking-tight">
                            <?php echo number_format($avgRating, 1, ".", ""); ?>
                        </p>

                        <?php [$full, $half, $empty] = formatStars($avgRating); ?>
                        <div class="flex text-yellow-500 text-sm">
                            <?php for ($i=0; $i<$full; $i++): ?>
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <?php endfor; ?>
                            <?php if ($half === 1): ?>
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star_half</span>
                            <?php endif; ?>
                            <?php for ($i=0; $i<$empty; $i++): ?>
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1; opacity: 0.25;">star</span>
                            <?php endfor; ?>
                        </div>

                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                            <?php echo (int)$reviewCount; ?> reviews
                        </p>
                    <?php else: ?>
                        <p class="text-[#111813] dark:text-white text-5xl font-black leading-none tracking-tight">–</p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">0 reviews</p>
                    <?php endif; ?>
                </div>

                <div class="grid flex-1 grid-cols-[12px_1fr_30px] items-center gap-y-2 gap-x-3">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <?php $p = pct($dist[$star], $reviewCount); ?>
                        <p class="text-gray-600 dark:text-gray-400 text-xs font-medium"><?php echo $star; ?></p>

                        <div class="flex h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="rounded-full bg-primary" style="width: <?php echo $p; ?>%;"></div>
                        </div>

                        <p class="text-gray-500 dark:text-gray-400 text-xs text-right"><?php echo $p; ?>%</p>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <?php if ($errMsg): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 text-sm">
                <?php echo htmlspecialchars($errMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Toggle Button + Collapsible Review Form -->
        <?php if (isset($_SESSION["user_id"])): ?>

            <button
                id="toggleReviewBtn"
                type="button"
                aria-expanded="false"
                aria-controls="reviewFormWrap"
                class="h-12 rounded-xl bg-primary font-bold text-[#111813] active:scale-[0.99] flex items-center justify-center gap-2"
            >
                <span class="material-symbols-outlined" style="font-size:22px;">edit</span>
                Rezension schreiben
            </button>

            <div id="reviewFormWrap" class="hidden">
                <form action="reviews/reviews_add.php" method="post"
                      class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 border border-gray-200 dark:border-gray-700 flex flex-col gap-3">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">

                    <div class="text-sm font-bold text-[#111813] dark:text-white">
                        Deine Rezension
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm font-semibold block mb-1">Name *</label>
                            <input name="author_name" required minlength="2" maxlength="100"
                                   class="w-full rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-[#14281b] p-2"
                                   placeholder="Vorname & Nachname">
                        </div>

                        <!-- Stern-Rating (JS) -->
                        <div>
                            <label class="text-sm font-semibold block mb-1">Bewertung (1–5) *</label>

                            <input type="hidden" name="rating" id="ratingInput" required>

                            <div class="flex items-center gap-1" id="starRating" aria-label="Bewertung auswählen">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button"
                                            data-value="<?php echo $i; ?>"
                                            class="starBtn text-gray-300 hover:text-yellow-500 transition-colors"
                                            title="<?php echo $i; ?> Sterne">
                                        <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">
                                            star
                                        </span>
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <p class="text-xs text-gray-500 mt-1" id="ratingHint">Bitte Sterne auswählen</p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold block mb-1">Text *</label>
                            <textarea name="text" required minlength="3" rows="3"
                                      class="w-full rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-[#14281b] p-2"
                                      placeholder="Schreibe deine Rezension..."></textarea>
                        </div>
                    </div>

                    <button class="h-12 rounded-xl bg-primary font-bold text-[#111813] active:scale-[0.99]">
                        Rezension speichern
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">
                Bitte einloggen, um eine Rezension zu schreiben.
                <a class="text-primary font-bold" href="../auth/login.php">Login</a>
            </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">
                Für dieses Produkt gibt es noch keine Bewertungen.
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 border border-gray-200 dark:border-gray-700 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="size-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-bold">
                                <?php echo htmlspecialchars(strtoupper(substr($r["author_name"], 0, 1))); ?>
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="text-sm font-bold"><?php echo htmlspecialchars($r["author_name"]); ?></span>
                                <?php if ((int)$r["verified_purchase"] === 1): ?>
                                    <span class="text-xs font-bold text-primary">Verifizierter Kauf</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">
                            <?php echo htmlspecialchars(date("d.m.Y", strtotime($r["created_at"]))); ?>
                        </span>
                    </div>

                    <div class="flex text-yellow-500 text-xs">
                        <?php for ($i=0; $i<(int)$r["rating"]; $i++): ?>
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
                        <?php endfor; ?>
                        <?php for ($i=(int)$r["rating"]; $i<5; $i++): ?>
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1; opacity: 0.25;">star</span>
                        <?php endfor; ?>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        <?php echo htmlspecialchars($r["text"] ?? ""); ?>
                    </p>

                    <!-- Helpful -->
                    <div class="flex items-center justify-between pt-2">
                        <form action="reviews/reviews_helpful.php" method="post">
                            <input type="hidden" name="review_id" value="<?php echo (int)$r["id"]; ?>">
                            <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                            <button class="text-sm font-bold underline underline-offset-4 decoration-primary">
                                Hilfreich
                            </button>
                        </form>
                        <span class="text-xs text-gray-500"><?php echo (int)$r["helpful"]; ?> x hilfreich</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>

<!-- Bottom Action -->
<div class="fixed bottom-0 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t border-gray-200 dark:border-gray-800 z-40 pb-safe">
    <form action="cart_add.php" method="post" class="flex gap-4 w-full max-w-2xl mx-auto">
        <input type="hidden" name="product_id" value="<?php echo (int)$product["id"]; ?>">
        <input type="hidden" name="quantity" id="qtyInput" value="1">

        <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 h-12">
            <button type="button" id="qtyMinus" class="size-12 flex items-center justify-center text-gray-500 dark:text-gray-400 active:text-primary">
                <span class="material-symbols-outlined">remove</span>
            </button>
            <span id="qtyText" class="w-8 text-center font-bold text-[#111813] dark:text-white">1</span>
            <button type="button" id="qtyPlus" class="size-12 flex items-center justify-center text-gray-500 dark:text-gray-400 active:text-primary">
                <span class="material-symbols-outlined">add</span>
            </button>
        </div>

        <button type="submit"
            <?php if ($stockQty <= 0): ?>disabled<?php endif; ?>
                class="flex-1 font-bold text-base rounded-lg shadow-lg flex items-center justify-center gap-2 h-12
            <?php echo $stockQty <= 0
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-primary active:bg-[#0fd650] text-[#111813] shadow-primary/20 active:scale-[0.98]';
            ?>"
        >
    <span class="material-symbols-outlined">shopping_bag</span>
    In den Warenkorb
</button>
    </form>
    <div class="h-1"></div>
</div>

<script>
    // Quantity Stepper
    const qtyText = document.getElementById("qtyText");
    const qtyInput = document.getElementById("qtyInput");
    const minus = document.getElementById("qtyMinus");
    const plus = document.getElementById("qtyPlus");

    let qty = 1;
    function renderQty() {
        qtyText.textContent = String(qty);
        qtyInput.value = String(qty);
    }
    if (minus && plus) {
        minus.addEventListener("click", () => { qty = Math.max(1, qty - 1); renderQty(); });
        plus.addEventListener("click", () => { qty = Math.min(99, qty + 1); renderQty(); });
        renderQty();
    }

    // Review Form Toggle (single toggle button)
    const toggleBtn = document.getElementById("toggleReviewBtn");
    const formWrap = document.getElementById("reviewFormWrap");

    function openReviewForm() {
        if (!toggleBtn || !formWrap) return;
        formWrap.classList.remove("hidden");
        toggleBtn.setAttribute("aria-expanded", "true");
        toggleBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:22px;">close</span> Rezension schließen';

        const firstInput = formWrap.querySelector('input[name="author_name"]');
        if (firstInput) firstInput.focus({ preventScroll: true });
    }

    function closeReviewForm() {
        if (!toggleBtn || !formWrap) return;
        formWrap.classList.add("hidden");
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:22px;">edit</span> Rezension schreiben';
    }

    if (toggleBtn && formWrap) {
        toggleBtn.addEventListener("click", () => {
            const isOpen = toggleBtn.getAttribute("aria-expanded") === "true";
            if (isOpen) closeReviewForm();
            else openReviewForm();
        });
    }

    // Auto-open if error present
    const hasErr = <?php echo $errMsg ? "true" : "false"; ?>;
    if (hasErr) {
        openReviewForm();
    }

    // Star Rating (1–5)
    const starWrap = document.getElementById("starRating");
    const starBtns = starWrap ? starWrap.querySelectorAll(".starBtn") : [];
    const ratingInput = document.getElementById("ratingInput");
    const ratingHint = document.getElementById("ratingHint");

    function paintStars(value) {
        starBtns.forEach(btn => {
            const v = Number(btn.dataset.value);
            btn.classList.toggle("text-yellow-500", v <= value);
            btn.classList.toggle("text-gray-300", v > value);
        });
    }

    starBtns.forEach(btn => {
        btn.addEventListener("mouseenter", () => {
            const v = Number(btn.dataset.value);
            paintStars(v);
        });
        btn.addEventListener("focus", () => {
            const v = Number(btn.dataset.value);
            paintStars(v);
        });
        btn.addEventListener("click", () => {
            const v = Number(btn.dataset.value);
            if (ratingInput) ratingInput.value = String(v);
            paintStars(v);
            if (ratingHint) ratingHint.textContent = v + " von 5 Sternen";
        });
    });

    if (starWrap) {
        starWrap.addEventListener("mouseleave", () => {
            const current = Number((ratingInput && ratingInput.value) ? ratingInput.value : 0);
            paintStars(current);
        });
    }
</script>

</body>
</html>
