<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $passwordRepeat = $_POST["password_repeat"] ?? "";

    if ($email === "" || $password === "" || $passwordRepeat === "") {
        $error = "Bitte alle Felder ausfüllen.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ungültige E-Mail-Adresse.";
    } elseif ($password !== $passwordRepeat) {
        $error = "Passwörter stimmen nicht überein.";
    } elseif (strlen($password) < 6) {
        $error = "Passwort muss mindestens 6 Zeichen lang sein.";
    } else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Diese E-Mail ist bereits registriert.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, role, active)
                VALUES (?, ?, 'user', 1)
            ");
            $stmt->execute([$email, $hash]);

            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["role"] = "user";

            header("Location: ../public/index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Registrieren – CampusShop</title>

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
                "surface-dark": "#1a2e22",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<style>
body { min-height: max(884px, 100dvh); }
</style>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4 font-display">

<div class="relative flex w-full max-w-[480px] flex-col bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">

    <!-- Top Bar -->
    <div class="flex items-center p-4 justify-between">
        <a href="login.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-lg font-bold flex-1 text-center pr-10">Mein Konto</h2>
    </div>

    <!-- Headline -->
    <div class="px-6 pt-4 pb-2 text-center">
        <h1 class="text-[28px] font-bold">Konto erstellen</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Registriere dich und starte sofort.
        </p>
    </div>

    <!-- Tabs -->
    <div class="flex px-6 pb-6">
        <div class="flex h-12 flex-1 rounded-lg bg-[#f0f4f2] dark:bg-white/10 p-1">
            <a href="login.php"
               class="flex-1 flex items-center justify-center rounded-md text-sm font-medium text-gray-500 hover:text-black dark:hover:text-white transition">
                Anmelden
            </a>
            <div class="flex-1 flex items-center justify-center rounded-md bg-white dark:bg-surface-dark shadow text-sm font-semibold">
                Registrieren
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <p class="text-red-600 text-sm text-center mb-4 px-6">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <!-- Register Form -->
    <form method="post" class="flex flex-col gap-4 px-6 pb-6">

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">E-Mail Adresse</span>
            <input
                type="email"
                name="email"
                required
                value="<?php echo htmlspecialchars($email ?? ""); ?>"
                placeholder="name@mail.at"
                class="w-full h-12 rounded-lg border px-4"
            >
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">Passwort</span>
            <input
                type="password"
                name="password"
                required
                placeholder="Mindestens 6 Zeichen"
                class="w-full h-12 rounded-lg border px-4"
            >
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">Passwort wiederholen</span>
            <input
                type="password"
                name="password_repeat"
                required
                placeholder="Passwort bestätigen"
                class="w-full h-12 rounded-lg border px-4"
            >
        </label>

        <button
            type="submit"
            class="w-full h-12 rounded-lg bg-primary font-bold text-[#102216] hover:brightness-105 active:scale-[0.98] transition">
            Registrieren
        </button>
    </form>

    <!-- Divider -->
    <div class="flex items-center px-6 py-4">
        <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
        <span class="px-3 text-xs font-medium text-gray-400 uppercase tracking-wider">
            Oder weiter mit
        </span>
        <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
    </div>

    <!-- Google Register -->
    <div class="px-6 pb-6">
        <a href="/webshop/auth/google_login.php"
           class="flex w-full items-center justify-center gap-3 h-12 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <!-- Google SVG -->
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span class="text-sm font-semibold">Mit Google registrieren</span>
        </a>
    </div>

    <!-- AGB Hinweis -->
    <div class="px-6 pb-6 text-center">
        <p class="text-xs text-gray-400">
            Durch die Registrierung stimmst du unseren
            <a href="#" class="underline hover:text-primary">AGB</a> und
            <a href="#" class="underline hover:text-primary">Datenschutzbestimmungen</a> zu.
        </p>
    </div>

</div>
</body>
</html>