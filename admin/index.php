<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../public/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>

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

<body class="bg-gray-100 font-display text-gray-800">

<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-3xl bg-white rounded-xl shadow-sm p-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">
                Verwaltungsbereich für den Webshop
            </p>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <a href="orders.php"
               class="block border rounded-lg p-6 hover:border-primary transition-colors">
                <h2 class="text-lg font-semibold mb-2">
                    Bestellungen verwalten
                </h2>
                <p class="text-sm text-gray-500">
                    Alle Bestellungen aller Benutzer anzeigen
                </p>
            </a>

            <div class="block border rounded-lg p-6 opacity-50 cursor-not-allowed">
                <h2 class="text-lg font-semibold mb-2">
                    Produkte
                </h2>
                <p class="text-sm text-gray-400">
                    Noch nicht implementiert
                </p>
            </div>

        </div>

        <!-- Footer -->
        <div class="mt-10 text-sm">
            <a href="../public/index.php"
               class="text-gray-500 hover:underline">
                ← Zurück zum Shop
            </a>
        </div>

    </div>
</div>

</body>
</html>