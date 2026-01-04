<?php
session_start();
require_once __DIR__ . "/../../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

$orderId = $_POST["order_id"] ?? null;
$userId  = (int)$_SESSION["user_id"];

if (!$orderId) {
    header("Location: ../cart.php");
    exit;
}

/* Order prüfen */
$stmt = $pdo->prepare("SELECT id, user_id, status FROM orders WHERE id = ?");
$stmt->execute([(int)$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || (int)$order["user_id"] !== $userId) {
    http_response_code(403);
    echo "Zugriff verweigert.";
    exit;
}

/* Nur pending darf bezahlt werden */
if ($order["status"] !== "pending") {
    header("Location: ../my_orders.php");
    exit;
}

/* Als bezahlt markieren (nimm paid_dummy, wenn du es lieber trennen willst) */
$pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([(int)$orderId]);

/* Warenkorb leeren */
unset($_SESSION["cart"]);
unset($_SESSION["checkout_address"]);
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Bestellung erfolgreich</title>

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
    <a href="../index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">Bestellung</h1>

    <div class="size-10"></div>
</header>

<!-- Content -->
<main class="flex-1 overflow-y-auto px-4 pb-44">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-6 shadow-sm text-center">
        <h2 class="text-2xl font-bold mb-2">Bestellung erfolgreich</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Vielen Dank für Ihre Bestellung.<br>
            Bestellnummer: <span class="font-semibold">#<?= htmlspecialchars($orderId) ?></span>
        </p>

        <a href="../index.php"
           class="inline-block bg-primary font-bold rounded-xl px-6 py-3">
            Zurück zur Startseite
        </a>
    </div>
</main>

<?php require_once "../../includes/bottom_nav.php"; ?>

</body>
</html>
