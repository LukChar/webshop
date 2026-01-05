<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zahlungsmethoden</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                background: "#f6f8f6",
                surface: "#ffffff",
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

<body class="bg-background text-[#111813] font-display">

<div class="max-w-md mx-auto min-h-screen flex flex-col">

<!-- Header -->
<header class="sticky top-0 z-10 bg-surface border-b">
    <div class="flex items-center justify-between p-4">
        <a href="/webshop/public/profile.php"
           class="size-10 flex items-center justify-center rounded-full hover:bg-gray-100">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-lg font-bold">Zahlungsmethoden</h1>
        <div class="size-10"></div>
    </div>
</header>

<main class="flex-1 px-4 pt-6 pb-24 space-y-6">

<!-- Info -->
<p class="text-sm text-gray-600">
    Hinterlege deine bevorzugte Zahlungsmethode für einen schnelleren Checkout.
</p>

<!-- Kreditkartenformular -->
<div class="bg-surface rounded-2xl p-5 border shadow-sm space-y-4">

    <h2 class="font-bold text-base">Kreditkarte</h2>

    <form method="post" action="#" class="space-y-4">

        <div>
            <label class="block text-sm font-medium mb-1">Karteninhaber</label>
            <input
                type="text"
                placeholder="Max Mustermann"
                class="w-full rounded-lg border-gray-300 h-11 px-3"
                required
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kartennummer</label>
            <input
                type="text"
                placeholder="1234 5678 9012 3456"
                inputmode="numeric"
                class="w-full rounded-lg border-gray-300 h-11 px-3"
                required
            >
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ablaufdatum</label>
                <input
                    type="text"
                    placeholder="MM / JJ"
                    class="w-full rounded-lg border-gray-300 h-11 px-3"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">CVC</label>
                <input
                    type="password"
                    placeholder="123"
                    inputmode="numeric"
                    class="w-full rounded-lg border-gray-300 h-11 px-3"
                    required
                >
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" class="rounded border-gray-300">
            Als Standard-Zahlungsmethode speichern
        </label>

        <button
            type="submit"
            class="w-full h-12 rounded-xl bg-primary font-bold text-[#102216]">
            Zahlungsmethode speichern
        </button>

    </form>

</div>

<!-- Hinweis -->
<p class="text-xs text-gray-500 text-center">
    Deine Zahlungsdaten werden sicher gespeichert.
</p>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</div>
</body>
</html>