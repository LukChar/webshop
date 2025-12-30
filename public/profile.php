<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = $_SESSION["user_id"];

/* User-Daten laden */
$stmt = $pdo->prepare("
    SELECT email, role
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Benutzer nicht gefunden.";
    exit;
}

/* Anzahl Bestellungen */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$orderCount = (int)$stmt->fetchColumn();

$email = $user["email"];
$role  = $user["role"];
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Mein Profil</title>

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

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-gray-100 font-display">

<div class="max-w-md mx-auto min-h-screen flex flex-col">

<!-- Top Bar -->
<header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b border-border-light dark:border-border-dark">
    <a href="/webshop/public/index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">Mein Profil</h1>

    <div class="size-10"></div>
</header>

<main class="flex-1 pb-24">

<!-- Avatar -->
<div class="flex flex-col items-center pt-8">
    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-28 w-28 flex items-center justify-center text-4xl font-bold">
        👤
    </div>
    <h2 class="mt-4 text-2xl font-bold">
        <?php echo $role === "admin" ? "Administrator" : "Benutzer"; ?>
    </h2>
    <p class="text-sm text-gray-500">
        <?php echo htmlspecialchars($email); ?>
    </p>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 gap-4 px-4 mt-8">
    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl text-center shadow-sm">
        <span class="material-symbols-outlined text-primary mb-1">shopping_bag</span>
        <p class="text-2xl font-bold"><?php echo $orderCount; ?></p>
        <p class="text-xs text-gray-500">Bestellungen</p>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl text-center shadow-sm">
        <span class="material-symbols-outlined text-primary mb-1">favorite</span>
        <p class="text-2xl font-bold">0</p>
        <p class="text-xs text-gray-500">Favoriten</p>
    </div>
</div>

<!-- Links -->
<div class="px-4 mt-8 space-y-3">
    <a href="/webshop/public/my_orders.php"
       class="block bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm">
        Meine Bestellungen
    </a>

    <a href="/webshop/auth/logout.php"
       class="block bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-xl text-center font-medium">
        Logout
    </a>
</div>

</main>

</div>
</body>
</html>