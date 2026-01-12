<?php
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
    <title>Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

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

<body class="bg-gray-100 font-display text-[#111813]">

    <div class="max-w-5xl mx-auto p-6">

        <!-- TOP BAR -->
        <div class="flex items-center gap-3 mb-6">
            <a href="../public/profile.php"
                class="flex size-10 items-center justify-center rounded-full hover:bg-gray-200 transition">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>

            <div>
                <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                <p class="text-gray-500 text-sm">
                    Verwaltung von Shop, Bestellungen und Benutzern
                </p>
            </div>
        </div>

        <!-- SECTION: Shop & Inhalte -->
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">
            Shop & Inhalte
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

            <a href="products.php" class="group bg-white rounded-xl p-5 border shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl">inventory_2</span>
                    <div>
                        <h3 class="font-bold text-lg">Produkte</h3>
                        <p class="text-gray-500 text-sm">
                            Produkte anlegen, bearbeiten und löschen
                        </p>
                    </div>
                </div>
            </a>

            <a href="categories.php" class="group bg-white rounded-xl p-5 border shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl">category</span>
                    <div>
                        <h3 class="font-bold text-lg">Kategorien</h3>
                        <p class="text-gray-500 text-sm">
                            Kategorien verwalten und zuweisen
                        </p>
                    </div>
                </div>
            </a>

        </div>

        <!-- SECTION: Bestellungen -->
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">
            Bestellungen
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

            <a href="orders.php" class="group bg-white rounded-xl p-5 border shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl">receipt_long</span>
                    <div>
                        <h3 class="font-bold text-lg">Bestellungen</h3>
                        <p class="text-gray-500 text-sm">
                            Alle Bestellungen und Lieferstatus
                        </p>
                    </div>
                </div>
            </a>

        </div>

        <!-- SECTION: Benutzer -->
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">
            Benutzer
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">

            <a href="users.php" class="group bg-white rounded-xl p-5 border shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl">group</span>
                    <div>
                        <h3 class="font-bold text-lg">Benutzer</h3>
                        <p class="text-gray-500 text-sm">
                            Rollen, Sperren und Verwaltung
                        </p>
                    </div>
                </div>
            </a>

        </div>

        <!-- Back to Shop -->
        <a href="../index.php" class="text-sm text-gray-500 hover:underline">
            ← Zurück zum Shop
        </a>

    </div>

</body>

</html>