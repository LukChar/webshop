<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT *
    FROM addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, created_at DESC
");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cards = [];
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM payments
        WHERE user_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$userId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cards = [];
}

$selectedAddressId = isset($_POST["address_id"]) ? (int)$_POST["address_id"] : ($_SESSION["checkout_address"]["address_id"] ?? 0);
$selectedCardId = isset($_POST["payment_id"]) ? (int)$_POST["payment_id"] : ($_SESSION["checkout_payment"]["payment_id"] ?? 0);

if ($selectedAddressId === 0 && !empty($addresses)) {
    $selectedAddressId = (int)$addresses[0]["id"];
}
$paymentMethod = $_POST["payment_method"] ?? "card";

$manualAddress = [
    "name"   => trim($_POST["manual_name"] ?? ($_SESSION["checkout_address"]["name"] ?? "")),
    "street" => trim($_POST["manual_street"] ?? ($_SESSION["checkout_address"]["street"] ?? "")),
    "zip"    => trim($_POST["manual_zip"] ?? ($_SESSION["checkout_address"]["postal_code"] ?? "")),
    "city"   => trim($_POST["manual_city"] ?? ($_SESSION["checkout_address"]["city"] ?? "")),
    "note"   => trim($_POST["manual_note"] ?? ($_SESSION["checkout_address"]["note"] ?? "")),
];

$manualCard = [
    "holder" => trim($_POST["card_holder"] ?? ($_SESSION["checkout_payment"]["card_holder"] ?? "")),
    "number" => trim($_POST["card_number"] ?? ""),
    "month"  => trim($_POST["expiry_month"] ?? ($_SESSION["checkout_payment"]["expiry_month"] ?? "")),
    "year"   => trim($_POST["expiry_year"] ?? ($_SESSION["checkout_payment"]["expiry_year"] ?? "")),
];

$error = null;
$orderTotal = 0.0;
foreach ($cart as $productId => $qty) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([(int)$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        continue;
    }
    $orderTotal += ((float)$product["price"]) * (int)$qty;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $addressData = null;

    if ($selectedAddressId > 0) {
        foreach ($addresses as $addr) {
            if ((int)$addr["id"] === $selectedAddressId) {
                $addressData = [
                    "address_id" => (int)$addr["id"],
                    "name"       => trim($addr["first_name"] . " " . $addr["last_name"]),
                    "street"     => $addr["street"],
                    "postal_code"=> $addr["postal_code"],
                    "city"       => $addr["city"],
                    "note"       => "",
                ];
                break;
            }
        }
    }

    if (!$addressData && $manualAddress["name"] !== "" && $manualAddress["street"] !== "" && $manualAddress["zip"] !== "" && $manualAddress["city"] !== "") {
        $addressData = [
            "address_id" => null,
            "name"       => $manualAddress["name"],
            "street"     => $manualAddress["street"],
            "postal_code"=> $manualAddress["zip"],
            "city"       => $manualAddress["city"],
            "note"       => $manualAddress["note"],
        ];
    }

    if (!$addressData) {
        $error = "Bitte wähle eine Lieferadresse oder trage eine neue Adresse ein.";
    }

    $paymentData = null;
    if ($paymentMethod === "card") {
        $selectedCard = null;
        if ($selectedCardId > 0) {
            foreach ($cards as $card) {
                if ((int)$card["id"] === $selectedCardId) {
                    $selectedCard = $card;
                    break;
                }
            }
        }

        if ($selectedCard) {
            $paymentData = [
                "method"     => "card",
                "payment_id" => (int)$selectedCard["id"],
                "card_holder"=> $selectedCard["card_holder"],
                "card_brand" => $selectedCard["card_brand"],
                "card_last4" => $selectedCard["card_last4"],
                "expiry_month" => $selectedCard["expiry_month"],
                "expiry_year"  => $selectedCard["expiry_year"],
                "type"       => "saved",
            ];
        } elseif ($manualCard["holder"] !== "" && preg_match('/\d{4,}/', $manualCard["number"]) && $manualCard["month"] !== "" && $manualCard["year"] !== "") {
            $sanitizedNumber = preg_replace('/\D/', '', $manualCard["number"]);
            $paymentData = [
                "method"     => "card",
                "payment_id" => null,
                "card_holder"=> $manualCard["holder"],
                "card_brand" => "Kreditkarte",
                "card_last4" => substr($sanitizedNumber, -4),
                "expiry_month" => $manualCard["month"],
                "expiry_year"  => $manualCard["year"],
                "type"       => "manual",
            ];
        } else {
            if (!$error) {
                $error = "Bitte wähle eine gespeicherte Karte oder gib neue Kartendaten ein.";
            }
        }
    } elseif ($paymentMethod === "paypal") {
        $paymentData = ["method" => "paypal"];
    }

    if (!$error && $addressData && $paymentData) {
        $total = 0.0;
        foreach ($cart as $productId => $qty) {
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([(int)$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                continue;
            }
            $total += ((float)$product["price"]) * (int)$qty;
        }

        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total, status)
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$userId, $total]);
        $orderId = $pdo->lastInsertId();

        if (!$orderId) {
            $error = "Fehler: Bestellung konnte nicht gespeichert werden.";
        } else {
            foreach ($cart as $productId => $qty) {
                $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([(int)$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$product) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    (int)$orderId,
                    (int)$productId,
                    (int)$qty,
                    (float)$product["price"],
                ]);
            }

            $_SESSION["checkout_address"] = $addressData;
            $_SESSION["checkout_payment"] = $paymentData;

            if ($paymentData["method"] === "paypal") {
                header("Location: payment/paypal_dummy.php?order_id=" . urlencode($orderId));
                exit;
            }

            header("Location: payment/card_dummy.php?order_id=" . urlencode($orderId));
            exit;
        }
    }
}

