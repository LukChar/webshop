<?php
session_start();
require_once __DIR__ . "/../../includes/db.php";

$orderId = $_GET["order_id"] ?? null;
if (!$orderId) {
    header("Location: ../cart.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    $redirect = "/payment/card_dummy.php?order_id=" . urlencode($orderId);
    header("Location: ../../auth/login.php?redirect=" . urlencode($redirect));
    exit;
}

$userId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT id, user_id, status, total FROM orders WHERE id = ?");
$stmt->execute([(int)$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || (int)$order["user_id"] !== $userId) {
    header("Location: ../cart.php");
    exit;
}

if ($order["status"] !== "pending") {
    header("Location: ../my_orders.php");
    exit;
}

$paymentInfo = $_SESSION["checkout_payment"] ?? ["method" => "card"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([(int)$orderId]);
    unset($_SESSION["cart"], $_SESSION["checkout_address"], $_SESSION["checkout_payment"]);

    ?>
    <!DOCTYPE html>
    <html class="light" lang="de">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Bezahlung erfolgreich</title>
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        <style>body { min-height: max(884px, 100dvh); }</style>
    </head>
    <body class="bg-[#f6f8f6] font-display text-[#111813]">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-lg rounded-2xl bg-white border border-[#e5e7eb] p-8 text-center shadow-lg shadow-black/10">
            <div class="mb-4 text-4xl text-[#13ec5b]"><span class="material-symbols-outlined">check_circle</span></div>
            <h1 class="text-2xl font-bold text-[#111813]">Bezahlung abgeschlossen</h1>
            <p class="mt-3 text-sm text-gray-600">
                Die Bestellung <strong>#<?= htmlspecialchars($orderId) ?></strong> wurde erfolgreich abgeschlossen.
            </p>
            <a href="../index.php"
               class="mt-6 inline-flex items-center justify-center rounded-2xl bg-[#13ec5b] px-6 py-3 font-semibold text-[#0b1f12] shadow-md shadow-[#13ec5b]/40 transition hover:brightness-95">
                Zurück zum Shop
            </a>
        </div>
    </div>
    <?php require_once "../../includes/bottom_nav.php"; ?>
    </body>
    </html>
    <?php
    exit;
}

$formattedTotal = number_format((float)$order["total"], 2, ",", ".");
$cardLabel = "Kreditkarte";
if (!empty($paymentInfo["card_brand"]) && !empty($paymentInfo["card_last4"])) {
    $cardLabel = htmlspecialchars($paymentInfo["card_brand"]) . " •••• " . htmlspecialchars($paymentInfo["card_last4"]);
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kartenzahlung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>body { min-height: max(884px, 100dvh); }</style>
</head>
<body class="bg-[#f5f7fa] font-display text-[#111813]">
<div class="flex min-h-screen items-center justify-center px-4 py-6">
    <div class="w-full max-w-md space-y-6 rounded-3xl bg-white p-6 shadow-xl shadow-black/10">
        <div class="text-center space-y-1">
            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Kartenzahlung</p>
            <h1 class="text-2xl font-bold">Sichere Zahlung</h1>
        </div>

        <div class="rounded-2xl border border-black/5 bg-slate-50 p-4 space-y-3">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Bestellnummer</span>
                <strong>#<?= htmlspecialchars($orderId) ?></strong>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Betrag</span>
                <strong><?= $formattedTotal ?> €</strong>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Zahlungsmethode</span>
                <strong><?= $cardLabel ?></strong>
            </div>
        </div>

        <form method="post" class="space-y-3">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
            <button
                type="submit"
                class="w-full rounded-2xl bg-[#102216] px-4 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-white shadow-lg shadow-[#102216]/30 transition-colors hover:bg-black">
                Zahlung abschließen
            </button>
        </form>
        <p class="text-center text-xs text-gray-500">
            Keine Weitergabe von Zahlungsdaten. Dieser Schritt bestätigt deine Bestellung.
        </p>
    </div>
</div>
</body>
</html>
