<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["set_default"])) {
    $addressId = (int)($_POST["address_id"] ?? 0);
    if ($addressId > 0) {
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$addressId, $userId]);
    }
    header("Location: addresses.php");
    exit;
}

/* Adressen laden */
$stmt = $pdo->prepare("
    SELECT *
    FROM addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, created_at DESC
");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <a href="/profile.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-lg font-bold">Lieferadressen</h1>
        <div class="size-10"></div>
    </header>

    <main class="flex-1 px-4 pt-6 pb-28 space-y-4">

        <div class="space-y-2">
            <p class="text-sm text-gray-600">
                Hier kannst du deine Lieferadressen verwalten, bearbeiten oder eine neue Adresse speichern.
            </p>
            <a href="/address_edit.php"
               class="flex items-center justify-center gap-2 rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark px-4 py-3 font-semibold text-sm shadow-sm hover:border-primary hover:text-primary transition-all">
                <span class="material-symbols-outlined text-primary">add_location</span>
                Neue Adresse hinzufügen
            </a>
        </div>

        <?php if (empty($addresses)): ?>

            <p class="text-gray-500 text-sm text-center mt-6">
                Du hast noch keine Lieferadresse gespeichert.
            </p>

        <?php else: ?>

            <?php foreach ($addresses as $address): ?>
                <?php $isDefault = (int)($address["is_default"] ?? 0) === 1; ?>
                <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-border-light dark:border-border-dark space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 space-y-1">
                            <p class="font-semibold text-[#111813] dark:text-white">
                                <?= htmlspecialchars(trim(($address["first_name"] ?? "") . " " . ($address["last_name"] ?? ""))) ?>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                <?= htmlspecialchars($address["street"]) ?><br>
                                <?= htmlspecialchars($address["postal_code"] . " " . $address["city"]) ?><br>
                                <?= htmlspecialchars($address["country"]) ?>
                            </p>
                        </div>
                        <?php if ($isDefault): ?>
                            <span class="px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-primary bg-primary/10 rounded-full">Standard</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span><?= htmlspecialchars($address["created_at"] ?? "") ?></span>
                        <div class="flex items-center gap-2">
                            <a href="/address_edit.php?id=<?= htmlspecialchars($address["id"]) ?>"
                               class="inline-flex items-center gap-1 rounded-full border border-border-light dark:border-border-dark px-3 py-1 text-xs font-semibold text-[#111813] dark:text-white hover:border-primary hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                Bearbeiten
                            </a>
                            <?php if (!$isDefault): ?>
                                <form method="post" class="inline">
                                    <input type="hidden" name="address_id" value="<?= htmlspecialchars($address["id"]) ?>">
                                    <button type="submit" name="set_default"
                                            class="inline-flex items-center gap-1 rounded-full border border-border-light dark:border-border-dark px-3 py-1 text-xs font-semibold text-[#111813] dark:text-white hover:border-primary hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">star</span>
                                        Als Standard
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <?php require_once "../includes/bottom_nav.php"; ?>

</div>
</body>
</html>