$selectedCard = null;
if ($selectedCardId > 0) {
    foreach ($cards as $card) {
        if ((int)$card["id"] === $selectedCardId) {
            $selectedCard = $card;
            break;
        }
    }
}

$selectedCardId = $selectedCard["id"] ?? (count($cards) ? $cards[0]["id"] : 0);

function formatAddress(array $addr): string {
    $lines = [];
    if (!empty($addr["first_name"]) || !empty($addr["last_name"])) {
        $lines[] = trim(($addr["first_name"] ?? "") . " " . ($addr["last_name"] ?? ""));
    }
    if (!empty($addr["street"])) {
        $lines[] = $addr["street"];
    }
    $cityLine = trim(($addr["postal_code"] ?? "") . " " . ($addr["city"] ?? ""));
    if ($cityLine !== "") {
        $lines[] = $cityLine;
    }
    if (!empty($addr["country"])) {
        $lines[] = $addr["country"];
    }
    return implode("<br>", $lines);
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lieferadresse &amp; Zahlung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                        "surface-dark": "#1c3326",
                        "border-light": "#e5e7eb",
                        "border-dark": "#2d4234",
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
<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-gray-100 font-display transition-colors duration-200">
<div class="relative flex h-full min-h-screen w-full flex-col overflow-x-hidden mx-auto max-w-5xl bg-background-light dark:bg-background-dark px-4 md:px-8">

    <header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md py-4 px-4 border-b border-border-light dark:border-border-dark">
        <a href="cart.php" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[#111813] dark:text-white">arrow_back</span>
        </a>
        <h1 class="text-lg font-bold text-center flex-1">Zahlung &amp; Adresse</h1>
        <div class="w-10"></div>
    </header>

    <form method="post" class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 pb-24 pt-4">

        <?php if ($error): ?>
            <div class="md:col-span-2 rounded-2xl border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="space-y-3 md:col-span-1">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#111813] dark:text-white">Lieferadresse</h2>
                <a href="addresses.php" class="text-sm text-primary font-semibold">Adressen verwalten</a>
            </div>

            <?php if (empty($addresses)): ?>
                <p class="text-sm text-gray-600">Du hast noch keine gespeicherte Lieferadresse. Lege eine neue in deinem Profil an oder nutze das Formular unten.</p>
            <?php endif; ?>

            <?php foreach ($addresses as $addr): ?>
                <?php $isChecked = (int)$addr["id"] === $selectedAddressId; ?>
                <label class="relative block">
                    <input type="radio"
                           name="address_id"
                           value="<?= htmlspecialchars($addr["id"]) ?>"
                           class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0"
                           <?= $isChecked ? "checked" : "" ?>>
                    <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4 transition duration-200 hover:border-gray-300 peer-checked:border-primary peer-checked:shadow-[0_0_20px_rgba(19,236,91,0.25)]">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <p class="font-semibold text-[#111813] dark:text-white">
                                    <?= htmlspecialchars(trim($addr["first_name"] . " " . $addr["last_name"])) ?>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <?= formatAddress($addr) ?>
                                </p>
                            </div>
                            <a href="address_edit.php?id=<?= htmlspecialchars($addr["id"]) ?>"
                               class="text-xs text-primary font-semibold whitespace-nowrap">Bearbeiten</a>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                            <span><?= htmlspecialchars($addr["created_at"] ?? "") ?></span>
                            <span class="text-transparent peer-checked:text-primary font-bold">Ausgewählt</span>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>

            <div class="space-y-3">
                <button type="button"
                        id="toggle-temp-address"
                        class="w-full rounded-2xl border border-border-light bg-surface-light dark:bg-surface-dark px-4 py-3 text-sm font-semibold flex items-center justify-between hover:border-primary transition">
                    <span>Andere Adresse verwenden</span>
                    <span class="material-symbols-outlined text-gray-500" id="icon-temp-address">expand_more</span>
                </button>

                <div id="temp-address-panel" class="space-y-3 hidden">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Andere Adresse</p>
                    <div class="grid grid-cols-2 gap-3">
                        <input
                            type="text"
                            name="manual_name"
                            value="<?= htmlspecialchars($manualAddress["name"]) ?>"
                            placeholder="Name"
                            class="h-12 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                        <input
                            type="text"
                            name="manual_street"
                            value="<?= htmlspecialchars($manualAddress["street"]) ?>"
                            placeholder="Straße &amp; Nr."
                            class="h-12 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary col-span-2"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input
                            type="text"
                            name="manual_zip"
                            value="<?= htmlspecialchars($manualAddress["zip"]) ?>"
                            placeholder="PLZ"
                            class="h-12 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                        <input
                            type="text"
                            name="manual_city"
                            value="<?= htmlspecialchars($manualAddress["city"]) ?>"
                            placeholder="Ort"
                            class="h-12 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                    </div>
                    <textarea
                        name="manual_note"
                        rows="2"
                        placeholder="Notiz (optional)"
                        class="w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary resize-none"><?= htmlspecialchars($manualAddress["note"]) ?></textarea>
                </div>
            </div>
        </section>

        <section class="space-y-3 md:col-span-1">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#111813] dark:text-white">Zahlungsmethode</h2>
                <a href="payments.php" class="text-sm text-primary font-semibold">Speicherbare Karten</a>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-[#61896f]">PayPal</p>
                    <p class="text-sm text-gray-500">Direkt zur PayPal-Zahlung</p>
                </div>
                <button
                    type="submit"
                    name="payment_method"
                    value="paypal"
                    class="w-full h-14 bg-[#009cde] text-white font-semibold rounded-2xl shadow-lg shadow-[#009cde]/30 flex items-center justify-center gap-3">
                    <span class="material-symbols-outlined">payments</span>
                    Mit PayPal fortfahren
                </button>
            </div>

            <div class="space-y-3">
                <?php foreach ($cards as $card): ?>
                    <?php $isCardActive = (int)$card["id"] === $selectedCardId; ?>
                    <label class="relative block">
                        <input type="radio"
                               name="payment_id"
                               value="<?= htmlspecialchars($card["id"]) ?>"
                               class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0"
                               <?= $isCardActive ? "checked" : "" ?>>
                        <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4 transition duration-200 hover:border-gray-300 peer-checked:border-primary peer-checked:shadow-[0_0_20px_rgba(19,236,91,0.25)]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold"><?= htmlspecialchars($card["card_brand"]) ?> •••• <?= htmlspecialchars($card["card_last4"]) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($card["card_holder"]) ?> · <?= sprintf("%02d/%s", $card["expiry_month"], $card["expiry_year"]) ?></p>
                                </div>
                                <?php if ((int)$card["is_default"] === 1): ?>
                                    <span class="px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-primary bg-primary/10 rounded-full">Standard</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 flex justify-between">
                                <span class="text-transparent peer-checked:text-primary font-semibold">Ausgewählt</span>
                                <span class="text-gray-400">gespeichert</span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="space-y-3">
                <button type="button"
                        id="toggle-temp-card"
                        class="w-full rounded-2xl border border-border-light bg-surface-light dark:bg-surface-dark px-4 py-3 text-sm font-semibold flex items-center justify-between hover:border-primary transition">
                    <span>Andere Karte verwenden</span>
                    <span class="material-symbols-outlined text-gray-500" id="icon-temp-card">expand_more</span>
                </button>

                <div id="temp-card-panel" class="space-y-3 hidden">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Neue Karte (optional)</p>
                    <input
                        type="text"
                        name="card_holder"
                        value="<?= htmlspecialchars($manualCard["holder"]) ?>"
                        placeholder="Karteninhaber*in"
                        class="h-12 w-full rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                    <input
                        type="text"
                        name="card_number"
                        value="<?= htmlspecialchars($manualCard["number"]) ?>"
                        placeholder="Kartennummer"
                        class="h-12 w-full rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                    >
                    <div class="flex gap-3">
                        <input
                            type="text"
                            name="expiry_month"
                            value="<?= htmlspecialchars($manualCard["month"]) ?>"
                            placeholder="MM"
                            class="h-12 flex-1 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                        <input
                            type="text"
                            name="expiry_year"
                            value="<?= htmlspecialchars($manualCard["year"]) ?>"
                            placeholder="JJJJ"
                            class="h-12 flex-1 rounded-2xl border border-border-light bg-background-light px-3 text-sm text-[#111813] focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                    </div>
                </div>
            </div>
        </section>

        <div class="md:col-span-2 space-y-3">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Gesamtbetrag</span>
                <strong><?= number_format($orderTotal, 2, ",", ".") ?> €</strong>
            </div>
            <button
                type="submit"
                name="payment_method"
                value="card"
                class="w-full h-14 bg-primary text-[#102216] font-semibold rounded-2xl shadow-lg shadow-primary/40 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">credit_card</span>
                Kartenzahlung fortsetzen
            </button>
        </div>

    </form>

    <?php require_once "../includes/bottom_nav.php"; ?>

</div>

<script>
    (function() {
        const tempAddrBtn = document.getElementById("toggle-temp-address");
        const tempAddrPanel = document.getElementById("temp-address-panel");
        const tempAddrIcon = document.getElementById("icon-temp-address");

        if (tempAddrBtn && tempAddrPanel && tempAddrIcon) {
            tempAddrBtn.addEventListener("click", () => {
                const isHidden = tempAddrPanel.classList.contains("hidden");
                tempAddrPanel.classList.toggle("hidden");
                tempAddrIcon.textContent = isHidden ? "expand_less" : "expand_more";
            });
        }

        const tempCardBtn = document.getElementById("toggle-temp-card");
        const tempCardPanel = document.getElementById("temp-card-panel");
        const tempCardIcon = document.getElementById("icon-temp-card");

        if (tempCardBtn && tempCardPanel && tempCardIcon) {
            tempCardBtn.addEventListener("click", () => {
                const isHidden = tempCardPanel.classList.contains("hidden");
                tempCardPanel.classList.toggle("hidden");
                tempCardIcon.textContent = isHidden ? "expand_less" : "expand_more";
            });
        }
    })();
</script>
</body>
</html>
