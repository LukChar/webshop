<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

$error = "";

/* Registrierung verarbeiten */
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
        $error = "Passwort muss mindestens 6 Zeichen haben.";
    } else {

        /* Prüfen ob E-Mail existiert */
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
<title>Registrieren</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

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
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4">

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
<h1 class="text-2xl font-bold text-center mt-2">Konto erstellen</h1>
<p class="text-sm text-gray-500 text-center mb-6">
    Erstelle dein CampusShop-Konto.
</p>

<!-- Segmented Tabs -->
<div class="flex px-6 pb-6">
    <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-[#f0f4f2] dark:bg-white/10 p-1">

        <!-- Anmelden (Link) -->
        <a href="login.php"
           class="flex h-full grow items-center justify-center overflow-hidden rounded-md px-2
                  text-gray-500 dark:text-gray-400
                  hover:text-[#111813] dark:hover:text-white
                  text-sm font-medium transition-colors">
            Anmelden
        </a>

        <!-- Registrieren (aktiv) -->
        <div
            class="flex h-full grow items-center justify-center overflow-hidden rounded-md px-2
                   bg-white dark:bg-surface-dark shadow
                   text-[#111813] dark:text-white text-sm font-semibold">
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

<label>
    <span class="text-sm font-medium">E-Mail Adresse</span>
    <input
        type="email"
        name="email"
        required
        class="w-full h-12 rounded-lg border p-3"
        placeholder="name@email.at"
        value="<?php echo htmlspecialchars($email ?? ""); ?>"
    >
</label>

<label>
    <span class="text-sm font-medium">Passwort</span>
    <input
        type="password"
        name="password"
        required
        class="w-full h-12 rounded-lg border p-3"
        placeholder="Mindestens 6 Zeichen"
    >
</label>

<label>
    <span class="text-sm font-medium">Passwort wiederholen</span>
    <input
        type="password"
        name="password_repeat"
        required
        class="w-full h-12 rounded-lg border p-3"
    >
</label>

<button
    type="submit"
    class="w-full h-12 rounded-lg bg-primary font-bold text-[#102216]">
    Registrieren
</button>

</form>

</div>
</body>
</html>