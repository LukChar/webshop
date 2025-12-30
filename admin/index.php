<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Bereich</title>

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

<div class="max-w-4xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Bestellungen -->
        <a href="orders.php"
           class="bg-white rounded-xl p-6 shadow-sm border hover:border-primary transition-colors">
            <h2 class="text-lg font-bold mb-2">Bestellungen verwalten</h2>
            <p class="text-gray-500 text-sm">
                Alle Bestellungen aller Benutzer einsehen
            </p>
        </a>

        <!-- Produkte -->
        <a href="products.php"
           class="bg-white rounded-xl p-6 shadow-sm border hover:border-primary transition-colors">
            <h2 class="text-lg font-bold mb-2">Produkte verwalten</h2>
            <p class="text-gray-500 text-sm">
                Produkte anlegen, bearbeiten und löschen
            </p>
        </a>

    </div>

    <div class="mt-8">
        <a href="../public/index.php"
           class="text-sm text-gray-500 hover:underline">
            ← Zurück zum Shop
        </a>
    </div>

</div>

</body>
</html>