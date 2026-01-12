<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = (int)$_SESSION["user_id"];
$isNew = isset($_GET["new"]) && $_GET["new"] === "1";
$paymentId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if (!$isNew && $paymentId <= 0) {
    header("Location: /payments.php");
    exit;
}

$payment = null;
if (!$isNew) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
    $stmt->execute([$paymentId, $userId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$payment) {
        header("Location: /payments.php");
        exit;
    }
}

$error = "";
$form = [
    "holder" => $payment["card_holder"] ?? "",
    "brand" => $payment["card_brand"] ?? "",
    "month" => $payment["expiry_month"] ?? "",
    "year" => $payment["expiry_year"] ?? "",
    "default" => !$isNew && (int)($payment["is_default"] ?? 0) === 1,
    "number" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$isNew && isset($_POST["delete"])) {
        $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?")->execute([$paymentId, $userId]);
        header("Location: /payments.php");
        exit;
    }

    $form = [
        "holder" => trim($_POST["card_holder"] ?? ""),
        "brand" => trim($_POST["card_brand"] ?? ""),
        "month" => trim($_POST["expiry_month"] ?? ""),
        "year" => trim($_POST["expiry_year"] ?? ""),
        "default" => isset($_POST["is_default"]) && $_POST["is_default"] === "1",
        "number" => trim($_POST["card_number"] ?? ""),
    ];

    $currentYear = (int)date("Y");
    $sanitizedNumber = preg_replace('/\D/', '', $form["number"]);
    $cardLast4 = $sanitizedNumber !== "" ? substr($sanitizedNumber, -4) : ($payment["card_last4"] ?? "");

    if ($form["holder"] === "" || $form["month"] === "" || $form["year"] === "") {
        $error = "Bitte fülle die Pflichtfelder aus.";
    } elseif (!ctype_digit($form["month"]) || (int)$form["month"] < 1 || (int)$form["month"] > 12) {
        $error = "Bitte gib einen gültigen Monat ein.";
    } elseif (!ctype_digit($form["year"]) || (int)$form["year"] < $currentYear) {
        $error = "Bitte gib ein gültiges Ablaufjahr ein.";
    } elseif ($isNew && $sanitizedNumber === "") {
        $error = "Bitte gib eine Kartennummer ein.";
    } elseif ($cardLast4 === "") {
        $error = "Die Kartennummer muss mindestens vier Ziffern enthalten.";
    } else {
        if ($form["default"]) {
            $pdo->prepare("UPDATE payments SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $brand = $form["brand"] ?: "Kreditkarte";
        $isDefaultFlag = $form["default"] ? 1 : 0;

        if ($isNew) {
            $stmt = $pdo->prepare("
                INSERT INTO payments (user_id, card_holder, card_last4, card_brand, expiry_month, expiry_year, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $form["holder"],
                $cardLast4,
                $brand,
                (int)$form["month"],
                (int)$form["year"],
                $isDefaultFlag,
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE payments
                SET card_holder = ?, card_last4 = ?, card_brand = ?, expiry_month = ?, expiry_year = ?, is_default = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $form["holder"],
                $cardLast4,
                $brand,
                (int)$form["month"],
                (int)$form["year"],
                $isDefaultFlag,
                $paymentId,
                $userId,
            ]);
        }

        header("Location: /payments.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $isNew ? "Neue Zahlungsmethode" : "Zahlungsmethode bearbeiten" ?></title>

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
                        "surface-dark": "#1a2c20",
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100">

<div class="max-w-md mx-auto min-h-screen flex flex-col">

    <header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b border-border-light dark:border-border-dark">
        <a href="/payments.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-lg font-bold"><?= $isNew ? "Neue Zahlungsmethode" : "Zahlungsmethode bearbeiten" ?></h1>
        <div class="size-10"></div>
    </header>

    <main class="flex-1 px-4 pt-6 pb-28 space-y-4">

        <?php if ($error): ?>
            <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-2xl px-3 py-2">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <?php if (!$isNew): ?>
            <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4 space-y-1">
                <p class="text-xs text-gray-500 uppercase tracking-[0.3em]">Gespeicherte Karte</p>
                <h2 class="text-xl font-semibold">
                    <?= htmlspecialchars($payment["card_brand"] ?? "Kreditkarte") ?> •••• <?= htmlspecialchars($payment["card_last4"] ?? "0000") ?>
                </h2>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($payment["created_at"] ?? "") ?></p>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-[0.3em]">Karteninhaber</label>
                <input type="text"
                       name="card_holder"
                       value="<?= htmlspecialchars($form["holder"]) ?>"
                       class="mt-2 w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                       placeholder="Max Mustermann"
                       required>
            </div>

            <div>
                <label class="text-xs text-gray-500 uppercase tracking-[0.3em]">Kartentyp (optional)</label>
                <input type="text"
                       name="card_brand"
                       value="<?= htmlspecialchars($form["brand"]) ?>"
                       class="mt-2 w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                       placeholder="z. B. Visa">
            </div>

            <div>
                <label class="text-xs text-gray-500 uppercase tracking-[0.3em]">Kartennummer <?= $isNew ? "" : "(nur letzte 4 Ziffern nötig)" ?></label>
                <input type="text"
                       name="card_number"
                       value="<?= htmlspecialchars($form["number"]) ?>"
                       inputmode="numeric"
                       autocomplete="off"
                       class="mt-2 w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                       placeholder="•••• •••• •••• 1234"
                       <?= $isNew ? "required" : "" ?>>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-[0.3em]">Ablaufmonat</label>
                    <input type="text"
                           name="expiry_month"
                           value="<?= htmlspecialchars($form["month"]) ?>"
                           class="mt-2 w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="MM"
                           required>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-[0.3em]">Ablaufjahr</label>
                    <input type="text"
                           name="expiry_year"
                           value="<?= htmlspecialchars($form["year"]) ?>"
                           class="mt-2 w-full rounded-2xl border border-border-light bg-background-light px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="JJJJ"
                           required>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox"
                       name="is_default"
                       value="1"
                       class="h-4 w-4 rounded border-gray-300"
                       <?= $form["default"] ? "checked" : "" ?>>
                Als Standard festlegen
            </label>

            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="w-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-[#102216] shadow-lg shadow-primary/40">
                    <?= $isNew ? "Speichern" : "Änderungen übernehmen" ?>
                </button>
                <?php if (!$isNew): ?>
                    <button type="submit"
                            name="delete"
                            value="1"
                            class="w-full rounded-2xl border border-red-200 bg-transparent px-4 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-red-600 hover:bg-red-50">
                        Zahlungsmethode entfernen
                    </button>
                <?php endif; ?>
            </div>
        </form>

    </main>

    <?php require_once "../includes/bottom_nav.php"; ?>

</div>
</body>
</html>
