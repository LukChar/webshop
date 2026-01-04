<?php
session_start();
require_once "../includes/db.php";

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Warenkorb darf nicht leer sein */
$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$error = null;

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
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$p) continue;
            $total += ((float)$p["price"]) * (int)$qty;
        }

        /* Bestellung anlegen (pending) */
        $stmt = $pdo->prepare(
            "INSERT INTO orders (user_id, total, status)
             VALUES (?, ?, 'pending')"
        );
        $stmt->execute([$userId, $total]);
        $orderId = $pdo->lastInsertId();

        if (!$orderId) {
            $error = "Fehler: Bestellung konnte nicht gespeichert werden.";
        } else {
            /* Bestellpositionen speichern */
            foreach ($cart as $productId => $qty) {
                $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([(int)$productId]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$p) continue;

                $stmt = $pdo->prepare(
                    "INSERT INTO order_items (order_id, product_id, quantity, price)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([
                    (int)$orderId,
                    (int)$productId,
                    (int)$qty,
                    (float)$p["price"]
                ]);
            }

            /* Lieferadresse (Demo) in Session speichern */
            $_SESSION["checkout_address"] = [
                "order_id" => (int)$orderId,
                "name"     => $name,
                "street"   => $street,
                "zip"      => $zip,
                "city"     => $city,
                "note"     => $note,
            ];

            /* Weiter zur Zahlung */
            header("Location: payment/paypal_dummy.php?order_id=" . urlencode($orderId));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lieferadresse</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Tailwind -->
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100 flex flex-col h-[100dvh] overflow-x-hidden antialiased">

<!-- Top Bar -->
<header class="shrink-0 px-4 py-4 flex items-center justify-between">
    <a href="cart.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">Lieferadresse</h1>

    <div class="size-10"></div>
</header>

<!-- Content -->
<main class="flex-1 overflow-y-auto px-4 pb-44">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-5 shadow-sm">

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form id="deliveryForm" method="post" class="space-y-4">

            <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input name="name" required
                       placeholder="Vorname & Nachname"
                       class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Straße & Hausnr. *</label>
                <input name="street" required
                       placeholder="Straße & Hausnummer"
                       class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-1">
                    <label class="block text-sm font-medium mb-1">PLZ *</label>
                    <input name="zip" required
                           placeholder="z.B. 1010"
                           class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Ort *</label>
                    <input name="city" required
                           placeholder="z.B. Wien"
                           class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"/>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Anmerkung (optional)</label>
                <textarea name="note" rows="3"
                          placeholder="z.B. bitte bei Nachbar abgeben"
                          class="w-full rounded-lg border p-2 bg-white/70 dark:bg-black/10"></textarea>
            </div>

        </form>
    </div>
</main>

<!-- Bottom Button -->
<div class="fixed bottom-16 left-0 right-0 p-4 bg-surface-light dark:bg-surface-dark border-t z-30">
    <button
        form="deliveryForm"
        type="submit"
        class="block text-center w-full h-14 bg-primary font-bold rounded-xl leading-[3.5rem]">
        Weiter zur Zahlung
    </button>
</div>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>
