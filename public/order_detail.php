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

$orderId = (int)($_GET["id"] ?? 0);
if ($orderId <= 0) {
    echo "Ungültige Bestellung.";
    exit;
}

$isAdmin = ($_SESSION["role"] ?? "") === "admin";

/* Bestellung laden */
if ($isAdmin) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.email
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderId]);
} else {
    $stmt = $pdo->prepare("
        SELECT *
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderId, $_SESSION["user_id"]]);
}

$order = $stmt->fetch();
if (!$order) {
    echo "Bestellung nicht gefunden.";
    exit;
}

/* Lieferstatus ändern (Admin) */
if ($isAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $itemId = (int)($_POST["item_id"] ?? 0);
    $status = $_POST["delivery_status"] ?? "";

    $allowed = ["neu", "in_bearbeitung", "versendet", "zugestellt"];
    if ($itemId > 0 && in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare("
            UPDATE order_items
            SET delivery_status = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $itemId]);
    }

    header("Location: order_detail.php?id=" . $orderId);
    exit;
}

/* Bestellpositionen */
$stmt = $pdo->prepare("
    SELECT 
        oi.id,
        oi.quantity,
        oi.price,
        oi.delivery_status,
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
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Bestellung #<?php echo $order["id"]; ?></title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-[#f6f8f6] font-display pb-28">

<!-- Header -->
<header class="sticky top-0 z-10 bg-white border-b">
    <div class="max-w-md mx-auto flex items-center p-4 gap-4">
        <a href="<?php echo $isAdmin ? '/webshop/admin/orders.php' : 'my_orders.php'; ?>"
           class="h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100">
            ←
        </a>
        <h1 class="text-lg font-bold">
            Bestellung #<?php echo $order["id"]; ?>
        </h1>
    </div>
</header>

<main class="max-w-md mx-auto px-4 pt-4 space-y-4">

<?php foreach ($items as $item): ?>

    <?php
    $statusColors = [
        "neu" => "bg-red-100 text-red-700",
        "in_bearbeitung" => "bg-yellow-100 text-yellow-700",
        "versendet" => "bg-blue-100 text-blue-700",
        "zugestellt" => "bg-green-100 text-green-700",
    ];
    ?>

    <div class="bg-white rounded-xl p-4 shadow-sm border">

        <div class="flex gap-4">
            <div
                class="w-[70px] h-[90px] rounded-lg bg-cover bg-center"
                style="background-image:url('<?php echo htmlspecialchars($item["image"]); ?>');">
            </div>

            <div class="flex-1 space-y-1">
                <p class="font-semibold">
                    <?php echo htmlspecialchars($item["name"]); ?>
                </p>
                <p class="text-sm text-gray-500">
                    Menge: <?php echo $item["quantity"]; ?>
                </p>
                <p class="font-bold">
                    <?php echo number_format($item["price"], 2, ",", "."); ?> €
                </p>
            </div>
        </div>

        <!-- Status -->
        <div class="mt-3 flex items-center justify-between">
            <span class="text-xs font-semibold px-3 py-1 rounded-full <?php echo $statusColors[$item["delivery_status"]] ?? ''; ?>">
                <?php echo strtoupper(str_replace("_", " ", $item["delivery_status"])); ?>
            </span>

            <?php if ($isAdmin): ?>
                <form method="post">
                    <input type="hidden" name="item_id" value="<?php echo $item["id"]; ?>">
                    <select name="delivery_status"
                            onchange="this.form.submit()"
                            class="text-sm rounded-lg border px-2 py-1">
                        <option value="neu" <?php if ($item["delivery_status"]==="neu") echo "selected"; ?>>Neu</option>
                        <option value="in_bearbeitung" <?php if ($item["delivery_status"]==="in_bearbeitung") echo "selected"; ?>>In Bearbeitung</option>
                        <option value="versendet" <?php if ($item["delivery_status"]==="versendet") echo "selected"; ?>>Versendet</option>
                        <option value="zugestellt" <?php if ($item["delivery_status"]==="zugestellt") echo "selected"; ?>>Zugestellt</option>
                    </select>
                </form>
            <?php endif; ?>
        </div>

    </div>

<?php endforeach; ?>

<!-- Summary -->
<div class="bg-white rounded-xl p-4 shadow-sm border mt-4">
    <div class="flex justify-between font-bold text-lg">
        <span>Gesamt</span>
        <span><?php echo number_format($order["total"], 2, ",", "."); ?> €</span>
    </div>
</div>

</main>

</body>
</html>