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

$userId  = $_SESSION["user_id"];
$message = "";
$error   = "";

/* ===== PROFIL AKTUALISIEREN ===== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newEmail    = trim($_POST["email"] ?? "");
    $newPassword = $_POST["password"] ?? "";

    if ($newEmail === "") {
        $error = "E-Mail darf nicht leer sein.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Ungültige E-Mail-Adresse.";
    } else {

        // E-Mail aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$newEmail, $userId]);

        // Passwort nur ändern, wenn gesetzt
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

/* ===== USER DATEN LADEN ===== */
$stmt = $pdo->prepare("
    SELECT email
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
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Profil bearbeiten</title>

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

<main class="max-w-md mx-auto px-4 pt-6 space-y-8">

    <!-- ===== PROFIL BEARBEITEN ===== -->
    <section>
        <h2 class="text-lg font-bold mb-4 px-1">Profil bearbeiten</h2>

        <?php if ($message): ?>
            <p class="text-green-600 text-sm mb-2"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm mb-2"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post"
              class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl shadow-sm border border-border-light dark:border-border-dark space-y-4">

            <label class="flex flex-col gap-1.5">
                <span class="text-sm font-medium">E-Mail Adresse</span>
                <input
                    type="email"
                    name="email"
                    required
                    value="<?php echo htmlspecialchars($email); ?>"
                    class="h-12 rounded-xl px-4 border border-border-light dark:border-border-dark bg-background-light dark:bg-black/20"
                >
            </label>

            <label class="flex flex-col gap-1.5">
                <span class="text-sm font-medium">Neues Passwort (optional)</span>
                <input
                    type="password"
                    name="password"
                    placeholder="Mindestens 6 Zeichen"
                    class="h-12 rounded-xl px-4 border border-border-light dark:border-border-dark bg-background-light dark:bg-black/20"
                >
            </label>

            <button
                type="submit"
                class="w-full h-12 rounded-xl bg-primary font-bold text-[#102216]">
                Änderungen speichern
            </button>
        </form>
    </section>

    <!-- ===== VERWALTUNG ===== -->
    <section>
        <h2 class="text-lg font-bold mb-4 px-1">Verwaltung</h2>

        <div class="bg-surface-light dark:bg-surface-dark rounded-2xl shadow-sm border border-border-light dark:border-border-dark divide-y">

            <a href="/my_orders.php"
               class="block p-4 hover:bg-gray-50 dark:hover:bg-white/5">
                Meine Bestellungen
            </a>

            <a href="#"
               class="block p-4 hover:bg-gray-50 dark:hover:bg-white/5">
                Favoriten
            </a>

            <a href="/auth/logout.php"
               class="block p-4 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10">
                Abmelden
            </a>

        </div>
    </section>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</body>
</html>