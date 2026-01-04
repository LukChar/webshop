<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = $_SESSION["user_id"];

/* USER DATEN LADEN */
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

/* BESTELLUNGEN ZÄHLEN */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$orderCount = (int)$stmt->fetchColumn();

$email = $user["email"];
$role  = $user["role"];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Mein Profil</title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

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

<body class="bg-background-light dark:bg-background-dark text-[#111813] dark:text-gray-100 font-display pb-24">

<?php require_once "../includes/header.php"; ?>

<main class="max-w-md mx-auto">

<!-- AVATAR -->
<div class="flex flex-col items-center pt-8">
    <div class="h-28 w-28 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-4xl">
        👤
    </div>
    <h2 class="mt-4 text-2xl font-bold">
        <?php echo $role === "admin" ? "Administrator" : "Benutzer"; ?>
    </h2>
    <p class="text-sm text-gray-500">
        <?php echo htmlspecialchars($email); ?>
    </p>
</div>

<!-- STATS -->
<div class="grid grid-cols-2 gap-4 px-4 mt-8">
    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-2xl text-center shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary mb-1">shopping_bag</span>
        <p class="text-2xl font-bold"><?php echo $orderCount; ?></p>
        <p class="text-xs text-gray-500">Bestellungen</p>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-2xl text-center shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary mb-1">favorite</span>
        <p class="text-2xl font-bold">0</p>
        <p class="text-xs text-gray-500">Favoriten</p>
    </div>
</div>

<!-- ACTIONS -->
<div class="px-4 mt-8 space-y-3">

    <a href="/webshop/public/profile_edit.php"
       class="flex items-center gap-3 bg-surface-light dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary">edit</span>
        <span class="font-medium">Profil bearbeiten</span>
    </a>

    <a href="/webshop/public/addresses.php"
       class="flex items-center gap-3 bg-surface-light dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary">location_on</span>
        <span class="font-medium">Lieferadressen</span>
    </a>

    <a href="/webshop/public/payments.php"
       class="flex items-center gap-3 bg-surface-light dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary">credit_card</span>
        <span class="font-medium">Zahlungsmethoden</span>
    </a>

    <a href="/webshop/public/my_orders.php"
       class="flex items-center gap-3 bg-surface-light dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-border-light dark:border-border-dark">
        <span class="material-symbols-outlined text-primary">receipt_long</span>
        <span class="font-medium">Meine Bestellungen</span>
    </a>

    <a href="/webshop/auth/logout.php"
       class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-2xl shadow-sm">
        <span class="material-symbols-outlined">logout</span>
        <span class="font-medium">Logout</span>
    </a>

</div>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>