<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$orderId = (int)($_GET["id"] ?? 0);

if ($orderId <= 0) {
    echo "Ungültige Bestellung.";
    exit;
}

/* Bestellung prüfen (gehört sie dem User?) */
$stmt = $pdo->prepare("
    SELECT id, total, created_at
    FROM orders
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo "Bestellung nicht gefunden.";
    exit;
}

/* Bestellpositionen laden */
$stmt = $pdo->prepare("
    SELECT 
        oi.quantity,
        oi.price,
        p.name,
        p.image
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Bestellung #<?php echo $order["id"]; ?></title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>

<!-- Icons -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "background-dark": "#102216",
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

<body class="bg-background-light font-display text-slate-900 antialiased pb-24">

<!-- Top Bar -->
<div class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b">
    <div class="flex items-center px-4 py-3 justify-between max-w-md mx-auto">
        <a href="my_orders.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>

        <h2 class="text-lg font-bold flex-1 text-center pr-10">
            Bestellung #<?php echo $order["id"]; ?>
        </h2>
    </div>
</div>

<!-- Content -->
<div class="max-w-md mx-auto px-4 pt-4 flex flex-col gap-4">

    <div class="text-sm text-gray-500">
        Bestelldatum:
        <?php echo date("d.m.Y H:i", strtotime($order["created_at"])); ?>
    </div>

    <?php foreach ($items as $item): ?>

        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex gap-4">

                <div
                    class="w-[70px] h-[90px] rounded-lg bg-cover bg-center"
                    style="background-image:url('<?php echo htmlspecialchars($item["image"]); ?>');">
                </div>

                <div class="flex-1">
                    <p class="font-semibold leading-tight">
                        <?php echo htmlspecialchars($item["name"]); ?>
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        Menge: <?php echo $item["quantity"]; ?>
                    </p>

                    <p class="font-bold mt-2">
                        <?php echo number_format($item["price"], 2, ",", "."); ?> €
                    </p>
                </div>
            </div>
        </div>

    <?php endforeach; ?>

    <!-- Summary -->
    <div class="bg-white rounded-xl p-4 shadow-sm border mt-2">
        <div class="flex justify-between font-bold text-lg">
            <span>Gesamt</span>
            <span>
                <?php echo number_format($order["total"], 2, ",", "."); ?> €
            </span>
        </div>
    </div>

</div>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>