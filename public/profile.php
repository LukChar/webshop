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

/* Benutzerdaten laden */
$stmt = $pdo->prepare("SELECT email, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Fehler: Benutzer nicht gefunden.";
    exit;
}

/* Anzahl Bestellungen */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$orderCount = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Benutzerprofil – CampusShop</title>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<!-- Material Symbols -->
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
body {
    min-height: max(884px, 100dvh);
}
</style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-gray-100 font-display transition-colors duration-200">

<div class="relative flex min-h-screen max-w-md mx-auto flex-col overflow-x-hidden">

<!-- Top App Bar -->
<header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b border-border-light dark:border-border-dark">
    <a href="index.php" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold flex-1 text-center">Mein Profil</h1>

    <span class="material-symbols-outlined text-gray-400">settings</span>
</header>

<!-- Content -->
<main class="flex-1 flex flex-col gap-6 pb-24">

<!-- Profile Header -->
<div class="flex flex-col items-center pt-6 px-4">
    <div class="h-28 w-28 rounded-full bg-gray-300"></div>

    <div class="mt-4 text-center">
        <h2 class="text-2xl font-bold">
            <?php echo htmlspecialchars($user["email"]); ?>
        </h2>
        <p class="text-[#61896f] text-sm">
            Rolle: <?php echo htmlspecialchars($user["role"]); ?>
        </p>
    </div>
</div>

<!-- Profile Stats -->
<div class="grid grid-cols-2 gap-3 px-4">
    <div class="flex flex-col gap-1 rounded-2xl bg-surface-light dark:bg-surface-dark p-4 items-center text-center shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary mb-1">shopping_bag</span>
        <p class="text-2xl font-bold"><?php echo $orderCount; ?></p>
        <p class="text-xs uppercase tracking-wide text-gray-500">Bestellungen</p>
    </div>

    <div class="flex flex-col gap-1 rounded-2xl bg-surface-light dark:bg-surface-dark p-4 items-center text-center shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary mb-1">favorite</span>
        <p class="text-2xl font-bold">0</p>
        <p class="text-xs uppercase tracking-wide text-gray-500">Favoriten</p>
    </div>
</div>

<!-- Verwaltung -->
<div class="px-4">
    <h3 class="text-lg font-bold mb-4">Verwaltung</h3>

    <div class="flex flex-col bg-surface-light dark:bg-surface-dark rounded-2xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden divide-y">
        <a href="my_orders.php"
           class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">shopping_bag</span>
                <span class="font-medium">Meine Bestellungen</span>
            </div>
            <span class="material-symbols-outlined text-gray-400">chevron_right</span>
        </a>
    </div>
</div>

<!-- Aktionen -->
<div class="px-4 flex flex-col gap-3 mt-2">
    <a href="../auth/logout.php"
       class="w-full border border-red-300 text-red-600 h-12 rounded-xl font-medium flex items-center justify-center">
        Abmelden
    </a>
</div>

</main>
</div>
</body>
</html>