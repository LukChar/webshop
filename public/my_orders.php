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

/* Bestellungen laden */
$stmt = $pdo->prepare("
    SELECT id, total, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meine Bestellungen</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "background-dark": "#102216",
                "surface-light": "#ffffff",
                "surface-dark": "#1a2e22",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-background-light min-h-screen font-display">

<div class="max-w-md mx-auto p-4 pb-28">

    <h1 class="text-2xl font-bold mb-6">Meine Bestellungen</h1>

    <?php if (empty($orders)): ?>

        <p class="text-gray-500">
            Du hast noch keine Bestellungen aufgegeben.
        </p>

    <?php else: ?>

        <div class="flex flex-col gap-4">

            <?php foreach ($orders as $order): ?>

                <div class="bg-surface-light rounded-xl shadow-sm border p-4 flex flex-col gap-2">

                    <div class="flex justify-between items-center">
                        <span class="font-semibold">
                            Bestellung #<?php echo $order["id"]; ?>
                        </span>
                        <span class="text-sm text-gray-500">
                            <?php echo date("d.m.Y", strtotime($order["created_at"])); ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">
                            Gesamtbetrag
                        </span>
                        <span class="font-bold">
                            <?php echo number_format($order["total"], 2, ",", "."); ?> €
                        </span>
                    </div>

                    <a href="order_detail.php?id=<?php echo $order["id"]; ?>"
                       class="text-sm font-medium text-primary hover:underline self-end">
                        Details ansehen
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>



</body>
</html>