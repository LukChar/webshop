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
    $name   = trim($_POST["name"] ?? "");
    $street = trim($_POST["street"] ?? "");
    $zip    = trim($_POST["zip"] ?? "");
    $city   = trim($_POST["city"] ?? "");
    $note   = trim($_POST["note"] ?? "");

    if ($name === "" || $street === "" || $zip === "" || $city === "") {
        $error = "Bitte fülle alle Pflichtfelder aus.";
    } else {

        $userId = (int)$_SESSION["user_id"];
        $total  = 0.0;

        /* Gesamtbetrag berechnen */
        foreach ($cart as $productId => $qty) {
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([(int)$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                continue;
            }
            $total += ((float)$product["price"]) * (int)$qty;
        }

        try {
            $pdo->beginTransaction();

            /* Bestellung anlegen */
            $stmt = $pdo->prepare(
                "INSERT INTO orders (user_id, total, status)
                 VALUES (?, ?, 'pending')"
            );
            $stmt->execute([$userId, $total]);
            $orderId = (int)$pdo->lastInsertId();

            if (!$orderId) {
                throw new Exception("Bestellung konnte nicht gespeichert werden.");
            }

            /* Bestellpositionen + Stock */
            foreach ($cart as $productId => $qty) {
                $productId = (int)$productId;
                $qty = (int)$qty;

                /* Bestand prüfen & sperren */
                $stmt = $pdo->prepare("
                    SELECT quantity
                    FROM stock
                    WHERE product_id = ?
                    FOR UPDATE
                ");
                $stmt->execute([$productId]);
                $stock = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$stock || $stock["quantity"] < $qty) {
                    throw new Exception("Nicht genügend Lagerbestand für ein Produkt.");
                }

                /* Preis laden */
                $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception("Produkt nicht gefunden.");
                }

                /* Order Item */
                $stmt = $pdo->prepare(
                    "INSERT INTO order_items (order_id, product_id, quantity, price)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([
                    $orderId,
                    $productId,
                    $qty,
                    (float)$product["price"]
                ]);

                /* Stock reduzieren */
                $stmt = $pdo->prepare("
                    UPDATE stock
                    SET quantity = quantity - ?
                    WHERE product_id = ?
                ");
                $stmt->execute([$qty, $productId]);
            }

            $pdo->commit();

            /* Lieferadresse (Demo) in Session speichern */
            $_SESSION["checkout_address"] = [
                "order_id" => $orderId,
                "name"     => $name,
                "street"   => $street,
                "zip"      => $zip,
                "city"     => $city,
                "note"     => $note,
            ];

            if ($paymentData["method"] === "paypal") {
                header("Location: payment/paypal_dummy.php?order_id=" . urlencode($orderId));
                exit;
            }

            header("Location: payment/card_dummy.php?order_id=" . urlencode($orderId));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
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
    <title>Lieferadresse</title>

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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100 flex flex-col h-[100dvh] overflow-x-hidden antialiased">

<header class="shrink-0 px-4 py-4 flex items-center justify-between">
    <a href="cart.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">Lieferadresse</h1>

    <div class="size-10"></div>
</header>

<main class="flex-1 overflow-y-auto px-4 pb-44">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-5 shadow-sm">

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section class="space-y-3">
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

            <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input name="name" required
                       class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
            </div>
        </section>

            <div>
                <label class="block text-sm font-medium mb-1">Straße & Hausnr. *</label>
                <input name="street" required
                       class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">PLZ *</label>
                    <input name="zip" required
                           class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
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

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Ort *</label>
                    <input name="city" required
                           class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
                </div>
            </div>
        </section>

            <div>
                <label class="block text-sm font-medium mb-1">Anmerkung (optional)</label>
                <textarea name="note" rows="3"
                          class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"></textarea>
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

        </form>
    </div>
</main>

<div class="fixed bottom-16 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t z-30">
    <button form="deliveryForm" type="submit"
            class="block w-full h-14 bg-primary font-bold rounded-xl">
        Weiter zur Zahlung
    </button>
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