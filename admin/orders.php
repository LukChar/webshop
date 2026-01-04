<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* Bestellungen inkl. Lieferstatus laden */
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.total,
        o.created_at,
        u.email,

        SUM(oi.delivery_status = 'neu') AS cnt_neu,
        SUM(oi.delivery_status = 'in_bearbeitung') AS cnt_bearbeitung,
        SUM(oi.delivery_status = 'versendet') AS cnt_versendet,
        SUM(oi.delivery_status = 'zugestellt') AS cnt_zugestellt,
        COUNT(*) AS cnt_items

    FROM orders o
    JOIN users u ON u.id = o.user_id
    JOIN order_items oi ON oi.order_id = o.id

    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Bestellungen</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b"
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-gray-100 font-display">

<div class="max-w-6xl mx-auto p-6">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="index.php"
           class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow hover:bg-gray-50">
            ←
        </a>
        <h1 class="text-2xl font-bold">Bestellungen verwalten</h1>
    </div>

    <?php if (empty($orders)): ?>

        <p class="text-gray-500">Keine Bestellungen vorhanden.</p>

    <?php else: ?>

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border">

            <table class="w-full border-collapse text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Bestell-ID</th>
                        <th class="p-3">Benutzer</th>
                        <th class="p-3">Datum</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Gesamt</th>
                        <th class="p-3">Aktion</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($orders as $order): ?>

                    <?php
                    /* Bestellstatus berechnen */
                    if ($order["cnt_zugestellt"] == $order["cnt_items"]) {
                        $statusText = "Abgeschlossen";
                        $statusClass = "bg-green-100 text-green-700";
                    } elseif ($order["cnt_versendet"] > 0) {
                        $statusText = "Teilweise versendet";
                        $statusClass = "bg-blue-100 text-blue-700";
                    } elseif ($order["cnt_bearbeitung"] > 0) {
                        $statusText = "In Bearbeitung";
                        $statusClass = "bg-yellow-100 text-yellow-700";
                    } else {
                        $statusText = "Neu";
                        $statusClass = "bg-red-100 text-red-700";
                    }
                    ?>

                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-medium">
                            #<?php echo $order["id"]; ?>
                        </td>

                        <td class="p-3">
                            <?php echo htmlspecialchars($order["email"]); ?>
                        </td>

                        <td class="p-3 text-gray-600">
                            <?php echo date("d.m.Y H:i", strtotime($order["created_at"])); ?>
                        </td>

                        <td class="p-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>

                        <td class="p-3 text-right font-semibold">
                            <?php echo number_format($order["total"], 2, ",", "."); ?> €
                        </td>

                        <td class="p-3">
                            <a
                                href="../public/order_detail.php?id=<?php echo $order["id"]; ?>"
                                class="text-primary font-medium hover:underline"
                            >
                                Details
                            </a>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    <?php endif; ?>

</div>

</body>
</html>