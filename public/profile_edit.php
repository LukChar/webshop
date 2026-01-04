<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId  = $_SESSION["user_id"];
$message = "";
$error   = "";

/* PROFIL AKTUALISIEREN */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newEmail    = trim($_POST["email"] ?? "");
    $newPassword = $_POST["password"] ?? "";

    if ($newEmail === "") {
        $error = "E-Mail darf nicht leer sein.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Ungültige E-Mail-Adresse.";
    } else {

        /* E-Mail aktualisieren */
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$newEmail, $userId]);

        /* Passwort nur ändern, wenn eingegeben */
        if ($newPassword !== "") {
            if (strlen($newPassword) < 6) {
                $error = "Passwort muss mindestens 6 Zeichen haben.";
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $userId]);
            }
        }

        if ($error === "") {
            $message = "Profil erfolgreich aktualisiert.";
        }
    }
}

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

$email = $user["email"];
$role  = $user["role"];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil bearbeiten</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

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

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813]">

<div class="max-w-md mx-auto min-h-screen flex flex-col">

<!-- HEADER -->
<header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b">
    <a href="/webshop/public/profile.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="text-lg font-bold">Profil bearbeiten</h1>

    <div class="size-10"></div>
</header>

<main class="flex-1 px-4 pt-6 pb-24">

<?php if ($message): ?>
    <p class="text-green-600 text-sm mb-4">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="text-red-600 text-sm mb-4">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<form method="post" class="space-y-5">

    <div>
        <label class="block text-sm font-medium mb-1">
            E-Mail-Adresse
        </label>
        <input
            type="email"
            name="email"
            required
            value="<?php echo htmlspecialchars($email); ?>"
            class="w-full h-12 rounded-lg border p-3"
        >
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">
            Neues Passwort (optional)
        </label>
        <input
            type="password"
            name="password"
            class="w-full h-12 rounded-lg border p-3"
            placeholder="Mindestens 6 Zeichen"
        >
    </div>

    <div class="pt-4 flex gap-3">
        <a href="/webshop/public/profile.php"
           class="flex-1 h-12 rounded-lg border flex items-center justify-center font-medium">
            Abbrechen
        </a>

        <button
            type="submit"
            class="flex-1 h-12 rounded-lg bg-primary font-bold text-[#102216]">
            Speichern
        </button>
    </div>

</form>

</main>
</div>
</body>
</html>