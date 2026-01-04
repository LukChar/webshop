<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = $_SESSION["user_id"];

/* Adressen laden */
$stmt = $pdo->prepare("
    SELECT *
    FROM addresses
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Lieferadressen</title>

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

<!-- HEADER -->
<header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b border-border-light dark:border-border-dark">
    <a href="/webshop/public/profile.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 class="text-lg font-bold">Lieferadressen</h1>
    <div class="size-10"></div>
</header>

<main class="flex-1 px-4 pb-32 pt-4 space-y-4">

    <!-- BUTTON: NEUE ADRESSE -->
    <a href="/webshop/public/address_edit.php"
       class="flex items-center justify-between p-4 w-full
              bg-surface-light dark:bg-surface-dark
              rounded-2xl shadow-sm
              border border-border-light dark:border-border-dark
              hover:bg-gray-50 dark:hover:bg-white/5
              transition-colors">

        <div class="flex items-center gap-3">
            <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-full">
                <span class="material-symbols-outlined text-green-700 dark:text-green-400 text-[20px]">
                    add_location
                </span>
            </div>
            <span class="font-medium text-[#111813] dark:text-white">
                Neue Lieferadresse hinzufügen
            </span>
        </div>

        <span class="material-symbols-outlined text-gray-400">
            chevron_right
        </span>
    </a>

    <!-- ADRESSEN -->
    <?php if (empty($addresses)): ?>

        <p class="text-gray-500 text-sm text-center mt-6">
            Du hast noch keine Lieferadresse gespeichert.
        </p>

    <?php else: ?>

        <?php foreach ($addresses as $address): ?>

            <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
                <p class="font-semibold">
                    <?php echo htmlspecialchars($address["first_name"] . " " . $address["last_name"]); ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    <?php echo htmlspecialchars($address["street"]); ?><br>
                    <?php echo htmlspecialchars($address["postal_code"] . " " . $address["city"]); ?><br>
                    <?php echo htmlspecialchars($address["country"]); ?>
                </p>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</div>
</body>
</html>