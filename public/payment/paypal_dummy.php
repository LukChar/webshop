<?php
session_start();
require_once __DIR__ . "/../../includes/db.php";

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    // Passe /webshop ggf. an deinen Projektordner an
    $redirect = "/webshop/public/payment/paypal_dummy.php?order_id=" . urlencode($orderId);
    header("Location: ../../auth/login.php?redirect=" . urlencode($redirect));
    exit;
}

$userId = (int)$_SESSION["user_id"];

/* Order prüfen */
$stmt = $pdo->prepare("SELECT id, user_id, status, total FROM orders WHERE id = ?");
$stmt->execute([(int)$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || (int)$order["user_id"] !== $userId || $order["status"] !== "pending") {
    header("Location: ../cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PayPal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>
        body { min-height: max(884px, 100dvh); }
    </style>
</head>

<body class="bg-[#f5f7fa] flex items-center justify-center min-h-screen p-4">

<div class="w-full max-w-md">
    <!-- Header -->
    <div class="text-center mb-6">
        <!-- "Logo" als Textmarke (wir vermeiden externe Assets) -->
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white shadow-sm">
            <span class="text-2xl font-extrabold tracking-tight text-[#003087]">Pay</span>
            <span class="text-2xl font-extrabold tracking-tight text-[#009cde]">Pal</span>
        </div>
        <p class="text-sm text-gray-500 mt-2">Sicher bezahlen</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/5 p-6">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-lg font-bold text-gray-900">Anmelden</h1>
            <span class="text-sm text-gray-500">
                <?= number_format((float)$order["total"], 2, ",", ".") ?> €
            </span>
        </div>

        <form id="payForm" action="paypal_success.php" method="post" class="space-y-4">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                <input
                    type="email"
                    name="email"
                    placeholder="E-Mail-Adresse"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-[#009cde] focus:ring-[#009cde] p-3"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Passwort</label>
                <input
                    type="password"
                    name="pw"
                    placeholder="Passwort"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-[#009cde] focus:ring-[#009cde] p-3"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-full bg-[#0070e0] hover:bg-[#005ea6] text-white font-bold py-3 transition">
                Weiter
            </button>
        </form>

        <form id="cancelForm" action="paypal_cancel.php" method="post" class="mt-3">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
            <button
                type="submit"
                class="w-full rounded-full border border-gray-300 hover:bg-gray-50 font-bold py-3 transition">
                Abbrechen
            </button>
        </form>

        <div class="mt-6 text-xs text-gray-500 leading-relaxed">
            Durch Klicken auf „Weiter“ wird der Bezahlvorgang fortgesetzt.
        </div>
    </div>

    <!-- Footer hint -->
    <div class="text-center text-xs text-gray-400 mt-4">
        © <?= date("Y") ?> PayPal
    </div>
</div>

</body>
</html>
